<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { motion } from 'motion-v';

const props = defineProps({
    liveProducts: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const activeFilter = ref('all');

// Modal Detail / Checkout Pesanan Otomatis
const selectedProduct = ref(null);
const showModal = ref(false);

// State Checkout Form
const orderQty = ref(1);
const customerEmail = ref('');
const customerWhatsapp = ref('');
const customFields = ref({});
const acceptNoWarranty = ref(false);
const orderLoading = ref(false);
const orderError = ref('');
const activeOrder = ref(null); // Data order hasil /api/create-order

let pollInterval = null;

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

onBeforeUnmount(() => {
    stopPolling();
});

const openDetail = (prod) => {
    selectedProduct.value = prod;
    orderQty.value = 1;
    customerEmail.value = '';
    customerWhatsapp.value = '';
    customFields.value = {};
    acceptNoWarranty.value = false;
    orderLoading.value = false;
    orderError.value = '';
    activeOrder.value = null;
    stopPolling();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    stopPolling();
};

// Polling status QRIS hingga berstatus success / processing
const startPolling = (orderId) => {
    stopPolling();
    pollInterval = setInterval(async () => {
        try {
            const res = await fetch(`/api/order/${orderId}`);
            const data = await res.json();
            if (data.ok && data.order) {
                activeOrder.value = data.order;
                if (data.order.status === 'success' || data.order.status === 'processing') {
                    stopPolling();
                }
            }
        } catch (e) {
            console.error('Polling error:', e);
        }
    }, 4000);
};


// Handle Checkout Otomatis
const handleDirectCheckout = async () => {
    if (!selectedProduct.value) return;
    
    // Validasi nomor WhatsApp
    const cleanWa = customerWhatsapp.value.replace(/[^0-9]/g, '');
    if (!cleanWa || cleanWa.length < 9) {
        orderError.value = 'Mohon masukkan nomor WhatsApp aktif yang valid (mis. 08123456789).';
        return;
    }

    // Validasi email jika produk manual minta email
    const needsEmail = selectedProduct.value.raw?.required_fields?.includes('Email') || selectedProduct.value.type === 'manual';
    if (needsEmail && !customerEmail.value.trim()) {
        orderError.value = 'Mohon masukkan email aktif kamu untuk menerima pesanan.';
        return;
    }

    // Validasi custom required_fields dari Waroengkuy API
    if (selectedProduct.value.raw?.required_fields) {
        for (const field of selectedProduct.value.raw.required_fields) {
            if (field !== 'Email' && field !== 'WhatsApp' && !customFields.value[field]?.trim()) {
                orderError.value = `Kolom ${field} wajib diisi untuk aktivasi produk ini.`;
                return;
            }
        }
    }

    if (selectedProduct.value.noWarranty && !acceptNoWarranty.value) {
        orderError.value = 'Kamu harus menyetujui ketentuan produk tanpa garansi.';
        return;
    }

    orderLoading.value = true;
    orderError.value = '';

    try {
        const res = await fetch('/api/create-order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                variant_id: selectedProduct.value.id,
                qty: orderQty.value,
                email: customerEmail.value.trim(),
                whatsapp: cleanWa,
                custom_fields: customFields.value,
                accept_no_warranty: acceptNoWarranty.value
            })
        });

        const data = await res.json();

        if (data.ok && data.order) {
            activeOrder.value = data.order;
            // Jika order masih pending QRIS, mulai polling status bayar
            if (data.order.status === 'pending' && data.order.id) {
                startPolling(data.order.id);
            }
        } else {
            orderError.value = data.error || 'Gagal memproses pesanan otomatis. Coba lagi sebentar.';
        }
    } catch (err) {
        orderError.value = 'Terjadi gangguan jaringan saat memproses pesanan.';
    } finally {
        orderLoading.value = false;
    }
};

