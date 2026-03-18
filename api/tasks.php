<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// ── Headers ───────────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Send a JSON response and stop execution.
 *
 * @param mixed $data
 */
function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    try {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
        http_response_code(500);
        echo '{"error":"Erro interno ao serializar resposta."}';
    }
    exit;
}

/**
 * Send a JSON error response and stop execution.
 */
function respondError(string $message, int $status = 400): never
{
    respond(['error' => $message], $status);
}

/**
 * Parse the raw request body as JSON and return the decoded array.
 * Returns an empty array when the body is absent or blank.
 */
function parseBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    try {
        $decoded = json_decode($raw, associative: true, flags: JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
        respondError('Corpo da requisição inválido (JSON malformado).', 400);
    }

    return is_array($decoded) ? $decoded : [];
}

// ── Router ────────────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    $pdo = getConnection();
} catch (\PDOException $e) {
    respondError('Não foi possível conectar ao banco de dados.', 503);
}

match ($method) {
    'GET'    => listTasks($pdo),
    'POST'   => createTask($pdo),
    'PUT'    => updateTask($pdo, $id),
    'DELETE' => deleteTask($pdo, $id),
    default  => respondError('Método não permitido.', 405),
};

// ── Handlers ──────────────────────────────────────────────────────────────────

/**
 * GET /api/tasks.php
 * Returns all tasks ordered by creation date (newest first).
 */
function listTasks(PDO $pdo): never
{
    $stmt = $pdo->query('SELECT id, title, completed, created_at, updated_at FROM tasks ORDER BY created_at DESC');
    $tasks = $stmt->fetchAll();

    // Cast types for consistent JSON output
    foreach ($tasks as &$task) {
        $task['id']        = (int) $task['id'];
        $task['completed'] = (bool) $task['completed'];
    }

    respond($tasks);
}

/**
 * POST /api/tasks.php
 * Body: { "title": "..." }
 * Creates a new task and returns it.
 */
function createTask(PDO $pdo): never
{
    $body  = parseBody();

    if (!isset($body['title'])) {
        respondError('O campo "title" é obrigatório.');
    }

    if (!is_string($body['title'])) {
        respondError('O campo "title" deve ser uma string.');
    }

    $title = trim($body['title']);

    if ($title === '') {
        respondError('O campo "title" é obrigatório e não pode estar vazio.');
    }

    if (mb_strlen($title) > 255) {
        respondError('O campo "title" não pode ter mais de 255 caracteres.');
    }

    $stmt = $pdo->prepare('INSERT INTO tasks (title) VALUES (:title)');
    $stmt->execute([':title' => $title]);

    $newId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT id, title, completed, created_at, updated_at FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $newId]);
    $task = $stmt->fetch();

    $task['id']        = (int) $task['id'];
    $task['completed'] = (bool) $task['completed'];

    respond($task, 201);
}

/**
 * PUT /api/tasks.php?id=X
 * Body: { "title": "...", "completed": true|false }
 * Updates title and/or completed status of an existing task.
 */
function updateTask(PDO $pdo, ?int $id): never
{
    if ($id === null || $id <= 0) {
        respondError('Parâmetro "id" inválido ou ausente.');
    }

    $body = parseBody();

    $sets   = [];
    $params = [':id' => $id];

    if (array_key_exists('title', $body)) {
        if (!is_string($body['title'])) {
            respondError('O campo "title" deve ser uma string.');
        }
        $title = trim($body['title']);
        if ($title === '') {
            respondError('O campo "title" não pode estar vazio.');
        }
        if (mb_strlen($title) > 255) {
            respondError('O campo "title" não pode ter mais de 255 caracteres.');
        }
        $sets[]          = 'title = :title';
        $params[':title'] = $title;
    }

    if (array_key_exists('completed', $body)) {
        if (!is_bool($body['completed'])) {
            respondError('O campo "completed" deve ser um booleano (true ou false).');
        }
        $sets[]              = 'completed = :completed';
        $params[':completed'] = $body['completed'] ? 1 : 0;
    }

    if (empty($sets)) {
        respondError('Nenhum campo válido para atualizar foi fornecido.');
    }

    $sql  = 'UPDATE tasks SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
        // Either nothing changed or the task was not found
        $check = $pdo->prepare('SELECT id FROM tasks WHERE id = :id');
        $check->execute([':id' => $id]);
        if (!$check->fetch()) {
            respondError('Tarefa não encontrada.', 404);
        }
    }

    $stmt = $pdo->prepare('SELECT id, title, completed, created_at, updated_at FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $task = $stmt->fetch();

    $task['id']        = (int) $task['id'];
    $task['completed'] = (bool) $task['completed'];

    respond($task);
}

/**
 * DELETE /api/tasks.php?id=X
 * Removes a task by id.
 */
function deleteTask(PDO $pdo, ?int $id): never
{
    if ($id === null || $id <= 0) {
        respondError('Parâmetro "id" inválido ou ausente.');
    }

    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        respondError('Tarefa não encontrada.', 404);
    }

    respond(['message' => 'Tarefa removida com sucesso.']);
}
