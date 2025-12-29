<!DOCTYPE html>
<html>
<head>
    <title>Verify - <?= $config['site']['name'] ?></title>
</head>
<body>
    <h1>Verify Login</h1>
    
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <p><a href="/login">Try again</a></p>
    <?php endif; ?>
</body>
</html>