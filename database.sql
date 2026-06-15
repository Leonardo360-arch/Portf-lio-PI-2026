CREATE DATABASE IF NOT EXISTS `danny`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `danny`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TRIGGER IF EXISTS `trigger_validar_agendamento_insert`;
DROP TRIGGER IF EXISTS `trigger_validar_agendamento_update`;
DROP TRIGGER IF EXISTS `trigger_newborn`;
DROP TRIGGER IF EXISTS `trigger_newborn_insert`;
DROP TRIGGER IF EXISTS `trigger_newborn_update`;

DROP PROCEDURE IF EXISTS `finalizar_agendamentos`;


DROP TABLE IF EXISTS `galeria`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `agendamentos`;
DROP TABLE IF EXISTS `cenarios`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `tipos_servico`;
DROP TABLE IF EXISTS `fotografos`;
DROP TABLE IF EXISTS `nichos`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `nichos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nichos_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fotografos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tipos_servico` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `nicho_id` BIGINT UNSIGNED NOT NULL,
  `exige_cenario` TINYINT(1) NOT NULL DEFAULT 0,
  `duracao_minutos` INT NOT NULL DEFAULT 60,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tipos_servico_nicho_nome` (`nicho_id`, `nome`),
  KEY `idx_tipos_servico_nicho` (`nicho_id`),
  CONSTRAINT `fk_tipos_servico_nicho`
    FOREIGN KEY (`nicho_id`) REFERENCES `nichos` (`id`),
  CONSTRAINT `chk_tipos_servico_duracao`
    CHECK (`duracao_minutos` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `admins` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `senha_hash` TEXT NOT NULL,
  `senha_provisoria` TINYINT(1) NOT NULL DEFAULT 1,
  `tipo` ENUM('CLIENTE','ADMIN') NOT NULL,
  `status_admin` VARCHAR(20) NOT NULL DEFAULT 'APROVADO',
  `nicho_id` BIGINT UNSIGNED DEFAULT NULL,
  `tipo_servico_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admins_email` (`email`),
  KEY `idx_admins_tipo` (`tipo`),
  KEY `idx_admins_nicho` (`nicho_id`),
  KEY `idx_admins_tipo_servico` (`tipo_servico_id`),
  CONSTRAINT `fk_admins_nicho`
    FOREIGN KEY (`nicho_id`) REFERENCES `nichos` (`id`),
  CONSTRAINT `fk_admins_tipo_servico`
    FOREIGN KEY (`tipo_servico_id`) REFERENCES `tipos_servico` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cenarios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `nicho_id` BIGINT UNSIGNED NOT NULL,
  `mes` INT DEFAULT NULL,
  `ano` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cenarios_nicho` (`nicho_id`),
  KEY `idx_cenarios_periodo` (`mes`, `ano`),
  CONSTRAINT `fk_cenarios_nicho`
    FOREIGN KEY (`nicho_id`) REFERENCES `nichos` (`id`),
  CONSTRAINT `chk_cenarios_mes`
    CHECK (`mes` IS NULL OR `mes` BETWEEN 1 AND 12)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `agendamentos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` BIGINT UNSIGNED NOT NULL,
  `fotografo_id` BIGINT UNSIGNED NOT NULL,
  `tipo_servico_id` BIGINT UNSIGNED NOT NULL,
  `cenario_id` BIGINT UNSIGNED DEFAULT NULL,
  `data` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `observacao` TEXT DEFAULT NULL,
  `valor` DECIMAL(10,2) DEFAULT NULL,
  `status` ENUM(
    'PENDENTE',
    'CONFIRMADO',
    'RECUSADO',
    'CANCELADO',
    'CONCLUIDO'
  ) NOT NULL DEFAULT 'PENDENTE',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_agendamentos_admin` (`admin_id`),
  KEY `idx_agendamentos_fotografo_data` (`fotografo_id`, `data`),
  KEY `idx_agendamentos_tipo_servico` (`tipo_servico_id`),
  KEY `idx_agendamentos_cenario` (`cenario_id`),
  KEY `idx_agendamentos_status` (`status`),
  CONSTRAINT `fk_agendamentos_admin`
    FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  CONSTRAINT `fk_agendamentos_fotografo`
    FOREIGN KEY (`fotografo_id`) REFERENCES `fotografos` (`id`),
  CONSTRAINT `fk_agendamentos_tipo_servico`
    FOREIGN KEY (`tipo_servico_id`) REFERENCES `tipos_servico` (`id`),
  CONSTRAINT `fk_agendamentos_cenario`
    FOREIGN KEY (`cenario_id`) REFERENCES `cenarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `galeria` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agendamento_id` BIGINT UNSIGNED NOT NULL,
  `url_imagem` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_galeria_agendamento` (`agendamento_id`),
  CONSTRAINT `fk_galeria_agendamento`
    FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DELIMITER //

