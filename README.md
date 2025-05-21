# 📒 API Carteira Financeira

Esta API permite o gerenciamento de carteira financeira com funcionalidades de registro, login, depósito, transferência e reversão de transações.

## 🚀 Tecnologias

-   PHP 8.x
-   Laravel Framework
-   Sanctum (autenticação)
-   PostgreSQL

## 📦 Instalação

1. Clone o repositório:

```bash
git clone https://github.com/suellykarine/carteira-financeira
cd carteira-financeira
```

2. Instale as dependências:

```
composer install
```

3. Copie o arquivo .env:

```
cp .env.example .env
```

4. Gere a chave da aplicação:

```
php artisan key:generate
```

5. Configure o banco de dados no arquivo .env e depois rode as migrações:

```
php artisan migrate
```

6. Inicie o servidor:

```
php artisan serve
```

### 🔐 Autenticação

Esta API utiliza tokens de autenticação do Laravel Sanctum, inclua token de acesso para acessar os endpoints protegidos.

### 🧩 Funcionalidades:

Registro de usuário ✅

Login de usuário (autenticação via token) ✅

Depósito ✅

Transferência entre usuários ✅

Reversão de transações (reversão de depósito e transferência) ✅

Lista dados do usuário autenticado com saldo ✅

Lista todos os usuários ✅

## 📌  Endpoints

### ✅ Registro de usuário

`POST /api/register`

**Request body:**

```json
{
    "name": "Seu Nome",
    "email": "email@example.com",
    "password": "senha123"
}
```

201 Response:

```json
{
    "message": "Usuário registrado com sucesso"
}
```

### 🔐 Login de usuário

`POST /api/login`

**Request body:**

```json
{
    "email": "email@example.com",
    "password": "senha123"
}
```

200 Response:

```json
{
    "user": {
        "id": 1,
        "name": "Seu Nome",
        "email": "email@example.com"
    },
    "token": "17|q6wP5iyXdzQ9jQEdcU3lV4nrRbdeGYb8GbMp3fvbc86ea472",
    "token_type": "Bearer"
}
```

### 👤 Detalhes do usuário logado

`GET /api/me`

Headers:

```
Authorization: Bearer {token}
```

reponse 200:

```json
{
    "id": 1,
    "name": "Seu nome",
    "email": "email@exemplo.com",
    "balance": "100.00",
    "transactions": [
        {
            "id": 10,
            "user_id": 1,
            "type": "deposit",
            "amount": "100.00",
            "recipient_id": null,
            "created_at": "2025-05-20T22:16:00.000000Z",
            "updated_at": "2025-05-20T22:16:00.000000Z",
            "reverted_at": null,
            "related_transaction_id": null
        }
    ]
}
```

### 👤 Lista todos os usuário

`GET /api/users`

reponse 200:

```json
[
    { "id": 1, "name": "Seu nome", "email": "email@exemplo.com" },
    { "id": 2, "name": "Seu nome2", "email": "email2@exemplo.com" }
]
```

### 💰 Depósito

`POST /api/deposit`

Headers:

```
Authorization: Bearer {token}
```

**Request body:**

```json
{
    "amount": 100.0
}
```

Response 200:

```json
{
    "message": "Depósito realizado com sucesso",
    "transaction": {
        "id": 10,
        "type": "deposit",
        "amount": 100,
        "created_at": "2025-05-20T22:00:00.000000Z"
    }
}
```

### 🔁 Transferência

`POST /api/transfer`

Headers:

```
Authorization: Bearer {token}
```

**Request body:**

```json
{
    "recipient_id": 2,
    "amount": 50.0
}
```

Response 200:

```json
{
    "message": "Transferência realizada com sucesso",
    "transaction": {
        "id": 23,
        "user_id": 1,
        "type": "transfer",
        "amount": 50,
        "recipient_id": 2,
        "created_at": "2025-05-20T22:05:35.000000Z"
    }
}
```

### ↩️ Reversão de Transação

`POST /api/transactions/{id}/revert`

Headers:

```
Authorization: Bearer {token}
```

Exemplo:

```
POST /api/transactions/23/revert
```

Response 200:

```json
{
    "message": "Transação revertida com sucesso."
}
```

#### ⚠️ Possíveis erros

-   Saldo insuficiente para transferência

-   Tentativa de transferir para si mesmo

-   Transação de destino não encontrada

-   Transação já foi revertida

-   Acesso negado à transação de outro usuário
-

### 🧪 Testes

Para rodar os testes automatizados:

```
php artisan test
```
