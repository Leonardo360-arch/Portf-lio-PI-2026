<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Mailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php#contato');
    exit;
}

$name = trim((string) ($_POST['nome'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['telefone'] ?? ''));
$message = trim((string) ($_POST['mensagem'] ?? ''));

if ($name === '' || $email === '' || $phone === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../index.php?contato=erro#contato');
    exit;
}

$html = sprintf(
    '<h1>Novo contato pelo site</h1><p><strong>Nome:</strong> %s</p><p><strong>E-mail:</strong> %s</p><p><strong>Telefone:</strong> %s</p><p><strong>Mensagem:</strong><br>%s</p>',
    htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
    htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
    nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'))
);

try {
    (new Mailer())->send((string) env('MAIL_TO_ADDRESS', 'contato@danny.local'), 'Novo contato pelo site Danny', $html);
    header('Location: ../index.php?contato=sucesso#contato');
} catch (Throwable) {
    header('Location: ../index.php?contato=erro#contato');
}

exit;