CREATE PROCEDURE `finalizar_agendamentos`()
BEGIN
  UPDATE agendamentos a
  INNER JOIN tipos_servico ts ON ts.id = a.tipo_servico_id
  SET a.status = 'CONCLUIDO'
  WHERE a.status = 'CONFIRMADO'
    AND DATE_ADD(TIMESTAMP(a.data, a.hora_inicio), INTERVAL ts.duracao_minutos MINUTE) < CURRENT_TIMESTAMP;
END//

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
END//

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
END//

DELIMITER ;


INSERT INTO nichos (`nome`, `slug`) VALUES
('Gestante', 'gestante'),
('Família', 'familia'),
('Newborn', 'newborn'),
('Infantil', 'infantil'),
('Casamento', 'casamento'),
('Empresarial', 'empresarial');

INSERT INTO tipos_servico (`nome`, `nicho_id`, `exige_cenario`, `duracao_minutos`) VALUES
('Ensaio Gestante', (SELECT id FROM nichos WHERE slug = 'gestante'), 1, 120),
('Ensaio Família', (SELECT id FROM nichos WHERE slug = 'familia'), 1, 120),
('Newborn', (SELECT id FROM nichos WHERE slug = 'newborn'), 1, 120),
('Ensaio Infantil', (SELECT id FROM nichos WHERE slug = 'infantil'), 1, 90),
('Casamento Meio Período', (SELECT id FROM nichos WHERE slug = 'casamento'), 0, 240),
('Casamento Completo', (SELECT id FROM nichos WHERE slug = 'casamento'), 0, 1440),
('Foto Profissional', (SELECT id FROM nichos WHERE slug = 'empresarial'), 0, 60),
('Ensaio Corporativo', (SELECT id FROM nichos WHERE slug = 'empresarial'), 0, 120),
('Branding Pessoal', (SELECT id FROM nichos WHERE slug = 'empresarial'), 1, 120),
('Fotos de Equipe', (SELECT id FROM nichos WHERE slug = 'empresarial'), 0, 180),
('Fotos para LinkedIn', (SELECT id FROM nichos WHERE slug = 'empresarial'), 0, 60);

INSERT INTO admins (`nome`, `email`, `telefone`, `senha_hash`, `senha_provisoria`, `tipo`) VALUES
('Administrador Principal', 'admin@danny.com', '19999999999', '$2y$12$VRA5Ae/gY/i5sLUQVyb7IeYz3KV6lI5e/QTo/VHAw90.mi9/SJVAm', 0, 'ADMIN');

INSERT INTO fotografos (`nome`, `telefone`, `email`) VALUES
('Dany Cardozo', '19999999999', 'contato@danycardozo.com');

INSERT INTO cenarios (`nome`, `nicho_id`, `mes`, `ano`) VALUES
('Cenário Gestante Clássico', (SELECT id FROM nichos WHERE slug = 'gestante'), NULL, NULL),
('Cenário Família Clean', (SELECT id FROM nichos WHERE slug = 'familia'), NULL, NULL),
('Cenário Newborn Aconchego', (SELECT id FROM nichos WHERE slug = 'newborn'), NULL, NULL),
('Cenário Infantil Colorido', (SELECT id FROM nichos WHERE slug = 'infantil'), NULL, NULL),
('Estúdio Clean Profissional', (SELECT id FROM nichos WHERE slug = 'empresarial'), NULL, NULL),
('Ambiente Corporativo', (SELECT id FROM nichos WHERE slug = 'empresarial'), NULL, NULL),
('Cenário Natalino', (SELECT id FROM nichos WHERE slug = 'familia'), 11, YEAR(CURRENT_DATE)),
('Cenário Natalino', (SELECT id FROM nichos WHERE slug = 'familia'), 12, YEAR(CURRENT_DATE)),
('Cenário Páscoa', (SELECT id FROM nichos WHERE slug = 'infantil'), 3, YEAR(CURRENT_DATE)),
('Cenário Páscoa', (SELECT id FROM nichos WHERE slug = 'infantil'), 4, YEAR(CURRENT_DATE)),
('Cenário Dia das Mães', (SELECT id FROM nichos WHERE slug = 'familia'), 4, YEAR(CURRENT_DATE)),
('Cenário Dia das Mães', (SELECT id FROM nichos WHERE slug = 'familia'), 5, YEAR(CURRENT_DATE));
