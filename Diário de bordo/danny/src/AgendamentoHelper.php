<?php

declare(strict_types=1);

namespace Danny;

use DateTime;
use PDO;

function colunaExiste(PDO $pdo, string $tabela, string $coluna): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :tabela
          AND COLUMN_NAME = :coluna
    ");

    $stmt->execute([
        ':tabela' => $tabela,
        ':coluna' => $coluna,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

function buscarOuCriarCliente(PDO $pdo, string $nomeCliente, int $tipoServicoId, ?string $telefoneCliente = null, ?string $emailCliente = null): int
{
    $stmtBuscaCliente = $pdo->prepare("
        SELECT id
        FROM admins
        WHERE tipo = 'CLIENTE'
          AND nome = :nome
        LIMIT 1
    ");

    $stmtBuscaCliente->execute([
        ':nome' => $nomeCliente,
    ]);

    $cliente = $stmtBuscaCliente->fetch();

    $temTelefone = colunaExiste($pdo, 'admins', 'telefone');

    if ($cliente) {
        $setsAtualizacao = ['tipo_servico_id = :tipo_servico_id'];
        $paramsAtualizacao = [
            ':tipo_servico_id' => $tipoServicoId,
            ':id' => $cliente['id'],
        ];

        if ($temTelefone && $telefoneCliente !== null && trim($telefoneCliente) !== '') {
            $setsAtualizacao[] = 'telefone = :telefone';
            $paramsAtualizacao[':telefone'] = trim($telefoneCliente);
        }

        if ($emailCliente !== null && trim($emailCliente) !== '' && filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
            $setsAtualizacao[] = 'email = :email_contato';
            $paramsAtualizacao[':email_contato'] = trim($emailCliente);
        }

        $stmtAtualizaCliente = $pdo->prepare("
            UPDATE admins
            SET " . implode(', ', $setsAtualizacao) . "
            WHERE id = :id
        ");

        $stmtAtualizaCliente->execute($paramsAtualizacao);

        return (int) $cliente['id'];
    }

    $emailClienteValido = $emailCliente !== null && filter_var(trim($emailCliente), FILTER_VALIDATE_EMAIL) ? trim($emailCliente) : null;
    $emailTemporario = $emailClienteValido ?? ('cliente_' . time() . '_' . random_int(1000, 9999) . '@sememail.local');

    if ($temTelefone) {
        $stmtCliente = $pdo->prepare("
            INSERT INTO admins
            (nome, email, telefone, senha_hash, senha_provisoria, tipo, tipo_servico_id)
            VALUES
            (:nome, :email, :telefone, :senha_hash, 1, 'CLIENTE', :tipo_servico_id)
        ");

        $stmtCliente->execute([
            ':nome' => $nomeCliente,
            ':email' => $emailTemporario,
            ':telefone' => $telefoneCliente !== null && trim($telefoneCliente) !== '' ? trim($telefoneCliente) : 'Não informado',
            ':senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
            ':tipo_servico_id' => $tipoServicoId,
        ]);
    } else {
        $stmtCliente = $pdo->prepare("
            INSERT INTO admins
            (nome, email, senha_hash, senha_provisoria, tipo, tipo_servico_id)
            VALUES
            (:nome, :email, :senha_hash, 1, 'CLIENTE', :tipo_servico_id)
        ");

        $stmtCliente->execute([
            ':nome' => $nomeCliente,
            ':email' => $emailTemporario,
            ':senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
            ':tipo_servico_id' => $tipoServicoId,
        ]);
    }

    return (int) $pdo->lastInsertId();
}

function validarAgendamento(
    PDO $pdo,
    int $fotografoId,
    int $tipoServicoId,
    ?int $cenarioId,
    string $data,
    string $horaInicio,
    string $status = 'CONFIRMADO',
    ?int $ignorarAgendamentoId = null
): array {
    if ($status === 'PENDENTE') {
        return [true, null];
    }

    if ($status !== 'CONFIRMADO') {
        return [true, null];
    }

    $dataAgendamento = new DateTime($data . ' ' . $horaInicio);
    $dataMinima = (new DateTime('+7 days'))->format('Y-m-d');

    if ($dataAgendamento->format('Y-m-d') < $dataMinima) {
        return [false, 'antecedencia'];
    }

    $horaMinimaPermitida = '06:00:00';
    $horaMaximaPermitida = '20:00:00';

    if ($dataAgendamento->format('H:i:s') < $horaMinimaPermitida || $dataAgendamento->format('H:i:s') >= $horaMaximaPermitida) {
        return [false, 'horario_limite'];
    }

    $stmtServico = $pdo->prepare("
        SELECT id, nicho_id, exige_cenario, duracao_minutos
        FROM tipos_servico
        WHERE id = :id
        LIMIT 1
    ");
    $stmtServico->execute([':id' => $tipoServicoId]);
    $servico = $stmtServico->fetch();

    if (!$servico) {
        return [false, 'servico'];
    }

    if ((int) $servico['exige_cenario'] === 1 && $cenarioId === null) {
        return [false, 'cenario_obrigatorio'];
    }

    if ($cenarioId !== null) {
        $stmtCenario = $pdo->prepare("
            SELECT id, nicho_id, mes, ano
            FROM cenarios
            WHERE id = :id
            LIMIT 1
        ");
        $stmtCenario->execute([':id' => $cenarioId]);
        $cenario = $stmtCenario->fetch();

        if (!$cenario) {
            return [false, 'cenario'];
        }

        if ((int) $cenario['nicho_id'] !== (int) $servico['nicho_id']) {
            return [false, 'cenario_nicho'];
        }

        if ($cenario['mes'] !== null && (int) $cenario['mes'] !== (int) $dataAgendamento->format('n')) {
            return [false, 'cenario_mes'];
        }

        if ($cenario['ano'] !== null && (int) $cenario['ano'] !== (int) $dataAgendamento->format('Y')) {
            return [false, 'cenario_ano'];
        }
    }

    $duracao = (int) $servico['duracao_minutos'];
    $fim = clone $dataAgendamento;
    $fim->modify('+' . $duracao . ' minutes');

    $horaInicioFormatada = $dataAgendamento->format('H:i:s');
    $horaFimFormatada = $fim->format('H:i:s');

    $inicioNovo = $dataAgendamento->format('Y-m-d H:i:s');
    $fimNovo = $fim->format('Y-m-d H:i:s');

    $sqlConflito = "
        SELECT 1
        FROM agendamentos a
        INNER JOIN tipos_servico ts ON ts.id = a.tipo_servico_id
        WHERE a.fotografo_id = :fotografo_id
          AND a.status IN ('PENDENTE', 'CONFIRMADO')
          AND :inicio_novo < DATE_ADD(TIMESTAMP(a.data, a.hora_inicio), INTERVAL ts.duracao_minutos MINUTE)
          AND :fim_novo > TIMESTAMP(a.data, a.hora_inicio)
    ";

    $paramsConflito = [
        ':fotografo_id' => $fotografoId,
        ':inicio_novo' => $inicioNovo,
        ':fim_novo' => $fimNovo,
    ];

    if ($ignorarAgendamentoId !== null) {
        $sqlConflito .= " AND a.id <> :id";
        $paramsConflito[':id'] = $ignorarAgendamentoId;
    }

    $sqlConflito .= " LIMIT 1";

    $stmtConflito = $pdo->prepare($sqlConflito);
    $stmtConflito->execute($paramsConflito);

    if ($stmtConflito->fetch()) {
        return [false, 'conflito'];
    }

    return [true, null];
}


function mensagemErroAgendamento(string $erro): string
{
    return match ($erro) {
        'campos' => 'Preencha todos os campos obrigatórios.',
        'banco' => 'Erro ao executar a ação no banco.',
        'status' => 'Status inválido.',
        'antecedencia' => 'Escolha uma data com pelo menos 7 dias de antecedência.',
        'servico' => 'Tipo de serviço inválido.',
        'cenario_obrigatorio' => 'Este serviço exige cenário.',
        'cenario' => 'Cenário inválido.',
        'cenario_nicho' => 'O cenário não pertence ao mesmo nicho do serviço.',
        'cenario_mes' => 'O cenário não está disponível para o mês escolhido.',
        'cenario_ano' => 'O cenário não está disponível para o ano escolhido.',
        'disponibilidade' => 'Horário indisponível.',
        'conflito' => 'Já existe um agendamento nesse período para o fotógrafo escolhido.',
        default => 'Ocorreu um erro. Verifique os dados informados.',
    };
}


function erroBancoParaCodigo(string $mensagem): string
{
    $mensagemNormalizada = mb_strtolower($mensagem);

    if (str_contains($mensagemNormalizada, 'horário inicial') || str_contains($mensagemNormalizada, 'horario inicial') || str_contains($mensagemNormalizada, 'período permitido') || str_contains($mensagemNormalizada, 'periodo permitido')) {
        return 'horario_limite';
    }

    if (str_contains($mensagemNormalizada, 'conflito')) {
        return 'conflito';
    }

    if (str_contains($mensagemNormalizada, 'disponibilidade')) {
        return 'disponibilidade';
    }

    if (str_contains($mensagemNormalizada, 'antecedência') || str_contains($mensagemNormalizada, 'antecedencia')) {
        return 'antecedencia';
    }

    if (str_contains($mensagemNormalizada, 'exige cenário') || str_contains($mensagemNormalizada, 'exige cenario')) {
        return 'cenario_obrigatorio';
    }

    if (str_contains($mensagemNormalizada, 'cenário indisponível') || str_contains($mensagemNormalizada, 'cenario indisponivel')) {
        return 'cenario_mes';
    }

    if (str_contains($mensagemNormalizada, 'cenário não pertence') || str_contains($mensagemNormalizada, 'cenario nao pertence')) {
        return 'cenario_nicho';
    }

    return 'banco';
}
