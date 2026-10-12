<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\WaroengkuyService;
use App\Models\Setting;
use App\Models\CustomProduct;
use App\Models\Order;

// Helper kalkulasi harga jual dan filter produk
function getProcessedProducts(array $items, bool $filterForBuyer = false): array
{
    $mode = Setting::get('margin_mode', 'fixed');
    $flat = (int) Setting::get('margin_flat', 5000);
    $percent = (int) Setting::get('margin_percent', 15);
    $hideOutOfStock = (bool) Setting::get('hide_out_of_stock', false);
    
    // Ambil daftar variant_id yang dinonaktifkan oleh admin
    $disabledIdsRaw = Setting::get('disabled_products', '[]');
    $disabledIds = json_decode($disabledIdsRaw, true) ?: [];

    $processed = [];

    // 1. Proses produk live dari Waroengkuy
    foreach ($items as $item) {
        $variantId = (string) ($item['variant_id'] ?? '');
        $isOutOfStock = $item['stock'] !== null && (int) $item['stock'] <= 0;
        $isDisabled = in_array($variantId, $disabledIds);

        if ($filterForBuyer) {
            if ($isDisabled) continue;
            if ($hideOutOfStock && $isOutOfStock) continue;
        }

        $cost = (int) ($item['price'] ?? 0);
        $normal = (int) ($item['normal_price'] ?? $cost);

        if ($mode === 'normal_price') {
            $sellPrice = max($cost, $normal);
        } elseif ($mode === 'percent') {
            $sellPrice = (int) round($cost * (1 + ($percent / 100)));
        } else {
            $sellPrice = $cost + $flat;
        }

        $profit = max(0, $sellPrice - $cost);

        $item['cost_price'] = $cost;
        $item['sell_price'] = $sellPrice;
        $item['profit'] = $profit;
        $item['price'] = $sellPrice;
        $item['is_disabled'] = $isDisabled;
        $item['status'] = $isDisabled ? 'inactive' : 'active';
        $item['is_custom'] = false;

        $processed[] = $item;
    }

    // 2. Sertakan Custom Product yang dibuat admin toko sendiri
    $customProducts = CustomProduct::all();
    foreach ($customProducts as $cp) {
        $item = $cp->toCatalogItem();
        if ($filterForBuyer) {
            if ($item['status'] !== 'active') continue;
            if ($hideOutOfStock && $item['stock'] !== null && (int)$item['stock'] <= 0) continue;
        }
        $processed[] = $item;
    }

    return $processed;
}

Route::get('/', function (WaroengkuyService $service) {
    $productsData = $service->getProducts();
    $items = getProcessedProducts($productsData['items'] ?? [], true);

    return Inertia::render('Welcome', [
        'version' => app()->version(),
        'liveProducts' => $items,
    ]);
});

Route::get('/lacak-pesanan', function (\Illuminate\Http\Request $request) {
    return Inertia::render('TrackOrder', [
        'initialQuery' => (string) $request->query('q', ''),
    ]);
});


Route::get('/adminpanel', function (WaroengkuyService $service) {
    $balanceData = $service->getBalance();
    $productsData = $service->getProducts();
    $items = getProcessedProducts($productsData['items'] ?? [], false);

    $settings = [
        'margin_mode' => Setting::get('margin_mode', 'fixed'),
        'margin_flat' => (int) Setting::get('margin_flat', 5000),
        'margin_percent' => (int) Setting::get('margin_percent', 15),
        'hide_out_of_stock' => (bool) Setting::get('hide_out_of_stock', false),
    ];

    // Ambil semua order toko dari database lokal
    $localOrders = Order::orderBy('id', 'desc')->get()->map(function ($o) {
        return [
            'id' => $o->order_id,
            'ref' => $o->ref,
            'customer' => $o->customer_name ?: 'Pembeli',
            'phone' => $o->customer_phone ?: '-',
            'email' => $o->customer_email ?: '-',
            'product' => $o->product_name . ($o->variant ? ' (' . $o->variant . ')' : ''),
            'price' => 'Rp ' . number_format($o->amount, 0, ',', '.'),
            'payment' => $o->payment_method,
            'status' => $o->status === 'completed' ? 'completed' : ($o->status === 'processing' ? 'processing' : 'pending'),
            'date' => $o->created_at->format('d M Y, H:i'),
            'type' => 'Otomatis QRIS',
            'accounts' => $o->accounts ?: [],
            'note' => $o->note ?: '',
        ];
    });

    return Inertia::render('AdminPanel', [
        'reseller' => $balanceData['reseller'] ?? null,
        'liveProducts' => $items,
        'settings' => $settings,
        'initialOrders' => $localOrders,
    ]);
});

