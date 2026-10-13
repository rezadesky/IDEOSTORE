<script setup>
import { ref, computed } from 'vue';
import { motion } from 'motion-v';

const props = defineProps({
    reseller: {
        type: Object,
        default: () => null
    },
    liveProducts: {
        type: Array,
        default: () => []
    },
    settings: {
        type: Object,
        default: () => ({
            margin_mode: 'fixed',
            margin_flat: 5000,
            margin_percent: 15
        })
    },
    initialOrders: {
        type: Array,
        default: () => []
    }
});

// Active Menu Tab: 'dashboard' | 'orders' | 'products' | 'settings'
const activeTab = ref('dashboard');

// Pengaturan Toko & Margin
const marginForm = ref({
    margin_mode: props.settings?.margin_mode || 'fixed',
    margin_flat: props.settings?.margin_flat || 5000,
    margin_percent: props.settings?.margin_percent || 15,
    hide_out_of_stock: !!props.settings?.hide_out_of_stock
});
const savingSettings = ref(false);
const saveSuccess = ref(false);

const saveMarginSettings = async () => {
    savingSettings.value = true;
    saveSuccess.value = false;
    try {
        const res = await fetch('/api/settings/margin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(marginForm.value)
        });
        const data = await res.json();
        if (data.ok) {
            saveSuccess.value = true;
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    } catch (e) {
        alert('Gagal menyimpan pengaturan.');
    } finally {
        savingSettings.value = false;
    }
};

// Toggle Aktif/Nonaktif Produk oleh Admin
const toggleProductStatus = async (prod) => {
    const oldStatus = prod.status;
    prod.status = prod.status === 'active' ? 'inactive' : 'active';
    try {
        const res = await fetch('/api/products/toggle-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ variant_id: prod.id })
        });
        const data = await res.json();
        if (!data.ok) {
            prod.status = oldStatus;
            alert(data.error || 'Gagal mengubah status produk.');
        }
    } catch (err) {
        prod.status = oldStatus;
        alert('Gangguan koneksi saat mengubah status produk.');
    }
};

// Search & Filter Orders
const searchQuery = ref('');
const statusFilter = ref('all');

// Modal Detail Pesanan
const selectedOrder = ref(null);
const showDetailModal = ref(false);

const openDetail = (order) => {
    selectedOrder.value = order;
    showDetailModal.value = true;
};

// Modal Edit Margin Khusus Per Item
const showMarginModal = ref(false);
const editingProduct = ref(null);
const itemMarginInput = ref('');
const savingItemMargin = ref(false);

const openMarginModal = (prod) => {
    editingProduct.value = prod;
    // Default nilai input margin saat ini
    itemMarginInput.value = prod.rawProfit !== undefined ? String(prod.rawProfit) : '5000';
    showMarginModal.value = true;
};

const saveItemMargin = async () => {
    if (!editingProduct.value) return;
    savingItemMargin.value = true;
    try {
        const rawInput = itemMarginInput.value !== null && itemMarginInput.value !== undefined ? String(itemMarginInput.value) : '0';
        const val = Number(rawInput.replace(/[^0-9]/g, '')) || 0;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/products/set-margin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                variant_id: editingProduct.value.id,
                margin: val
            })
        });
        const data = await res.json();
        if (data.ok) {
            // Update objek reaktif di tabel
            const rawCost = editingProduct.value.raw?.cost_price || editingProduct.value.raw?.price || 0;
            editingProduct.value.rawProfit = val;
            editingProduct.value.profit = formatRupiah(val);
            editingProduct.value.price = formatRupiah(rawCost + val);
            editingProduct.value.hasCustomMargin = true;
            showMarginModal.value = false;
        } else {
            alert(data.error || 'Gagal menyimpan margin produk.');
        }
    } catch (e) {
        alert('Gagal menghubungi server: ' + (e?.message || 'Koneksi terputus'));
    } finally {
        savingItemMargin.value = false;
    }
};

// Format Rupiah
const formatRupiah = (val) => {
    if (val === null || val === undefined) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
};

// Data Pesanan (Dimuat langsung dari Database Lokal Toko)
const orders = ref(props.initialOrders ? [...props.initialOrders] : []);

// Stats Ringkasan Dashboard (Terhubung dengan API Waroengkuy & Database Lokal)
const stats = computed(() => [
    {
        title: 'Saldo Deposit API',
        value: formatRupiah(props.reseller?.balance ?? 0),
        change: props.reseller ? 'Live API' : 'Offline',
        trend: 'up',
        icon: 'cash',
        desc: props.reseller ? `ID: #${props.reseller.id}` : 'Waroengkuy'
    },
    {
        title: 'Pesanan Sukses',
        value: String(orders.value.filter(o => o.status === 'completed' || o.status === 'success').length),
        change: 'Database',
        trend: 'neutral',
        icon: 'shopping-bag',
        desc: 'Selesai'
    },
    {
        title: 'Menunggu / Proses',
        value: String(orders.value.filter(o => o.status === 'processing' || o.status === 'pending').length),
        change: 'Antrean',
        trend: 'neutral',
        icon: 'clock',
        desc: 'Perlu dicek'
    },
    {
        title: 'Katalog Produk',
        value: String(productsList.value?.length || props.liveProducts?.length || 0),
        change: 'Tersinkron',
        trend: 'neutral',
        icon: 'tag',
        desc: 'API & Custom'
    }
]);

