<!DOCTYPE html>
<html>
<head>
    <title>Register - <?= $config['site']['name'] ?></title>
</head>
<body>
    <h1>Register</h1>
    
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
    <?php else: ?>
        <form method="POST" action="/register">
            <?= $csrf ?>
            <p>
                <label>Email:</label><br>
                <input type="email" name="email" required>
            </p>
            <p>
                <label>Username:</label><br>
                <input type="text" name="username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
            </p>
            <button type="submit">Register</button>
        </form>
        <p><a href="/login">Already have an account? Login</a></p>
    <?php endif; ?>
</body>
</html>