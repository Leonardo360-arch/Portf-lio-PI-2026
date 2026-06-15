-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.0.46 - MySQL Community Server - GPL
-- OS do Servidor:               Linux
-- HeidiSQL Versão:              12.17.0.7270
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para estudio_dani_duarte
CREATE DATABASE IF NOT EXISTS `estudio_dani_duarte` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `estudio_dani_duarte`;

-- Copiando estrutura para tabela estudio_dani_duarte.agendamentos
CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `fotografo_id` bigint unsigned NOT NULL,
  `tipo_servico_id` bigint unsigned NOT NULL,
  `cenario_id` bigint unsigned DEFAULT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `observacao` text,
  `status` enum('CONFIRMADO','PENDENTE_CANCELAMENTO','CANCELADO','CONCLUIDO') DEFAULT 'CONFIRMADO',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fotografo_id` (`fotografo_id`),
  KEY `tipo_servico_id` (`tipo_servico_id`),
  KEY `cenario_id` (`cenario_id`),
  CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`fotografo_id`) REFERENCES `fotografos` (`id`),
  CONSTRAINT `agendamentos_ibfk_3` FOREIGN KEY (`tipo_servico_id`) REFERENCES `tipos_servico` (`id`),
  CONSTRAINT `agendamentos_ibfk_4` FOREIGN KEY (`cenario_id`) REFERENCES `cenarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.agendamentos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela estudio_dani_duarte.cenarios
CREATE TABLE IF NOT EXISTS `cenarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `nicho_id` bigint unsigned NOT NULL,
  `mes` int NOT NULL,
  `ano` int NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `nicho_id` (`nicho_id`),
  CONSTRAINT `cenarios_ibfk_1` FOREIGN KEY (`nicho_id`) REFERENCES `nichos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.cenarios: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela estudio_dani_duarte.disponibilidade
CREATE TABLE IF NOT EXISTS `disponibilidade` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fotografo_id` bigint unsigned NOT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fotografo_id` (`fotografo_id`),
  CONSTRAINT `disponibilidade_ibfk_1` FOREIGN KEY (`fotografo_id`) REFERENCES `fotografos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.disponibilidade: ~0 rows (aproximadamente)

-- Copiando estrutura para procedure estudio_dani_duarte.finalizar_agendamentos
DELIMITER //
CREATE PROCEDURE `finalizar_agendamentos`()
BEGIN

    UPDATE agendamentos
    SET status = 'CONCLUIDO'
    WHERE status = 'CONFIRMADO'
      AND TIMESTAMP(data, hora_fim) < CURRENT_TIMESTAMP;

END//
DELIMITER ;

-- Copiando estrutura para tabela estudio_dani_duarte.fotografos
CREATE TABLE IF NOT EXISTS `fotografos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.fotografos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela estudio_dani_duarte.galeria
CREATE TABLE IF NOT EXISTS `galeria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agendamento_id` bigint unsigned NOT NULL,
  `url_imagem` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `agendamento_id` (`agendamento_id`),
  CONSTRAINT `galeria_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.galeria: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela estudio_dani_duarte.nichos
CREATE TABLE IF NOT EXISTS `nichos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.nichos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela estudio_dani_duarte.tipos_servico
CREATE TABLE IF NOT EXISTS `tipos_servico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `nicho_id` bigint unsigned NOT NULL,
  `exige_cenario` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nicho_id` (`nicho_id`,`nome`),
  CONSTRAINT `tipos_servico_ibfk_1` FOREIGN KEY (`nicho_id`) REFERENCES `nichos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.tipos_servico: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela estudio_dani_duarte.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `senha_hash` text NOT NULL,
  `senha_provisoria` tinyint(1) DEFAULT '1',
  `tipo` enum('CLIENTE','ADMIN') NOT NULL,
  `nicho_id` bigint unsigned DEFAULT NULL,
  `tipo_servico_id` bigint unsigned DEFAULT NULL,
  `data_previsao_parto` date DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `nicho_id` (`nicho_id`),
  KEY `tipo_servico_id` (`tipo_servico_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`nicho_id`) REFERENCES `nichos` (`id`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`tipo_servico_id`) REFERENCES `tipos_servico` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela estudio_dani_duarte.usuarios: ~0 rows (aproximadamente)

