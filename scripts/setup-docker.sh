#!/bin/bash
# Setup inicial do ERP com Docker — rode na raiz do projeto
# Uso: bash scripts/setup-docker.sh

set -e

cd "$(dirname "$0")/.."

echo ""
echo "=========================================="
echo "  ERP Estoque — Setup Docker (1ª vez)"
echo "=========================================="
echo ""

# 1. Verificar Docker
if ! command -v docker &> /dev/null; then
  echo "❌ Docker não encontrado."
  echo "   Instale primeiro: doc/instalacao-docker.md"
  exit 1
fi

if ! docker compose version &> /dev/null; then
  echo "❌ Docker Compose não encontrado."
  exit 1
fi

echo "✓ Docker OK: $(docker --version)"

# 2. Criar .env se não existir
if [ ! -f .env ]; then
  cp .env.example .env
  echo "✓ Arquivo .env criado"
else
  echo "✓ Arquivo .env já existe"
fi

# 3. Ajustar porta MySQL se 3306 estiver ocupada
if ss -tln 2>/dev/null | grep -q ':3306 ' || netstat -tln 2>/dev/null | grep -q ':3306 '; then
  if grep -q '^DB_PORT=3306' .env 2>/dev/null; then
    sed -i 's/^DB_PORT=3306/DB_PORT=3307/' .env
    echo "✓ Porta 3306 ocupada — alterado DB_PORT para 3307 no .env"
  fi
fi

# 4. Garantir APP_KEY limpa no backend (evita corrupção)
if [ -f backend/.env ] && grep -q 'APP_KEY=base64:.*base64:' backend/.env 2>/dev/null; then
  sed -i 's/^APP_KEY=.*/APP_KEY=/' backend/.env
  echo "✓ APP_KEY corrompida corrigida no backend/.env"
fi

echo ""
echo "Subindo containers (MySQL + Backend + Frontend)..."
echo "Isso pode demorar alguns minutos na primeira vez."
echo ""

docker compose down 2>/dev/null || true
docker compose up -d --build

echo ""
echo "Aguardando serviços ficarem prontos..."
sleep 5

echo ""
docker compose ps

echo ""
echo "=========================================="
echo "  Pronto!"
echo "=========================================="
echo ""
echo "  Frontend:  http://localhost:5173"
echo "  API:       http://localhost:8000/api/produtos"
echo ""
echo "  As migrations rodam AUTOMATICAMENTE ao subir o backend."
echo "  Você NÃO precisa rodar php artisan migrate manualmente."
echo ""
echo "  Ver logs:     docker compose logs -f"
echo "  Parar tudo:   docker compose down"
echo ""
