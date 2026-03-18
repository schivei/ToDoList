# ToDoList

Aplicação de lista de tarefas (*To-Do*) com frontend em HTML/CSS/JavaScript e backend em **PHP 8.4** + **MySQL 8**, comunicação via **AJAX**.

---

## Requisitos

| Componente | Versão mínima |
|------------|---------------|
| PHP        | 8.4           |
| MySQL      | 8.0           |
| Servidor web | Apache 2.4 / Nginx / `php -S` |

> PHP precisa ter a extensão **PDO** e **pdo_mysql** habilitadas.

---

## Configuração do banco de dados

### 1. Criar banco e tabela

Execute o script SQL incluído no projeto conectando-se ao MySQL com um usuário que tenha permissões de criação:

```bash
mysql -u root -p < database.sql
```

Isso criará o banco `todolist` e a tabela `tasks`.

### 2. Configurar credenciais

As credenciais de acesso ao banco são lidas de variáveis de ambiente (recomendado) ou dos valores padrão definidos em `api/config.php`.

**Via variáveis de ambiente (recomendado):**

```bash
export DB_HOST=localhost
export DB_PORT=3306
export DB_NAME=todolist
export DB_USER=root
export DB_PASSWORD=sua_senha
```

**Via edição direta** (alternativa simples para ambiente local):

Edite `api/config.php` e ajuste os valores padrão nas chamadas `getenv(…) ?: 'valor_padrão'`:

```php
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_PORT',     (int)(getenv('DB_PORT')     ?: 3306));
define('DB_NAME',     getenv('DB_NAME')     ?: 'todolist');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'sua_senha');
```

---

## Executar localmente

### Opção A — Servidor embutido do PHP (mais simples)

```bash
# Na raiz do projeto
php -S localhost:8080
```

Acesse [http://localhost:8080](http://localhost:8080) no navegador.

### Opção B — Apache / Nginx

Aponte o *document root* do seu virtual host para a raiz do projeto (onde está `index.html`). Certifique-se de que o PHP está configurado como módulo ou via FastCGI (PHP-FPM).

---

## Estrutura do projeto

```
ToDoList/
├── api/
│   ├── config.php      # Conexão e configuração do banco de dados
│   └── tasks.php       # API REST (JSON) – endpoints de tarefas
├── img/                # Imagens usadas na interface
├── database.sql        # Script de criação do banco e da tabela
├── index.html          # Interface da aplicação
├── scripts.js          # Lógica do frontend (AJAX)
└── styles.css          # Estilos da interface
```

---

## API – Endpoints

Todos os endpoints estão em `api/tasks.php` e retornam JSON.

| Método | URL                    | Descrição                         |
|--------|------------------------|-----------------------------------|
| GET    | `api/tasks.php`        | Lista todas as tarefas            |
| POST   | `api/tasks.php`        | Cria uma nova tarefa              |
| PUT    | `api/tasks.php?id=X`   | Atualiza título e/ou status       |
| DELETE | `api/tasks.php?id=X`   | Remove uma tarefa                 |

### Exemplos

**Criar tarefa**
```bash
curl -X POST http://localhost:8080/api/tasks.php \
     -H 'Content-Type: application/json' \
     -d '{"title":"Comprar leite"}'
```

**Marcar como concluída**
```bash
curl -X PUT http://localhost:8080/api/tasks.php?id=1 \
     -H 'Content-Type: application/json' \
     -d '{"completed":true}'
```

**Remover tarefa**
```bash
curl -X DELETE http://localhost:8080/api/tasks.php?id=1
```

---

## Como usar a aplicação

1. Configure o banco de dados conforme descrito acima.
2. Inicie o servidor PHP.
3. Abra [http://localhost:8080](http://localhost:8080) no navegador.
4. Digite uma tarefa no campo de texto e clique em **Adicionar** (ou pressione **Enter**).
5. Clique no ícone ✔ para marcar/desmarcar uma tarefa como concluída.
6. Clique no ícone 🗑 para remover uma tarefa.

Todas as operações são salvas no banco de dados MySQL e a interface é atualizada dinamicamente via AJAX, sem recarregar a página.
