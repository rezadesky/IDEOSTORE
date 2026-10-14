<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Services\WaroengkuyService;
use App\Models\Setting;
use App\Models\CustomProduct;
use App\Models\Order;

// Helper kalkulasi harga jual dan filter produk
if (!function_exists('getProcessedProducts')) {
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

    // 1. Sertakan Custom Product / Produk Toko di posisi terdepan (paling atas)
    $customProducts = CustomProduct::orderBy('id', 'desc')->get();
    foreach ($customProducts as $cp) {
        $item = $cp->toCatalogItem();
        if ($filterForBuyer) {
            if ($item['status'] !== 'active') continue;
            if ($hideOutOfStock && $item['stock'] !== null && (int)$item['stock'] <= 0) continue;
        }
        $processed[] = $item;
    }

    // Ambil margin khusus per item (variant_id => margin_amount)
    $productMarginsRaw = Setting::get('product_margins', '{}');
    $productMargins = json_decode($productMarginsRaw, true) ?: [];

    // 2. Sertakan produk live dari Waroengkuy
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

        // Jika produk memiliki margin khusus per-item
        if (isset($productMargins[$variantId]) && is_numeric($productMargins[$variantId])) {
            $itemMargin = (int) $productMargins[$variantId];
            $sellPrice = $cost + $itemMargin;
            $hasCustomMargin = true;
        } else {
            $itemMargin = null;
            $hasCustomMargin = false;
            if ($mode === 'normal_price') {
                $sellPrice = max($cost, $normal);
            } elseif ($mode === 'percent') {
                $sellPrice = (int) round($cost * (1 + ($percent / 100)));
            } else {
                $sellPrice = $cost + $flat;
            }
        }

        $profit = max(0, $sellPrice - $cost);

        $item['cost_price'] = $cost;
        $item['sell_price'] = $sellPrice;
        $item['profit'] = $profit;
        $item['price'] = $sellPrice;
        $item['custom_margin'] = $itemMargin;
        $item['has_custom_margin'] = $hasCustomMargin;
        $item['is_disabled'] = $isDisabled;
        $item['status'] = $isDisabled ? 'inactive' : 'active';
        $item['is_custom'] = false;

        $processed[] = $item;
    }

    return $processed;
    }
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

Route::get('/invoice/{id}', function (string $id) {
    return redirect('/lacak-pesanan?q=' . urlencode($id));
});

Route::get('/order/{id}', function (string $id) {
    return redirect('/lacak-pesanan?q=' . urlencode($id));
});

use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Route Autentikasi Admin
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/adminpanel');
    }
    return Inertia::render('Auth/Login', [
        'error' => session('error'),
    ]);
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $remember = (bool) $request->boolean('remember');

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/adminpanel');
    }

    return back()->withErrors([
        'email' => 'Email atau password yang Anda masukkan salah.',
    ])->onlyInput('email');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Rute Admin Panel (Wajib Login)
