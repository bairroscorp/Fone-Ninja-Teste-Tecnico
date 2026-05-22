# Docker Compose — Subir e gerenciar containers

O arquivo [`docker-compose.yml`](../docker-compose.yml) na raiz orquestra três serviços:

| Serviço | Container | Porta | Função |
|---------|-----------|-------|--------|
| `mysql` | `erp_mysql` | 3306 | Banco de dados MySQL 8 |
| `backend` | `erp_backend` | 8000 | API Laravel (PHP 8.3) |
| `frontend` | `erp_frontend` | 5173 | SPA Vue (Vite dev server) |

---

## Pré-requisitos

- Docker Engine instalado ([guia](instalacao-docker.md))
- Estar na **raiz do projeto** (`Fone-Ninja-Teste-Tecnico/`)

---

## Primeira execução

```bash
# 1. Copiar variáveis de ambiente
cp .env.example .env

# 2. Build e subir em background
docker compose up -d --build
```

Na primeira subida, o backend irá automaticamente:

1. Rodar `composer install`
2. Gerar `APP_KEY`
3. Executar `php artisan migrate`

### URLs de acesso

| Serviço | URL |
|---------|-----|
| **Frontend** | http://localhost:5173 |
| **API** | http://localhost:8000/api |
| **Health check Laravel** | http://localhost:8000/up |
| **MySQL** | `localhost:3306` |

### Credenciais padrão do banco (`.env.example`)

| Variável | Valor padrão |
|----------|--------------|
| `DB_DATABASE` | `erp_estoque` |
| `DB_USERNAME` | `erp_user` |
| `DB_PASSWORD` | `erp_secret` |
| `DB_ROOT_PASSWORD` | `root` |

---

## Comandos do dia a dia

### Ver status dos containers

```bash
docker compose ps
```

### Ver logs (todos os serviços)

```bash
docker compose logs -f
```

### Ver logs de um serviço específico

```bash
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f mysql
```

### Parar os containers (mantém dados)

```bash
docker compose down
```

### Parar e remover volumes (apaga banco de dados)

```bash
docker compose down -v
```

> **Atenção:** `-v` apaga todos os dados do MySQL. Use apenas se quiser recomeçar do zero.

### Rebuild após mudanças no Dockerfile

```bash
docker compose up -d --build
```

### Reiniciar um serviço

```bash
docker compose restart backend
docker compose restart frontend
```

---

## Comandos dentro dos containers

### Backend (Laravel)

```bash
# Rodar migrations manualmente
docker compose exec backend php artisan migrate

# Rodar testes
docker compose exec backend php artisan test

# Abrir Tinker
docker compose exec backend php artisan tinker

# Ver rotas
docker compose exec backend php artisan route:list
```

### Frontend

```bash
# Instalar dependência nova
docker compose exec frontend npm install nome-do-pacote

# Rebuild de produção (dentro do container)
docker compose exec frontend npm run build
```

### MySQL

```bash
# Acessar console MySQL
docker compose exec mysql mysql -u erp_user -perp_secret erp_estoque
```

---

## Variáveis de ambiente (`.env` na raiz)

```env
DB_ROOT_PASSWORD=root
DB_DATABASE=erp_estoque
DB_USERNAME=erp_user
DB_PASSWORD=erp_secret
DB_PORT=3306

BACKEND_PORT=8000
APP_KEY=

FRONTEND_PORT=5173
VITE_API_URL=http://localhost:8000/api
```

| Variável | Descrição |
|----------|-----------|
| `BACKEND_PORT` | Porta exposta da API |
| `FRONTEND_PORT` | Porta exposta do Vite |
| `VITE_API_URL` | URL que o frontend usa para chamar a API |
| `DB_PORT` | Porta MySQL no host (mude se 3306 já estiver em uso) |

---

## Fluxo de teste manual (via browser)

1. Acesse http://localhost:5173
2. **Produtos** → cadastre um produto (ex: "Fone Bluetooth", R$ 150)
3. **Compras** → registre entrada (ex: 50 un × R$ 20)
4. Verifique que o **estoque** e **custo médio** do produto atualizaram
5. **Vendas** → registre saída (ex: 2 un × R$ 50)
6. Confira **total** e **lucro** na listagem
7. **Cancele** a venda e verifique que o estoque voltou

---

## Solução de problemas

### Porta 3306, 5173 ou 8000 já em uso

Altere no `.env`:

```env
DB_PORT=3307
BACKEND_PORT=8001
FRONTEND_PORT=5174
```

Depois:

```bash
docker compose down
docker compose up -d --build
```

### Backend não conecta ao MySQL / reinicia em loop

**Erro típico:**
```
getaddrinfo for mysql failed: Temporary failure in name resolution
```

**Causas comuns:**
1. MySQL ainda não estava pronto quando o backend rodou `migrate`
2. Porta 3306 ocupada — container MySQL não subiu
3. `APP_KEY` corrompida no `backend/.env` (vários `key:generate` em loop)

**Solução:**

```bash
# 1. Parar tudo
docker compose down

# 2. Limpar APP_KEY corrompida (deixe vazio)
#    backend/.env → APP_KEY=

# 3. Subir de novo (entrypoint aguarda MySQL automaticamente)
docker compose up -d --build

# 4. Verificar status
docker compose ps
docker compose logs mysql --tail 30
docker compose logs backend --tail 30
```

Se o MySQL não aparecer como `healthy`, a porta **3306** pode estar em uso. Altere no `.env` raiz:

```env
DB_PORT=3307
```

O backend aguarda o MySQL via healthcheck + entrypoint (até 90s).

### Frontend não carrega dados da API

1. Confirme que o backend responde: http://localhost:8000/api/produtos
2. Verifique `VITE_API_URL` no `.env`
3. Após alterar `.env`, reinicie o frontend:

```bash
docker compose restart frontend
```

### Erro de permissão ao buildar

```bash
sudo usermod -aG docker $USER
newgrp docker
```

### Container backend reiniciando em loop

Veja o erro:

```bash
docker compose logs backend --tail 50
```

Causas comuns: falha no `composer install`, migration com erro, ou porta ocupada.

### Limpar tudo e recomeçar

```bash
docker compose down -v
docker system prune -f
cp .env.example .env
docker compose up -d --build
```

---

## Modo produção (frontend estático)

O [`frontend/Dockerfile`](../frontend/Dockerfile) possui stage `production` com nginx. Para usar:

1. Altere no `docker-compose.yml` o target do frontend de `development` para `production`
2. Ajuste a porta para `80`
3. Rebuild: `docker compose up -d --build`

Para desenvolvimento, mantenha `target: development` (Vite com hot reload).

---

## Diagrama dos serviços

```
┌─────────────────────────────────────────────────────────┐
│  Host (sua máquina)                                     │
│                                                         │
│  :5173 ──► erp_frontend (Vue/Vite)                      │
│                 │                                       │
│                 │ HTTP → VITE_API_URL                   │
│                 ▼                                       │
│  :8000 ──► erp_backend (Laravel API)                    │
│                 │                                       │
│                 │ DB_HOST=mysql                         │
│                 ▼                                       │
│  :3306 ──► erp_mysql (MySQL 8)                          │
│                 │                                       │
│                 └── volume: mysql_data (persistência)   │
└─────────────────────────────────────────────────────────┘
```
