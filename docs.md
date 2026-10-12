# Waroengkuy Reseller API v1

Diperbarui 9 Okt 2026. Dokumentasi web: https://waroengkuy.com/reseller/docs · Markdown: https://waroengkuy.com/reseller/docs.md

API server-ke-server untuk reseller Waroengkuy: cek saldo, daftar produk (harga reseller, stok, foto, deskripsi), beli akun digital pakai saldo deposit (akun langsung ada di respons), isi saldo lewat QRIS, dan mutasi saldo.

## Dasar

- Base URL: `https://waroengkuy.com/api/reseller/v1`
- Auth: header `Authorization: Bearer wrk_…` (atau `X-API-Key: wrk_…`). Buat/cabut key di panel reseller → Pengaturan → API.
- Body POST: JSON (`Content-Type: application/json`); `application/x-www-form-urlencoded` juga diterima.
- Respons selalu JSON dengan `ok` true/false. Gagal → `error` (pesan utk manusia) + `code` (utk program) + field tambahan sesuai kasus.
- Nominal = Rupiah (integer, tanpa desimal). Waktu = ISO-8601 UTC. ID = string berisi angka.
- Batas: 120 baca/menit, 60 order/menit, 10 isi saldo/menit per key; 180 permintaan/menit per IP → HTTP 429 + header `retry-after`.
- Key hanya untuk SERVER — jangan taruh di aplikasi mobile/HTML publik. Isi whitelist IP bila server-mu ber-IP tetap.

## Alur yang disarankan

1. `GET /products` → simpan daftar varian, harga, stok, foto & deskripsi (cache 1–5 menit; jangan dipanggil tiap pembeli).
2. Pembeli bayar ke kamu → `POST /orders` dengan `ref_id` = nomor transaksi di sistemmu → kirim `order.accounts` ke pembeli.
3. Timeout / koneksi putus? Kirim ULANG `POST /orders` dengan `ref_id` yang SAMA (atau `GET /orders?ref_id=…`). Jangan buat ref_id baru.
4. `code: insufficient_balance` → `POST /deposits` (isi saldo QRIS) → cek `GET /deposits/{id}` tiap 5–10 detik sampai `credited: true`.
5. Rekap keuangan → `GET /mutations` (semua uang masuk/keluar saldo).
6. Tanpa saldo? `POST /orders` + `"payment": "qris"` → tampilkan `order.payment.qr_url` ke pembeli → `GET /orders/{id}` tiap 5–10 detik sampai status bukan `pending`. Pembeli batal? `POST /orders/{id}/cancel` (akun yang ditahan langsung dilepas).
7. Produk `type: manual` (undangan/akses diproses admin): kirim `data` (mis. `{"Email": "…"}`) saat order, ATAU setelah lunas status `need_data` → kirim `POST /invites` dengan `claim_code` + `data`.

## ref_id (anti dobel) — WAJIB dibaca

Setiap `POST /orders` WAJIB membawa `ref_id` unik (1–64 karakter: huruf, angka, `. _ : -`). Mengirim ulang dengan ref_id yang sama mengembalikan order yang SAMA (`"duplicate": true`) — saldo tidak terpotong dua kali. Ini cara aman menangani timeout.

## Endpoint

### GET /balance — Cek saldo