// Quick Filter Chips yang dinamis menghitung dari liveProducts
const filterChips = computed(() => {
    const list = props.liveProducts || [];
    return [
        { 
            id: 'tersedia', 
            label: 'Stok Ready', 
            count: list.filter(p => p.stock === null || p.stock > 0).length, 
            iconType: 'sparkles' 
        },
        { 
            id: 'otomatis', 
            label: 'Otomatis', 
            count: list.filter(p => p.type === 'auto').length, 
            iconType: 'flame' 
        },
        { 
            id: 'hemat', 
            label: 'Hemat / Diskon', 
            count: list.filter(p => p.normal_price && p.normal_price > p.price).length, 
            iconType: 'tag' 
        },
        { 
            id: 'garansi', 
            label: 'Ada Garansi', 
            count: list.filter(p => p.warranty_days || !p.no_warranty).length, 
            iconType: 'star' 
        },
    ];
});

// Format Rupiah Helper
const formatRupiah = (val) => {
    if (val === null || val === undefined) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
};

const getCategoryColor = (cat) => {
    const c = (cat || '').toLowerCase();
    if (c.includes('ai')) return 'from-purple-900 to-indigo-950';
    if (c.includes('aplikasi')) return 'from-slate-900 to-slate-800';
    return 'from-blue-900 to-slate-900';
};

// Sorting Mode: 'default' | 'cheapest' | 'expensive' | 'name'
const sortBy = ref('default');

// Copy Text Helper
const copySuccess = ref(false);
const copyText = (txt) => {
    if (!txt) return;
    navigator.clipboard.writeText(txt);
    copySuccess.value = true;
    setTimeout(() => {
        copySuccess.value = false;
    }, 2000);
};

// Computed grouped categories dari Live Products API
const filteredCategories = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    const rawItems = props.liveProducts || [];

    let mapped = rawItems.map((item) => {
        const hasDiscount = item.normal_price && item.normal_price > item.price;
        const discountPct = hasDiscount 
            ? `HEMAT ${Math.round((1 - item.price / item.normal_price) * 100)}%` 
            : null;

        const isStockReady = item.stock === null || item.stock > 0;
        const stockLabel = item.stock !== null ? `Sisa ${item.stock}` : 'Ready';

        return {
            id: item.variant_id,
            productId: item.product_id,
            name: `${item.product} - ${item.variant}`,
            baseProduct: item.product,
            variant: item.variant,
            category: item.category || 'Aplikasi Premium',
            price: formatRupiah(item.price),
            rawPrice: item.price,
            originalPrice: hasDiscount ? formatRupiah(item.normal_price) : null,
            discount: discountPct,
            stockText: stockLabel,
            stock: item.stock,
            isStockReady,
            type: item.type,
            isAuto: item.type === 'auto',
            image: item.image || item.image_original || null,
            description: item.description || '',
            warrantyDays: item.warranty_days,
            noWarranty: item.no_warranty,
            color: getCategoryColor(item.category),
            raw: item
        };
    });

    if (q) {
        mapped = mapped.filter((p) => 
            p.name.toLowerCase().includes(q) || 
            p.baseProduct.toLowerCase().includes(q) ||
            p.category.toLowerCase().includes(q)
        );
    }

    if (activeFilter.value === 'tersedia') {
        mapped = mapped.filter((p) => p.isStockReady);
    } else if (activeFilter.value === 'otomatis') {
        mapped = mapped.filter((p) => p.isAuto);
    } else if (activeFilter.value === 'hemat') {
        mapped = mapped.filter((p) => p.discount);
    } else if (activeFilter.value === 'garansi') {
        mapped = mapped.filter((p) => p.warrantyDays || !p.noWarranty);
    }

    // Urutkan (Sorting)
    if (sortBy.value === 'cheapest') {
        mapped.sort((a, b) => a.rawPrice - b.rawPrice);
    } else if (sortBy.value === 'expensive') {
        mapped.sort((a, b) => b.rawPrice - a.rawPrice);
    } else if (sortBy.value === 'name') {
        mapped.sort((a, b) => a.name.localeCompare(b.name));
    }

    const groupMap = new Map();
    for (const item of mapped) {
        const catName = item.category || 'Aplikasi Premium';
        if (!groupMap.has(catName)) {
            groupMap.set(catName, []);
        }
        groupMap.get(catName).push(item);
    }

    const result = [];
    for (const [title, products] of groupMap.entries()) {
        result.push({
            id: title.toLowerCase().replace(/\s+/g, '-'),
            title: title,
            count: products.length,
            filteredProducts: products
        });
    }

    return result;
});
</script>

