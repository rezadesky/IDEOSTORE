<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - {{ $order->order_id }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <!-- Header Success -->
        <tr>
            <td style="background: linear-gradient(135deg, #059669 0%, #064e3b 100%); padding: 32px 28px; text-align: center;">
                <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; border-radius: 50%; background-color: rgba(255,255,255,0.2); font-size: 22px; color: #ffffff; margin-bottom: 10px;">✓</div>
                <h1 style="color: #ffffff; margin: 0 0 6px 0; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;">Pembayaran Berhasil!</h1>
                <p style="color: #a7f3d0; margin: 0; font-size: 13px;">Pesanan kamu telah selesai dan siap digunakan</p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 28px;">
                <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.5; color: #334155;">
                    Halo <strong>{{ $order->customer_name ?: 'Pelanggan Setia' }}</strong>,
                </p>
                <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                    Pembayaran untuk pesanan <strong>{{ $order->order_id }}</strong> telah kami terima. Berikut adalah detail akun / akses produk kamu:
                </p>

                <!-- Account Credentials Card -->
                @if(!empty($order->accounts) && count($order->accounts) > 0)
                <div style="background-color: #0f172a; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px; border: 1px solid #1e293b; color: #38bdf8;">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 10px;">
                        Detail Kredensial Akun / Lisensi
                    </div>
                    @foreach($order->accounts as $acc)
                        <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; line-height: 1.6; color: #f8fafc; padding: 6px 0; border-bottom: 1px dashed #334155;">
                            {{ $acc }}
                        </div>
                    @endforeach
                </div>
                @endif

                @if(!empty($order->note))
                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; color: #166534;">
                    <strong>Catatan:</strong> {{ $order->note }}
                </div>
                @endif

                <!-- Order Summary -->
                <div style="background-color: #f8fafc; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; border: 1px solid #e2e8f0; font-size: 13px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">Produk:</td>
                            <td style="text-align: right; font-weight: 600;">{{ $order->product_name }}</td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">Total Dibayar:</td>
                            <td style="text-align: right; font-weight: 700; color: #059669;">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Track Order Button -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <a href="{{ url('/lacak-pesanan') }}?order_id={{ $order->order_id }}" target="_blank" style="display: inline-block; background-color: #0f172a; color: #ffffff; font-weight: 600; font-size: 13px; text-decoration: none; padding: 11px 24px; border-radius: 8px;">
                        Buka Halaman Lacak Pesanan
                    </a>
                </div>

                <p style="margin: 20px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;">
                    Simpan informasi ini dengan aman. Jika ada kendala dengan akses akun, segera hubungi kami melalui WhatsApp di 
                    <a href="https://wa.me/62895391960539" style="color: #059669; font-weight: 600; text-decoration: none;">+62 895-3919-60539</a>.
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f1f5f9; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                © {{ date('Y') }} Ideostore. Layanan Aplikasi Premium & Akun Digital Terpercaya.
            </td>
        </tr>
    </table>
</body>
</html>