Saldo deposit saat ini + identitas reseller pemilik key.

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/balance -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "reseller": { "id": 12, "name": "Toko Budi", "balance": 150000 }
}
```

**Field penting**

- `reseller.balance` — Saldo deposit (Rp) — dipakai POST /orders.

### GET /products — Daftar produk: harga, stok, foto & deskripsi

Satu baris per VARIAN yang bisa dibeli via API (produk otomatis dari stok). Kelompokkan dengan product_id.

- `image` = URL foto persegi 1:1 (WebP) siap unggah ke marketplace/katalog; `image_original` = file asli. Keduanya URL publik — boleh langsung dipakai di tokomu (hotlink) atau diunduh sekali lalu disimpan. Nilai `v` berubah bila foto diganti admin.
- `description` = deskripsi produk; `variant_description` = detail varian. Teks biasa dengan bagian opsional `## Syarat & Ketentuan` dan `## Catatan` (format Markdown ringan).
- `type: manual` = undangan/akses diproses admin pusat (mis. ChatGPT invite): `stock` null (tak terbatas), qty selalu 1, wajib isi `required_fields` (lewat `data` di POST /orders atau POST /invites). Kabar selesai/ditolak: GET /orders/{id}; ditolak → dana otomatis kembali ke saldo.
- `no_warranty: true` = produk TANPA GARANSI (akun bisa backfree/berkendala kapan saja, tanpa penggantian maupun pengembalian dana). POST /orders varian ini WAJIB menyertakan `accept_no_warranty: true` — sampaikan risikonya ke pelangganmu.

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/products -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "items": [
    {
      "variant_id": "8",
      "product_id": "4",
      "product": "Meitu",
      "variant": "7 Hari",
      "category": "Akun Premium",
      "price": 8500,
      "normal_price": 10000,
      "stock": 80,
      "image": "https://waroengkuy.com/api/store-image/4?v=1727600000&sq=1",
      "image_original": "https://waroengkuy.com/api/store-image/4?v=1727600000",
      "description": "Akun Meitu VIP untuk edit foto tanpa watermark.\n\n## Syarat & Ketentuan\n- Jangan ganti password",
      "variant_description": "Masa aktif 7 hari sejak login pertama.",
      "warranty_days": 7,
      "no_warranty": false
    }
  ]
}
```

**Field penting**

- `variant_id` — ID yang dipakai saat membeli (POST /orders).
- `product_id / product / variant` — ID & nama produk, nama varian.
- `category` — Kategori produk (boleh null).
- `price` — Harga RESELLER per akun (yang dipotong dari saldo).
- `normal_price` — Harga toko biasa (utk acuan harga jualmu).
- `stock` — Jumlah akun tersedia sekarang (null = produk manual, tak terbatas).
- `type / required_fields` — auto · manual. Manual: daftar field yang wajib diisi (mis. ["Email"]).
- `image / image_original` — URL foto persegi 1:1 / foto asli; null bila belum ada foto.
- `description / variant_description` — Deskripsi produk / detail varian; null bila kosong.
- `warranty_days / no_warranty` — Garansi varian (hari; 0 = tanpa garansi; null = belum diatur). no_warranty true → POST /orders wajib `accept_no_warranty: true`.

### POST /orders — Beli: pakai saldo (akun langsung di respons) atau QRIS

Saldo: atomik — saldo terpotong, stok diambil, data akun dikembalikan di respons yang sama, atau tidak terjadi apa pun. QRIS (`payment: qris`): order `pending` + `order.payment` (QR) → polling GET /orders/{id}; akun ditahan 30 menit selama menunggu bayar.

**Parameter (body)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `variant_id` | string | number | ya | variant_id dari GET /products. |
| `qty` | integer | tidak | 1–50 akun (default 1). |
| `ref_id` | string | ya | Nomor transaksi unik di sistemmu (anti dobel, lihat ref_id). |
| `max_price` | integer | tidak | Tolak (409 price_changed) bila harga per akun naik di atas angka ini. |
| `payment` | string | tidak | saldo (default) · qris = tanpa saldo, bayar QRIS (maks 10 akun, maks 20 order menunggu bayar). |
| `customer_email` | string | tidak | Produk manual yang cuma minta email: email pelangganmu (pengganti data, label field apa pun). |
| `data` | object | tidak | Produk manual: isian required_fields, mis. {"Email": "pembeli@gmail.com"}. Boleh dikosongkan → kirim belakangan lewat POST /invites. |
| `accept_no_warranty` | boolean | tidak | WAJIB `true` untuk varian `no_warranty` (GET /products): menyetujui risiko produk TANPA GARANSI — akun bisa backfree/berkendala kapan saja, tanpa penggantian maupun pengembalian dana. Tanpa ini → 400 no_warranty_ack_required. |

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/orders \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"variant_id": 8, "qty": 1, "ref_id": "INV-0001", "max_price": 8500}'
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "order": {
    "id": "3656",
    "ref": "WEBWRG755651774121",
    "ref_id": "INV-0001",
    "status": "success",
    "product": "Meitu — 7 Hari",
    "qty": 1,
    "price": 8500,
    "total": 8500,
    "paid_with": "saldo",
    "created_at": "2026-09-24T15:29:49.693Z",
    "accounts": ["Email : contoh@meitu.id\nPassword : rahasia123"],
    "note": "Catatan penggunaan dari admin (bisa kosong)"
  },
  "balance": 141500
}
```

