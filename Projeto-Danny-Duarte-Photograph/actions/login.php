<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    header('Location: ../login.php?erro=campos');
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("
        SELECT id, nome, email, senha_hash, status_admin
        FROM admins
        WHERE email = :email
          AND tipo = 'ADMIN'
        LIMIT 1
    ");

    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
        header('Location: ../login.php?erro=login');
        exit;
    }

    if ((int) $admin['id'] !== 1 && $admin['status_admin'] !== 'APROVADO') {
        header('Location: ../login.php?erro=pendente');
        exit;
    }

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_nome'] = $admin['nome'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_autorizado'] = true;

    header('Location: ../admin.php');
    exit;
} catch (PDOException $e) {
    header('Location: ../login.php?erro=banco');
    exit;
}
