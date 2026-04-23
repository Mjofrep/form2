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
            app_log('mail', 'SMTP deshabilitado. No se intentó envío a ' . $toEmail . '.');
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
            $mailer->Timeout = (int) config('mail.timeout', 20);

            $encryption = (string) config('mail.encryption', 'ssl');

            if ($encryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $debugLevel = (int) config('mail.debug_level', 0);

            if ($debugLevel > 0) {
                $mailer->SMTPDebug = $debugLevel;
                $mailer->Debugoutput = static function (string $message, int $level): void {
                    app_log('mail', 'SMTP[' . $level . '] ' . trim($message));
                };
            }

            if ((bool) config('mail.allow_self_signed', false)) {
                $mailer->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom((string) config('mail.from_email'), (string) config('mail.from_name'));
            $mailer->addAddress($toEmail, $toName);
            $mailer->isHTML(true);
            $mailer->Subject = $subject;
            $mailer->Body = $html;
            $mailer->AltBody = $plainText !== '' ? $plainText : strip_tags(str_replace(['<br>', '<br/>', '<br />'], PHP_EOL, $html));

            $result = $mailer->send();
            app_log('mail', 'Correo enviado a ' . $toEmail . ' con asunto: ' . $subject . '.');
            return $result;
        } catch (Exception $exception) {
            app_log('mail', 'Error enviando correo a ' . $toEmail . ': ' . $exception->getMessage() . ' | PHPMailer: ' . $mailer->ErrorInfo);
            return false;
        }
    }
}