// Endpoint Tambah / Edit Produk Custom Milik Toko
Route::post('/api/products/custom', function (\Illuminate\Http\Request $request) {
    $data = json_decode($request->getContent(), true) ?: $request->all();

    $name = trim($data['name'] ?? '');
    $price = (int) ($data['price'] ?? 0);

    if (!$name || $price <= 0) {
        return response()->json(['ok' => false, 'error' => 'Nama produk dan harga jual wajib diisi.'], 400);
    }

    $cp = CustomProduct::create([
        'name' => $name,
        'variant' => trim($data['variant'] ?? 'Standar'),
        'category' => trim($data['category'] ?? 'Aplikasi Premium'),
        'price' => $price,
        'normal_price' => !empty($data['normal_price']) ? (int) $data['normal_price'] : $price,
        'stock' => isset($data['stock']) && $data['stock'] !== '' ? (int) $data['stock'] : null,
        'type' => $data['type'] ?? 'manual',
        'description' => trim($data['description'] ?? ''),
        'status' => 'active',
    ]);

    return response()->json([
        'ok' => true,
        'message' => 'Produk berhasil ditambahkan ke katalog!',
        'product' => $cp->toCatalogItem(),
    ]);
});

// Endpoint Toggle Status Produk (Aktif / Nonaktif)
Route::post('/api/products/toggle-status', function (\Illuminate\Http\Request $request) {
    $data = json_decode($request->getContent(), true) ?: $request->all();
    $variantId = (string) ($data['variant_id'] ?? $request->input('variant_id') ?? '');
    if (!$variantId) {
        return response()->json(['ok' => false, 'error' => 'variant_id wajib diisi'], 400);
    }

    // Jika produk custom
    if (str_starts_with($variantId, 'cp_')) {
        $id = str_replace('cp_', '', $variantId);
        $cp = CustomProduct::find($id);
        if ($cp) {
            $cp->status = $cp->status === 'active' ? 'inactive' : 'active';
            $cp->save();
            return response()->json(['ok' => true, 'variant_id' => $variantId, 'status' => $cp->status]);
        }
    }

    $disabledIdsRaw = Setting::get('disabled_products', '[]');
    $disabledIds = json_decode($disabledIdsRaw, true) ?: [];

    if (in_array($variantId, $disabledIds)) {
        $disabledIds = array_values(array_diff($disabledIds, [$variantId]));
        $status = 'active';
    } else {
        $disabledIds[] = $variantId;
        $status = 'inactive';
    }

    Setting::set('disabled_products', json_encode(array_values(array_unique($disabledIds))));

    return response()->json([
        'ok' => true,
        'variant_id' => $variantId,
        'status' => $status,
        'message' => 'Status produk berhasil diperbarui.'
    ]);
});

// Endpoint Simpan Pengaturan Toko (Margin & Hide Out of Stock)
Route::post('/api/settings/margin', function (\Illuminate\Http\Request $request) {
    $mode = $request->input('margin_mode', 'fixed');
    $flat = (int) $request->input('margin_flat', 5000);
    $percent = (int) $request->input('margin_percent', 15);
    $hideOutOfStock = (bool) $request->input('hide_out_of_stock', false);

    Setting::set('margin_mode', $mode);
    Setting::set('margin_flat', $flat);
    Setting::set('margin_percent', $percent);
    Setting::set('hide_out_of_stock', $hideOutOfStock ? '1' : '0');

    return response()->json([
        'ok' => true,
        'message' => 'Pengaturan toko berhasil disimpan!',
        'settings' => [
            'margin_mode' => $mode,
            'margin_flat' => $flat,
            'margin_percent' => $percent,
            'hide_out_of_stock' => $hideOutOfStock,
        ]
    ]);
});

