<?php
/**
 * Admin - Users List
 */
$pageTitle = 'Users';
$breadcrumbs = [['label' => 'Users']];
?>

<div class="page-header">
    <h1 class="page-title">Users</h1>
    <p class="page-subtitle"><?= count($users) ?> registered users</p>
</div>

<?php if (empty($users)): ?>
    <div class="card">
        <div class="empty-state">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <p class="empty-state-title">No users yet</p>
            <p>Users will appear here when they register.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="user-name">
                                        <?= htmlspecialchars($user['username']) ?>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge badge-magenta">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-email"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($user['is_confirmed']): ?>
                                <span class="status status-complete">
                                    <span class="status-dot"></span>
                                    Confirmed
                                </span>
                            <?php else: ?>
                                <span class="status status-on_hold">
                                    <span class="status-dot"></span>
                                    Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-secondary"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                        </td>
                        <td>
                            <div class="flex gap-2 justify-end">
                                <a href="/admin/users/<?= $user['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>