<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;
use Danny\Mailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../esqueci-senha.php');
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../esqueci-senha.php?erro=email');
    exit;
}

function recovery_base_url(): string
{
    $appUrl = rtrim((string) env('APP_URL', ''), '/');

    if ($appUrl !== '') {
        return $appUrl;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("
        SELECT id, nome, email
        FROM admins
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

        $pdo->prepare("
            UPDATE password_resets
            SET used_at = NOW()
            WHERE admin_id = :admin_id
              AND used_at IS NULL
        ")->execute([':admin_id' => $usuario['id']]);

        $pdo->prepare("
            INSERT INTO password_resets (admin_id, token_hash, expires_at)
            VALUES (:admin_id, :token_hash, :expires_at)
        ")->execute([
            ':admin_id' => $usuario['id'],
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
        ]);

        $link = recovery_base_url() . '/redefinir-senha.php?token=' . urlencode($token);
        $nome = htmlspecialchars((string) $usuario['nome'], ENT_QUOTES, 'UTF-8');
        $linkEscapado = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        $html = "
            <p>Ola, {$nome}.</p>
            <p>Recebemos uma solicitacao para redefinir sua senha no site Danny.</p>
            <p><a href=\"{$linkEscapado}\">Clique aqui para criar uma nova senha</a>.</p>
            <p>Esse link expira em 1 hora. Se voce nao solicitou, ignore este e-mail.</p>
        ";

        $alt = "Acesse o link para redefinir sua senha: {$link}";

        (new Mailer())->send((string) $usuario['email'], 'Redefinicao de senha - Danny', $html, $alt);
    }

    header('Location: ../esqueci-senha.php?status=enviado');
    exit;
} catch (Throwable $e) {
    header('Location: ../esqueci-senha.php?erro=envio');
    exit;
}