// Endpoint Update Status Pesanan (Manual dari Admin)
Route::post('/api/orders/update-status', function (\Illuminate\Http\Request $request) {
    $data = json_decode($request->getContent(), true) ?: $request->all();
    $orderId = $data['order_id'] ?? null;
    $newStatus = $data['status'] ?? 'completed';

    if (!$orderId) {
        return response()->json(['ok' => false, 'error' => 'order_id wajib diisi'], 400);
    }

    $order = Order::where('order_id', $orderId)->orWhere('ref', $orderId)->first();
    if ($order) {
        $order->status = $newStatus;
        $order->save();
        return response()->json(['ok' => true, 'order' => $order]);
    }

    return response()->json(['ok' => false, 'error' => 'Pesanan tidak ditemukan di database lokal.'], 404);
});

// Endpoint Cek Pesanan (Mendukung ID, Ref, Email, atau WhatsApp)
Route::get('/api/check-order', function (\Illuminate\Http\Request $request, WaroengkuyService $service) {
    $q = trim($request->query('q', ''));
    if (!$q) {
        return response()->json(['ok' => false, 'error' => 'Nomor pesanan / WhatsApp wajib diisi'], 400);
    }

    // 1. Cek dulu di database lokal toko
    $cleanPhone = preg_replace('/[^0-9]/', '', $q);
    $localOrder = Order::where('order_id', $q)
        ->orWhere('ref', $q)
        ->orWhere('customer_phone', $cleanPhone)
        ->orWhere('customer_email', $q)
        ->orderBy('id', 'desc')
        ->first();

    if ($localOrder) {
        return response()->json([
            'ok' => true,
            'order' => [
                'id' => $localOrder->order_id,
                'ref' => $localOrder->ref,
                'status' => $localOrder->status,
                'product' => $localOrder->product_name . ($localOrder->variant ? ' — ' . $localOrder->variant : ''),
                'qty' => $localOrder->qty,
                'price' => $localOrder->amount,
                'total' => $localOrder->amount,
                'accounts' => $localOrder->accounts ?: [],
                'note' => $localOrder->note ?: '',
            ]
        ]);
    }

    // 2. Jika tidak ada di lokal, coba cari di API Waroengkuy
    $result = $service->getOrder($q, true);
    if (!($result['ok'] ?? false)) {
        $result = $service->getOrder($q, false);
    }

    return response()->json($result);
});

// Endpoint Refresh Data Produk untuk Pembeli
Route::get('/api/products', function (WaroengkuyService $service) {
    $productsData = $service->getProducts();
    $items = getProcessedProducts($productsData['items'] ?? [], true);
    return response()->json([
        'ok' => true,
        'items' => $items,
    ]);
});

