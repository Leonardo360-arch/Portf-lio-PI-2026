<?php

declare(strict_types=1);

namespace Danny;

use PDO;
use PDOException;

final class SchemaFixer
{
    private static bool $ran = false;

    public static function run(PDO $pdo): void
    {
        if (self::$ran) {
            return;
        }

        self::$ran = true;

        try {
            self::ensureTables($pdo);
            self::ensureColumns($pdo);
            self::removePendingCancelamento($pdo);
            self::replaceTriggers($pdo);
        } catch (PDOException $e) {
            // Não derruba a página. Se o usuário do banco não tiver permissão de ALTER/TRIGGER,
            // o erro real ainda aparecerá na ação, mas o site continua carregando.
            return;
        }
    }

    private static function columnExists(PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table_name
              AND COLUMN_NAME = :column_name
        ");

        $stmt->execute([
            ':table_name' => $table,
            ':column_name' => $column,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private static function tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table_name
        ");

        $stmt->execute([':table_name' => $table]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private static function ensureTables(PDO $pdo): void
    {
        if (self::tableExists($pdo, 'password_resets')) {
            return;
        }

        $pdo->exec("
            CREATE TABLE `password_resets` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `admin_id` BIGINT UNSIGNED NOT NULL,
              `token_hash` CHAR(64) NOT NULL,
              `expires_at` DATETIME NOT NULL,
              `used_at` DATETIME DEFAULT NULL,
              `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_password_resets_token_hash` (`token_hash`),
              KEY `idx_password_resets_admin` (`admin_id`),
              KEY `idx_password_resets_expires` (`expires_at`),
              CONSTRAINT `fk_password_resets_admin`
                FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`)
                ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function ensureColumns(PDO $pdo): void
    {
        if (!self::columnExists($pdo, 'fotografos', 'telefone')) {
            $pdo->exec("ALTER TABLE `fotografos` ADD COLUMN `telefone` VARCHAR(20) DEFAULT NULL");
        }

        if (!self::columnExists($pdo, 'fotografos', 'email')) {
            $pdo->exec("ALTER TABLE `fotografos` ADD COLUMN `email` VARCHAR(150) DEFAULT NULL");
        }

        if (!self::columnExists($pdo, 'admins', 'telefone')) {
            $pdo->exec("ALTER TABLE `admins` ADD COLUMN `telefone` VARCHAR(20) NOT NULL DEFAULT 'Não informado'");
        }

        if (!self::columnExists($pdo, 'admins', 'status_admin')) {
            $pdo->exec("ALTER TABLE `admins` ADD COLUMN `status_admin` VARCHAR(20) NOT NULL DEFAULT 'APROVADO' AFTER `tipo`");
            $pdo->exec("UPDATE `admins` SET `status_admin` = 'APROVADO' WHERE `id` = 1");
            $pdo->exec("UPDATE `admins` SET `status_admin` = 'PENDENTE' WHERE `tipo` = 'ADMIN' AND `id` <> 1");
        }

        if (!self::columnExists($pdo, 'agendamentos', 'valor')) {
            $pdo->exec("ALTER TABLE `agendamentos` ADD COLUMN `valor` DECIMAL(10,2) DEFAULT NULL AFTER `observacao`");
        }
    }

    private static function removePendingCancelamento(PDO $pdo): void
    {
        try {
            $pdo->exec("UPDATE `agendamentos` SET `status` = 'CANCELADO' WHERE `status` = 'PENDENTE_CANCELAMENTO'");
        } catch (PDOException $e) {
            // Se o enum antigo já não existir, ignora.
        }

        try {
            $pdo->exec("
                ALTER TABLE `agendamentos`
                MODIFY `status` ENUM('PENDENTE','CONFIRMADO','RECUSADO','CANCELADO','CONCLUIDO') NOT NULL DEFAULT 'PENDENTE'
            ");
        } catch (PDOException $e) {
            // Se o banco não permitir alterar enum agora, continua com as demais correções.
        }
    }

    private static function replaceTriggers(PDO $pdo): void
    {
        $pdo->exec("DROP TRIGGER IF EXISTS `trigger_validar_agendamento_insert`");
        $pdo->exec("DROP TRIGGER IF EXISTS `trigger_validar_agendamento_update`");

        $pdo->exec("
            CREATE TRIGGER `trigger_validar_agendamento_insert`
            BEFORE INSERT ON `agendamentos`
            FOR EACH ROW
            BEGIN
              DECLARE v_duracao INT DEFAULT 60;
              DECLARE v_exige_cenario TINYINT DEFAULT 0;
              DECLARE v_tipo_nicho_id BIGINT UNSIGNED DEFAULT NULL;
              DECLARE v_cenario_nicho_id BIGINT UNSIGNED DEFAULT NULL;
              DECLARE v_cenario_mes INT DEFAULT NULL;
              DECLARE v_cenario_ano INT DEFAULT NULL;

              IF NEW.status IS NULL THEN
                SET NEW.status = 'PENDENTE';
              END IF;

              IF NEW.status = 'CONFIRMADO' THEN
                IF NEW.data < CURRENT_DATE + INTERVAL 7 DAY THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'Necessário 7 dias de antecedência';
                END IF;

                IF NEW.hora_inicio < '06:00:00' OR NEW.hora_inicio >= '20:00:00' THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'O horário inicial deve estar dentro do período permitido de atendimento, entre 06:00 e 20:00';
                END IF;

                SELECT duracao_minutos, exige_cenario, nicho_id
                INTO v_duracao, v_exige_cenario, v_tipo_nicho_id
                FROM tipos_servico
                WHERE id = NEW.tipo_servico_id
                LIMIT 1;

                IF v_exige_cenario = 1 AND NEW.cenario_id IS NULL THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'Este tipo de serviço exige cenário';
                END IF;

                IF NEW.cenario_id IS NOT NULL THEN
                  SELECT nicho_id, mes, ano
                  INTO v_cenario_nicho_id, v_cenario_mes, v_cenario_ano
                  FROM cenarios
                  WHERE id = NEW.cenario_id
                  LIMIT 1;

                  IF v_cenario_nicho_id <> v_tipo_nicho_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cenário não pertence ao mesmo nicho do serviço';
                  END IF;

                  IF v_cenario_mes IS NOT NULL AND v_cenario_mes <> MONTH(NEW.data) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cenário indisponível para o mês escolhido';
                  END IF;

                  IF v_cenario_ano IS NOT NULL AND v_cenario_ano <> YEAR(NEW.data) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cenário indisponível para o ano escolhido';
                  END IF;
                END IF;

                IF EXISTS (
                  SELECT 1
                  FROM agendamentos a
                  INNER JOIN tipos_servico ts ON ts.id = a.tipo_servico_id
                  WHERE a.fotografo_id = NEW.fotografo_id
                    AND a.data = NEW.data
                    AND a.status = 'CONFIRMADO'
                    AND NEW.hora_inicio < ADDTIME(a.hora_inicio, SEC_TO_TIME(ts.duracao_minutos * 60))
                    AND ADDTIME(NEW.hora_inicio, SEC_TO_TIME(v_duracao * 60)) > a.hora_inicio
                ) THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'Conflito de horário com outro atendimento já confirmado para este fotógrafo';
                END IF;
              END IF;
            END
        ");

        $pdo->exec("
            CREATE TRIGGER `trigger_validar_agendamento_update`
            BEFORE UPDATE ON `agendamentos`
            FOR EACH ROW
            BEGIN
              DECLARE v_duracao INT DEFAULT 60;
              DECLARE v_exige_cenario TINYINT DEFAULT 0;
              DECLARE v_tipo_nicho_id BIGINT UNSIGNED DEFAULT NULL;
              DECLARE v_cenario_nicho_id BIGINT UNSIGNED DEFAULT NULL;
              DECLARE v_cenario_mes INT DEFAULT NULL;
              DECLARE v_cenario_ano INT DEFAULT NULL;

              IF NEW.status = 'CONFIRMADO' THEN
                IF NEW.data < CURRENT_DATE + INTERVAL 7 DAY THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'Necessário 7 dias de antecedência';
                END IF;

                IF NEW.hora_inicio < '06:00:00' OR NEW.hora_inicio >= '20:00:00' THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'O horário inicial deve estar dentro do período permitido de atendimento, entre 06:00 e 20:00';
                END IF;

                SELECT duracao_minutos, exige_cenario, nicho_id
                INTO v_duracao, v_exige_cenario, v_tipo_nicho_id
                FROM tipos_servico
                WHERE id = NEW.tipo_servico_id
                LIMIT 1;

                IF v_exige_cenario = 1 AND NEW.cenario_id IS NULL THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'Este tipo de serviço exige cenário';
                END IF;

                IF NEW.cenario_id IS NOT NULL THEN
                  SELECT nicho_id, mes, ano
                  INTO v_cenario_nicho_id, v_cenario_mes, v_cenario_ano
                  FROM cenarios
                  WHERE id = NEW.cenario_id
                  LIMIT 1;

                  IF v_cenario_nicho_id <> v_tipo_nicho_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cenário não pertence ao mesmo nicho do serviço';
                  END IF;

                  IF v_cenario_mes IS NOT NULL AND v_cenario_mes <> MONTH(NEW.data) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cenário indisponível para o mês escolhido';
                  END IF;

                  IF v_cenario_ano IS NOT NULL AND v_cenario_ano <> YEAR(NEW.data) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cenário indisponível para o ano escolhido';
                  END IF;
                END IF;

                IF EXISTS (
                  SELECT 1
                  FROM agendamentos a
                  INNER JOIN tipos_servico ts ON ts.id = a.tipo_servico_id
                  WHERE a.id <> OLD.id
                    AND a.fotografo_id = NEW.fotografo_id
                    AND a.data = NEW.data
                    AND a.status = 'CONFIRMADO'
                    AND NEW.hora_inicio < ADDTIME(a.hora_inicio, SEC_TO_TIME(ts.duracao_minutos * 60))
                    AND ADDTIME(NEW.hora_inicio, SEC_TO_TIME(v_duracao * 60)) > a.hora_inicio
                ) THEN
                  SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'Conflito de horário com outro atendimento já confirmado para este fotógrafo';
                END IF;
              END IF;
            END
        ");
    }
}
