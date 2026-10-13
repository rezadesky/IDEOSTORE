<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaroengPayService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.waroengpay.url', 'https://waroengpay.com'), '/');
        $this->apiKey = config('services.waroengpay.key');
    }

    /**
     * Membuat tagihan QRIS baru
     */
    public function createPayment(array $payload): array
    {
        if (!$this->apiKey) {
            return ['ok' => false, 'error' => 'WAROENGPAY_API_KEY belum dikonfigurasi.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Idempotency-Key' => $payload['reference'] ?? ('IDEO-' . time()),
            ])->timeout(15)->post("{$this->baseUrl}/api/payments", $payload);

            if ($response->successful()) {
                return ['ok' => true, 'payment' => $response->json()];
            }

            Log::error('WaroengPay Create Payment Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            $err = $response->json('error') ?? 'Gagal membuat tagihan QRIS WaroengPay.';
            return ['ok' => false, 'error' => $err];
        } catch (\Throwable $e) {
            Log::error('WaroengPay Exception: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Gangguan koneksi ke gateway WaroengPay: ' . $e->getMessage()];
        }
    }

    /**
     * Cek status tagihan pembayaran
     */
    public function getPayment(string $id): array
    {
        if (!$this->apiKey) {
            return ['ok' => false, 'error' => 'WAROENGPAY_API_KEY belum dikonfigurasi.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(10)->get("{$this->baseUrl}/api/payments/{$id}");

            if ($response->successful()) {
                return ['ok' => true, 'payment' => $response->json()];
            }

            return ['ok' => false, 'error' => $response->json('error') ?? 'Tagihan tidak ditemukan.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Simulasi pelunasan (Khusus API key mode sandbox wp_test_...)
     */
    public function simulatePayment(string $id, string $action = 'pay'): array
    {
        if (!$this->apiKey) {
            return ['ok' => false, 'error' => 'WAROENGPAY_API_KEY belum dikonfigurasi.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post("{$this->baseUrl}/api/payments/{$id}/simulate", [
                'action' => $action
            ]);

            if ($response->successful()) {
                return ['ok' => true, 'payment' => $response->json()];
            }

            return ['ok' => false, 'error' => $response->json('error') ?? 'Simulasi gagal dijalankan.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Membatalkan tagihan pembayaran
     */
    public function cancelPayment(string $id): array
    {
        if (!$this->apiKey) {
            return ['ok' => false, 'error' => 'WAROENGPAY_API_KEY belum dikonfigurasi.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post("{$this->baseUrl}/api/payments/{$id}/cancel");

            if ($response->successful()) {
                return ['ok' => true, 'payment' => $response->json()];
            }

            return ['ok' => false, 'error' => $response->json('error') ?? 'Gagal membatalkan tagihan.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
