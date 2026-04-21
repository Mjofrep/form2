<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public function sendHtml(string $toEmail, string $toName, string $subject, string $html, string $plainText = ''): bool
    {
        if (!(bool) config('mail.enabled', false)) {
            return false;
        }

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = (string) config('mail.host');
            $mailer->SMTPAuth = true;
            $mailer->Username = (string) config('mail.username');
            $mailer->Password = (string) config('mail.password');
            $mailer->Port = (int) config('mail.port', 465);

            $encryption = (string) config('mail.encryption', 'ssl');

            if ($encryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom((string) config('mail.from_email'), (string) config('mail.from_name'));
            $mailer->addAddress($toEmail, $toName);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $html;
            $mailer->AltBody = $plainText !== '' ? $plainText : strip_tags(str_replace(['<br>', '<br/>', '<br />'], PHP_EOL, $html));

            return $mailer->send();
        } catch (Exception) {
            return false;
        }
    }
}
