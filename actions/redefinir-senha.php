<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../esqueci-senha.php');
    exit;
}

$token = trim($_POST['token'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if ($token === '') {
    header('Location: ../esqueci-senha.php?erro=token');
    exit;
}

if (strlen($senha) < 6) {
    header('Location: ../redefinir-senha.php?token=' . urlencode($token) . '&erro=senha_curta');
    exit;
}

if ($senha !== $confirmarSenha) {
    header('Location: ../redefinir-senha.php?token=' . urlencode($token) . '&erro=senhas');
    exit;
}

try {
    $pdo = Database::connection();
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("
        SELECT pr.id AS reset_id, a.id AS admin_id, a.tipo
        FROM password_resets pr
        INNER JOIN admins a ON a.id = pr.admin_id
        WHERE pr.token_hash = :token_hash
          AND pr.used_at IS NULL
          AND pr.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([':token_hash' => $tokenHash]);
    $reset = $stmt->fetch();

    if (!$reset) {
        header('Location: ../redefinir-senha.php?erro=token');
        exit;
    }

    $pdo->beginTransaction();

    $pdo->prepare("
        UPDATE admins
        SET senha_hash = :senha_hash,
            senha_provisoria = 0
        WHERE id = :admin_id
    ")->execute([
        ':senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        ':admin_id' => $reset['admin_id'],
    ]);

    $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE id = :reset_id
    ")->execute([':reset_id' => $reset['reset_id']]);

    $pdo->commit();

    $destino = $reset['tipo'] === 'CLIENTE' ? '../login-cliente.php' : '../login.php';
    header('Location: ' . $destino . '?senha=alterada');
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../redefinir-senha.php?token=' . urlencode($token) . '&erro=banco');
    exit;
}
