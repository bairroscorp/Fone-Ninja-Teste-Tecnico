# Instalação do Docker (Linux)

Este guia cobre a instalação do **Docker Engine** e **Docker Compose** no Ubuntu/Debian. É necessário para subir o projeto com `docker compose up`.

bash scripts/setup-docker.sh
Sistema: http://localhost:5173
API: http://localhost:8000/api/produtos
---

## Verificar se o Docker já está instalado

```bash
docker --version
docker compose version
```

Se ambos retornarem a versão, pule para [Docker Compose](docker-compose.md).

Se aparecer `Comando 'docker' não encontrado`, continue abaixo.

---

## Método 1 — Repositório oficial (recomendado)

Execute **um bloco por vez** no terminal. Cada bloco pedirá sua senha de `sudo`.

### Passo 1: Dependências

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg
```

### Passo 2: Chave GPG e repositório

```bash
sudo install -m 0755 -d /etc/apt/keyrings

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
  sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

> **Ubuntu 22.04:** se o comando acima falhar, substitua `$(. /etc/os-release && echo $VERSION_CODENAME)` por `jammy`.

### Passo 3: Instalar pacotes

```bash
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

### Passo 4: Permissão para rodar sem sudo

```bash
sudo usermod -aG docker $USER
newgrp docker
```

> Se `newgrp` não funcionar, faça **logout e login** (ou reinicie o terminal).

### Passo 5: Verificar

```bash
docker --version
docker compose version
docker run hello-world
```

Se o `hello-world` imprimir uma mensagem de sucesso, a instalação está correta.

---

## Método 2 — Repositório Ubuntu (alternativa rápida)

```bash
sudo apt update
sudo apt install -y docker.io docker-compose-v2
sudo usermod -aG docker $USER
newgrp docker
docker --version
docker compose version
```

---

## Problemas comuns

### `permission denied` ao rodar docker

Você ainda não está no grupo `docker`. Rode:

```bash
sudo usermod -aG docker $USER
newgrp docker
```

Ou faça logout/login.

### `sudo: uma senha é necessária`

Os comandos de instalação precisam ser executados **no seu terminal**, onde você digita a senha. O assistente/IDE não consegue inserir a senha por você.

### Comandos colados juntos

Evite colar vários comandos na mesma linha. Exemplo **errado**:

```bash
cp .env.example .envdocker compose up -d --build
```

Exemplo **correto**:

```bash
cp .env.example .env
docker compose up -d --build
```

### Docker instalado mas `compose` não funciona

Use `docker compose` (com espaço), não `docker-compose` (com hífen), a menos que tenha instalado a versão standalone.

---

## Próximo passo

Com o Docker instalado, siga para [Docker Compose — subir o projeto](docker-compose.md).