<template>
    <section class="mt-8 sm:mt-12 w-full flex flex-col gap-5 sm:gap-6">
        <!-- Search & Filter Bar -->
        <div class="flex flex-col gap-3">
            <!-- Search Input -->
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4 sm:w-4.5 sm:h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari produk live (mis. CapCut, Gemini, Mail)..."
                    class="w-full pl-10 sm:pl-11 pr-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-white border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-royal-600 focus:border-royal-600 shadow-2xs transition-all"
                />
            </div>

            <!-- Filter Badges -->
            <div class="flex flex-wrap items-center justify-between gap-2.5">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <button
                        v-for="chip in filterChips"
                        :key="chip.id"
                        type="button"
                        @click="activeFilter = activeFilter === chip.id ? 'all' : chip.id"
                        :class="[
                            'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg sm:rounded-xl text-xs font-medium border transition-all cursor-pointer',
                            activeFilter === chip.id
                                ? 'bg-royal-50 border-royal-300 text-royal-700 font-semibold shadow-2xs'
                                : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 shadow-2xs'
                        ]"
                    >
                        <!-- Sparkles (Stok Ready) -->
                        <svg v-if="chip.iconType === 'sparkles'" class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>

                        <!-- Flame (Otomatis) -->
                        <svg v-else-if="chip.iconType === 'flame'" class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.527.82-1.173 1.55-1.874 2.18-.705.63-1.455 1.18-2.22 1.63a9.42 9.42 0 00-2.02 1.77C3.12 9.77 2.5 11.23 2.5 12.8c0 5.08 4.25 9.2 9.5 9.2 5.08 0 9.2-4.25 9.2-9.5 0-3.32-1.74-6.32-4.43-7.97-.61-.37-1.12-.9-1.51-1.52-.39-.62-.64-1.3-.7-2.01a1 1 0 00-.76-.85zM12 18a5 5 0 100-10 5 5 0 000 10z" clip-rule="evenodd" />
                        </svg>

                        <!-- Tag (Hemat) -->
                        <svg v-else-if="chip.iconType === 'tag'" class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V5a2 2 0 012-2z" />
                        </svg>

                        <!-- Star (Garansi) -->
                        <svg v-else class="w-3.5 h-3.5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>

                        <span>{{ chip.label }}</span>
                        <span class="text-[10px] px-1 py-0.2 rounded-full font-semibold" :class="activeFilter === chip.id ? 'bg-royal-200 text-royal-950' : 'bg-slate-200 text-slate-800'">{{ chip.count }}</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Dropdown Urutkan -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <label for="sortProductSelect" class="hidden sm:inline font-medium">Urutkan:</label>
                        <select
                            id="sortProductSelect"
                            v-model="sortBy"
                            aria-label="Urutkan produk berdasarkan"
                            class="px-2.5 py-1.5 rounded-lg sm:rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-royal-600 shadow-2xs cursor-pointer"
                        >
                            <option value="default">Rekomendasi</option>
                            <option value="cheapest">Harga Termurah</option>
                            <option value="expensive">Harga Tertinggi</option>
                            <option value="name">Nama A-Z</option>
                        </select>
                    </div>

                    <span class="text-xs text-slate-400 font-medium hidden md:inline">
                        • {{ props.liveProducts?.length || 0 }} Varian
                    </span>
                </div>
            </div>
        </div>

        <!-- Empty State jika tidak ada produk -->
        <div v-if="filteredCategories.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center flex flex-col items-center justify-center gap-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <p class="text-sm font-bold text-slate-700">Tidak ada produk yang cocok</p>
            <p class="text-xs text-slate-400">Coba gunakan kata kunci pencarian yang lain.</p>
        </div>

        <!-- Product Sections by Live Category -->
        <div v-for="category in filteredCategories" :key="category.id" class="flex flex-col gap-3 pt-2">
            <!-- Category Header -->
            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                    {{ category.title }}
                </h3>
                <span class="text-[11px] font-bold text-slate-700 bg-slate-200 px-1.5 py-0.5 rounded-md">
                    {{ category.filteredProducts.length }}
                </span>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 sm:gap-3">
                <motion.div
                    v-for="product in category.filteredProducts"
                    :key="product.id"
                    :while-hover="{ y: -3, transition: { duration: 0.18 } }"
                    @click="openDetail(product)"
                    class="group bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-royal-200 transition-all flex flex-col overflow-hidden cursor-pointer p-2.5 sm:p-3"
                >
                    <!-- Brand Banner / Image -->
                    <div class="relative w-full aspect-[4/3] rounded-lg sm:rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-100/80">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div v-else :class="['w-full h-full bg-gradient-to-tr flex items-center justify-center text-white font-black text-sm uppercase', product.color]">
                            {{ product.baseProduct.substring(0, 3) }}
                        </div>

                        <!-- Badge Pengiriman / Otomatis -->
                        <div
                            class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs uppercase tracking-wider text-white"
                            :class="product.isAuto ? 'bg-emerald-700' : 'bg-royal-700'"
                        >
                            {{ product.isAuto ? 'Otomatis' : 'Manual' }}
                        </div>

                        <!-- Stock Badge -->
                        <div
                            v-if="!product.isStockReady"
                            class="absolute inset-0 bg-slate-900/60 backdrop-blur-[1px] flex items-center justify-center text-white font-bold text-xs"
                        >
                            Habis
                        </div>
                    </div>

                    <!-- Details Area -->
                    <div class="flex flex-col flex-1 justify-between pt-2">
                        <div>
                            <!-- Product Title -->
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 leading-snug">
                                {{ product.name }}
                            </h4>

                            <!-- Badges / Tags -->
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                <span
                                    v-if="product.discount"
                                    class="text-[9px] font-bold text-emerald-800 bg-emerald-100 px-1 py-0.2 rounded"
                                >
                                    {{ product.discount }}
                                </span>
                                <span
                                    v-if="product.warrantyDays"
                                    class="text-[9px] font-semibold text-royal-900 bg-royal-100 px-1 py-0.2 rounded"
                                >
                                    Garansi {{ product.warrantyDays }}h
                                </span>
                            </div>

                            <!-- Price Display -->
                            <div class="mt-1 flex items-baseline gap-1.5 flex-wrap">
                                <span class="text-xs sm:text-sm font-extrabold text-slate-900">
                                    {{ product.price }}
                                </span>
                                <span
                                    v-if="product.originalPrice"
                                    class="text-[10px] text-slate-500 line-through"
                                >
                                    {{ product.originalPrice }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-600">
                            <span :class="product.isStockReady ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                                {{ product.stockText }}
                            </span>
                            <span class="text-royal-800 font-bold group-hover:underline">Beli &rarr;</span>
                        </div>
                    </div>
                </motion.div>
            </div>
        </div>

        <!-- MODAL CHECKOUT OTOMATIS WEB (QRIS INSTAN & REALTIME AKTIVASI) -->
        <div v-if="showModal && selectedProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 border border-slate-100 shadow-2xl flex flex-col gap-4 max-h-[92vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="selectedProduct.image"
                            :src="selectedProduct.image"
                            :alt="selectedProduct.name"
                            class="w-12 h-12 rounded-xl object-cover border border-slate-100 shadow-2xs shrink-0"
                        />
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-snug">{{ selectedProduct.name }}</h3>
                            <span class="text-xs text-royal-700 font-semibold">{{ selectedProduct.category }}</span>
                        </div>
                    </div>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- TAMPILAN 1: FORM PEMBELIAN (Sebelum Generate QRIS) -->
                <div v-if="!activeOrder" class="flex flex-col gap-4">
                    <!-- Price & Stock Info Box -->
                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Total Harga</span>
                            <span class="text-lg font-extrabold text-slate-900">
                                {{ formatRupiah(selectedProduct.rawPrice * orderQty) }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 block">Metode Pengiriman</span>
                            <span class="text-xs font-bold" :class="selectedProduct.isAuto ? 'text-emerald-600' : 'text-royal-600'">
                                {{ selectedProduct.isAuto ? '⚡ Otomatis (Instan)' : '🕒 Proses Manual Admin' }}
                            </span>
                        </div>
                    </div>

                    <!-- Input Identitas Pembeli & Kontak -->
                    <div class="flex flex-col gap-3">
                        <!-- Nomor WhatsApp (Wajib untuk notifikasi & pelacakan pesanan) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-semibold text-slate-400">
                                    +62 / 08
                                </span>
                                <input
                                    v-model="customerWhatsapp"
                                    type="tel"
                                    placeholder="81234567890"
                                    class="w-full pl-20 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                    required
                                />
                            </div>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">
                                Digunakan untuk konfirmasi pesanan dan pengecekan di menu Lacak Pesanan.
                            </span>
                        </div>

                        <!-- Email Pembeli -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">
                                Email Kamu <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="customerEmail"
                                type="email"
                                placeholder="nama@email.com"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                required
                            />
                        </div>

                        <!-- Dynamic Required Fields dari Waroengkuy API (jika ada permintaan data khusus selain email) -->
                        <template v-if="selectedProduct.raw?.required_fields">
                            <div
                                v-for="field in selectedProduct.raw.required_fields.filter(f => f !== 'Email' && f !== 'WhatsApp')"
                                :key="field"
                            >
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    {{ field }} <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="customFields[field]"
                                    type="text"
                                    :placeholder="'Masukkan ' + field"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-royal-600"
                                    required
                                />
                            </div>
                        </template>

                        <!-- Jumlah Akun (Qty) -->

                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <span class="text-xs font-semibold text-slate-700">Jumlah Akun</span>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="orderQty = Math.max(1, orderQty - 1)"
                                    class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center cursor-pointer shadow-2xs hover:bg-slate-100"
                                >
                                    -
                                </button>
                                <span class="w-8 text-center text-xs font-bold text-slate-900">{{ orderQty }}</span>
                                <button
                                    type="button"
                                    @click="orderQty = Math.min(10, orderQty + 1)"
                                    class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-sm flex items-center justify-center cursor-pointer shadow-2xs hover:bg-slate-100"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Checkbox Tanpa Garansi jika produk no_warranty -->
                        <div v-if="selectedProduct.noWarranty" class="flex items-start gap-2 bg-amber-50 p-3 rounded-xl border border-amber-200">
                            <input
                                v-model="acceptNoWarranty"
                                type="checkbox"
                                id="noWarrantyCheck"
                                class="mt-0.5 rounded text-amber-600 focus:ring-amber-500"
                            />
                            <label for="noWarrantyCheck" class="text-[11px] text-amber-800 leading-tight cursor-pointer">
                                <strong>Produk Tanpa Garansi</strong>: Saya menyetujui risiko produk tanpa garansi dan tidak dapat direfund.
                            </label>
                        </div>
                    </div>

                    <!-- Deskripsi Produk -->
                    <div v-if="selectedProduct.description" class="text-[11px] text-slate-500 leading-relaxed whitespace-pre-line max-h-36 overflow-y-auto bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                        {{ selectedProduct.description }}
                    </div>

                    <!-- Error Alert -->
                    <div v-if="orderError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ orderError }}</span>
                    </div>

                    <!-- Tombol Aksi Beli Langsung Otomatis -->
                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            :disabled="orderLoading"
                            @click="handleDirectCheckout"
                            class="flex-2 py-2.5 rounded-xl bg-royal-700 hover:bg-royal-800 active:bg-royal-900 text-white text-xs font-bold text-center shadow-md shadow-royal-950/20 transition-all cursor-pointer disabled:opacity-60 flex items-center justify-center gap-2"
                        >
                            <svg v-if="orderLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ orderLoading ? 'Menerbitkan QRIS...' : 'Bayar QRIS Otomatis' }}</span>
                        </button>
                    </div>
                </div>

                <!-- TAMPILAN 2: QRIS PEMBAYARAN & STATUS AKTIVASI -->
                <div v-else class="flex flex-col gap-4">
                    <!-- Status Header -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-mono">No. Pesanan:</span>
                            <span class="text-xs font-bold text-slate-800 font-mono">{{ activeOrder.ref || activeOrder.id }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block">Status:</span>
                            <span
                                :class="[
                                    'text-[11px] font-extrabold uppercase px-2 py-0.5 rounded-full inline-block',
                                    activeOrder.status === 'success' ? 'bg-emerald-100 text-emerald-800' :
                                    activeOrder.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                                    'bg-amber-100 text-amber-800 animate-pulse'
                                ]"
                            >
                                {{ activeOrder.status === 'pending' ? 'Menunggu Bayar' : activeOrder.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Jika Status PENDING (Tampilkan QRIS) -->
                    <div v-if="activeOrder.status === 'pending'" class="flex flex-col items-center justify-center gap-3 p-4 bg-royal-50/50 rounded-2xl border border-royal-100">
                        <span class="text-xs font-bold text-royal-950">Scan QRIS untuk Bayar Otomatis</span>
                        
                        <!-- Gambar QRIS -->
                        <div v-if="activeOrder.payment?.qr_url" class="p-3 bg-white rounded-xl shadow-md border border-slate-200">
                            <img :src="activeOrder.payment.qr_url" alt="QRIS Waroengkuy" class="w-48 h-48 object-contain" />
                        </div>
                        
                        <!-- Nominal Bayar (+ Kode Unik) -->
                        <div class="text-center">
                            <span class="text-[11px] text-slate-500 block">Total Pembayaran:</span>
                            <span class="text-xl font-extrabold text-royal-800">
                                {{ formatRupiah(activeOrder.payment?.amount || activeOrder.total) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Mendukung Gopay, OVO, Dana, ShopeePay, BCA, Mandiri, dll</span>
                        </div>

                        <!-- Petunjuk Live Polling -->
                        <div class="flex items-center gap-1.5 text-[11px] text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Mendeteksi pembayaran otomatis...</span>
                        </div>

                    </div>

                    <!-- Jika Status SUCCESS (Akun / Lisensi Terbit Otomatis) -->
                    <div v-else-if="activeOrder.status === 'success'" class="flex flex-col gap-3 p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200">
                        <div class="flex items-center gap-2 text-emerald-800 font-bold text-sm">
                            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Pembayaran Lunas! Akun Kamu Siap</span>
                        </div>

                        <!-- Data Akun Terkirim -->
                        <div v-if="activeOrder.accounts && activeOrder.accounts.length" class="flex flex-col gap-2">
                            <span class="text-[11px] font-bold text-slate-700">Detail Lisensi / Akun:</span>
                            <div
                                v-for="(acc, i) in activeOrder.accounts"
                                :key="i"
                                class="bg-white p-3 rounded-xl border border-slate-200 text-xs font-mono text-slate-800 whitespace-pre-wrap relative group"
                            >
                                {{ acc }}
                                <button
                                    type="button"
                                    @click="copyText(acc)"
                                    class="absolute top-2 right-2 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-sans font-semibold text-slate-600 cursor-pointer"
                                >
                                    Salin
                                </button>
                            </div>
                        </div>

                        <p v-if="activeOrder.note" class="text-[11px] text-slate-600 bg-white/70 p-2.5 rounded-lg border border-slate-200">
                            <strong>Catatan Admin:</strong> {{ activeOrder.note }}
                        </p>
                    </div>

                    <!-- Jika Status PROCESSING (Manual atau Undangan) -->
                    <div v-else class="p-4 bg-blue-50 rounded-2xl border border-blue-200 text-xs text-blue-900 flex flex-col gap-2">
                        <span class="font-bold text-sm">Pembayaran Diterima!</span>
                        <p class="text-blue-700 text-xs leading-relaxed">
                            Pesanan kamu sedang diproses oleh sistem admin pusat. Kamu juga dapat melacak statusnya kapan saja di menu <strong>Cek Pesanan</strong> di halaman utama.
                        </p>
                    </div>

                    <!-- Tombol Selesai -->
                    <button
                        type="button"
                        @click="closeModal"
                        class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold text-center cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
