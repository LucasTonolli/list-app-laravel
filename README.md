## 📋 Estado Atual do Projeto (Checklist Mental)

### 1. Entidades & Banco

- [x] `User`: Identidade baseada em UUID (Sanctum).
- [x] `CustomList`: O agrupador (Dono + Título).
- [x] `CustomListUser`: A pivô (Controle de Acesso: Owner/Editor).
- [x] `ListItem`: O recurso folha (Conteúdo + Status + Concorrência).

### 2. Rotas de Itens (Nested Resources)

- `POST /v1/lists/{list_uuid}/items` -> Criar.
- `GET /v1/lists/{list_uuid}/items` -> Listar os itens daquela lista (opcional, se não vier tudo no `show` da lista).

---

## 3. O Próximo "Grande Porquê": Regras de Itens

### Decisões Técnicas para documentar:

- **Optimistic Locking:** Usaremos a coluna `version`. O front-end envia a versão que leu; se no banco estiver diferente, a API nega a alteração.
- _Por que:_ Evita que o usuário A apague o que o usuário B acabou de escrever sem saber.

- **Pessimistic "Soft" Lock:** Usaremos `locked_by` e `locked_at`.
- _Por que:_ Em uma edição colaborativa, é educado avisar: "O Usuário X está editando este item agora".

- **Cascade:** Se a lista morre, o item morre (já garantido na sua migration).

---

## 4. Estrutura de Pastas e Camadas

1. **ListItemController:** Orquestra (Valida Request -> Autoriza via ListPolicy -> Chama Service).
2. **ListItemService:** Persiste (Cria o registro, gerencia versões e locks).
3. **ListItemResource:** Formata (Garante o Flat Data para o item).
