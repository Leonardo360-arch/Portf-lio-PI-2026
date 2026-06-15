<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if (empty($_SESSION['admin_autorizado']) || (int) ($_SESSION['admin_id'] ?? 0) !== 1) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php#acessos-admin');
    exit;
}

$adminId = (int) ($_POST['admin_id'] ?? 0);
$decisao = $_POST['decisao'] ?? '';

if ($adminId <= 1 || !in_array($decisao, ['aprovar', 'recusar'], true)) {
    header('Location: ../admin.php?erro=admin_acesso#acessos-admin');
    exit;
}

$status = $decisao === 'aprovar' ? 'APROVADO' : 'RECUSADO';

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("
        UPDATE admins
        SET status_admin = :status
        WHERE id = :id
          AND tipo = 'ADMIN'
          AND status_admin = 'PENDENTE'
    ");

    $stmt->execute([
        ':status' => $status,
        ':id' => $adminId,
    ]);

    $resultado = $status === 'APROVADO' ? 'aprovado' : 'recusado';
    header('Location: ../admin.php?admin_acesso=' . $resultado . '#acessos-admin');
    exit;
} catch (PDOException $e) {
    header('Location: ../admin.php?erro=admin_acesso#acessos-admin');
    exit;
}