**Field penting**

- `order.id` — ID order di Waroengkuy (pakai utk GET /orders/{id}).
- `order.ref` — Nomor pesanan Waroengkuy (tampil di invoice/WA).
- `order.ref_id` — ref_id milikmu yang dikirim saat membeli.
- `order.status` — success (terkirim / selesai diproses) · processing (lunas, sedang dikirim / diproses admin) · pending (menunggu bayar QRIS) · need_data (manual: lunas, menunggu data undangan) · refunded (manual: ditolak admin, dana kembali ke saldo) · expired · cancelled.
- `order.type` — auto (akun dari stok) · manual (diproses admin pusat: tanpa accounts; note = pesan admin / alasan ditolak).
- `order.price / total` — Harga per akun & total yang dipotong dari saldo.
- `order.paid_with` — saldo (API/web pakai saldo) · qris (QRIS: API payment=qris / belanja web) · site (pesanan web tokomu).
- `order.payment` — Hanya saat pending: { qris, amount (sudah + kode unik), expires_at, pay_url, qr_url } — tampilkan qr_url/qris ke pembeli.
- `order.data / claim_code` — Manual: data undangan yang terkirim; claim_code (WK-XXXXX-XXXXX) muncul saat status need_data → dipakai POST /invites.
- `order.accounts` — Array data akun (satu string per akun, baris dipisah \n). Hanya pada order lunas.
- `order.note` — Catatan penggunaan dari admin utk produk itu (boleh kosong).
- `balance` — Sisa saldo setelah order.
- `duplicate` — true bila ref_id sudah pernah dipakai — order lama dikembalikan, saldo tidak dipotong lagi.

**Contoh gagal**

- Saldo kurang (HTTP 402):

```json
{ "ok": false, "error": "Saldo tidak cukup: perlu Rp8.500, saldo Rp2.000.", "code": "insufficient_balance", "balance": 2000, "total": 8500 }
```

- Stok kurang (HTTP 409):

```json
{ "ok": false, "error": "Stok tidak cukup (tersedia 0).", "code": "out_of_stock", "stock": 0 }
```

- Harga naik di atas max_price (HTTP 409):

```json
{ "ok": false, "error": "Harga sekarang Rp9.000, di atas max_price Rp8.500.", "code": "price_changed", "price": 9000 }
```

- ref_id tidak valid (HTTP 400):

```json
{ "ok": false, "error": "ref_id wajib: 1–64 karakter (huruf, angka, . _ : -), unik per order.", "code": "invalid_request" }
```

- Data manual salah (HTTP 400):

```json
{ "ok": false, "error": "Email harus email yang valid.", "code": "invalid_request", "required_fields": ["Email"] }
```

- Produk tanpa garansi belum disetujui (HTTP 400):

```json
{ "ok": false, "error": "Produk TANPA GARANSI (ChatGPT — Invite 1 Bulan) — centang persetujuan risiko dulu: …", "code": "no_warranty_ack_required", "noWarranty": ["46"] }
```

- Terlalu banyak QRIS menunggu (HTTP 429):

```json
{ "ok": false, "error": "Masih ada 20 order QRIS menunggu bayar — bayar atau tunggu kedaluwarsa dulu.", "code": "too_many_pending" }
```

- Terlalu sering batal (HTTP 429):

```json
{ "ok": false, "error": "Terlalu banyak order QRIS dibatalkan — coba lagi dalam 1 jam.", "code": "too_many_cancels" }
```

### POST /invites — Kirim data undangan (produk manual yang sudah lunas)

Order manual yang lunas TANPA data berstatus `need_data` dan membawa `claim_code`. Kirim kode itu + data undangan → order masuk antrean proses admin pusat (status `processing`). Kode berlaku sekali.

