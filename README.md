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
POST /api/v1/identities
```

```json
{ "token": "1|abc123..." }
```

Todas as rotas de recurso abaixo vivem sob o prefixo `/api/v1`.

## Endpoints

### Identidade

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| POST | `/api/v1/identities` | Criar usuario | Nao |
| GET | `/api/user` | Ver usuario autenticado | Sim |

### Listas

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| GET | `/api/v1/lists` | Listar listas | Sim |
| POST | `/api/v1/lists` | Criar lista | Sim |
| GET | `/api/v1/lists/{uuid}` | Ver lista com itens | Sim |
| PATCH | `/api/v1/lists/{uuid}` | Atualizar titulo | Sim |
| DELETE | `/api/v1/lists/{uuid}` | Deletar lista | Sim |

**Body (POST/PATCH):**
```json
{ "title": "Minha lista" }
```

### Itens

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| POST | `/api/v1/lists/{list}/items` | Adicionar item | Sim |
| POST | `/api/v1/lists/{list}/items/bulk` | Adicionar varios itens | Sim |
| PATCH | `/api/v1/lists/{list}/items/{item}` | Atualizar item | Sim |
| DELETE | `/api/v1/lists/{list}/items/{item}` | Deletar item | Sim |
| PATCH | `/api/v1/lists/{list}/items/{item}/toggle` | Alternar conclusao | Sim |

**Body (POST):**
```json
{ "name": "Comprar leite", "description": "Integral" }
```

**Body (POST bulk):**
```json
{ "items": [{ "name": "Comprar leite" }, { "name": "Comprar pao" }] }
```

**Body (PATCH):** requer `version` para optimistic locking
```json
{ "name": "Comprar leite desnatado", "description": "Marca X", "version": 1 }
```

### Convites

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| POST | `/api/v1/lists/{list}/invitations` | Criar convite | Sim |
| GET | `/api/v1/lists/{list}/invitations/{token}` | Ver convite | Nao |
| POST | `/api/v1/lists/{list}/invitations/{token}/accept` | Aceitar convite | Sim |

**Criar convite:**
```json
// POST /api/v1/lists/{list}/invitations
{ "max_uses": 5, "expires_in_minutes": 30 }
```

```json
// Response 200
{
    "invitation": {
        "uuid": "019c2450-f539-709e-b261-f19b171de042",
        "max_uses": 5,
        "created_at": "2026-02-03T16:23:31Z",
        "expires_at": "2026-02-03T16:28:31Z",
        "list_uuid": "019c2450-f539-709e-b261-f19b171dabcd",
        "share_url": "http://localhost/api/v1/lists/{list}/invitations/{token}"
    }
}
```

**Ver convite:** rota publica, pensada para pre-visualizar o convite (titulo + link de aceite) antes do usuario logar, como nos links de convite do Google Docs/Notion. A seguranca depende da entropia do token (128 bits).
```json
// GET /api/v1/lists/{list}/invitations/{token}
// Response 200
{
    "invitation": {
        "uuid": "019c2450-f539-709e-b261-f19b171de042",
        "max_uses": 5,
        "created_at": "2026-02-03T16:23:31Z",
        "expires_at": "2026-02-03T16:28:31Z",
        "list_title": "Minha lista",
        "accept_url": "http://localhost/api/v1/lists/{list}/invitations/{token}/accept"
    }
}
```

**Aceitar convite:**
```json
// POST /api/v1/lists/{list}/invitations/{token}/accept
// Response 200
{ "accepted": true }
```

Erros possiveis (`409`): convite expirado, limite de usos atingido, usuario ja e membro da lista, dono tentando aceitar o proprio convite. `404`: token invalido ou convite nao pertence a lista informada na URL.

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
Controllers (App\Http\Controllers\V1) -> Services -> Models
     |
FormRequests (validacao)
Policies (autorizacao)
Resources (transformacao JSON)
Exceptions (regras de negocio -> respostas HTTP)
```

- **Controllers:** versionados em `V1`, entrada da requisicao, autorizam via Policy (`$this->authorize()`) e delegam para Services
- **Services:** logica de negocio e transacoes; convites usam locking otimista via `UPDATE ... WHERE uses < max_uses` e constraint unica no banco para evitar condicoes de corrida
- **Policies:** autorizacao baseada em roles (owner/editor)
- **Resources:** transformam models em JSON com links HATEOAS
- **Exceptions:** exceções de dominio (`InvitationException` e subclasses, `ItemVersionMismatchException`) traduzidas para status HTTP nos controllers
- **Route Model Binding escopado:** `{list}/{item}` e `{list}/{invitation}` usam `->scoped()`/`Route::scopeBindings()` para garantir que um recurso filho so resolve se pertencer ao pai informado na URL
