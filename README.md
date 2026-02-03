# 📝 Collaborative List API

API RESTful de alta performance desenvolvida em Laravel 11 para gerenciamento de listas colaborativas com foco em integridade de dados e concorrência.

## 🏗️ Arquitetura do Sistema

O projeto segue os princípios da **Clean Architecture** e **S.O.L.I.D**, separando responsabilidades em camadas para facilitar a testabilidade e manutenção:

- **Controllers:** Portas de entrada da aplicação. Validam a requisição (via FormRequests) e orquestram a resposta usando Resources.
- **Services:** Camada de lógica de negócio pura. Aqui residem as regras de transações de banco de dados, cálculos e políticas de estado.
- **Resources (HATEOAS):** Transformam modelos em JSON, incluindo links dinâmicos que guiam o cliente sobre o próximo estado da aplicação.
- **Policies:** Centralizam a autorização, garantindo que usuários só acessem recursos que lhes pertencem ou foram compartilhados.

---

## 🔐 Fluxos Principais

### 🔄 Concorrência (Optimistic Locking)

Para evitar que dois usuários sobrescrevam o trabalho um do outro simultaneamente, implementamos uma trava de versão nos itens da lista.

1. O cliente lê o item com `version: 1`.
2. Ao atualizar, o cliente envia `version: 1`.
3. O servidor verifica: se a versão no banco ainda for `1`, o update ocorre e a versão sobe para `2`.
4. Se outro usuário já tiver atualizado para `2`, o servidor retorna `409 Conflict`.

### 🔗 Compartilhamento via Link (Invitations)

O sistema de convites utiliza tokens efêmeros e seguros:

1. **Geração:** O dono cria um convite com expiração (ex: 5 min) e limite de usos.
2. **Descoberta:** O convidado acessa um link que retorna os metadados do convite via `GET`.
3. **Aceite:** O cliente consome uma URL de `POST` fornecida pela API para se vincular à lista.

---

## 🛠️ Stack Técnica

- **Framework:** Laravel 12
- **Auth:** Laravel Sanctum (Token-based)
- **Testes:** PEST PHP
- **Banco de Dados:** PostgreSQL / MySQL (UUIDs como chaves primárias)

## 🧹 Manutenção Automática (Jobs)

A API conta com rotinas agendadas para garantir a limpeza do ambiente:

- **Invites:** Remoção automática de tokens expirados.
- **Inatividade:** Arquivamento de listas sem interação por mais de 30 dias.
- **Tokens:** Limpeza de `personal_access_tokens` órfãos.

---

## 🚀 Como Executar

1. Clone o repositório.
2. Configure o `.env` (especialmente `SANCTUM_STATEFUL_DOMAINS` e `FRONTEND_URL`).
3. Execute `php artisan migrate`.
4. Para rodar os testes: `php artisan test --parallel`.

---

Com certeza. Adicionar exemplos de **Request/Response** no README é o que transforma uma documentação técnica em um guia prático para desenvolvedores.

Aqui está o complemento para o seu `README.md`, focando no fluxo de convites que você desenhou com tanto cuidado:

---

### 📡 Documentação de Endpoints (Exemplos)

#### 1. Criar Convite

**POST** `/v1/lists/{list_uuid}/invitations`

> Gera um token de acesso para novos colaboradores.

- **Request Body:**

```json
{
    "max_uses": 5
}
```

- **Response (201 Created):**

```json
{
    "invitation": {
        "uuid": "019c2450-f539-709e-b261-f19b171de042",
        "max_uses": 5,
        "created_at": "2026-02-03T16:23:31Z",
        "expires_at": "2026-02-03T16:28:31Z",
        "share_url": "http://localhost/api/lists/uuid-da-lista/invitations/token-gerado"
    }
}
```

---

#### 2. Visualizar Convite (Landing Page do Convite)

**GET** `/v1/lists/{list_uuid}/invitations/{token}`

> Endpoint que o Front-end consome para exibir os detalhes do convite antes do aceite.

- **Response (200 OK):**

```json
{
    "invitation": {
        "uuid": "019c2450-f539-709e-b261-f19b171de042",
        "max_uses": 5,
        "uses": 0,
        "expires_at": "2026-02-03T16:28:31Z",
        "accept_url": "http://localhost/api/lists/uuid-da-lista/invitations/token-gerado/accept"
    }
}
```

---

#### 3. Aceitar Convite

**POST** `/v1/lists/{list_uuid}/invitations/{token}/accept`

> Efetiva a entrada do usuário logado na lista.

- **Response (200 OK):**

```json
{
    "accepted": true
}
```

- **Possíveis Erros:**
- `403 Forbidden`: Usuário não autenticado.
- `404 Not Found`: Token não existe ou não pertence a esta lista.
- `409 Conflict`: Link expirado, limite de usos atingido ou usuário já é membro.

---
