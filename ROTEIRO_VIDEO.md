# Roteiro do vídeo (YouTube) — sugestão de ~8 a 10 minutos

> O professor pede que a **equipe** apresente os requisitos técnicos. Divida os blocos entre os integrantes.

| # | Tempo | Quem | O que mostrar |
|---|---|---|---|
| 1 | 0:00–0:45 | Integrante 1 | Apresentação do grupo e do tema: *Estação Meteorológica Solar*. Mostre o **dispositivo físico** ligado (OLED com temperatura, umidade, luz). |
| 2 | 0:45–1:45 | Integrante 2 | **Hardware**: ESP32, DHT22, LDR, OLED, painel solar + bateria 18650. Mostre o esquema em `hardware/` (Fritzing). |
| 3 | 1:45–3:00 | Integrante 3 | **Banco de dados**: abra o `database/MODELO_LOGICO.md` (diagrama) e o `schema.sql` mostrando os **comentários**. Explique as 3 tabelas, as chaves estrangeiras e o CASCADE. Abra o phpMyAdmin com as tabelas. |
| 4 | 3:00–4:15 | Integrante 4 | **Arquitetura em serviços**: mostre as pastas `Controllers → Services → Database` e explique que o controller não tem SQL. Abra um Service (ex.: `EstacaoService.php`) e os comentários. |
| 5 | 4:15–6:30 | Integrante 1 | **CRUD ao vivo** (login `admin@feira.com`): <br>• Estações: criar, editar, excluir <br>• Leituras: criar manual, editar, excluir, filtro e paginação <br>• Visitantes: cadastro público + busca + editar + excluir |
| 6 | 6:30–8:00 | Integrante 2 | **IoT ao vivo**: deixe o painel (`/`) aberto, mostre o ESP32 enviando (Monitor Serial com `HTTP 201`), cubra o LDR com a mão e veja a luminosidade cair no gráfico. (Plano B: `php tools/simular_esp32.php`.) |
| 7 | 8:00–9:00 | Integrante 3 | **API e segurança**: mostre no Postman/curl o `POST /api/leituras` com e sem `X-API-KEY` (201 × 401). Cite: prepared statements (SQL Injection), `htmlspecialchars` (XSS), token CSRF, `password_hash`. |
| 8 | 9:00–9:30 | Todos | **GitHub**: mostre o repositório e o README com prints. Encerramento e agradecimento. |

## Dicas de gravação
* Grave a tela em 1080p (OBS Studio ou Win+G) e narre com calma.
* Deixe o zoom do navegador em 110–125% para a banca enxergar.
* Teste o Wi-Fi do local antes: use o **roteador/hotspot do celular** e confira o IP do servidor no `config.h`.
* Título sugerido: *"Estação Meteorológica Solar IoT — ESP32 + PHP + MySQL (Feira Tecnológica)"*.
