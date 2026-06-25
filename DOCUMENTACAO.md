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

## 4. Dica de Integração com a ESPN
A API da ESPN não precisa de Token. Basta o frontend fazer chamadas GET para:
- Tabela de Classificação: `https://site.api.espn.com/apis/v2/sports/soccer/fifa.world/standings`
- Placar dos Jogos: `https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard`
