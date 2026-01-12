<?php

namespace App\Libraries;

use Twilio\Rest\Client;
use Config\Services;
use Exception;

class WhatsAppService
{
    protected Client $twilio;
    protected string $from;

    public function __construct()
    {
        $sid = getenv('TWILIO_SID');
        $token = getenv('TWILIO_AUTH_TOKEN');
        $this->from = getenv('TWILIO_WHATSAPP_FROM');
        
        if (empty($sid) || empty($token) || empty($this->from)) {
            log_message('error', 'Twilio credentials are not configured in .env file.');
            // We don't throw an exception here to avoid crashing the app if the service is loaded elsewhere
            // but methods will fail if called.
        }
        log_message('debug', 'Twilio SID loaded: ' . substr($sid, 0, 6) . '...');

        $this->twilio = new Client($sid, $token);
    }

    /**
     * Sends an OTP message via WhatsApp.
     *
     * @param string $to The recipient's phone number in E.164 format (e.g., +14155238886)
     * @param string $otp The One-Time Password to send.
     * @return bool True on success, false on failure.
     */
    public function sendOtp(string $to, string $otp): bool
    {
        // Basic validation for the 'to' number format
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $to)) {
            log_message('error', "WhatsAppService: Invalid 'to' phone number format: {$to}");
            return false;
        }

        $message = "Your SeamlessCall verification code is: {$otp}";

        try {
            $this->twilio->messages->create(
                "whatsapp:{$to}",
                [
                    'from' => "whatsapp:{$this->from}",
                    'body' => $message,
                ]
            );
            return true;
        } catch (Exception $e) {
            log_message('error', "Twilio WhatsApp sending failed: " . $e->getMessage());
            return false;
        }
    }
}