**Parameter (body)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `claim_code` | string | ya | order.claim_code dari GET /orders/{id} (format WK-XXXXX-XXXXX). |
| `data` | object | ya | Isian required_fields produk, mis. {"Email": "pembeli@gmail.com"}. |

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/invites \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"claim_code": "WK-7KQ2M-PX9TA", "data": {"Email": "pembeli@gmail.com"}}'
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "order": {
    "id": "3701", "ref": "WEBWRG812233445566", "ref_id": "INV-0002", "status": "processing",
    "product": "ChatGPT — Invite 1 Bulan - No Garansi", "qty": 1, "price": 50000, "total": 50000,
    "paid_with": "qris", "type": "manual", "created_at": "2026-10-02T03:10:00.000Z",
    "accounts": [], "note": "", "data": { "Email": "pembeli@gmail.com" }
  }
}
```

**Contoh gagal**

- Kode salah / sudah dipakai (HTTP 404):

```json
{ "ok": false, "error": "Kode klaim tidak ditemukan atau sudah dipakai.", "code": "invalid_claim" }
```

- Data kurang (HTTP 400):

```json
{ "ok": false, "error": "Isi dulu: Email.", "code": "invalid_request", "required_fields": ["Email"] }
```

### GET /orders/{id}  ·  /orders?ref_id=… — Lihat ulang satu order + akunnya

Ambil kembali order (termasuk data akun) memakai id Waroengkuy atau ref_id milikmu. Bentuk `order` sama dengan POST /orders.

**Parameter (query)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `id` | path | tidak | order.id dari respons beli. |
| `ref_id` | query | tidak | ref_id milikmu (alternatif id). |

**Contoh request**

```bash
curl "https://waroengkuy.com/api/reseller/v1/orders?ref_id=INV-0001" -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "order": {
    "id": "3656",
    "ref": "WEBWRG755651774121",
    "ref_id": "INV-0001",
    "status": "success",
    "product": "Meitu — 7 Hari",
    "qty": 1,
    "price": 8500,
    "total": 8500,
    "paid_with": "saldo",
    "created_at": "2026-09-24T15:29:49.693Z",
    "accounts": ["Email : contoh@meitu.id\nPassword : rahasia123"],
    "note": "Catatan penggunaan dari admin (bisa kosong)"
  }
}
```

**Contoh gagal**

- Tidak ditemukan (HTTP 404):

```json
{ "ok": false, "error": "Order tidak ditemukan.", "code": "not_found" }
```

### POST /orders/{id}/cancel — Batalkan order QRIS yang belum dibayar

Membatalkan tagihan QRIS (payment: qris) yang masih `pending` dan melepas akun yang ditahan. Dicek ke gateway dulu: kalau ternyata pembeli SUDAH membayar, order tidak dibatalkan — respons berisi order yang sudah lunas (`cancelled: false`).

- Hanya order QRIS milikmu. Order pakai saldo (langsung lunas) & pesanan web toko tidak bisa dibatalkan lewat sini. Tanpa dibatalkan, order QRIS kedaluwarsa sendiri setelah 30 menit.

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/orders/3702/cancel -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "cancelled": true,
  "order": { "id": "3702", "ref": "WEBWRG812233445599", "ref_id": "INV-0003", "status": "cancelled", "product": "Meitu — 7 Hari", "qty": 1, "price": 8500, "total": 8500, "paid_with": "qris", "type": "auto", "created_at": "2026-10-02T04:00:00.000Z", "accounts": [], "note": "" }
}
```

**Field penting**

- `cancelled` — true = dibatalkan. false = ternyata sudah dibayar (lihat order.status).

**Contoh gagal**

- Sudah lunas / kedaluwarsa (HTTP 409):

```json
{ "ok": false, "error": "Order berstatus paid — hanya order yang menunggu bayar yang bisa dibatalkan.", "code": "not_cancellable", "order": { "id": "3702", "status": "success" } }
```

- Gateway sibuk (HTTP 502):

```json
{ "ok": false, "error": "Gateway pembayaran tak terjangkau — coba lagi sebentar (order tetap aman).", "code": "gateway_error" }
```

