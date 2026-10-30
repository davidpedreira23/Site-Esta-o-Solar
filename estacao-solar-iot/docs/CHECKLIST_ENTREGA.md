# Checklist de entrega

## Antes de subir no GitHub
- [ ] Rodar o projeto (XAMPP ou Docker) e conferir o painel funcionando
- [ ] Tirar os **prints** e salvar em `docs/img/` com os nomes abaixo (o README já aponta para eles)
  - [ ] `01-dashboard.png` — painel ao vivo com gráficos
  - [ ] `02-estacoes.png` — lista de estações
  - [ ] `03-estacao-detalhe.png` — detalhe da estação com a chave da API
  - [ ] `04-leituras.png` — lista de leituras
  - [ ] `05-visitantes.png` — lista de visitantes
  - [ ] `06-cadastro-visitante.png` — formulário público
  - [ ] `07-dispositivo.jpg` — foto do ESP32 montado com o OLED ligado
  - [ ] `08-banco.png` — tabelas no phpMyAdmin
- [ ] Trocar a senha do admin (`admin123`) e conferir que **não há** `.env` nem `config.h` no repositório
- [ ] Preencher **nomes da equipe** e o **link do vídeo** no README

## Entrega ao professor
1. [ ] Link do repositório **público** no GitHub (README estruturado, com prints e comentado)
2. [ ] Link do vídeo no YouTube (com a equipe apresentando os requisitos técnicos)

## Comandos do GitHub
```bash
git init
git add .
git commit -m "Estação Meteorológica Solar IoT - Feira Tecnológica"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/estacao-solar-iot.git
git push -u origin main
```
