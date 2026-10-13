/**
 * Konfigurasi kontak & bantuan Ideostore
 * 
 * Untuk mengubah nomor WhatsApp admin:
 * Ganti nilai `whatsappNumber` di bawah ini (gunakan format kode negara tanpa '+', misal '628xxxxxxxxxx')
 * atau set variabel environment VITE_WHATSAPP_NUMBER di file .env.
 */
export const contactConfig = {
    whatsappNumber: import.meta.env.VITE_WHATSAPP_NUMBER || '62895391960539',
    defaultMessage: 'Halo Admin Ideostore, saya ingin bertanya tentang produk / pesanan.',
};

