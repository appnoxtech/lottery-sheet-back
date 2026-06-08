<?php

namespace App\Services;

use App\Models\LotteryRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WatiService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected Client $client;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('wati.api_url', ''), '/');
        $this->apiToken = config('wati.api_token', '');

        $this->client = new Client([
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Send WhatsApp notification to all approved admins with a saved whatsapp_number.
     * Attempts sendTemplateMessage first (works at any time),
     * and falls back to sendSessionMessage (requires an active 24h window).
     *
     * @param LotteryRequest $lotteryRequest
     * @param Collection     $admins
     */
    public function notifyAdmins(LotteryRequest $lotteryRequest, Collection $admins): void
    {
        if (empty($this->apiUrl) || empty($this->apiToken)) {
            Log::warning('WATI: API URL or Token not configured. Skipping WhatsApp notification.');
            return;
        }

        $lotteryNames = is_array($lotteryRequest->lottery_selections)
            ? implode(', ', $lotteryRequest->lottery_selections)
            : ($lotteryRequest->lottery_selections ?? 'N/A');

        $numbers = is_array($lotteryRequest->lottery_numbers)
            ? implode(', ', $lotteryRequest->lottery_numbers)
            : ($lotteryRequest->lottery_numbers ?? 'N/A');

        $numberTypes = is_array($lotteryRequest->number_types)
            ? implode(', ', $lotteryRequest->number_types)
            : ($lotteryRequest->number_types ?? 'N/A');

        $submittedAt = now()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A');

        // Form parameters for template message
        $templateParams = [
            ['name' => 'customer_name', 'value' => $lotteryRequest->name],
            ['name' => 'phone', 'value' => $lotteryRequest->country_code . $lotteryRequest->phone],
            ['name' => 'email', 'value' => $lotteryRequest->email ?? 'N/A'],
            ['name' => 'lottery_name', 'value' => $lotteryNames],
            ['name' => 'numbers', 'value' => $numbers]
        ];

        // Format message body for session fallback (plain text)
        $sessionMessage = "📋 *New Lottery Request*\n\n"
            . "👤 *Name:* {$lotteryRequest->name}\n"
            . "📞 *Phone:* {$lotteryRequest->country_code}{$lotteryRequest->phone}\n"
            . "📧 *Email:* {$lotteryRequest->email}\n"
            . "🎟️ *Lottery:* {$lotteryNames}\n"
            . "🔢 *Numbers:* {$numbers}\n"
            . "🏷️ *Number Type:* {$numberTypes}\n"
            . ($lotteryRequest->notes ? "📝 *Notes:* {$lotteryRequest->notes}\n" : "")
            . "⏰ *Submitted:* {$submittedAt}\n\n"
            . "⏳ *Status: PENDING*";

        foreach ($admins as $admin) {
            $whatsappNumber = $this->sanitizeNumber($admin->whatsapp_number);

            if (empty($whatsappNumber)) {
                Log::info("WATI: Skipping admin {$admin->email} — no WhatsApp number.");
                continue;
            }

            // Attempt Template first
            Log::info("WATI: Attempting template message to {$admin->email} ({$whatsappNumber})...");
            $success = $this->sendTemplateMessage($whatsappNumber, $templateParams, $admin->email);

            if (!$success) {
                // Fallback to Session Message
                Log::info("WATI: Falling back to session message to {$admin->email} ({$whatsappNumber})...");
                $this->sendSessionMessage($whatsappNumber, $sessionMessage, $admin->email);
            }
        }
    }

    /**
     * Send a template message. Returns true on success, false on failure.
     */
    protected function sendTemplateMessage(string $whatsappNumber, array $parameters, string $adminEmail): bool
    {
        $templateName = config('wati.template_name', 'lottery_notification_paysigur');
        $url = "{$this->apiUrl}/api/v1/sendTemplateMessage?whatsappNumber={$whatsappNumber}";

        $payload = [
            'template_name' => $templateName,
            'broadcast_name' => 'admin_notification',
            'parameters' => $parameters
        ];

        try {
            $response = $this->client->post($url, [
                'json' => $payload
            ]);
            $body = json_decode($response->getBody()->getContents(), true);
            // print_r($body);

            if (isset($body['result']) && ($body['result'] === true || $body['result'] === 'True')) {
                Log::info("WATI: ✅ Template message successfully sent to {$adminEmail} ({$whatsappNumber}).");
                return true;
            }

            if (isset($body['items'][0]['code']) && $body['items'][0]['code'] === 'Template') {
                Log::warning("WATI: Template '{$templateName}' does not exist or is not approved in your WATI dashboard.");
                return false;
            }

            Log::warning("WATI: Template message send returned false/error for {$adminEmail}: " . json_encode($body));
            return false;
        } catch (RequestException $e) {
            $responseBody = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : '';

            if (str_contains($responseBody, 'Template') || str_contains($responseBody, 'template_name')) {
                Log::warning("WATI: Template '{$templateName}' does not exist or is not approved in your WATI dashboard (400 bad request).");
                return false;
            }

            Log::error("WATI: Failed to send template message to {$adminEmail} ({$whatsappNumber}). Error: {$e->getMessage()}. Response: {$responseBody}");
            return false;
        } catch (\Exception $e) {
            Log::error("WATI: Unexpected error in sendTemplateMessage for {$adminEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a plain text session message via WATI.
     * No template approval required.
     * NOTE: Recipient must have messaged your WATI number within the last 24 hours.
     */
    protected function sendSessionMessage(string $whatsappNumber, string $message, string $adminEmail): void
    {
        $encodedMessage = urlencode($message);
        $url = "{$this->apiUrl}/api/v1/sendSessionMessage/{$whatsappNumber}?messageText={$encodedMessage}";

        try {
            $response = $this->client->post($url);
            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['result']) && ($body['result'] === true || $body['result'] === 'True')) {
                Log::info("WATI: ✅ Session message successfully sent to {$adminEmail} ({$whatsappNumber}).");
            } else {
                Log::warning("WATI: Unexpected session message response for {$adminEmail}: " . json_encode($body));
            }
        } catch (RequestException $e) {
            $responseBody = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : 'No response body';

            if ($e->hasResponse() && ($e->getResponse()->getStatusCode() === 400 || str_contains($responseBody, 'Conversation') || str_contains($responseBody, 'Contact'))) {
                Log::warning(
                    "WATI: Session message failed for {$adminEmail} ({$whatsappNumber}) due to expired/missing session.\n" .
                    "👉 FIX 1: Admin must first send an incoming message to the WATI WhatsApp business number to open a 24-hour session window.\n" .
                    "👉 FIX 2 (Recommended): Create and approve a template named 'lottery_admin_notification' in your WATI dashboard to receive notifications at any time.\n" .
                    "Response: {$responseBody}"
                );
            } else {
                Log::error(
                    "WATI: Failed to send session message to {$adminEmail} ({$whatsappNumber}). " .
                    "Error: {$e->getMessage()}. Response: {$responseBody}"
                );
            }
        } catch (\Exception $e) {
            Log::error("WATI: Unexpected session message error for {$adminEmail}: " . $e->getMessage());
        }
    }

    /**
     * Strip all non-digit characters.
     */
    protected function sanitizeNumber(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }
}
