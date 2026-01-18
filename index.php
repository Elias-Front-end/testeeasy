<?php
require __DIR__ . '/config.php';
$name = '';
$email = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name === '' || $email === '') {
        $message = 'Preencha nome e e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'E-mail inválido.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
        try {
            $stmt->execute([':name' => $name, ':email' => $email]);
            $message = 'Cadastro realizado com sucesso.';
            $name = '';
            $email = '';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $message = 'Este e-mail já está cadastrado.';
            } else {
                $message = 'Erro ao salvar cadastro.';
            }
        }
    }
}
$users = [];
try {
    $stmt = $pdo->query('SELECT id, name, email, created_at FROM users ORDER BY id DESC LIMIT 10');
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Cadastro de usuários</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;margin:2rem;background:#f5f5f5}
.container{max-width:600px;margin:0 auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
h1{margin-top:0}
label{display:block;margin-top:.75rem}
input[type="text"],input[type="email"]{width:100%;padding:.5rem;margin-top:.25rem;border:1px solid #ccc;border-radius:4px}
button{margin-top:1rem;padding:.5rem 1rem;border:none;border-radius:4px;background:#2563eb;color:#fff;cursor:pointer}
button:hover{background:#1d4ed8}
.message{margin-top:1rem;padding:.75rem;border-radius:4px;background:#eef}
table{width:100%;border-collapse:collapse;margin-top:1.5rem}
th,td{padding:.5rem;border-bottom:1px solid #eee;font-size:.9rem;text-align:left}
</style>
</head>
<body>
<div class="container">
<h1>Cadastro de usuários</h1>
<p>Versão do PHP: <?php echo phpversion(); ?></p>
<p>Data do servidor: <?php echo date('Y-m-d H:i:s'); ?></p>
<?php if ($message !== ''): ?>
<div class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<form method="post">
<label for="name">Nome</label>
<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
<label for="email">E-mail</label>
<input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit">Cadastrar</button>
</form>
<?php if ($users): ?>
<h2>Últimos cadastros</h2>
<table>
<thead>
<tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Criado em</th></tr>
</thead>
<tbody>
<?php foreach ($users as $user): ?>
<tr>
<td><?php echo (int) $user['id']; ?></td>
<td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</body>
</html>
