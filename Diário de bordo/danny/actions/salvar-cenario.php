<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$nichoId = (int) ($_POST['nicho_id'] ?? 0);
$mes = $_POST['mes'] !== '' ? (int) $_POST['mes'] : null;
$ano = $_POST['ano'] !== '' ? (int) $_POST['ano'] : null;

if ($nome === '' || $nichoId <= 0 || ($mes !== null && ($mes < 1 || $mes > 12))) {
    header('Location: ../servicos-cadastrados.php?erro=campos');
    exit;
}

try {
    $pdo = Database::connection();

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE cenarios
            SET nome = :nome,
                nicho_id = :nicho_id,
                mes = :mes,
                ano = :ano
            WHERE id = :id
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':nicho_id' => $nichoId,
            ':mes' => $mes,
            ':ano' => $ano,
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO cenarios
            (nome, nicho_id, mes, ano)
            VALUES
            (:nome, :nicho_id, :mes, :ano)
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':nicho_id' => $nichoId,
            ':mes' => $mes,
            ':ano' => $ano,
        ]);
    }

    header('Location: ../servicos-cadastrados.php?opcao=sucesso');
    exit;
} catch (PDOException $e) {
    header('Location: ../servicos-cadastrados.php?erro=banco');
    exit;
}
