# 🚀 Roadmap de Consolidação e Manutenção

## 🛡️ Segurança e Performance

- [ ] **Configurar Rate Limiting customizado**
- [ ] Limite para criação de convites (evitar spam de tokens).
- [ ] Limite para aceitação de convites (prevenir brute-force de tokens).
- [ ] Aplicar middlewares `throttle` nas rotas críticas.

## 🧪 Qualidade de Código (Testes com PEST)

- [ ] **Testes de Itens (ListItem)**
- [ ] Validar CRUD e Toggle.
- [ ] **Teste de Concorrência:** Simular erro 409 quando a `version` é incompatível.

- [ ] **Testes de Convite (Invitations)**
- [ ] Validar fluxo completo: Gerar -> Show -> Accept.
- [ ] Testar limites de expiração e `max_uses`.

- [ ] **Testes de Integração de API**
- [ ] Garantir que o `CustomListResource` entrega os itens apenas quando carregados.

## 🧹 Manutenção e Background Jobs (Scheduler)

- [ ] **Limpeza de Convites Expirados**
- [ ] Criar Job para deletar registros da tabela `list_invitations` onde `expires_at < now()`.

- [ ] **Arquivamento/Limpeza de Listas Inativas**
- [ ] Identificar listas com itens não atualizados há mais de 30 dias.
- [ ] Decidir política de limpeza (Soft Delete ou remoção definitiva).

- [ ] **Expurgo de Tokens e Usuários Inativos**
- [ ] Limpar tokens do Sanctum expirados (`personal_access_tokens`).
- [ ] Criar rotina para lidar com usuários sem atividade recente (limpeza de conta).

## 📈 Evolução Futura (Backlog)

- [ ] **Identificação de Usuários** (Nome/E-mail para sincronização).
- [ ] **Gestão de Membros** (Visualização e remoção de colaboradores).

---

### Dica Técnica para os Jobs:

Para os **Invites Expirados**, você não precisa de um Job complexo. Pode usar um comando simples no `routes/console.php` ou no `Task Scheduler`:

```php
// No arquivo de agendamento (Schedule)
$schedule->call(function () {
    ListInvitation::where('expires_at', '<', now())->delete();
})->everyFiveMinutes();

```

Para a **Limpeza de Usuários/Tokens**, o Sanctum já possui um comando nativo que você pode agendar:

```php
$schedule->command('sanctum:prune-expired --hours=24')->daily();

```
