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
$duracao = (int) ($_POST['duracao_minutos'] ?? 60);
$exigeCenario = isset($_POST['exige_cenario']) ? 1 : 0;

if ($nome === '' || $nichoId <= 0 || $duracao <= 0) {
    header('Location: ../servicos-cadastrados.php?erro=campos');
    exit;
}

try {
    $pdo = Database::connection();

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE tipos_servico
            SET nome = :nome,
                nicho_id = :nicho_id,
                duracao_minutos = :duracao_minutos,
                exige_cenario = :exige_cenario
            WHERE id = :id
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':nicho_id' => $nichoId,
            ':duracao_minutos' => $duracao,
            ':exige_cenario' => $exigeCenario,
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO tipos_servico
            (nome, nicho_id, exige_cenario, duracao_minutos)
            VALUES
            (:nome, :nicho_id, :exige_cenario, :duracao_minutos)
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':nicho_id' => $nichoId,
            ':exige_cenario' => $exigeCenario,
            ':duracao_minutos' => $duracao,
        ]);
    }

    header('Location: ../servicos-cadastrados.php?opcao=sucesso');
    exit;
} catch (PDOException $e) {
    header('Location: ../servicos-cadastrados.php?erro=banco');
    exit;
}
