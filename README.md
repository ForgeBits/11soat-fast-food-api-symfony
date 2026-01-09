# 🍔 Fast-Food API - Microsserviço de Pedidos (Symfony)

[![PHP](https://img.shields.io/badge/PHP-8.3-777bb3.svg)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-API-000000.svg)](https://symfony.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-316192.svg)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg)](https://www.docker.com/)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=ForgeBits_11soat-fast-food-api-symfony&metric=coverage)](https://sonarcloud.io/summary/new_code?id=ForgeBits_11soat-fast-food-api-symfony)

## 📋 Descrição

API de Fast-Food desenvolvida com Symfony (PHP), oferecendo gerenciamento de categorias, produtos, itens (customizações), composição produto–item e fluxo de pedidos. O projeto utiliza Docker (Nginx + PHP-FPM) e PostgreSQL.

### 🎯 Funcionalidades

- ✅ CRUD de Categorias
- ✅ CRUD de Produtos
- ✅ CRUD de Itens (customizações)
- ✅ Vínculo Produto ↔ Itens (montagem de cardápio)
- ✅ Criação e consulta de Pedidos, com status e customizações
- ✅ Paginação e validações com Symfony Validator
- ✅ Documentação com Anotações OpenAPI (NelmioApiDoc)
- ✅ Arquitetura inspirada em Clean Architecture

---

## 📊 Evidências de Cobertura de Testes

Este repositório já contém diretórios de cobertura em `app/coverage`. Para gerar/atualizar a cobertura localmente, execute:

```bash
docker compose exec app ./vendor/bin/phpunit \
  --coverage-html coverage/html \
  --coverage-clover coverage/coverage-xml/coverage.xml
```

O relatório HTML ficará disponível em: `app/coverage/html/index.html`.

Exemplo de execução do PHPUnit:

```
PHPUnit with Coverage - sucesso
```

---

## 🚀 Como Executar

### Requisitos
- Docker e Docker Compose

### Subindo o ambiente (dev)

```bash
# Na raiz do projeto
docker compose up -d --build

# Instalar dependências (primeira execução)
docker compose exec app composer install

# (Opcional) Aplicar migrações se houver
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

- Aplicação (Nginx): http://localhost:8084
- PostgreSQL: localhost:5433 (db:5432 dentro da rede docker)

---

## 📚 Endpoints Principais

Os controladores utilizam `#[Route]` com prefixos sob `/api`. A documentação Swagger/Nelmio geralmente estará em `http://localhost:8084/api/docs` (se habilitada na sua configuração do NelmioApiDoc).

### Categories (`/api/categories`)
- `POST /api/categories` – cria categoria
- `GET /api/categories` – lista paginada
- `GET /api/categories/{id}` – consulta por id
- `PUT /api/categories/{id}` – atualiza
- `DELETE /api/categories/{id}` – remove

### Products (`/api/products`)
- `POST /api/products` – cria produto
- `GET /api/products` – lista paginada
- `GET /api/products/{id}` – consulta por id
- `PUT /api/products/{id}` – atualiza
- `DELETE /api/products/{id}` – remove

### Items (`/api/items`)
- `POST /api/items` – cria item de customização
- `GET /api/items` – lista paginada
- `GET /api/items/{id}` – consulta por id
- `PUT /api/items/{id}` – atualiza

### ProductItem (composição) (`/api/product-items`)
- Endpoints para vincular itens a produtos (consulte `/api/docs` para o contrato detalhado)

### Orders (`/api/orders`)
- `POST /api/orders` – cria pedido com itens e customizações
- `GET /api/orders` – lista paginada
- `GET /api/orders/{id}` – consulta por id
- `PATCH /api/orders/{id}/status` – atualiza status do pedido

> Observação: As definições detalhadas de payloads estão anotadas com OpenAPI diretamente nos Controllers (ex.: `OrderController`, `ItemsController`, `ProductController`, `CategoriesController`).

---

## 🏗️ Arquitetura

Estrutura baseada em princípios de separação de camadas e portas/adaptadores:

```
app/src/
├── Application/                 # Casos de uso, DTOs, Presenters e Controllers (entrada)
│   ├── Controller/              # Controllers HTTP (Symfony)
│   ├── Domain/                  # DTOs, Entidades, Enums, Erros
│   ├── Helpers/                 # Utilitários (ex.: ApiResponse)
│   ├── Port/                    # Portas de entrada/saída (interfaces)
│   └── UseCases/                # Regras de aplicação (casos de uso)
├── Infrastructure/              # Implementações de portas (repositórios, serviços externos)
└── Repository/                  # Repositórios concretos (quando aplicável no projeto)
```

---

## 🧪 Testes

### Executando Testes Unitários
```bash
docker compose exec app ./vendor/bin/phpunit
```

### Cobertura de Testes
```bash
docker compose exec app ./vendor/bin/phpunit \
  --coverage-html coverage/html \
  --coverage-clover coverage/coverage-xml/coverage.xml
```

### Modo watch (opcional, via fswatch/entr no host)
Use ferramentas do seu host para reexecutar os testes automaticamente ao salvar arquivos.

Estrutura de testes (exemplo):

```
app/tests/Unit/
└── Application/UseCases/
    ├── Items/
    │   ├── CreateItemUseCaseTest.php
    │   └── FindAllItemsUseCaseTest.php
    └── Orders/
        └── CreateOrderUseCaseTest.php
```

---

## 🔧 Tecnologias Utilizadas

### Backend
- Symfony (PHP 8.3)
- PostgreSQL 16

### Testes
- PHPUnit

### DevOps
- Docker & Docker Compose
- Nginx + PHP-FPM

---

## 📦 Serviços e Scripts Úteis

```bash
# Subir/derrubar serviços
docker compose up -d --build
docker compose down -v

# Acessar o container app
docker compose exec app bash

# Composer
docker compose exec app composer install
docker compose exec app composer dump-autoload -o

# Symfony Console
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Testes
docker compose exec app ./vendor/bin/phpunit
```

Serviços (docker-compose.yaml):
- `app`: PHP-FPM (composer incluso) – trabalha em `/var/www/html`
- `web`: Nginx servindo `public/` na porta `8084`
- `db`: PostgreSQL 16, com volume `db_data` e porta `5433` exposta

---

## 🐳 Docker

### Desenvolvimento

```bash
# Subir stack
docker compose up -d --build

# Logs dos serviços
docker compose logs -f web app db

# Parar e limpar
docker compose down -v
```

### Produção (exemplo simplificado)

```bash
# Build da imagem PHP-FPM (multi-stage composer já incluso no Dockerfile)
docker build -t fast-food-php ./docker/php

# Subir com docker compose (ajuste variáveis e APP_ENV)
docker compose -f docker-compose.yaml up -d
```

Aplicação disponível em: `http://localhost:8084`

---

## 📝 Modelo de Dados (visão geral)

- Category: id, title, description, created_at, updated_at
- Product: id, title, description, price, category_id, url_img, available, created_at, updated_at
- Item: id, name, description, price, url_img, available, created_at, updated_at
- ProductItem: vínculo N:N entre Product e Item, com preço/observação opcionais
- Order: id, status, amount, clientId (ou random), observation, created_at, updated_at
- OrderItem + Customizations: itens do pedido com possíveis customizações

> O esquema exato é definido pelas entidades, portas e repositórios. Consulte os DTOs e entidades em `app/src/Application/Domain`.

---

## 🔐 Status de Pedido (exemplo)

Pedidos utilizam enum `OrderStatus` (ex.: `PENDING`, `CONFIRMED`, `IN_PREPARATION`, `READY`, `FINISHED`, `CANCELED`). Atualização via `PATCH /api/orders/{id}/status`.

---

## 👥 Autores

- Equipe 11SOAT – Fast-Food API (Symfony)

---

## 📄 Licença

Projeto acadêmico FIAP – uso educacional.

---

## ✅ Status do Projeto

🚧 Em evolução

- [x] CRUD de categorias/produtos/itens
- [x] Montagem produto–item
- [x] Pedidos com customizações
- [x] Testes unitários (PHPUnit)
- [x] Docker (Nginx, PHP-FPM, PostgreSQL)
- [x] Anotações OpenAPI (Nelmio)
- [ ] Pipeline CI/CD e Sonar integrados
- [ ] Documentação completa de todos os endpoints

---

Desenvolvido com ❤️