### GET /orders — Riwayat order

Terbaru dulu, TANPA data akun (pakai GET /orders/{id} utk akunnya). Termasuk pesanan web tokomu (`paid_with: site`).

**Parameter (query)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `limit` | integer | tidak | 1–100 (default 20). |
| `before_id` | string | tidak | Halaman berikut = nilai next_before_id dari respons sebelumnya. |

**Contoh request**

```bash
curl "https://waroengkuy.com/api/reseller/v1/orders?limit=20" -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "orders": [
    {
      "id": "3656", "ref": "WEBWRG755651774121", "ref_id": "INV-0001", "status": "success",
      "product": "Meitu — 7 Hari", "qty": 1, "price": 8500, "total": 8500,
      "paid_with": "saldo", "created_at": "2026-09-24T15:29:49.693Z"
    }
  ],
  "next_before_id": null
}
```

**Field penting**

- `next_before_id` — null = tidak ada halaman berikutnya.
- `fee` — Hanya pesanan web toko: biaya pemrosesan/platform.

### POST /deposits — Isi saldo lewat QRIS

Membuat tagihan QRIS. Bayar PERSIS `amount` (sudah termasuk kode unik kecil yang ikut masuk saldo). Berlaku 30 menit; maks 3 QRIS aktif.

- Tampilkan gambar `qr_url` (PNG) ke dirimu/pembayar, atau buka `pay_url`. Bisa juga render sendiri dari string `qris`.
- Bila ada promo bonus isi saldo, bonus masuk otomatis saat lunas (terlihat di GET /mutations sebagai `bonus`).

**Parameter (body)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `amount` | integer | ya | Rp10.000 – Rp10.000.000. |

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/deposits \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"amount": 100000}'
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "deposit": {
    "id": "3701", "status": "pending", "amount": 100137, "base_amount": 100000,
    "qris": "00020101021226…", "expires_at": "2026-09-24T16:10:00Z",
    "pay_url": "https://waroengpay.com/pay/WRG-…", "qr_url": "https://waroengpay.com/pay/WRG-…/qr.png"
  }
}
```

**Contoh gagal**

- Sudah 3 QRIS aktif (HTTP 429):

```json
{ "ok": false, "error": "Masih ada 3 QRIS isi saldo aktif — bayar atau tunggu kedaluwarsa dulu.", "code": "too_many_pending" }
```

### GET /deposits/{id} — Status isi saldo

Saldo masuk otomatis ±1 menit setelah bayar; `credited: true` = sudah masuk. Cek tiap 5–10 detik, jangan lebih cepat.

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/deposits/3701 -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "deposit": {
    "id": "3701", "status": "paid", "amount": 100137, "base_amount": 100000,
    "credited": true, "expires_at": "2026-09-24T16:10:00Z", "paid_at": "2026-09-24T15:42:11Z"
  },
  "balance": 250137
}
```

**Field penting**

- `deposit.status` — pending · paid · expired · cancelled.
- `deposit.credited` — true = sudah masuk saldo (termasuk bonus bila ada).

### GET /mutations — Mutasi saldo

Semua uang masuk/keluar saldo, terbaru dulu. Paginasi sama dengan GET /orders.

**Parameter (query)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `limit` | integer | tidak | 1–100 (default 20). |
| `before_id` | string | tidak | next_before_id dari halaman sebelumnya. |

**Contoh request**

```bash
curl "https://waroengkuy.com/api/reseller/v1/mutations?limit=20" -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "mutations": [
    { "id": "88", "kind": "order", "amount": -8500, "balance_after": 141500, "payment_id": "3656",
      "note": "Meitu — 7 Hari · WEBWRG755651774121", "created_at": "2026-09-24T15:29:49.693Z" }
  ],
  "next_before_id": null
}
```

**Field penting**

- `kind` — topup (isi saldo) · bonus (bonus isi saldo) · order (pembelian) · refund (pengembalian) · late (modal ditahan ulang) · adjust (penyesuaian admin).
- `amount` — Bertanda: + masuk, − keluar.

### GET /coupons — Daftar kupon web toko

