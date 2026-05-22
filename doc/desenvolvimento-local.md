# Desenvolvimento local (sem Docker)

Use este modo se preferir rodar backend e frontend diretamente na máquina, ou enquanto o Docker ainda não estiver instalado.

---

## Pré-requisitos

| Ferramenta | Como verificar | Como instalar |
|------------|----------------|---------------|
| PHP 8.3 | `php -v` | `sudo apt install php8.3-cli php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-zip` |
| Composer | `composer -V` | https://getcomposer.org |
| Node.js 20 | `node -v` | nvm (veja abaixo) |

### Node.js com nvm

Sempre que abrir um terminal para o frontend:

```bash
cd frontend
nvm install    # lê .nvmrc (Node 20)
nvm use
node -v        # deve mostrar v20.x
npm -v
```

> Se `nvm` não for encontrado, instale em: https://github.com/nvm-sh/nvm

---

## Backend (Laravel)

```bash
cd backend

# Instalar dependências PHP
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Banco de dados (SQLite — padrão, zero config)
php artisan migrate

# Subir servidor
php artisan serve
```

API disponível em: **http://localhost:8000/api**

### Usar MySQL local (opcional)

Edite `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_estoque
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

Depois:

```bash
php artisan migrate
```

---

## Frontend (Vue)

Abra **outro terminal**:

```bash
cd frontend
nvm use
npm install
npm run dev
```

App disponível em: **http://localhost:5173**

### Proxy da API

O [`vite.config.js`](../frontend/vite.config.js) redireciona `/api` para `http://localhost:8000`. Ou seja, o frontend chama `/api/produtos` e o Vite encaminha ao Laravel — não é necessário configurar CORS em dev.

Se quiser apontar direto para a API:

```bash
# frontend/.env
VITE_API_URL=http://localhost:8000/api
```

---

## Rodar testes automatizados

```bash
cd backend
php artisan test
```

O teste `ErpFlowTest` cobre o fluxo completo: produto → compra → venda → estoque insuficiente → cancelamento.

---

## Checklist de verificação

```bash
# Backend respondendo
curl http://localhost:8000/api/produtos

# Health check
curl http://localhost:8000/up

# Criar produto de teste
curl -X POST http://localhost:8000/api/produtos \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"nome":"Produto Teste","preco_venda":99.90}'
```

---

## Quando usar Docker vs local

| Cenário | Recomendação |
|---------|--------------|
| Entregar o teste / ambiente igual produção | Docker |
| Desenvolver frontend com hot reload rápido | Local |
| Não quer instalar MySQL na máquina | Docker |
| Debugar PHP com Xdebug | Local |
| Máquina sem Docker instalado ainda | Local |