// Data Produk Reactive (dimuat dari props.liveProducts dan responsif saat ditoggle)
const initProducts = () => {
    if (props.liveProducts && props.liveProducts.length > 0) {
        return props.liveProducts.map(p => ({
            id: p.variant_id,
            name: `${p.product} (${p.variant})`,
            category: p.category || 'Akun Premium',
            costPrice: formatRupiah(p.cost_price || p.price),
            price: formatRupiah(p.sell_price || p.price),
            profit: formatRupiah(p.profit || 0),
            rawProfit: p.profit || 0,
            originalPrice: p.normal_price && p.normal_price > p.price ? formatRupiah(p.normal_price) : '-',
            discount: p.normal_price && p.normal_price > p.price ? `HEMAT ${Math.round((1 - p.price / p.normal_price) * 100)}%` : '-',
            sold: p.type === 'manual' ? 'Proses Manual' : 'Otomatis',
            rawStock: p.stock,
            isOutOfStock: p.stock !== null && Number(p.stock) <= 0,
            stock: p.stock !== null ? (Number(p.stock) <= 0 ? 'Habis (0)' : `${p.stock} Akun`) : 'Unlimited / Manual',
            status: p.status || (p.is_disabled ? 'inactive' : 'active'),
            raw: p
        }));
    }
    return [];
};

const productsList = ref(initProducts());

// Search & Filter Produk di Admin Panel
const productSearchQuery = ref('');
const productCategoryFilter = ref('all');

const availableCategories = computed(() => {
    const cats = new Set();
    productsList.value.forEach(p => {
        if (p.category) cats.add(p.category);
    });
    return Array.from(cats);
});

const filteredProductsList = computed(() => {
    const q = productSearchQuery.value.trim().toLowerCase();
    return productsList.value.filter(prod => {
        const matchesQuery = !q ||
            (prod.name && prod.name.toLowerCase().includes(q)) ||
            (prod.category && prod.category.toLowerCase().includes(q)) ||
            (prod.id && String(prod.id).toLowerCase().includes(q));

        const matchesCategory = productCategoryFilter.value === 'all' || prod.category === productCategoryFilter.value;

        return matchesQuery && matchesCategory;
    });
});

// Filtered Orders
const filteredOrders = computed(() => {
    return orders.value.filter(order => {
        const q = searchQuery.value.toLowerCase();
        const matchesQuery = 
            (order.id && String(order.id).toLowerCase().includes(q)) ||
            (order.ref && String(order.ref).toLowerCase().includes(q)) ||
            (order.customer && order.customer.toLowerCase().includes(q)) ||
            (order.product && order.product.toLowerCase().includes(q)) ||
            (order.phone && String(order.phone).includes(q));
        
        const matchesStatus = statusFilter.value === 'all' || 
            order.status === statusFilter.value ||
            (statusFilter.value === 'completed' && order.status === 'success');
        return matchesQuery && matchesStatus;
    });
});

// Sinkronkan Status Pesanan Realtime dari Server API (Force Sync)
const syncingOrderId = ref(null);
const syncOrderStatus = async (order) => {
    syncingOrderId.value = order.id || order.ref;
    try {
        const res = await fetch(`/api/order/${order.id || order.ref}`);
        const data = await res.json();
        if (data.ok && data.order) {
            const updated = data.order;
            order.status = in_array_status(updated.status);
            if (updated.accounts && updated.accounts.length) {
                order.accounts = updated.accounts;
            }
            if (updated.note) {
                order.note = updated.note;
            }
            alert(`Status berhasil disinkronkan: ${order.status.toUpperCase()}`);
        } else {
            alert(data.error || 'Gagal menyinkronkan status dari server pusat.');
        }
    } catch (e) {
        alert('Gagal menghubungi server untuk sinkronisasi.');
    } finally {
        syncingOrderId.value = null;
    }
};

const in_array_status = (st) => {
    if (st === 'paid' || st === 'success' || st === 'completed') return 'completed';
    if (st === 'processing') return 'processing';
    return st || 'pending';
};


