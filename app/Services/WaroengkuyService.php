<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WaroengkuyService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.waroengkuy.url', env('WAROENGKUY_API_URL', 'https://waroengkuy.com/api/reseller/v1'));
        $this->apiKey = config('services.waroengkuy.key', env('WAROENGKUY_API_KEY'));
    }

    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout(25);
    }

    /**
     * Cek Saldo & Info Reseller
     */
    public function getBalance(): array
    {
        try {
            $response = $this->client()->get('/balance');
            return $response->json() ?? ['ok' => false, 'error' => 'Koneksi gagal'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Ambil Katalog Produk (Dengan Cache 2 Menit sesuai anjuran docs.md)
     */
    public function getProducts(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('waroengkuy_products');
        }

        return Cache::remember('waroengkuy_products', 120, function () {
            try {
                $response = $this->client()->get('/products');
                return $response->json() ?? ['ok' => false, 'items' => []];
            } catch (\Exception $e) {
                return ['ok' => false, 'items' => [], 'error' => $e->getMessage()];
            }
        });
    }

    /**
     * Beli Akun (Order Otomatis atau QRIS)
     */
    public function createOrder(array $params): array
    {
        try {
            $response = $this->client()->post('/orders', $params);
            return $response->json() ?? ['ok' => false, 'error' => 'Order gagal'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek Status Order via ID atau Ref_ID
     */
    public function getOrder(string $identifier, bool $byRefId = false): array
    {
        try {
            if ($byRefId) {
                $response = $this->client()->get('/orders', ['ref_id' => $identifier]);
            } else {
                $response = $this->client()->get('/orders/' . $identifier);
            }
            return $response->json() ?? ['ok' => false, 'error' => 'Order tidak ditemukan'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Buat Tagihan Deposit QRIS
     */
    public function createDeposit(int $amount): array
    {
        try {
            $response = $this->client()->post('/deposits', ['amount' => $amount]);
            return $response->json() ?? ['ok' => false, 'error' => 'Gagal membuat deposit'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek Status Deposit QRIS
     */
    public function getDeposit(string $depositId): array
    {
        try {
            $response = $this->client()->get('/deposits/' . $depositId);
            return $response->json() ?? ['ok' => false, 'error' => 'Gagal cek deposit'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Batalkan Order QRIS (POST /orders/{id}/cancel)
     */
    public function cancelOrder(string $orderId): array
    {
        try {
            $response = $this->client()->post('/orders/' . $orderId . '/cancel');
            return $response->json() ?? ['ok' => false, 'error' => 'Gagal membatalkan pesanan'];
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