// Endpoint Pembelian Otomatis Langsung di Website
Route::post('/api/create-order', function (\Illuminate\Http\Request $request, WaroengkuyService $service) {
    $data = json_decode($request->getContent(), true) ?: $request->all();
    $variantId = $data['variant_id'] ?? null;
    $qty = (int) ($data['qty'] ?? 1);
    $customerEmail = trim($data['email'] ?? '');
    $customerWhatsapp = trim($data['whatsapp'] ?? '');
    $customFields = $data['custom_fields'] ?? [];
    $acceptNoWarranty = (bool) ($data['accept_no_warranty'] ?? false);

    if (!$variantId) {
        return response()->json(['ok' => false, 'error' => 'Varian produk wajib dipilih'], 400);
    }

    $refId = 'IDEO-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();

    // Cek apakah produk custom
    if (str_starts_with((string)$variantId, 'cp_')) {
        $id = str_replace('cp_', '', $variantId);
        $cp = CustomProduct::find($id);
        if (!$cp || $cp->status !== 'active') {
            return response()->json(['ok' => false, 'error' => 'Produk tidak tersedia.'], 400);
        }

        $totalAmount = $cp->price * $qty;
        $order = Order::create([
            'order_id' => $refId,
            'ref' => $refId,
            'product_name' => $cp->name,
            'variant' => $cp->variant,
            'qty' => $qty,
            'amount' => $totalAmount,
            'customer_name' => 'Pembeli',
            'customer_email' => $customerEmail,
            'customer_phone' => $customerWhatsapp,
            'status' => 'pending',
            'note' => 'Menunggu verifikasi admin toko.',
        ]);

        return response()->json([
            'ok' => true,
            'order' => [
                'id' => $order->order_id,
                'ref' => $order->ref,
                'status' => $order->status,
                'product' => $cp->name,
                'qty' => $qty,
                'total' => $totalAmount,
                'payment' => [
                    'amount' => $totalAmount,
                    'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode('00020101021126570011ID.WIRAGUNA011893600014000413350102150008850041335010303UMI51440014ID.CO.QRIS.WWW0215ID10254407486770303UMI5204599953033605802ID5909IDEOSTORE6008JAKARTA61051234062' . $totalAmount),
                ]
            ]
        ]);
    }

    // Produk Waroengkuy API
    $disabledIdsRaw = Setting::get('disabled_products', '[]');
    $disabledIds = json_decode($disabledIdsRaw, true) ?: [];
    if (in_array((string)$variantId, $disabledIds)) {
        return response()->json(['ok' => false, 'error' => 'Produk ini sedang tidak tersedia untuk dibeli.'], 400);
    }

    $orderData = [];
    if ($customerEmail) $orderData['Email'] = $customerEmail;
    if ($customerWhatsapp) $orderData['WhatsApp'] = $customerWhatsapp;
    if (is_array($customFields)) {
        foreach ($customFields as $k => $v) {
            if ($v) $orderData[$k] = $v;
        }
    }

    $payload = [
        'variant_id' => $variantId,
        'qty' => max(1, min($qty, 10)),
        'ref_id' => $refId,
        'payment' => 'qris',
    ];

    if ($customerEmail) $payload['customer_email'] = $customerEmail;
    if (!empty($orderData)) $payload['data'] = $orderData;
    if ($acceptNoWarranty) $payload['accept_no_warranty'] = true;

    $result = $service->createOrder($payload);

    // Simpan pesanan ke database tabel orders lokal toko
    if ($result['ok'] ?? false) {
        $resOrder = $result['order'];
        Order::create([
            'order_id' => (string) ($resOrder['id'] ?? $refId),
            'ref' => (string) ($resOrder['ref'] ?? $refId),
            'product_name' => $resOrder['product'] ?? 'Produk Premium',
            'variant' => '',
            'qty' => (int) ($resOrder['qty'] ?? $qty),
            'amount' => (int) ($resOrder['payment']['amount'] ?? $resOrder['total'] ?? 0),
            'customer_name' => 'Pembeli',
            'customer_email' => $customerEmail,
            'customer_phone' => $customerWhatsapp,
            'payment_method' => 'QRIS',
            'status' => $resOrder['status'] ?? 'pending',
            'accounts' => $resOrder['accounts'] ?? [],
            'note' => $resOrder['note'] ?? '',
            'qr_url' => $resOrder['payment']['qr_url'] ?? null,
        ]);
    }

    return response()->json($result);
});

// Polling Status Order Detail & Sinkronisasi DB Lokal
Route::get('/api/order/{id}', function ($id, WaroengkuyService $service) {
    $result = $service->getOrder((string) $id, false);
    
    // Jika berhasil dicek dan statusnya berubah, update DB lokal
    if (($result['ok'] ?? false) && isset($result['order'])) {
        $resOrder = $result['order'];
        $order = Order::where('order_id', (string) $id)->orWhere('ref', (string) $id)->first();
        if ($order) {
            $order->status = $resOrder['status'] ?? $order->status;
            if (!empty($resOrder['accounts'])) {
                $order->accounts = $resOrder['accounts'];
            }
            $order->save();
        }
    }

    return response()->json($result);
});
