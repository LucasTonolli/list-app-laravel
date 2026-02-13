# Lists App API

API REST para gerenciamento colaborativo de listas, construída com Laravel 12. Permite criar, compartilhar e gerenciar listas com controle de concorrência via optimistic locking e sistema de convites com expiração.

## Stack

- **PHP 8.4+** / **Laravel 12**
- **PostgreSQL** (UUIDs como chave primária)
- **Laravel Sanctum** (autenticação via token)
- **PEST PHP** (testes)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Autenticação

Registro anônimo via token. Todas as rotas protegidas exigem o header `Authorization: Bearer {token}`.

```
POST /api/identities
```

```json
{ "token": "1|abc123..." }
```

## Endpoints

### Identidade

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| POST | `/api/identities` | Criar usuario | Nao |

### Listas

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| GET | `/api/lists` | Listar listas | Sim |
| POST | `/api/lists` | Criar lista | Sim |
| GET | `/api/lists/{uuid}` | Ver lista com itens | Sim |
| PATCH | `/api/lists/{uuid}` | Atualizar titulo | Sim |
| DELETE | `/api/lists/{uuid}` | Deletar lista | Sim |

**Body (POST/PATCH):**
```json
{ "title": "Minha lista" }
```

### Itens

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| POST | `/api/lists/{list}/items` | Adicionar item | Sim |
| PATCH | `/api/lists/{list}/items/{item}` | Atualizar item | Sim |
| DELETE | `/api/lists/{list}/items/{item}` | Deletar item | Sim |
| PATCH | `/api/lists/{list}/items/{item}/toggle` | Alternar conclusao | Sim |

**Body (POST):**
```json
{ "name": "Comprar leite", "description": "Integral" }
```

**Body (PATCH):** requer `version` para optimistic locking
```json
{ "name": "Comprar leite desnatado", "description": "Marca X", "version": 1 }
```

### Convites

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| POST | `/api/lists/{list}/invitations` | Criar convite | Sim |
| GET | `/api/lists/{list}/invitations/{token}` | Ver convite | Nao |
| POST | `/api/lists/{list}/invitations/{token}/accept` | Aceitar convite | Sim |

**Criar convite:**
```json
// POST /api/lists/{list}/invitations
{ "max_uses": 5 }
```

```json
// Response 201
{
    "invitation": {
        "uuid": "019c2450-f539-709e-b261-f19b171de042",
        "max_uses": 5,
        "created_at": "2026-02-03T16:23:31Z",
        "expires_at": "2026-02-03T16:28:31Z",
        "share_url": "http://localhost/api/lists/{list}/invitations/{token}"
    }
}
```

**Ver convite:**
```json
// GET /api/lists/{list}/invitations/{token}
// Response 200
{
    "invitation": {
        "uuid": "019c2450-f539-709e-b261-f19b171de042",
        "max_uses": 5,
        "uses": 0,
        "expires_at": "2026-02-03T16:28:31Z",
        "accept_url": "http://localhost/api/lists/{list}/invitations/{token}/accept"
    }
}
```

**Aceitar convite:**
```json
// POST /api/lists/{list}/invitations/{token}/accept
// Response 200
{ "accepted": true }
```

Erros possiveis: `404` token invalido, `409` expirado/limite atingido/ja e membro.

## Concorrencia (Optimistic Locking)

Itens possuem um campo `version` que incrementa a cada atualizacao. O cliente deve enviar a versao atual ao atualizar — se outro usuario ja modificou o item, a API retorna `409 Conflict`.

1. Cliente le o item com `version: 1`
2. Cliente envia update com `version: 1`
3. Se a versao no banco ainda for `1`, o update ocorre e a versao sobe para `2`
4. Se ja foi alterado, retorna `409 Conflict`

## Permissoes

| Acao | Owner | Editor |
|------|-------|--------|
| Ver lista | Sim | Sim |
| Atualizar titulo | Sim | Nao |
| Deletar lista | Sim | Nao |
| Gerenciar itens | Sim | Sim |
| Criar convites | Sim | Nao |

## Rate Limiting

| Escopo | Limite |
|--------|--------|
| API geral | 50 req/min |
| Criar identidade | 5 req/min |
| Criar convites | 5 req/min |
| Aceitar convites | 5 req/min |

## Testes

```bash
php artisan test
```

## Arquitetura

```
Controllers -> Services -> Models
     |
FormRequests (validacao)
Policies (autorizacao)
Resources (transformacao JSON)
```

- **Controllers:** entrada da requisicao, delegam para Services
- **Services:** logica de negocio e transacoes
- **Policies:** autorizacao baseada em roles (owner/editor)
- **Resources:** transformam models em JSON com links HATEOAS