-- Copiando estrutura para view estudio_dani_duarte.vw_agendamentos
-- Criando tabela temporária para evitar erros de dependência de VIEW
CREATE TABLE `vw_agendamentos` (
	`id` BIGINT UNSIGNED NOT NULL,
	`cliente` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`tipo_servico` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`cenario` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`data` DATE NOT NULL,
	`hora_inicio` TIME NOT NULL,
	`hora_fim` TIME NOT NULL,
	`status` ENUM('CONFIRMADO','PENDENTE_CANCELAMENTO','CANCELADO','CONCLUIDO') NULL COLLATE 'utf8mb4_0900_ai_ci'
);

-- Copiando estrutura para trigger estudio_dani_duarte.trigger_antecedencia
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_antecedencia` BEFORE INSERT ON `agendamentos` FOR EACH ROW BEGIN

    IF NEW.data < CURRENT_DATE + INTERVAL 7 DAY THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Necessário 7 dias de antecedência';

    END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger estudio_dani_duarte.trigger_cancelamento
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_cancelamento` BEFORE UPDATE ON `agendamentos` FOR EACH ROW BEGIN

    DECLARE data_hora DATETIME;

    IF NEW.status = 'PENDENTE_CANCELAMENTO' THEN

        SET data_hora = TIMESTAMP(NEW.data, NEW.hora_inicio);

        IF data_hora <= CURRENT_TIMESTAMP + INTERVAL 48 HOUR THEN

            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cancelamento só permitido com 48h';

        END IF;

    END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger estudio_dani_duarte.trigger_cenario
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_cenario` BEFORE INSERT ON `agendamentos` FOR EACH ROW BEGIN

    DECLARE exige BOOLEAN;

    SELECT exige_cenario
    INTO exige
    FROM tipos_servico
    WHERE id = NEW.tipo_servico_id;

    IF exige = TRUE AND NEW.cenario_id IS NULL THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Este tipo exige cenário';

    END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger estudio_dani_duarte.trigger_conflito
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_conflito` BEFORE INSERT ON `agendamentos` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1
        FROM agendamentos
        WHERE fotografo_id = NEW.fotografo_id
          AND data = NEW.data
          AND status = 'CONFIRMADO'
          AND NEW.hora_inicio < hora_fim
          AND NEW.hora_fim > hora_inicio
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Conflito de horário';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger estudio_dani_duarte.trigger_newborn
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_newborn` AFTER UPDATE ON `usuarios` FOR EACH ROW BEGIN

    IF OLD.data_nascimento IS NULL 
       AND NEW.data_nascimento IS NOT NULL THEN

        UPDATE usuarios
        SET tipo_servico_id = (
            SELECT id
            FROM tipos_servico
            WHERE LOWER(nome) = 'newborn'
            LIMIT 1
        )
        WHERE id = NEW.id;

    END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removendo tabela temporária e criando a estrutura VIEW final
DROP TABLE IF EXISTS `vw_agendamentos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_agendamentos` AS select `a`.`id` AS `id`,`u`.`nome` AS `cliente`,`ts`.`nome` AS `tipo_servico`,`c`.`nome` AS `cenario`,`a`.`data` AS `data`,`a`.`hora_inicio` AS `hora_inicio`,`a`.`hora_fim` AS `hora_fim`,`a`.`status` AS `status` from (((`agendamentos` `a` join `usuarios` `u` on((`a`.`usuario_id` = `u`.`id`))) join `tipos_servico` `ts` on((`a`.`tipo_servico_id` = `ts`.`id`))) left join `cenarios` `c` on((`a`.`cenario_id` = `c`.`id`)))
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
