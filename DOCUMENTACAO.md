# Projeto Bolão da Copa - Documentação do Desenvolvedor

## 1. Problema e Solução
**O Problema:** Durante grandes campeonatos, os torcedores não possuem uma plataforma unificada e gratuita onde possam, simultaneamente, acompanhar a classificação/tabela em tempo real e registrar seus palpites para os jogos em um ambiente social com os amigos.
**A Solução:** Uma aplicação web dinâmica construída em PHP e Javascript que atua como um "Mural de Palpites" (Bolão). O sistema consome a API oficial da ESPN para dados em tempo real e armazena as interações dos usuários de forma leve e descentralizada, permitindo o compartilhamento de palpites por links públicos.

---

## 2. Arquitetura e Integração (Back x Front)
O projeto foi desenhado para ser rápido e não onerar a hospedagem gratuita (InfinityFree):
- **O Backend (PHP/MySQL)** age apenas como uma **API de Armazenamento Seguro**. Ele não se comunica com a ESPN. Ele apenas cadastra o usuário, gerencia a sessão (login) e guarda/retorna os palpites (Strings como "CASA" ou "EMPATE").
- **O Frontend (Javascript)** é o **Motor de Exibição e Regras**. É o Javascript rodando no navegador do usuário que fará os `fetch()` para a API da ESPN, receberá o placar oficial, cruzará com o que o nosso PHP devolveu, e exibirá na tela os acertos e a pontuação localmente.

### O Requisito CRUD do Professor:
Nossa aplicação atende aos 4 requisitos do CRUD de forma elegante no Banco de Dados:
- **Create:** Cadastro de Usuário e Criação de Palpites (`cadastrar.php` e `salvar_palpite.php`).
- **Read:** Login, Busca dos Palpites próprios e Busca do Bolão Compartilhado de terceiros.
- **Update:** Alteração de um palpite existente (`ON DUPLICATE KEY UPDATE` no `salvar_palpite.php`).
- **Delete:** Exclusão completa da conta do usuário (`deletar_conta.php`), que apaga seus dados e palpites via *Cascade*.

---

## 3. Guia de Endpoints (Para o Front-end)

> **Aviso Importante para o Front-end:** Como o PHP lida com Sessões (`$_SESSION`), todas as requisições `fetch` para endpoints protegidos devem incluir as credenciais/cookies nativos. Quase todos os requests recebem JSON via `php://input`, então não esqueça de usar o cabeçalho `'Content-Type': 'application/json'`.

### 🔐 Autenticação e Conta

#### POST `/backend/cadastrar.php`
- **O que faz:** Cria a conta e gera um token de compartilhamento.
- **Corpo (FormData ou urlencoded):** `nome`, `email`, `senha`
- **Retorno:** `{ "sucesso": true, "mensagem": "..." }`

#### POST `/backend/login.php`
- **O que faz:** Verifica credenciais e inicia a Sessão.
- **Corpo (FormData ou urlencoded):** `email`, `senha`
- **Retorno:** `{ "sucesso": true, "nome": "João" }`

#### POST `/backend/deletar_conta.php`
- **O que faz:** Exclui a conta logada e todos os seus palpites para sempre (Requisito Delete).
- **Corpo:** Vazio.
- **Retorno:** `{ "sucesso": true }`

---

### ⚽ Palpites

#### POST `/backend/salvar_palpite.php`
- **O que faz:** Salva múltiplos palpites da rodada de uma só vez (Insert ou Update).
- **Corpo esperado (JSON Array):**
```json
[
  {"jogo_id": "760462", "resultado_escolhido": "CASA", "fase": "Grupos"},
  {"jogo_id": "760463", "resultado_escolhido": "EMPATE", "fase": "Grupos"}
]
```

#### GET `/backend/buscar_palpites.php`
- **O que faz:** Traz as informações do usuário logado para montar o painel dele.
- **Retorno:** Retorna o token para o botão de "Compartilhar meu bolão" e a lista de palpites para o front-end pintar os botões previamente escolhidos de verde.
```json
{
  "sucesso": true,
  "token_compartilhamento": "ab12c3d...",
  "palpites": [
    {"jogo_id": "760462", "resultado_escolhido": "CASA", "fase": "Grupos"}
  ]
}
```

#### POST `/backend/salvar_campeao.php`
- **O que faz:** Salva o palpite final do torneio.
- **Corpo esperado (JSON):** `{"time_campeao_id": "Brasil", "time_vice_id": "França"}`

---

### 🌐 Área Pública (Compartilhamento)

#### GET `/backend/bolao_compartilhado.php?token=XYZ`
- **O que faz:** Permite que qualquer pessoa veja os palpites de um amigo sem precisar logar. O front-end usa essa URL e passa o token na Query String.
- **Retorno:**
```json
{
  "sucesso": true,
  "nome_dono": "João Silva",
  "palpites": [
    {"jogo_id": "760462", "resultado_escolhido": "CASA"}
  ]
}
```

