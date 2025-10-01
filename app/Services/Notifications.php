<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\EmailTemplate;

final class Notifications
{
    private Mailer $mailer;
    private EmailTemplate $tpl;

    public function __construct()
    {
        $this->mailer = new Mailer();
        $this->tpl = new EmailTemplate();
    }

    private function render(string $key, string $locale, array $vars): array
    {
        $tpl = $this->tpl->get($key, $locale) ?? ['subject' => $key, 'body' => ''];
        $repl = $vars + ['site' => ($_SERVER['HTTP_HOST'] ?? 'Site')];
        $subject = $tpl['subject'];
        $body = $tpl['body'];
        foreach ($repl as $k => $v) {
            $subject = str_replace('{{' . $k . '}}', (string)$v, $subject);
            $body = str_replace('{{' . $k . '}}', (string)$v, $body);
        }
        return [$subject, $body];
    }

    public function orderCreated(array $order, string $locale): void
    {
        if (!$this->mailer->configured()) return;

        $invoiceHtml = $this->invoiceHtml($order);
        [$subA, $bodyA] = $this->render('order_new', $locale, [
            'order_id' => $order['order_code'],
            'customer' => $order['customer_name'],
            'total' => $order['total'],
            'invoice_html' => $invoiceHtml,
        ]);
        if (!empty($order['customer_email'])) {
            $this->mailer->send($order['customer_email'], $order['customer_name'], $subA, $bodyA);
        }

        // notify admin
        $contact = \db()->query('SELECT email FROM contact_settings WHERE id=1')->fetch();
        if (!empty($contact['email'])) {
            $this->mailer->send($contact['email'], 'Admin', 'New order ' . $order['order_code'], $invoiceHtml);
        }
    }

    public function orderStatusChanged(array $order, string $locale): void
    {
        if (!$this->mailer->configured() || empty($order['customer_email'])) return;
        [$sub, $body] = $this->render('order_status', $locale, [
            'order_id' => $order['order_code'],
            'customer' => $order['customer_name'],
            'status' => $order['status'],
            'total' => $order['total'],
        ]);
        $this->mailer->send($order['customer_email'], $order['customer_name'], $sub, $body);
    }

    public function invoiceHtml(array $order): string
    {
        $logo = '';
        $site = $_SERVER['HTTP_HOST'] ?? 'Store';
        $rows = '';
        foreach ($order['items'] as $it) {
            $rows .= '<tr><td>' . htmlspecialchars((string)$it['name']) . '</td><td>' . (int)$it['qty'] . '</td><td>' . number_format((float)$it['price'],2) . '</td><td>' . number_format((float)$it['total'],2) . '</td></tr>';
        }
        $html = '<div style="font-family:Arial,sans-serif">'
              . '<h2 style="margin:0 0 10px">' . $site . '</h2>'
              . '<h3>Invoice #' . htmlspecialchars((string)$order['order_code']) . '</h3>'
              . '<table width="100%" cellspacing="0" cellpadding="6" border="1" style="border-collapse:collapse">'
              . '<thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>'
              . '<tbody>' . $rows . '</tbody>'
              . '</table>'
              . '<p>Subtotal: ' . number_format((float)$order['subtotal'],2) . '<br>'
              . 'Shipping: ' . number_format((float)$order['shipping'],2) . '<br>'
              . 'Deposit: ' . number_format((float)$order['deposit'],2) . '<br>'
              . '<strong>Total: ' . number_format((float)$order['total'],2) . '</strong></p>'
              . '</div>';
        return $html;
    }
}
?>
