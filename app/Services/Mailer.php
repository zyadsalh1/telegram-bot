<?php
declare(strict_types=1);

namespace App\Services;

final class Mailer
{
    private array $cfg;

    public function __construct()
    {
        $row = \db()->query('SELECT * FROM smtp_settings WHERE id=1')->fetch();
        $this->cfg = $row ?: [];
    }

    public function configured(): bool
    {
        return !empty($this->cfg['host']) && !empty($this->cfg['from_email']);
    }

    public function send(string $toEmail, string $toName, string $subject, string $html, ?string $text = null): bool
    {
        if (!$this->configured()) { return false; }

        $boundary = uniqid('np');
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative;boundary=' . $boundary;
        $headers[] = 'From: ' . ($this->cfg['from_name'] ?: '') . ' <' . $this->cfg['from_email'] . '>';
        $headers[] = 'Reply-To: ' . $this->cfg['from_email'];

        $message = '';
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $message .= ($text ?? strip_tags($html)) . "\r\n";
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $message .= $html . "\r\n";
        $message .= "--$boundary--";

        // Use PHP mail() to stay cPanel-compatible without external libs.
        return mail($toEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, implode("\r\n", $headers));
    }
}
?>