Semua kupon web tokomu + pemakaian. Potongan kupon diambil dari marginmu (bukan dari pusat).

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/coupons -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "coupons": [
    { "id": "41", "code": "HEMAT10", "kind": "pct", "value": 10, "max_disc": 5000, "min_order": 20000, "quota": 100, "per_wa": 1,
      "product_ids": [], "starts_at": null, "ends_at": "2026-10-31T16:59:59Z", "enabled": true, "auto_after": false,
      "used": 12, "pending": 1, "disc_total": 41500, "revenue": 398000, "text": "Diskon 10% maks Rp5.000 · min Rp20.000" }
  ],
  "max": 30
}
```

### POST /coupons — Buat kupon

Membuat kupon baru untuk web tokomu. Kode unik (huruf/angka 3–20, otomatis huruf besar).

**Parameter (body)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `code` | string | ya | 3–20 huruf/angka tanpa spasi. |
| `kind` | string | ya | `pct` (persen) atau `amount` (potongan Rupiah). |
| `value` | integer | ya | pct: 1–90 · amount: Rp100–Rp1.000.000. |
| `max_disc` | integer | tidak | Batas potongan untuk pct (0 = tanpa batas). |
| `min_order` | integer | tidak | Minimal belanja (Rp). |
| `quota` | integer | tidak | Total pemakaian maksimal (0 = tanpa batas). |
| `per_wa` | integer | tidak | Maks pemakaian per nomor WA pembeli (default 1, 0 = bebas). |
| `product_ids` | string[] | tidak | Batasi ke produk tertentu (id dari GET /products). Kosong = semua. |
| `starts_at / ends_at` | ISO-8601 | tidak | Masa berlaku (opsional). |
| `enabled` | boolean | tidak | Default true. |

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/coupons \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"code": "HEMAT10", "kind": "pct", "value": 10, "max_disc": 5000, "min_order": 20000, "quota": 100}'
```

**Contoh respons (200)**

```json
{ "ok": true, "id": "41" }
```

**Contoh gagal**

- Kode sudah dipakai (HTTP 409):

```json
{ "ok": false, "error": "Kode HEMAT10 sudah dipakai kupon lain." }
```

- Nilai tidak valid (HTTP 400):

```json
{ "ok": false, "error": "Diskon 1–90%." }
```

### PUT /coupons/{id} — Ubah kupon

Mengganti isi kupon. Kirim objek LENGKAP (field yang tidak dikirim kembali ke nilai bawaan, mis. `enabled` = true). Matikan kupon: `"enabled": false`.

**Contoh request**

```bash
curl -X PUT https://waroengkuy.com/api/reseller/v1/coupons/41 \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"code": "HEMAT10", "kind": "pct", "value": 15, "max_disc": 7000, "enabled": true}'
```

**Contoh respons (200)**

```json
{ "ok": true, "id": "41" }
```

### DELETE /coupons/{id} — Hapus kupon

Menghapus kupon. Pesanan lama yang memakai kupon ini tetap tercatat.

**Contoh request**

```bash
curl -X DELETE https://waroengkuy.com/api/reseller/v1/coupons/41 -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{ "ok": true }
```

### GET /store/orders — Pesanan web tokomu

Pesanan pembeli di web tokomu (bukan order API-mu), terbaru dulu, dengan laba per pesanan. Paginasi sama dengan GET /orders.

**Parameter (query)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `limit` | integer | tidak | 1–100 (default 20). |
| `before_id` | string | tidak | id terakhir dari halaman sebelumnya. |

**Contoh request**

```bash
curl "https://waroengkuy.com/api/reseller/v1/store/orders?limit=20" -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "orders": [
    { "id": "5120", "ref": "WEBWRG812345678901", "status": "success", "product": "Netflix — 1 Bulan · Sharing", "qty": 1,
      "sell": 30000, "cost": 25000, "fee": 0, "profit": 5000, "buyer": "62812****7890",
      "created_at": "2026-10-09T08:01:12Z", "paid_at": "2026-10-09T08:02:03Z", "mode": "deposit" }
  ]
}
```

### GET /store/stats — Statistik web toko