// Tambah Produk Baru Milik Toko Sendiri (Tersambung ke /api/products/custom)
const saveProduct = async () => {
    const rawPrice = Number(String(newProduct.value.price).replace(/[^0-9]/g, ''));
    const rawNormalPrice = newProduct.value.originalPrice ? Number(String(newProduct.value.originalPrice).replace(/[^0-9]/g, '')) : null;
    const stockVal = newProduct.value.stock !== '' ? Number(newProduct.value.stock) : null;

    if (!newProduct.value.name.trim() || rawPrice <= 0) {
        alert('Nama produk dan harga jual harus diisi dengan benar.');
        return;
    }

    savingProduct.value = true;
    try {
        const res = await fetch('/api/products/custom', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: newProduct.value.name.trim(),
                variant: newProduct.value.variant.trim() || 'Standar',
                category: newProduct.value.category,
                price: rawPrice,
                normal_price: rawNormalPrice,
                stock: stockVal,
                type: newProduct.value.type,
                description: newProduct.value.description
            })
        });

        const data = await res.json();
        if (data.ok && data.product) {
            const p = data.product;
            productsList.value.unshift({
                id: p.variant_id,
                name: `${p.product} (${p.variant})`,
                category: p.category,
                costPrice: formatRupiah(p.price),
                price: formatRupiah(p.price),
                profit: 'Rp 0',
                rawProfit: 0,
                originalPrice: p.normal_price && p.normal_price > p.price ? formatRupiah(p.normal_price) : '-',
                discount: p.normal_price && p.normal_price > p.price ? `HEMAT ${Math.round((1 - p.price / p.normal_price) * 100)}%` : '-',
                sold: 'Proses Manual',
                rawStock: p.stock,
                isOutOfStock: p.stock !== null && Number(p.stock) <= 0,
                stock: p.stock !== null ? `${p.stock} Akun` : 'Unlimited / Manual',
                status: 'active',
                raw: p
            });
            newProduct.value = {
                name: '',
                variant: 'Standar',
                category: 'Aplikasi Premium',
                price: '',
                originalPrice: '',
                stock: '',
                description: '',
                type: 'manual'
            };
            showProductModal.value = false;
            alert('Produk berhasil ditambahkan ke katalog!');
        } else {
            alert(data.error || 'Gagal menambahkan produk baru.');
        }
    } catch (e) {
        alert('Gangguan koneksi saat menambahkan produk.');
    } finally {
        savingProduct.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen w-full bg-[#f8fafc] text-slate-800 flex font-sans antialiased selection:bg-royal-600 selection:text-white">
        <!-- Sidebar Navigation (Posisi Tetap / Fixed) -->
        <aside class="w-64 bg-white border-r border-slate-200/80 hidden md:flex flex-col justify-between shrink-0 h-screen fixed top-0 left-0 bottom-0 z-40">
            <div>
                <!-- Brand Logo -->
                <div class="h-16 flex items-center gap-2.5 px-6 border-b border-slate-100">
                    <img src="/logo.png" alt="Ideostore Logo" class="w-8 h-8 object-contain" />
                    <div>
                        <span class="text-base font-extrabold tracking-tight text-slate-900">
                            Ideo<span class="text-royal-700">store</span>
                        </span>
                        <span class="block text-[10px] font-bold text-royal-600 -mt-0.5 tracking-wider uppercase">
                            Admin Panel
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-4 flex flex-col gap-1.5">
                    <button
                        type="button"
                        @click="activeTab = 'dashboard'"
                        :class="[
                            'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-left',
                            activeTab === 'dashboard'
                                ? 'bg-royal-800 text-white shadow-xs shadow-royal-950/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Dashboard</span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'orders'"
                        :class="[
                            'w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-left',
                            activeTab === 'orders'
                                ? 'bg-royal-800 text-white shadow-xs shadow-royal-950/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Kelola Pesanan</span>
                        </div>
                        <span
                            :class="[
                                'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
                                activeTab === 'orders' ? 'bg-white/20 text-white' : 'bg-royal-100 text-royal-700'
                            ]"
                        >
                            {{ orders.length }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'products'"
                        :class="[
                            'w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-left',
                            activeTab === 'products'
                                ? 'bg-royal-800 text-white shadow-xs shadow-royal-950/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span>Kelola Produk</span>
                        </div>
                        <span
                            :class="[
                                'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
                                activeTab === 'products' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'
                            ]"
                        >
                            {{ productsList.length }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'settings'"
                        :class="[
                            'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-left',
                            activeTab === 'settings'
                                ? 'bg-royal-800 text-white shadow-xs shadow-royal-950/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                        ]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Margin & Keuntungan</span>
                    </button>
                </nav>
            </div>

            <!-- Bottom User & Back to Store -->
            <div class="p-4 border-t border-slate-100 flex flex-col gap-2">
                <a
                    href="/"
                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-royal-800 hover:bg-slate-100 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Lihat Toko Utama</span>
                </a>
                <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-royal-700 text-white font-bold flex items-center justify-center text-xs">
                        {{ props.reseller?.name?.charAt(0) || 'R' }}
                    </div>
                    <div class="flex flex-col truncate">
                        <span class="text-xs font-bold text-slate-800 truncate">{{ props.reseller?.name || 'Reza Saputra' }}</span>
                        <span class="text-[10px] text-royal-600 font-medium">Reseller #{{ props.reseller?.id || '172' }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 md:ml-64">
            <!-- Topbar Header -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <div class="md:hidden flex items-center gap-2">
                        <img src="/logo.png" alt="Logo" class="w-7 h-7 object-contain" />
                        <span class="font-bold text-sm text-slate-900">Admin</span>
                    </div>
                    <div class="hidden md:block">
                        <h1 class="text-base font-bold text-slate-900">
                            {{ activeTab === 'dashboard' ? 'Overview Dashboard' : activeTab === 'orders' ? 'Daftar Semua Pesanan' : 'Katalog Produk Toko' }}
                        </h1>
                        <p class="text-[11px] text-slate-400">Update operasional, aktivasi akun, dan katalog produk</p>
                    </div>
                </div>

                <!-- Right Action -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sistem Online
                    </span>
                    <button
                        v-if="activeTab === 'products'"
                        type="button"
                        @click="showProductModal = true"
                        class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 rounded-lg bg-royal-700 hover:bg-royal-800 text-white text-xs font-semibold shadow-xs shadow-royal-950/20 transition-all cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Produk</span>
                    </button>
                    <a
                        href="/"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors"
                    >
                        <span>Ke Web</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                    <form method="POST" action="/logout" class="inline">
                        <input type="hidden" name="_token" :value="$page.props.csrf_token || ''" />
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-colors cursor-pointer"
                            title="Keluar dari Panel Admin"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Main Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 flex flex-col gap-6 max-w-7xl w-full mx-auto">
                <!-- Tab: DASHBOARD & PESANAN -->
                <template v-if="activeTab === 'dashboard' || activeTab === 'orders'">
                    <!-- Stats Grid Cards (Only on Dashboard) -->
                    <div v-if="activeTab === 'dashboard'" class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                        <div
                            v-for="stat in stats"
                            :key="stat.title"
                            class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs flex flex-col justify-between"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-slate-500">{{ stat.title }}</span>
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-royal-50 text-royal-700 flex items-center justify-center">
                                    <svg v-if="stat.icon === 'cash'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else-if="stat.icon === 'shopping-bag'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <svg v-else-if="stat.icon === 'clock'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V5a2 2 0 012-2z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-3">
                                <span class="text-lg sm:text-2xl font-extrabold text-slate-900 tracking-tight block">
                                    {{ stat.value }}
                                </span>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span
                                        v-if="stat.trend === 'up'"
                                        class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.2 rounded"
                                    >
                                        {{ stat.change }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded"
                                    >
                                        {{ stat.change }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">{{ stat.desc }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Table Section -->
                    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col">
                        <!-- Table Header & Controls -->
                        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900">
                                    {{ activeTab === 'dashboard' ? 'Transaksi & Pesanan Terbaru' : 'Seluruh Daftar Pesanan' }}
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Lacak data pembeli dan update status aktivasi secara realtime
                                </p>
                            </div>

                            <!-- Search & Filter Controls -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="relative">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Cari order / nama / WA..."
                                        class="pl-8 pr-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-royal-600 focus:bg-white w-48 sm:w-56"
                                    />
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                <select
                                    v-model="statusFilter"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                >
                                    <option value="all">Semua Status</option>
                                    <option value="completed">Selesai</option>
                                    <option value="processing">Diproses</option>
                                    <option value="pending">Menunggu</option>
                                </select>
                            </div>
                        </div>

                        <!-- Orders Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50/70 text-slate-500 font-semibold border-b border-slate-100">
                                        <th class="py-3 px-4 sm:px-6">Order ID & Waktu</th>
                                        <th class="py-3 px-4">Pembeli / Kontak</th>
                                        <th class="py-3 px-4">Produk & Varian</th>
                                        <th class="py-3 px-4">Nominal</th>
                                        <th class="py-3 px-4">Metode</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    <tr
                                        v-for="order in filteredOrders"
                                        :key="order.id"
                                        class="hover:bg-slate-50/60 transition-colors"
                                    >
                                        <!-- Order ID -->
                                        <td class="py-3.5 px-4 sm:px-6">
                                            <span class="font-bold text-slate-900 font-mono">{{ order.id }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ order.date }}</span>
                                        </td>

                                        <!-- Customer -->
                                        <td class="py-3.5 px-4">
                                            <span class="font-semibold text-slate-800 block">{{ order.customer }}</span>
                                            <a
                                                :href="'https://wa.me/' + order.phone.replace(/[^0-9]/g, '')"
                                                target="_blank"
                                                class="text-[10px] text-royal-700 hover:underline flex items-center gap-1 mt-0.5"
                                            >
                                                <span>{{ order.phone }}</span>
                                            </a>
                                        </td>

                                        <!-- Product -->
                                        <td class="py-3.5 px-4">
                                            <span class="font-medium text-slate-900 block">{{ order.product }}</span>
                                            <span class="text-[10px] text-slate-400">{{ order.type }}</span>
                                        </td>

                                        <!-- Nominal -->
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            {{ order.price }}
                                        </td>

                                        <!-- Payment -->
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-medium text-[10px]">
                                                {{ order.payment }}
                                            </span>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3.5 px-4">
                                            <span
                                                v-if="order.status === 'completed'"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold text-[10px]"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Selesai
                                            </span>
                                            <span
                                                v-else-if="order.status === 'processing'"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60 font-semibold text-[10px]"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                Diproses
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60 font-semibold text-[10px]"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Menunggu
                                            </span>
                                        </td>

                                        <!-- Action Button -->
                                        <td class="py-3.5 px-4 sm:px-6 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    @click="openDetail(order)"
                                                    class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-semibold transition-colors cursor-pointer"
                                                >
                                                    Detail
                                                </button>
                                                <!-- Jika produk manual toko sendiri: ada tombol Selesai manual -->
                                                <button
                                                    v-if="!order.is_api && order.status !== 'completed' && order.status !== 'success'"
                                                    type="button"
                                                    :disabled="updatingOrderId === order.id"
                                                    @click="updateStatus(order, 'completed')"
                                                    class="px-2.5 py-1 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-[10px] shadow-2xs transition-colors cursor-pointer disabled:opacity-60"
                                                >
                                                    {{ updatingOrderId === order.id ? '...' : 'Selesai' }}
                                                </button>
                                                <!-- Jika pesanan dari API Waroengkuy: otomatis diproses sistem pusat -->
                                                <span
                                                    v-else-if="order.is_api"
                                                    class="text-[9px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded"
                                                >
                                                    Auto API
                                                </span>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredOrders.length === 0">
                                        <td colspan="7" class="py-8 text-center text-slate-400">
                                            Tidak ada pesanan yang sesuai dengan pencarian atau filter status.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Tab: KELOLA PRODUK -->
                <template v-else-if="activeTab === 'products'">
                    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col">
                        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900">
                                    Daftar Produk & Layanan
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Atur harga jual, ketersediaan stok, dan status tampil di katalog
                                </p>
                            </div>

                            <!-- Search & Filter Controls -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <!-- Kolom Cari Produk -->
                                <div class="relative">
                                    <input
                                        v-model="productSearchQuery"
                                        type="text"
                                        placeholder="Cari nama produk / varian..."
                                        class="pl-8 pr-7 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-royal-600 focus:bg-white w-52 sm:w-64 transition-all"
                                    />
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <button
                                        v-if="productSearchQuery"
                                        type="button"
                                        @click="productSearchQuery = ''"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 text-xs"
                                        title="Hapus pencarian"
                                    >
                                        &times;
                                    </button>
                                </div>

                                <!-- Filter Kategori -->
                                <select
                                    v-if="availableCategories.length > 0"
                                    v-model="productCategoryFilter"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                >
                                    <option value="all">Semua Kategori</option>
                                    <option v-for="cat in availableCategories" :key="cat" :value="cat">
                                        {{ cat }}
                                    </option>
                                </select>

                                <!-- Total Badge -->
                                <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg shrink-0">
                                    {{ filteredProductsList.length }} / {{ productsList.length }} Produk
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50/70 text-slate-500 font-semibold border-b border-slate-100">
                                        <th class="py-3 px-4 sm:px-6">Nama Produk</th>
                                        <th class="py-3 px-4">Kategori</th>
                                        <th class="py-3 px-4">Modal API</th>
                                        <th class="py-3 px-4">Harga Jual</th>
                                        <th class="py-3 px-4">Margin Untung</th>
                                        <th class="py-3 px-4">Stok</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-700">
                                    <tr v-for="prod in filteredProductsList" :key="prod.id" class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">
                                            {{ prod.name }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-600">
                                            {{ prod.category }}
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 font-mono">
                                            {{ prod.costPrice }}
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 font-mono">
                                            {{ prod.price }}
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-emerald-600 font-mono">
                                            +{{ prod.profit }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span
                                                v-if="prod.isOutOfStock"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Stok Habis (0)
                                            </span>
                                            <span
                                                v-else-if="prod.rawStock !== null"
                                                class="font-semibold text-slate-700"
                                            >
                                                {{ prod.stock }}
                                            </span>
                                            <span
                                                v-else
                                                class="text-slate-400 italic text-[11px]"
                                            >
                                                {{ prod.stock }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span
                                                :class="[
                                                    'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold',
                                                    prod.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-500'
                                                ]"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full" :class="prod.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                                {{ prod.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 sm:px-6 text-right space-x-1.5 whitespace-nowrap">
                                            <button
                                                type="button"
                                                @click="openMarginModal(prod)"
                                                class="px-2.5 py-1 rounded-md text-[10px] font-bold transition-colors cursor-pointer border bg-royal-50 text-royal-700 border-royal-200 hover:bg-royal-100"
                                                title="Atur margin untung khusus produk ini"
                                            >
                                                Ubah Margin
                                            </button>
                                            <button
                                                type="button"
                                                @click="toggleProductStatus(prod)"
                                                :class="[
                                                    'px-2.5 py-1 rounded-md text-[10px] font-semibold transition-colors cursor-pointer border',
                                                    prod.status === 'active'
                                                        ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100'
                                                        : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                                                ]"
                                            >
                                                {{ prod.status === 'active' ? 'Sembunyikan' : 'Tampilkan' }}
                                            </button>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredProductsList.length === 0">
                                        <td colspan="8" class="py-8 text-center text-slate-400">
                                            Tidak ada produk yang cocok dengan pencarian "{{ productSearchQuery }}".
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Tab: PENGATURAN MARGIN KEUNTUNGAN -->
                <template v-else-if="activeTab === 'settings'">
                    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-7 flex flex-col gap-6 max-w-2xl">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Pengaturan Margin & Keuntungan Toko
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">
                                Tentukan margin laba yang otomatis ditambahkan di atas harga modal Waroengkuy API untuk setiap produk di katalog.
                            </p>
                        </div>

                        <!-- Alert Sukses Simpan -->
                        <div v-if="saveSuccess" class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Pengaturan margin berhasil disimpan! Memperbarui katalog toko...</span>
                        </div>

                        <form @submit.prevent="saveMarginSettings" class="flex flex-col gap-5">
                            <!-- Pilihan Skema Margin -->
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-bold text-slate-800">Skema Penentuan Keuntungan</label>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <!-- 1. Flat Rupiah -->
                                    <label
                                        :class="[
                                            'p-3.5 rounded-xl border flex flex-col gap-1.5 cursor-pointer transition-all',
                                            marginForm.margin_mode === 'fixed'
                                                ? 'bg-royal-50 border-royal-600 shadow-2xs'
                                                : 'bg-white border-slate-200 hover:bg-slate-50'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-900">Untung Flat (Rp)</span>
                                            <input type="radio" value="fixed" v-model="marginForm.margin_mode" class="text-royal-600 focus:ring-royal-500" />
                                        </div>
                                        <span class="text-[11px] text-slate-500 leading-tight">
                                            Tambah nominal tetap ke setiap produk (mis. +Rp 5.000).
                                        </span>
                                    </label>

                                    <!-- 2. Persentase Markup -->
                                    <label
                                        :class="[
                                            'p-3.5 rounded-xl border flex flex-col gap-1.5 cursor-pointer transition-all',
                                            marginForm.margin_mode === 'percent'
                                                ? 'bg-royal-50 border-royal-600 shadow-2xs'
                                                : 'bg-white border-slate-200 hover:bg-slate-50'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-900">Markup Persen (%)</span>
                                            <input type="radio" value="percent" v-model="marginForm.margin_mode" class="text-royal-600 focus:ring-royal-500" />
                                        </div>
                                        <span class="text-[11px] text-slate-500 leading-tight">
                                            Tambah persentase dari harga modal (mis. +15%).
                                        </span>
                                    </label>

                                    <!-- 3. Rekomendasi Normal Price -->
                                    <label
                                        :class="[
                                            'p-3.5 rounded-xl border flex flex-col gap-1.5 cursor-pointer transition-all',
                                            marginForm.margin_mode === 'normal_price'
                                                ? 'bg-royal-50 border-royal-600 shadow-2xs'
                                                : 'bg-white border-slate-200 hover:bg-slate-50'
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-900">Harga Normal API</span>
                                            <input type="radio" value="normal_price" v-model="marginForm.margin_mode" class="text-royal-600 focus:ring-royal-500" />
                                        </div>
                                        <span class="text-[11px] text-slate-500 leading-tight">
                                            Gunakan normal_price resmi Waroengkuy sebagai harga jual.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Input Nominal Flat -->
                            <div v-if="marginForm.margin_mode === 'fixed'" class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-800">
                                    Nominal Keuntungan Tambahan per Akun (Rupiah)
                                </label>
                                <div class="relative max-w-sm">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                    <input
                                        v-model.number="marginForm.margin_flat"
                                        type="number"
                                        min="1000"
                                        step="1000"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                        required
                                    />
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    Contoh: Modal CapCut Rp 20.000 &rarr; Dijual ke pembeli Rp {{ (20000 + (marginForm.margin_flat || 0)).toLocaleString('id-ID') }} (Untung bersih Rp {{ (marginForm.margin_flat || 0).toLocaleString('id-ID') }})
                                </span>
                            </div>

                            <!-- Input Persentase -->
                            <div v-if="marginForm.margin_mode === 'percent'" class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-800">
                                    Persentase Keuntungan (%)
                                </label>
                                <div class="relative max-w-sm">
                                    <input
                                        v-model.number="marginForm.margin_percent"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                        required
                                    />
                                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-slate-400">%</span>
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    Contoh: Modal CapCut Rp 20.000 &rarr; Dijual ke pembeli Rp {{ Math.round(20000 * (1 + (marginForm.margin_percent || 0)/100)).toLocaleString('id-ID') }}
                                </span>
                            </div>

                            <!-- Pengaturan Tampilan Stok Habis -->
                            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                                <label class="text-xs font-bold text-slate-800">Pengaturan Tampilan Stok</label>
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="marginForm.hide_out_of_stock"
                                        class="mt-0.5 rounded text-royal-600 focus:ring-royal-500"
                                    />
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-900">Sembunyikan Otomatis Produk yang Stoknya Habis (0)</span>
                                        <span class="text-[11px] text-slate-500 leading-tight mt-0.5">
                                            Jika dicentang, varian produk dengan stok 0 dari API Waroengkuy tidak akan muncul sama sekali di katalog depan pembeli.
                                        </span>
                                    </div>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    :disabled="savingSettings"
                                    class="px-6 py-2.5 rounded-xl bg-royal-700 hover:bg-royal-800 text-white text-xs font-bold shadow-md shadow-royal-950/20 transition-all cursor-pointer disabled:opacity-60 flex items-center gap-2"
                                >
                                    <svg v-if="savingSettings" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>{{ savingSettings ? 'Menyimpan...' : 'Simpan Perubahan Margin' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </main>
        </div>

        <!-- MODAL DETAIL PESANAN -->
        <div
            v-if="showDetailModal && selectedOrder"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200/80 flex flex-col gap-4"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Detail Pesanan</h3>
                        <p class="text-xs text-slate-400 font-mono">{{ selectedOrder.id }} • {{ selectedOrder.date }}</p>
                    </div>
                    <button
                        type="button"
                        @click="showDetailModal = false"
                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <div class="flex flex-col gap-3 text-xs">
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Nama Pembeli</span>
                            <span class="font-bold text-slate-800 text-sm">{{ selectedOrder.customer }}</span>
                            <span v-if="selectedOrder.email && selectedOrder.email !== '-'" class="text-[11px] text-slate-500 block mt-0.5">{{ selectedOrder.email }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">WhatsApp Pembeli</span>
                            <a
                                v-if="selectedOrder.phone && selectedOrder.phone !== '-'"
                                :href="'https://wa.me/' + (selectedOrder.phone.startsWith('0') ? '62' + selectedOrder.phone.slice(1) : selectedOrder.phone.replace(/[^0-9]/g, ''))"
                                target="_blank"
                                class="font-semibold text-royal-700 hover:underline inline-flex items-center gap-1 mt-0.5"
                            >
                                <span>{{ selectedOrder.phone }}</span>
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1 py-0.2 rounded font-bold">Chat WA</span>
                            </a>
                            <span v-else class="text-slate-400">-</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-1">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Produk</span>
                            <span class="font-semibold text-slate-800">{{ selectedOrder.product }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Tipe / Varian</span>
                            <span class="font-medium text-slate-700">{{ selectedOrder.type || 'Standar' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Total Pembayaran</span>
                            <span class="font-extrabold text-slate-900 text-sm">{{ selectedOrder.price }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Metode Bayar</span>
                            <span class="font-medium text-slate-700">{{ selectedOrder.payment || 'QRIS' }}</span>
                        </div>
                    </div>

                    <div v-if="selectedOrder.accounts && selectedOrder.accounts.length" class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-200">
                        <span class="text-emerald-800 font-bold block text-[10px] mb-1">Data Akun / Lisensi Terbit:</span>
                        <div v-for="(acc, i) in selectedOrder.accounts" :key="i" class="font-mono text-emerald-950 font-semibold text-[11px] whitespace-pre-wrap select-all">
                            {{ acc }}
                        </div>
                    </div>

                    <div v-if="selectedOrder.note" class="p-2 bg-slate-50 rounded-lg border border-slate-100 text-slate-600">
                        <span class="text-slate-400 block text-[10px]">Catatan:</span>
                        <p class="italic text-[11px]">{{ selectedOrder.note }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-xs text-slate-400">Status: <strong class="capitalize text-slate-700">{{ selectedOrder.status }}</strong></span>
                    <div class="flex items-center gap-2">
                        <!-- Tombol Force Sync Status Terkini dari Server API -->
                        <button
                            type="button"
                            :disabled="syncingOrderId === (selectedOrder.id || selectedOrder.ref)"
                            @click="syncOrderStatus(selectedOrder)"
                            class="px-3 py-1.5 rounded-lg bg-royal-50 hover:bg-royal-100 text-royal-700 font-semibold text-xs border border-royal-200 transition-colors cursor-pointer flex items-center gap-1.5 disabled:opacity-60"
                        >
                            <svg :class="['w-3.5 h-3.5', syncingOrderId === (selectedOrder.id || selectedOrder.ref) ? 'animate-spin' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>{{ syncingOrderId === (selectedOrder.id || selectedOrder.ref) ? 'Sinkron...' : 'Cek Status API' }}</span>
                        </button>

                        <!-- Tombol Selesai hanya untuk produk manual toko sendiri -->
                        <button
                            v-if="!selectedOrder.is_api && selectedOrder.status !== 'completed' && selectedOrder.status !== 'success'"
                            type="button"
                            :disabled="updatingOrderId === selectedOrder.id"
                            @click="updateStatus(selectedOrder, 'completed'); showDetailModal = false;"
                            class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs cursor-pointer disabled:opacity-60"
                        >
                            {{ updatingOrderId === selectedOrder.id ? 'Memproses...' : 'Tandai Selesai' }}
                        </button>
                        <button
                            type="button"
                            @click="showDetailModal = false"
                            class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </motion.div>
        </div>

        <!-- MODAL TAMBAH PRODUK -->
        <div
            v-if="showProductModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200/80 flex flex-col gap-4"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-base">Tambah Produk Baru</h3>
                    <button
                        type="button"
                        @click="showProductModal = false"
                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="saveProduct" class="flex flex-col gap-3 text-xs">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                        <input
                            v-model="newProduct.name"
                            type="text"
                            placeholder="Mis: Canva Pro Lifetime, ChatGPT Plus"
                            class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Varian / Durasi</label>
                            <input
                                v-model="newProduct.variant"
                                type="text"
                                placeholder="Mis: 1 Bulan / Full Garansi"
                                class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Kategori</label>
                            <select
                                v-model="newProduct.category"
                                class="w-full px-2.5 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                            >
                                <option value="Aplikasi Premium">Aplikasi Premium</option>
                                <option value="AI (Artificial Intelligence)">AI & Productivity</option>
                                <option value="Streaming & Entertainment">Streaming & Hiburan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                            <input
                                v-model="newProduct.price"
                                type="number"
                                placeholder="Mis: 25000"
                                class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                required
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Harga Normal (Coret)</label>
                            <input
                                v-model="newProduct.originalPrice"
                                type="number"
                                placeholder="Mis: 50000"
                                class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Stok Akun (Kosongkan jika Unlimited)</label>
                            <input
                                v-model="newProduct.stock"
                                type="number"
                                placeholder="Mis: 10 atau kosong"
                                class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">Tipe Pemenuhan</label>
                            <select
                                v-model="newProduct.type"
                                class="w-full px-2.5 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                            >
                                <option value="manual">Manual (Kirim Akun via WA/Email)</option>
                                <option value="auto">Otomatis</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Deskripsi & Ketentuan Garansi (Opsional)</label>
                        <textarea
                            v-model="newProduct.description"
                            rows="2"
                            placeholder="Tuliskan petunjuk login atau ketentuan garansi bagi pembeli..."
                            class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-royal-600"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 mt-2">
                        <button
                            type="button"
                            @click="showProductModal = false"
                            class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="savingProduct"
                            class="px-4 py-2 rounded-lg bg-royal-700 hover:bg-royal-800 text-white font-semibold cursor-pointer disabled:opacity-60 flex items-center gap-1.5"
                        >
                            <span v-if="savingProduct">Menyimpan...</span>
                            <span v-else>Simpan ke Katalog</span>
                        </button>
                    </div>
                </form>
            </motion.div>
        </div>

        <!-- Modal Edit Margin Khusus Per Item -->
        <div
            v-if="showMarginModal && editingProduct"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.95 }"
                :animate="{ opacity: 1, scale: 1 }"
                class="bg-white rounded-2xl max-w-sm w-full p-5 sm:p-6 shadow-2xl border border-slate-100 flex flex-col gap-4 text-xs"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Atur Margin Produk</h3>
                        <p class="text-[11px] text-slate-500 truncate max-w-[220px]">{{ editingProduct.name }}</p>
                    </div>
                    <button
                        type="button"
                        @click="showMarginModal = false"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Info Harga Modal -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 space-y-1.5">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Harga Modal API:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ editingProduct.costPrice }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Harga Jual Saat Ini:</span>
                        <span class="font-bold text-royal-700 font-mono">{{ editingProduct.price }}</span>
                    </div>
                </div>

                <!-- Form Margin -->
                <form @submit.prevent="saveItemMargin" class="space-y-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Margin Untung Bersih (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input
                                v-model="itemMarginInput"
                                type="number"
                                step="500"
                                required
                                placeholder="Misal: 5000"
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-royal-600 focus:bg-white"
                            />
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">
                            *Untung ini otomatis ditambahkan ke harga modal untuk pembeli.
                        </p>
                    </div>

                    <!-- Tombol Cepat Margin -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button
                            v-for="amt in [2000, 3000, 5000, 10000, 15000]"
                            :key="amt"
                            type="button"
                            @click="itemMarginInput = String(amt)"
                            class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-royal-50 hover:text-royal-700 text-slate-600 font-medium text-[10px] transition-colors"
                        >
                            +{{ amt.toLocaleString('id-ID') }}
                        </button>
                    </div>

                    <!-- Footer Action -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 mt-2">
                        <button
                            type="button"
                            @click="showMarginModal = false"
                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="savingItemMargin"
                            class="px-3.5 py-1.5 rounded-lg bg-royal-700 hover:bg-royal-800 text-white font-semibold cursor-pointer disabled:opacity-60 flex items-center gap-1.5 shadow-sm"
                        >
                            <span v-if="savingItemMargin">Menyimpan...</span>
                            <span v-else>Terapkan Margin</span>
                        </button>
                    </div>
                </form>
            </motion.div>
        </div>
    </div>
</template>