Route::middleware('auth')->group(function () {
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
            $isApi = !str_starts_with((string)$o->order_id, 'WRG-') && !str_starts_with((string)$o->ref, 'IDEO-');
            return [
                'id' => $o->order_id,
                'ref' => $o->ref,
                'customer' => $o->customer_name ?: 'Pembeli',
                'phone' => $o->customer_phone ?: '-',
                'email' => $o->customer_email ?: '-',
                'product' => $o->product_name . ($o->variant ? ' (' . $o->variant . ')' : ''),
                'price' => 'Rp ' . number_format($o->amount, 0, ',', '.'),
                'payment' => $o->payment_method,
                'status' => in_array($o->status, ['completed', 'success']) ? 'completed' : ($o->status === 'processing' ? 'processing' : 'pending'),
                'date' => $o->created_at->format('d M Y, H:i'),
                'type' => $isApi ? 'Otomatis API Waroengkuy' : 'Produk Toko Sendiri',
                'is_api' => $isApi,
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

    // Endpoint Update Margin Khusus Per Item / Produk
    Route::post('/api/products/set-margin', function (\Illuminate\Http\Request $request) {
        $data = json_decode($request->getContent(), true) ?: $request->all();
        $variantId = (string) ($data['variant_id'] ?? '');
        $margin = isset($data['margin']) && $data['margin'] !== '' ? (int) $data['margin'] : null;

        if (!$variantId) {
            return response()->json(['ok' => false, 'error' => 'variant_id wajib diisi'], 400);
        }

        // Jika custom product
        if (str_starts_with($variantId, 'cp_')) {
            $id = str_replace('cp_', '', $variantId);
            $cp = CustomProduct::find($id);
            if ($cp) {
                if ($margin !== null) {
                    $cp->price = max(1000, $margin); // Untuk produk custom, harga jual langsung diupdate
                    $cp->save();
                }
                return response()->json(['ok' => true, 'variant_id' => $variantId, 'price' => $cp->price]);
            }
        }

        $marginsRaw = Setting::get('product_margins', '{}');
        $margins = json_decode($marginsRaw, true) ?: [];

        if ($margin === null || $margin < 0) {
            unset($margins[$variantId]);
        } else {
            $margins[$variantId] = $margin;
        }

        Setting::set('product_margins', json_encode($margins));

        return response()->json([
            'ok' => true,
            'variant_id' => $variantId,
            'margin' => $margin,
            'message' => 'Margin produk berhasil diperbarui!'
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
                'qr_url' => $localOrder->qr_url,
                'payment' => [
                    'amount' => $localOrder->amount,
                    'qr_url' => $localOrder->qr_url,
                ],
                'accounts' => $localOrder->accounts ?: [],
                'note' => (function() use ($localOrder) {
                    $note = (string) ($localOrder->note ?? '');
                    if (str_starts_with(trim($note), '{') && str_ends_with(trim($note), '}')) {
                        return '';
                    }
                    return $note;
                })(),
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
        
        // Panggil WaroengPay untuk membuat invoice QRIS resmi
        $wpService = app(\App\Services\WaroengPayService::class);
        $wpRes = $wpService->createPayment([
            'amount' => max(1000, $totalAmount),
            'reference' => $refId,
            'description' => "Order {$cp->name} ({$qty}x)",
        ]);

        $wpPayment = $wpRes['payment'] ?? null;
        $finalAmount = $wpPayment['amount'] ?? $totalAmount;
        $paymentId = $wpPayment['id'] ?? $refId;
        $qrisString = $wpPayment['qris'] ?? '00020101021126570011ID.WIRAGUNA011893600014000413350102150008850041335010303UMI51440014ID.CO.QRIS.WWW0215ID10254407486770303UMI5204599953033605802ID5909IDEOSTORE6008JAKARTA61051234062';
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrisString);

        $order = Order::create([
            'order_id' => $paymentId,
            'ref' => $refId,
            'product_name' => $cp->name,
            'variant' => $cp->variant,
            'qty' => $qty,
            'amount' => $finalAmount,
            'customer_name' => 'Pembeli',
            'customer_email' => $customerEmail,
            'customer_phone' => $customerWhatsapp,
            'payment_method' => 'QRIS WaroengPay',
            'status' => 'pending',
            'qr_url' => $qrUrl,
            'note' => $wpPayment ? 'Tagihan WaroengPay terbit otomatis.' : 'Menunggu verifikasi admin toko.',
        ]);

        // Kirim email tagihan ke pembeli jika mencantumkan email
        if (!empty($order->customer_email) && filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::send('emails.order_invoice', [
                    'order' => $order,
                    'payUrl' => $wpPayment['pay_url'] ?? null,
                ], function ($m) use ($order) {
                    $m->to($order->customer_email)
                      ->subject("Tagihan Pembayaran: {$order->order_id} - Ideostore");
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal kirim email tagihan: ' . $e->getMessage());
            }
        }

        return response()->json([
            'ok' => true,
            'order' => [
                'id' => $order->order_id,
                'ref' => $order->ref,
                'status' => $order->status,
                'product' => $cp->name,
                'qty' => $qty,
                'total' => $finalAmount,
                'livemode' => $wpPayment['livemode'] ?? false,
                'pay_url' => $wpPayment['pay_url'] ?? null,
                'payment' => [
                    'amount' => $finalAmount,
                    'qr_url' => $qrUrl,
                    'qris' => $qrisString,
                ]
            ]
        ]);
    }

    // 2. Produk Live dari Waroengkuy API
    $disabledIdsRaw = Setting::get('disabled_products', '[]');
    $disabledIds = json_decode($disabledIdsRaw, true) ?: [];
    if (in_array((string)$variantId, $disabledIds)) {
        return response()->json(['ok' => false, 'error' => 'Produk ini sedang tidak tersedia untuk dibeli.'], 400);
    }

    // Ambil data produk untuk menghitung harga jual toko (Modal + Untung)
    $productsData = $service->getProducts();
    $rawItems = $productsData['items'] ?? [];
    $targetItem = collect($rawItems)->firstWhere('variant_id', (string)$variantId);

    if (!$targetItem) {
        return response()->json(['ok' => false, 'error' => 'Produk atau varian tidak ditemukan.'], 404);
    }

    $cost = (int) ($targetItem['price'] ?? 0);
    $normal = (int) ($targetItem['normal_price'] ?? $cost);
    $mode = Setting::get('margin_mode', 'fixed');
    $flat = (int) Setting::get('margin_flat', 5000);
    $percent = (int) Setting::get('margin_percent', 15);

    $productMarginsRaw = Setting::get('product_margins', '{}');
    $productMargins = json_decode($productMarginsRaw, true) ?: [];

    if (isset($productMargins[(string)$variantId]) && is_numeric($productMargins[(string)$variantId])) {
        $sellPrice = $cost + (int) $productMargins[(string)$variantId];
    } else {
        if ($mode === 'normal_price') {
            $sellPrice = max($cost, $normal);
        } elseif ($mode === 'percent') {
            $sellPrice = (int) round($cost * (1 + ($percent / 100)));
        } else {
            $sellPrice = $cost + $flat;
        }
    }

    $totalSellPrice = $sellPrice * $qty;
    $productLabel = ($targetItem['product'] ?? 'Produk') . ' - ' . ($targetItem['variant'] ?? 'Standar');

    // Buat Tagihan QRIS Resmi Toko via WaroengPay (Nominal Sesuai Harga Jual Toko)
    $wpService = app(\App\Services\WaroengPayService::class);
    $wpRes = $wpService->createPayment([
        'amount' => max(1000, $totalSellPrice),
        'reference' => $refId,
        'description' => "Order {$productLabel} ({$qty}x)",
    ]);

    $wpPayment = $wpRes['payment'] ?? null;
    $finalAmount = $wpPayment['amount'] ?? $totalSellPrice;
    $paymentId = $wpPayment['id'] ?? $refId;
    $qrisString = $wpPayment['qris'] ?? '00020101021126570011ID.WIRAGUNA011893600014000413350102150008850041335010303UMI51440014ID.CO.QRIS.WWW0215ID10254407486770303UMI5204599953033605802ID5909IDEOSTORE6008JAKARTA61051234062';
    $qrUrl = $wpPayment['qr_url'] ?? ('https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrisString));

    // Simpan pesanan ke database tabel orders lokal toko
    $order = Order::create([
        'order_id' => $paymentId,
        'ref' => $refId,
        'product_name' => $targetItem['product'] ?? 'Produk Premium',
        'variant' => $targetItem['variant'] ?? '',
        'qty' => $qty,
        'amount' => $finalAmount,
        'customer_name' => 'Pembeli',
        'customer_email' => $customerEmail,
        'customer_phone' => $customerWhatsapp,
        'payment_method' => 'QRIS WaroengPay',
        'status' => 'pending',
        'qr_url' => $qrUrl,
        'note' => json_encode([
            'is_waroengkuy' => true,
            'variant_id' => $variantId,
            'cost_price' => $cost,
            'sell_price' => $sellPrice,
            'profit' => max(0, $sellPrice - $cost),
            'custom_fields' => $customFields,
            'accept_no_warranty' => $acceptNoWarranty,
        ]),
    ]);

    // Kirim email tagihan ke pembeli jika mencantumkan email
    if (!empty($order->customer_email) && filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)) {
        try {
            Mail::send('emails.order_invoice', [
                'order' => $order,
                'payUrl' => $wpPayment['pay_url'] ?? null,
            ], function ($m) use ($order) {
                $m->to($order->customer_email)
                  ->subject("Tagihan Pembayaran: {$order->order_id} - Ideostore");
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim email tagihan: ' . $e->getMessage());
        }
    }

    return response()->json([
        'ok' => true,
        'order' => [
            'id' => $order->order_id,
            'ref' => $order->ref,
            'status' => $order->status,
            'product' => $order->product_name . ($order->variant ? ' — ' . $order->variant : ''),
            'qty' => $qty,
            'total' => $finalAmount,
            'livemode' => $wpPayment['livemode'] ?? false,
            'pay_url' => $wpPayment['pay_url'] ?? null,
            'payment' => [
                'amount' => $finalAmount,
                'qr_url' => $qrUrl,
                'qris' => $qrisString,
            ]
        ]
    ]);
});

// Polling Status Order Detail & Sinkronisasi DB Lokal (Mendukung WaroengPay & Waroengkuy)
Route::get('/api/order/{id}', function ($id, WaroengkuyService $service) {
    // 1. Cek apakah ini transaksi WaroengPay (berawalan WRG- atau tersimpan di lokal)
    if (str_starts_with((string)$id, 'WRG-') || str_starts_with((string)$id, 'IDEO-')) {
        $wpService = app(\App\Services\WaroengPayService::class);
        $wpRes = $wpService->getPayment((string) $id);
        
        if ($wpRes['ok'] ?? false) {
            $payment = $wpRes['payment'];
            $order = Order::where('order_id', (string) $id)->orWhere('ref', (string) $id)->first();
            if ($order) {
                if ($payment['status'] === 'paid') {
                    // Parse meta jika produk waroengkuy
                    $meta = json_decode($order->note ?? '', true);
                    if (is_array($meta) && ($meta['is_waroengkuy'] ?? false) && empty($order->accounts)) {
                        // Jalankan auto-fulfill via saldo Waroengkuy
                        $fulfillPayload = [
                            'variant_id' => $meta['variant_id'],
                            'qty' => max(1, min($order->qty, 10)),
                            'ref_id' => 'FULFILL-' . $order->ref,
                            'payment' => 'saldo',
                        ];
                        if ($order->customer_email) $fulfillPayload['customer_email'] = $order->customer_email;
                        if (!empty($meta['custom_fields'])) $fulfillPayload['data'] = $meta['custom_fields'];
                        if (!empty($meta['accept_no_warranty'])) $fulfillPayload['accept_no_warranty'] = true;

                        $wkRes = $service->createOrder($fulfillPayload);
                        if ($wkRes['ok'] ?? false) {
                            $wkOrder = $wkRes['order'];
                            $order->status = $wkOrder['status'] ?? 'success';
                            $order->accounts = $wkOrder['accounts'] ?? [];
                            $order->note = $wkOrder['note'] ?: 'Pesanan berhasil diproses otomatis oleh Waroengkuy!';
                        } else {
                            $order->status = 'processing';
                            $order->note = 'Pembayaran QRIS lunas. Akun sedang diproses: ' . ($wkRes['error'] ?? 'menunggu antrean');
                        }
                    } else {
                        $order->status = 'success';
                        if (empty($order->accounts)) {
                            $order->accounts = [
                                "Email: {$order->customer_email}",
                                "Status: Berhasil diverifikasi sistem Ideostore",
                                "Ref: " . ($payment['payment']['bca_ref'] ?? 'LUNAS-AUTO')
                            ];
                        }
                    }
                    $order->save();

                    // Kirim email notifikasi pesanan selesai & kredensial akun
                    if (in_array($order->status, ['success', 'completed']) && !empty($order->customer_email) && filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)) {
                        try {
                            Mail::send('emails.order_completed', [
                                'order' => $order,
                            ], function ($m) use ($order) {
                                $m->to($order->customer_email)
                                  ->subject("Pesanan Berhasil: {$order->order_id} - Ideostore");
                            });
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Gagal kirim email order completed: ' . $e->getMessage());
                        }
                    }
                } elseif ($payment['status'] === 'expired' || $payment['status'] === 'cancelled') {
                    $order->status = $payment['status'];
                    $order->save();
                }
            }

            return response()->json([
                'ok' => true,
                'order' => [
                    'id' => $order ? $order->order_id : $id,
                    'ref' => $order ? $order->ref : $id,
                    'status' => $order ? $order->status : ($payment['status'] === 'paid' ? 'success' : $payment['status']),
                    'product' => $order ? $order->product_name : 'Produk',
                    'qty' => $order ? $order->qty : 1,
                    'total' => $payment['amount'] ?? ($order ? $order->amount : 0),
                    'accounts' => $order ? $order->accounts : [],
                    'note' => (function() use ($order) {
                        $note = (string) ($order->note ?? '');
                        if (str_starts_with(trim($note), '{') && str_ends_with(trim($note), '}')) {
                            return '';
                        }
                        return $note;
                    })(),
                    'livemode' => $payment['livemode'] ?? false,
                    'pay_url' => $payment['pay_url'] ?? null,
                    'qr_url' => $order ? $order->qr_url : null,
                    'payment' => [
                        'amount' => $payment['amount'],
                        'qr_url' => $order ? $order->qr_url : null,
                    ]
                ]
            ]);
        }
    }

    // 2. Fallback ke Waroengkuy API
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

// Endpoint Batalkan Transaksi / Pesanan
Route::post('/api/order/{id}/cancel', function ($id, WaroengkuyService $service) {
    $order = Order::where('order_id', (string) $id)->orWhere('ref', (string) $id)->first();
    $targetId = $order ? $order->order_id : (string) $id;
    
    // 1. Coba batalkan di WaroengPay jika transaksi berawalan WRG- atau IDEO-
    $wpService = app(\App\Services\WaroengPayService::class);
    $wpRes = $wpService->cancelPayment($targetId);
    
    if ($wpRes['ok'] ?? false) {
        if ($order) {
            $order->status = 'cancelled';
            $order->note = 'Pesanan dibatalkan oleh pelanggan.';
            $order->save();
        }
        return response()->json(['ok' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    }

    // 2. Coba batalkan di Waroengkuy jika ada order ID Waroengkuy
    $wkRes = $service->cancelOrder($targetId);
    if ($wkRes['ok'] ?? false) {
        if ($order) {
            $order->status = 'cancelled';
            $order->note = 'Pesanan dibatalkan oleh pelanggan.';
            $order->save();
        }
        return response()->json(['ok' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    }

    // 3. Fallback: jika status di database lokal masih pending, batalkan langsung di DB lokal
    if ($order && $order->status === 'pending') {
        $order->status = 'cancelled';
        $order->note = 'Pesanan dibatalkan oleh pelanggan.';
        $order->save();
        return response()->json(['ok' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    }

    return response()->json([
        'ok' => false, 
        'error' => $wpRes['error'] ?? $wkRes['error'] ?? 'Pesanan tidak dapat dibatalkan atau sudah diproses.'
    ], 400);
});