Ringkasan penjualan web tokomu per hari: pesanan, omzet, laba, checkout & kunjungan, produk terlaris.

**Parameter (query)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `days` | integer | tidak | 7 · 30 (default) · 90. |

**Contoh request**

```bash
curl "https://waroengkuy.com/api/reseller/v1/store/stats?days=30" -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "stats": {
    "days": 30,
    "totals": { "orders": 42, "revenue": 1260000, "profit": 210000, "checkouts": 61, "views": 1830 },
    "series": [{ "day": "2026-10-09", "orders": 3, "revenue": 90000, "profit": 15000, "checkouts": 4, "views": 77 }],
    "top": [{ "product": "Netflix — 1 Bulan · Sharing", "units": 12, "orders": 12, "revenue": 360000, "profit": 60000 }]
  }
}
```

### GET /own-products — Produk milikmu (stok sendiri)

Produk yang kamu jual dengan stok sendiri di web toko, lengkap dengan varian (`variant_id`), harga, stok & status tinjauan.

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/own-products -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "products": [
    { "product_id": "251", "title": "Voucher WiFi B-Link", "review_status": "approved",
      "variants": [{ "variant_id": "903", "label": "30 Hari · 115 GB", "price": 45000, "stock": 8, "used": 31, "manual": false }] }
  ]
}
```

### POST /own-products/stock — Tambah stok produk milikmu

Menambah baris stok (satu akun/kode per baris) ke varian produk milikmu. Maks 500 baris per permintaan; terkirim otomatis ke pembeli.

**Parameter (body)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `variant_id` | string | ya | Dari GET /own-products. |
| `lines` | string[] | ya | Satu akun per elemen (atau `text` berisi baris dipisah newline). |

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/own-products/stock \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"variant_id": "903", "lines": ["KODE-AAA-111", "KODE-BBB-222"]}'
```

**Contoh respons (200)**

```json
{ "ok": true, "added": 2 }
```

### GET /smm/services — Layanan jasa sosmed