**💡 Lógica de Implementação do Compartilhamento (Passo a Passo para o Frontend):**
1. Na tela principal logada (`painel.html`), quando você buscar os palpites da pessoa em `/backend/buscar_palpites.php`, o PHP vai te devolver um `token_compartilhamento` (Ex: `abc123xyz`).
2. Com esse token em mãos, crie um campo ou botão de "Copiar Link" na tela, contendo um link gerado por você, no formato: `http://localhost/frontend/compartilhado.html?token=abc123xyz`.
3. Crie a página `compartilhado.html`. Ela será *Read-Only* (somente leitura), não terá os botões interativos de votar, servirá apenas para mostrar o resultado.
4. Assim que alguém carregar a página `compartilhado.html`, use o Javascript para fisgar aquele Token direto da URL do navegador:
   ```javascript
   const urlParams = new URLSearchParams(window.location.search);
   const tokenDaUrl = urlParams.get('token');
   ```
5. Jogue esse token num `fetch()` para o endpoint público: `fetch('../backend/bolao_compartilhado.php?token=' + tokenDaUrl)`.
6. O PHP vai te devolver o "nome_dono" (Ex: "João Silva") e o array com as apostas dele. Use isso para desenhar uma tela dizendo "Este é o Bolão do João Silva!" e cruze os dados com a API da ESPN exatamente como você fez no painel original.

## 4. Dica de Integração com a ESPN (Para o Front-end)
A API da ESPN é pública e não precisa de Token. Aqui está uma prévia de como os dados retornam para facilitar a construção da interface:

### A) Placar e Jogos (Scoreboard)
**Endpoint:** `GET https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard`

Neste endpoint você pega os jogos e manda pro usuário votar.
**Onde encontrar os dados úteis no JSON retornado:**
- `events`: Array principal contendo todos os jogos do dia/rodada.
- `events[i].id`: É o **`jogo_id`** que você deve enviar para o nosso Backend quando o usuário votar!
- `events[i].status.type.state`: Diz se o jogo não começou (`pre`), está rolando (`in`) ou acabou (`post`).
- `events[i].competitions[0].competitors`: Array com os 2 times jogando.
  - O índice `0` é o time da CASA (`homeAway: "home"`).
  - O índice `1` é o time VISITANTE (`homeAway: "away"`).
- `...competitors[x].team.name`: Nome da seleção (ex: "Brazil").
- `...competitors[x].team.logo`: Link da imagem da bandeira/escudo do time!
- `...competitors[x].score`: Placar do time (útil para verificar quem ganhou e pintar a tela do bolão de verde).

### B) Tabelas e Grupos (Standings)
**Endpoint:** `GET https://site.api.espn.com/apis/v2/sports/soccer/fifa.world/standings`

Neste endpoint você monta a tabela de classificação clássica.
**Onde encontrar os dados úteis no JSON retornado:**
- `children`: Array representando os Grupos da Copa (Grupo A, Grupo B, etc).
- `children[i].name`: O nome do grupo (ex: "Group A").
- `children[i].standings.entries`: Array com os 4 times do grupo, **já ordenados do 1º ao 4º lugar**.
- `...entries[j].team.name`: Nome da seleção.
- `...entries[j].stats`: Array de estatísticas do time na tabela. Procure pelo objeto onde `type == "points"` para exibir os pontos, `type == "wins"` para vitórias, etc.

### C) Informações do Torneio e Mata-mata
**Endpoint:** `GET https://sports.core.api.espn.com/v2/sports/soccer/leagues/fifa.world`

Este endpoint traz as informações estruturais do torneio. Para as chaves de mata-mata (Oitavas, Quartas, Semi e Final), a API da ESPN funciona da seguinte forma:
- No endpoint de **Placar e Jogos (`scoreboard`)**, os jogos da fase de mata-mata vão aparecer normalmente.
- O que muda é que dentro de `events[i].season` ou `events[i].notes`, a ESPN vai mandar uma flag identificando que aquele jogo é um "Round of 16" (Oitavas) ou "Quarterfinal" (Quartas). 
- O frontend apenas precisa ler o texto/tipo do jogo retornado no `scoreboard` e agrupá-los visualmente em chaves para o usuário.

### D) Outros Endpoints Interessantes (Bônus para o Front-end)
A ESPN possui uma infinidade de dados públicos debaixo do caminho `.../fifa.world/`. Caso você queira criar seções extras (como "Estatísticas" ou "Notícias"), aqui estão algumas rotas úteis:
- **Equipes e Elencos (`/teams` e `/teams/{id}/athletes`):** Retorna os times da copa e todos os jogadores convocados com foto, idade e posição.
- **Estatísticas Globais (`/leaders`):** Lista quem fez mais gols (artilharia), quem deu mais assistências, estatísticas de cartões, etc.
- **Lance a Lance / Resumo:** Acessando os detalhes de um `jogo_id` específico, você tem o "play-by-play", que é a narrativa em texto minuto a minuto (ex: "Chute de fora da área defendido..."), posse de bola, etc.
- **Notícias (`/news`):** Manchetes e links de imagens oficiais sobre o andamento do torneio.
