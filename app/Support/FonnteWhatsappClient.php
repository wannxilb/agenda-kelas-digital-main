<?php

namespace App\Support;

use App\Models\DailyAttendanceSetting;
use Illuminate\Support\Facades\Http;
use Throwable;

class FonnteWhatsappClient
{
    public function send(DailyAttendanceSetting $setting, string $target, string $message): array
    {
        if (!$setting->whatsapp_token) {
            return [
                'ok' => false,
                'error' => 'Token Fonnte belum diisi.',
                'body' => null,
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->withHeaders(['Authorization' => $setting->whatsapp_token])
                ->post($setting->whatsapp_api_url ?: 'https://api.fonnte.com/send', [
                    'target' => $this->normalizeTarget($target),
                    'message' => $message,
                    'countryCode' => $setting->whatsapp_country_code ?: '62',
                ]);

            $body = $response->body();
            $payload = $response->json();
            $ok = $response->successful() && (bool) data_get($payload, 'status');

            return [
                'ok' => $ok,
                'process' => data_get($payload, 'process'),
                'message_id' => data_get($payload, 'id.0'),
                'detail' => data_get($payload, 'detail'),
                'error' => $ok ? null : ($payload['reason'] ?? $payload['detail'] ?? 'Pengiriman WhatsApp gagal.'),
                'body' => $body,
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'process' => null,
                'message_id' => null,
                'detail' => null,
                'error' => $exception->getMessage(),
                'body' => null,
            ];
        }
    }

    private function normalizeTarget(string $target): string
    {
        return preg_replace('/[^0-9]/', '', $target) ?: $target;
    }
}