Daftar layanan jasa sosmed (followers, likes, views…) dengan harga MODAL-mu per 1.000. Cache 5–15 menit di sisimu.

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/smm/services -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "services": [
    { "service": 20001234, "name": "Instagram Followers Indonesia", "category": "Instagram Followers", "platform": "INSTAGRAM",
      "min": 100, "max": 10000, "refill": true, "unitRate": 18500 }
  ],
  "enabled": true
}
```

**Field penting**

- `unitRate` — Harga modal per 1.000 (Rp). Total = ceil(qty/1000 × unitRate) dibulatkan ke Rp100.

### POST /smm/orders — Pesan jasa sosmed (potong saldo)

Memesan layanan sosmed memakai saldo. `ref_id` mencegah order dobel saat kirim ulang (sama seperti POST /orders).

**Parameter (body)**

| Nama | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `service` | integer | ya | Dari GET /smm/services. |
| `link` | string | ya | Tautan profil/postingan target (akun harus publik). |
| `qty` | integer | ya | Antara min–max layanan. |
| `ref_id` | string | tidak | Nomor transaksi unik di sistemmu. |

**Contoh request**

```bash
curl -X POST https://waroengkuy.com/api/reseller/v1/smm/orders \
  -H "Authorization: Bearer wrk_KEY_KAMU" \
  -H "Content-Type: application/json" \
  -d '{"service": 20001234, "link": "https://instagram.com/akunpembeli", "qty": 1000, "ref_id": "INV-7781"}'
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "order": { "id": "5233", "ref": "WEBWRG…", "product": "Instagram Followers Indonesia", "qty": 1000, "total": 18500,
    "link": "https://instagram.com/akunpembeli", "delivery_status": "ordered", "panel_order": "4129", "message": "" },
  "balance": 131500
}
```

**Field penting**

- `duplicate` — true = ref_id sudah pernah dipakai → order lama dikembalikan, saldo tidak dipotong lagi.

### GET /smm/orders — Riwayat jasa sosmed

30 pesanan jasa sosmed terbaru (via saldo) beserta status panel & pengembalian dana.

**Contoh request**

```bash
curl https://waroengkuy.com/api/reseller/v1/smm/orders -H "Authorization: Bearer wrk_KEY_KAMU"
```

**Contoh respons (200)**

```json
{
  "ok": true,
  "orders": [
    { "id": "5233", "ref": "WEBWRG…", "product": "Instagram Followers Indonesia", "qty": 1000, "total": 18500,
      "link": "https://instagram.com/akunpembeli", "panel_order": "4129", "delivery_status": "ordered", "refunded": 0 }
  ]
}
```

## Kode error

| HTTP | code | Arti |
|---|---|---|
| 400 | `invalid_request` | Parameter salah/kurang (mis. ref_id kosong, qty di luar 1–50, amount di luar batas). |
| 400 | `manual_product` | Produk proses-manual tidak bisa dibeli pakai saldo/API. |
| 400 | `no_warranty_ack_required` | Varian TANPA GARANSI (no_warranty) — kirim ulang dengan accept_no_warranty: true. |
| 401 | `unauthorized` | API key salah, dicabut, atau reseller tidak aktif. |
| 402 | `insufficient_balance` | Saldo kurang — respons memuat balance & total. |
| 403 | `ip_not_allowed` | IP server tidak ada di whitelist. |
| 403 | `buyer_only` | Varian khusus pembeli biasa (tak dijual ke reseller). |
| 404 | `not_found` | Produk/order/endpoint tidak ada. |
| 409 | `out_of_stock` | Stok kurang dari qty — respons memuat stock tersedia. |
| 409 | `price_changed` | Harga sekarang di atas max_price — respons memuat price terbaru. |
| 429 | `rate_limited` | Terlalu cepat — tunggu sesuai header retry-after. |
| 429 | `too_many_pending` | QRIS menunggu bayar sudah penuh (isi saldo: 3 aktif · order: 20 order / 20 akun tertahan). |
| 429 | `too_many_cancels` | Lebih dari 10 order QRIS dibatalkan dalam 1 jam — tunggu sebentar. |
| 502 | `upstream_error / gateway_error` | Gangguan sementara — ulangi (order: dengan ref_id yang sama). |

## Contoh kode

### Node.js (fetch)

```js
const API = "https://waroengkuy.com/api/reseller/v1";
const headers = { Authorization: "Bearer " + process.env.WRK_KEY, "Content-Type": "application/json" };

// 1) Katalog (cache 1–5 menit)
const { items } = await (await fetch(API + "/products", { headers })).json();

// 2) Beli — ref_id = nomor invoice di sistemmu (kirim ulang dgn ref_id sama bila timeout)
const res = await fetch(API + "/orders", {
  method: "POST", headers,
  body: JSON.stringify({ variant_id: items[0].variant_id, qty: 1, ref_id: invoiceNo }),
});
const j = await res.json();
if (j.ok) kirimKePembeli(j.order.accounts);
else if (j.code === "insufficient_balance") alertAdmin("Isi saldo dulu");
```

### PHP (cURL)

```php
$ch = curl_init("https://waroengkuy.com/api/reseller/v1/orders");
curl_setopt_array($ch, [
  CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTPHEADER => ["Authorization: Bearer " . getenv("WRK_KEY"), "Content-Type: application/json"],
  CURLOPT_POSTFIELDS => json_encode(["variant_id" => 8, "qty" => 1, "ref_id" => $invoiceNo]),
]);
$j = json_decode(curl_exec($ch), true);
if ($j["ok"] ?? false) { $akun = $j["order"]["accounts"]; }
```

### Python (requests)

```python
import os, requests
API = "https://waroengkuy.com/api/reseller/v1"
H = {"Authorization": f"Bearer {os.environ['WRK_KEY']}"}

produk = requests.get(f"{API}/products", headers=H, timeout=30).json()["items"]
r = requests.post(f"{API}/orders", headers=H, timeout=30,
                  json={"variant_id": produk[0]["variant_id"], "qty": 1, "ref_id": invoice_no}).json()
if r["ok"]:
    kirim_ke_pembeli(r["order"]["accounts"])
elif r.get("code") == "insufficient_balance":
    print("Isi saldo dulu")
```