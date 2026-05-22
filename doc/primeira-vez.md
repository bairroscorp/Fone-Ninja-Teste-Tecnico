# Primeira vez com Docker — Guia passo a passo

Este guia é para quem **nunca usou Docker** neste projeto. Siga na ordem.

---

## O que o Docker faz por você?

Quando você roda **um único comando**, o Docker sobe automaticamente:

1. **MySQL** — banco de dados
2. **Backend Laravel** — API na porta 8000
3. **Frontend Vue** — tela na porta 5173

E o backend **já roda as migrations sozinho**. Você **não precisa** entrar no container para executar `php artisan migrate`.

---

## Passo 0 — Docker instalado?

No terminal:

```bash
docker --version
docker compose version
```

Se der erro, instale primeiro: [instalacao-docker.md](instalacao-docker.md)

---

## Passo 1 — Ir para a pasta do projeto

```bash
cd ~/Público/Fone-Ninja-Teste-Tecnico
```

---

## Passo 2 — Escolha UMA das opções abaixo

### Opção A — Script automático (mais fácil)

```bash
bash scripts/setup-docker.sh
```

O script:
- Cria o `.env` se não existir
- Detecta se a porta 3306 está ocupada e muda para 3307
- Sobe todos os containers
- As migrations rodam sozinhas

### Opção B — Manual (3 comandos, um por linha)

```bash
cp .env.example .env

docker compose up -d --build

docker compose ps
```

> **Importante:** pressione Enter após cada comando. Não cole tudo numa linha só.

---

## Passo 3 — Verificar se funcionou

```bash
docker compose ps
```

Você deve ver algo assim:

```
NAME           STATUS
erp_mysql      Up (healthy)
erp_backend    Up
erp_frontend   Up
```

Se os 3 estiverem **Up**, abra no navegador:

| O quê | URL |
|-------|-----|
| Sistema (telas) | http://localhost:5173 |
| API (teste) | http://localhost:8000/api/produtos |

A API deve retornar `{"data":[]}` — lista vazia, normal na primeira vez.

---

## Preciso rodar migrate manualmente?

**Não.** O backend executa automaticamente:

1. Espera o MySQL ficar pronto
2. `composer install`
3. Gera `APP_KEY` (se necessário)
4. `php artisan migrate --force`
5. Inicia o servidor

Só rode migrate manualmente se quiser **forçar de novo**:

```bash
docker compose exec backend php artisan migrate
```

---

## Erro comum: porta 3306 em uso

Se aparecer:

```
failed to bind host port 0.0.0.0:3306/tcp: address already in use
```

Significa que você já tem MySQL instalado na máquina. **Solução:**

Edite o arquivo `.env` na raiz do projeto:

```env
DB_PORT=3307
```

Depois:

```bash
docker compose down
docker compose up -d --build
```

> O MySQL do Docker usará a porta **3307** no seu computador. Dentro do Docker, o backend continua conectando normalmente.

---

## Comandos úteis (depois do setup)

| O que você quer | Comando |
|-----------------|---------|
| Ver se está rodando | `docker compose ps` |
| Ver logs em tempo real | `docker compose logs -f` |
| Ver logs só do backend | `docker compose logs -f backend` |
| Parar tudo | `docker compose down` |
| Subir de novo (dia a dia) | `docker compose up -d` |
| Rodar testes | `docker compose exec backend php artisan test` |

---

## Fluxo de teste no navegador

1. Abra http://localhost:5173
2. **Produtos** → clique em **+ Novo** → cadastre um produto
3. **Compras** → **+ Nova Compra** → registre entrada de estoque
4. **Vendas** → **+ Nova Venda** → registre uma venda
5. Confira estoque, custo médio e lucro nas listagens

---

## Ainda com problema?

Envie a saída destes comandos:

```bash
docker compose ps
docker compose logs mysql --tail 20
docker compose logs backend --tail 30
```

Mais detalhes: [docker-compose.md](docker-compose.md)
