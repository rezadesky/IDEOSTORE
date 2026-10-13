<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Pembayaran {{ $order->order_id }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #1e293b;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #1d3fd6 0%, #111c54 100%); padding: 32px 28px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0 0 6px 0; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;">Ideostore</h1>
                <p style="color: #9fc2fd; margin: 0; font-size: 13px;">Tagihan Pembayaran Pesanan Kamu</p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 28px;">
                <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.5; color: #334155;">
                    Halo <strong>{{ $order->customer_name ?: 'Pelanggan Setia' }}</strong>,
                </p>
                <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                    Terima kasih telah memesan di <strong>Ideostore</strong>. Tagihan pesanan kamu telah diterbitkan, silakan lakukan pembayaran melalui QRIS agar pesanan langsung diproses otomatis.
                </p>

                <!-- Order Details Card -->
                <div style="background-color: #f1f5f9; border-radius: 12px; padding: 18px 20px; margin-bottom: 24px; border: 1px solid #e2e8f0;">
                    <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px; color: #334155;">
                        <tr>
                            <td style="padding: 6px 0; color: #64748b;">No. Pesanan:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #1d3fd6;">{{ $order->order_id }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #64748b;">Produk:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $order->product_name }} {{ $order->variant ? '('.$order->variant.')' : '' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #64748b;">Jumlah:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $order->qty }} item</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: #64748b;">Metode:</td>
                            <td style="padding: 6px 0; text-align: right; font-weight: 600;">QRIS Otomatis (WaroengPay)</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-top: 1px dashed #cbd5e1; padding-top: 10px; margin-top: 6px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;">Total Tagihan:</td>
                            <td style="padding: 6px 0; text-align: right; font-size: 17px; font-weight: 800; color: #1d3fd6;">
                                Rp {{ number_format($order->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Pay Button -->
                @if(!empty($payUrl))
                <div style="text-align: center; margin-bottom: 24px;">
                    <a href="{{ $payUrl }}" target="_blank" style="display: inline-block; background-color: #1d3fd6; color: #ffffff; font-weight: 700; font-size: 14px; text-decoration: none; padding: 13px 32px; border-radius: 10px; box-shadow: 0 4px 10px rgba(29, 63, 214, 0.3);">
                        👉 Klik di Sini untuk Bayar QRIS
                    </a>
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #64748b;">
                        Link pembayaran: <a href="{{ $payUrl }}" style="color: #1d3fd6; word-break: break-all;">{{ $payUrl }}</a>
                    </p>
                </div>
                @endif

                <!-- Track Link -->
                <div style="background-color: #f8fafc; border-radius: 10px; padding: 14px 16px; border: 1px solid #e2e8f0; font-size: 12px; color: #475569; text-align: center;">
                    Kamu juga bisa memantau status pesanan kapan saja di:
                    <br>
                    <a href="{{ url('/lacak-pesanan') }}?order_id={{ $order->order_id }}" style="color: #1d3fd6; font-weight: 600; text-decoration: none;">
                        {{ url('/lacak-pesanan') }}?order_id={{ $order->order_id }}
                    </a>
                </div>

                <p style="margin: 24px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;">
                    Jika butuh bantuan segera, hubungi WhatsApp kami di 
                    <a href="https://wa.me/62895391960539" style="color: #1d3fd6; font-weight: 600; text-decoration: none;">+62 895-3919-60539</a>.
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
