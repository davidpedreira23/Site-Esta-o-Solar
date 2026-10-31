-- =============================================================================
-- DADOS INICIAIS (opcional, para demonstração na Feira)
-- Execute DEPOIS do schema.sql
-- =============================================================================
SET NAMES utf8mb4;   -- garante acentos corretos ao importar (mysql, phpMyAdmin, etc.)
USE estacao_solar;
SET time_zone = '-03:00';   -- mesmo fuso usado pela aplicação (Horário de Brasília), para as horas baterem

-- Administrador padrão  ->  e-mail: admin@feira.com  |  senha: admin123
-- (TROQUE a senha antes de publicar o sistema de verdade!)
INSERT INTO usuarios (nome, email, senha_hash) VALUES
('Administrador', 'admin@feira.com', '$2y$10$rcnecFroHq2CgzBpKfI0auk/n3qKkqygzdFNsGefxBq1LakAR9GGC');

-- Estação de demonstração. A api_key abaixo é a que vai no firmware do ESP32.
INSERT INTO estacoes (nome, local, descricao, latitude, longitude, api_key) VALUES
('Estação Solar 01', 'Estande da Feira Tecnológica',
 'ESP32 + DHT22 (temperatura/umidade) + LDR (luminosidade) + display OLED, alimentada por painel solar e bateria 18650.',
 -23.550520, -46.633308, 'estacao-solar-demo-0001'),
('Estação Solar 02', 'Pátio da escola',
 'Segunda unidade de teste para comparar medições em outro ambiente.',
 -23.561414, -46.655881, 'estacao-solar-demo-0002');

-- 48 leituras fictícias (últimas 24h, uma a cada 30 min) para o gráfico já nascer preenchido.
-- Usa CTE recursiva só para gerar a sequência 0..47; temperatura e luz seguem uma senoide diária.
INSERT INTO leituras (estacao_id, temperatura, umidade, luminosidade, ldr_bruto, origem, registrada_em)
WITH RECURSIVE seq AS (
    SELECT 0 AS n UNION ALL SELECT n + 1 FROM seq WHERE n < 47
), base AS (
    SELECT n,
           NOW() - INTERVAL (47 - n) * 30 MINUTE AS t
    FROM seq
), calc AS (
    SELECT t,
           HOUR(t) + MINUTE(t) / 60 AS h
    FROM base
)
SELECT 1,
       ROUND(23 + 6 * SIN((h - 9) * PI() / 12) + RAND(), 2),
       ROUND(LEAST(100, 65 - 20 * SIN((h - 9) * PI() / 12) + RAND() * 3), 2),
       LEAST(100, ROUND(GREATEST(0, SIN((h - 6) * PI() / 12)) * 92 + RAND() * 5)),
       LEAST(4095, ROUND(GREATEST(0, SIN((h - 6) * PI() / 12)) * 3800 + RAND() * 200)),
       'esp32',
       t
FROM calc;

UPDATE estacoes SET ultimo_contato = (SELECT MAX(registrada_em) FROM leituras WHERE estacao_id = 1) WHERE id = 1;

INSERT INTO visitantes (nome, email, instituicao, cidade, estacao_id, comentario) VALUES
('Maria Souza',  'maria@exemplo.com',  'Escola Técnica Estadual', 'São Paulo', 1, 'Adorei ver os dados em tempo real!'),
('João Pereira', 'joao@exemplo.com',   'Fatec Zona Leste',        'São Paulo', 1, 'Projeto muito bem montado.'),
('Ana Lima',     'ana@exemplo.com',    NULL,                      'Guarulhos', 2, NULL);
