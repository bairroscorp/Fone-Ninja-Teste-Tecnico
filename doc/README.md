# Documentação — ERP de Estoque

Guia completo do projeto Laravel + Vue para o teste técnico Fone Ninja.

## Índice

| Documento | Conteúdo |
|-----------|----------|
| **[Primeira vez com Docker](primeira-vez.md)** | **Comece por aqui** — setup completo passo a passo |
| [Instalação do Docker](instalacao-docker.md) | Como instalar Docker no Linux (Ubuntu) |
| [Docker Compose](docker-compose.md) | Subir, parar e gerenciar os containers |
| [Desenvolvimento local](desenvolvimento-local.md) | Rodar backend e frontend sem Docker |
| [Arquitetura](arquitetura.md) | Estrutura do sistema, pastas e stack |
| [API REST](api.md) | Endpoints, payloads e exemplos |
| [Regras de negócio](regras-de-negocio.md) | Estoque, custo médio, lucro e validações |
| [Frontend](frontend.md) | Telas, componentes e padrões de UX |

## Início rápido

```bash
# 1. Instale o Docker (se ainda não tiver)
#    Veja: doc/instalacao-docker.md

# 2. Na raiz do projeto
cp .env.example .env
docker compose up -d --build

# 3. Acesse
#    Frontend → http://localhost:5173
#    API      → http://localhost:8000/api
```

## Ambiente utilizado neste projeto

| Ferramenta | Versão recomendada |
|------------|-------------------|
| PHP | 8.3 |
| Composer | 2.7+ |
| Node.js | 20 LTS (via nvm) |
| Docker Engine | 24+ |
| Docker Compose | v2 (plugin) |
| MySQL | 8.0 |
