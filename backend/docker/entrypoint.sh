#!/bin/sh
set -e

echo "==> Aguardando MySQL ficar disponível..."

attempt=0
max_attempts=30

while [ "$attempt" -lt "$max_attempts" ]; do
  if php -r "
    try {
      new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
      );
      exit(0);
    } catch (Throwable \$e) {
      exit(1);
    }
  " 2>/dev/null; then
    echo "==> MySQL pronto!"
    break
  fi

  attempt=$((attempt + 1))
  echo "    Tentativa ${attempt}/${max_attempts} — MySQL ainda indisponível, aguardando 3s..."
  sleep 3
done

if [ "$attempt" -eq "$max_attempts" ]; then
  echo "==> ERRO: MySQL não respondeu após ${max_attempts} tentativas."
  echo "    Verifique: docker compose ps && docker compose logs mysql"
  exit 1
fi

echo "==> Instalando dependências PHP..."
composer install --no-interaction --prefer-dist

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  echo "==> Gerando APP_KEY..."
  php artisan key:generate --force
else
  echo "==> APP_KEY já definida, pulando key:generate"
fi

echo "==> Executando migrations..."
php artisan migrate --force

echo "==> Iniciando servidor Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
