<!DOCTYPE html>
<html>
<head>
    <title>Login - <?= $config['site']['name'] ?></title>
</head>
<body>
    <h1>Login</h1>
    
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php else: ?>
        <form method="POST" action="/login">
            <?= $csrf ?>
            <p>
                <label>Email:</label><br>
                <input type="email" name="email" required>
            </p>
            <button type="submit">Send login link</button>
        </form>
        <p><a href="/register">Need an account? Register</a></p>
    <?php endif; ?>
</body>
</html>