-- =============================================================================
-- ESTAÇÃO METEOROLÓGICA SOLAR IoT  |  Feira Tecnológica
-- Script de criação do banco de dados (modelo FÍSICO)
-- SGBD: MySQL 8+ / MariaDB 10.5+  (testado com MariaDB 10.11)
--
-- MODELO LÓGICO (resumo):
--
--   usuarios                (administradores do sistema)
--
--   estacoes 1 ────< N leituras      (uma estação gera várias leituras)
--   estacoes 1 ────< N visitantes    (visitante registra qual estação viu; opcional)
--
-- O diagrama entidade-relacionamento completo está em: database/MODELO_LOGICO.md
-- =============================================================================

SET NAMES utf8mb4;   -- garante acentos corretos ao importar (mysql, phpMyAdmin, etc.)

CREATE DATABASE IF NOT EXISTS estacao_solar
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE estacao_solar;

-- Recria as tabelas na ordem correta (filhas primeiro) para permitir reexecutar o script
DROP TABLE IF EXISTS leituras;
DROP TABLE IF EXISTS visitantes;
DROP TABLE IF EXISTS estacoes;
DROP TABLE IF EXISTS usuarios;

-- -----------------------------------------------------------------------------
-- TABELA: usuarios
-- Quem pode entrar no painel administrativo (gerenciar estações, leituras, visitantes).
-- A senha NUNCA é guardada em texto puro: só o hash gerado por password_hash() do PHP.
-- -----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    senha_hash  VARCHAR(255)  NOT NULL,                       -- bcrypt (60 chars) cabe folgado
    criado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)                      -- não repete e-mail
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: estacoes   (ENTIDADE PRINCIPAL 1 - CRUD completo)
-- Cada linha representa UM dispositivo ESP32 + sensores (DHT22 + LDR).
-- A api_key é a "senha" do dispositivo: o ESP32 envia no cabeçalho X-API-KEY.
-- -----------------------------------------------------------------------------
CREATE TABLE estacoes (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(100)   NOT NULL,
    local           VARCHAR(150)   NOT NULL,                  -- ex.: "Pátio da escola"
    descricao       TEXT           NULL,
    latitude        DECIMAL(9,6)   NULL,                      -- -90 a 90
    longitude       DECIMAL(9,6)   NULL,                      -- -180 a 180
    api_key         VARCHAR(64)    NOT NULL,                  -- credencial do dispositivo
    ativa           TINYINT(1)     NOT NULL DEFAULT 1,        -- 1 = aceita leituras, 0 = desativada
    ultimo_contato  DATETIME       NULL,                      -- última vez que o ESP32 enviou dados
    criado_em       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estacoes_api_key (api_key)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: leituras   (ENTIDADE PRINCIPAL 2 - CRUD completo)
-- Cada linha é uma medição enviada pelo ESP32 (ou lançada manualmente no painel).
-- Relacionamento N:1 com estacoes.
--   ON DELETE CASCADE -> se a estação for excluída, suas leituras vão junto
--                        (leitura sem estação não tem sentido).
-- -----------------------------------------------------------------------------
CREATE TABLE leituras (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    estacao_id     INT UNSIGNED    NOT NULL,
    temperatura    DECIMAL(5,2)    NOT NULL,                  -- °C   (DHT22: -40 a 80)
    umidade        DECIMAL(5,2)    NOT NULL,                  -- %    (DHT22: 0 a 100)
    luminosidade   TINYINT UNSIGNED NOT NULL,                 -- %    (LDR convertido: 0 a 100)
    ldr_bruto      SMALLINT UNSIGNED NULL,                    -- valor cru do ADC do ESP32 (0 a 4095)
    origem         ENUM('esp32','manual') NOT NULL DEFAULT 'esp32',
    registrada_em  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- Índice composto: acelera "últimas leituras da estação X" (consulta mais usada do dashboard)
    KEY idx_leituras_estacao_data (estacao_id, registrada_em),
    CONSTRAINT fk_leituras_estacao
        FOREIGN KEY (estacao_id) REFERENCES estacoes (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    -- Regras de integridade: o banco recusa valores impossíveis mesmo se a aplicação falhar
    CONSTRAINT ck_leituras_temp CHECK (temperatura BETWEEN -40 AND 80),
    CONSTRAINT ck_leituras_umid CHECK (umidade BETWEEN 0 AND 100),
    CONSTRAINT ck_leituras_luz  CHECK (luminosidade BETWEEN 0 AND 100)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- TABELA: visitantes   (cadastro de visitantes da Feira Tecnológica)
-- estacao_id é opcional: o visitante pode dizer qual estação visitou.
--   ON DELETE SET NULL -> se a estação sumir, o cadastro do visitante é mantido.
-- -----------------------------------------------------------------------------
CREATE TABLE visitantes (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(120)  NOT NULL,
    email         VARCHAR(150)  NOT NULL,
    instituicao   VARCHAR(150)  NULL,                         -- escola / empresa de origem
    cidade        VARCHAR(100)  NULL,
    estacao_id    INT UNSIGNED  NULL,
    comentario    VARCHAR(500)  NULL,
    visitado_em   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_visitantes_email (email),                   -- um cadastro por e-mail
    KEY idx_visitantes_estacao (estacao_id),
    CONSTRAINT fk_visitantes_estacao
        FOREIGN KEY (estacao_id) REFERENCES estacoes (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
