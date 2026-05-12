<?php

declare(strict_types=1);

namespace Danny;

use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public function send(string $to, string $subject, string $html, string $alt = ''): bool
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = (string) env('MAIL_HOST', 'mailpit');
        $mail->Port = (int) env('MAIL_PORT', 1025);
        $mail->SMTPAuth = env('MAIL_USERNAME') !== null;

        if ($mail->SMTPAuth) {
            $mail->Username = (string) env('MAIL_USERNAME');
            $mail->Password = (string) env('MAIL_PASSWORD');
        }

        $encryption = env('MAIL_ENCRYPTION');
        if ($encryption) {
            $mail->SMTPSecure = (string) $encryption;
        }

        $mail->CharSet = 'UTF-8';
        $mail->setFrom((string) env('MAIL_FROM_ADDRESS', 'contato@danny.local'), (string) env('MAIL_FROM_NAME', 'Danny'));
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = $alt ?: strip_tags($html);

        return $mail->send();
    }
}
