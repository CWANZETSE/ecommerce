<?php

namespace App\Contracts;

interface SmsContract
{
    /**
     * Send a raw SMS.
     *
     * @param string $to The recipient phone number (e.g., 254712345678)
     * @param string $message The text body
     * @return bool True if accepted by the provider, false otherwise
     */
    public function send(string $to, string $message): bool;
}