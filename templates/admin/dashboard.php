<!DOCTYPE html>
<html>
<head>
    <title>Admin - <?= $config['site']['name'] ?></title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <ul>
        <li>Projects: <?= $stats['projects'] ?></li>
        <li>Published Posts: <?= $stats['posts'] ?></li>
        <li>Drafts: <?= $stats['drafts'] ?></li>
        <li>Users: <?= $stats['users'] ?></li>
        <li>Comments: <?= $stats['comments'] ?></li>
        <li>Devlog Entries: <?= $stats['devlogs'] ?></li>
        <li>Unread Messages: <?= $unreadMessages ?></li>
    </ul>
    <nav>
        <a href="/admin/projects">Projects</a> |
        <a href="/admin/blog">Blog</a> |
        <a href="/admin/tags">Tags</a> |
        <a href="/admin/users">Users</a> |
        <a href="/admin/messages">Messages</a> |
        <a href="/admin/media">Media</a>
    </nav>
</body>
</html>