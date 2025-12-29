<?php
/**
 * Admin - Message Thread
 */
$pageTitle = 'Chat with ' . $user['username'];
$breadcrumbs = [
    ['label' => 'Messages', 'url' => '/admin/messages'],
    ['label' => $user['username']]
];
?>

<div class="page-header">
    <div class="flex items-center gap-4">
        <a href="/admin/messages" class="btn btn-ghost btn-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12,19 5,12 12,5"/>
            </svg>
        </a>
        <div class="flex items-center gap-3">
            <div class="user-avatar">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            </div>
            <div>
                <h1 class="page-title" style="margin-bottom: 0;"><?= htmlspecialchars($user['username']) ?></h1>
                <p class="text-secondary text-small"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="message-thread-container">
    <div class="message-thread" id="message-thread">
        <?php if (empty($messages)): ?>
            <div class="empty-state">
                <p class="text-secondary">No messages in this conversation yet.</p>
            </div>
        <?php else: ?>
            <?php 
            $lastDate = null;
            foreach ($messages as $message): 
                $messageDate = date('Y-m-d', strtotime($message['created_at']));
                if ($messageDate !== $lastDate):
                    $lastDate = $messageDate;
            ?>
                <div class="message-date-divider">
                    <span><?= date('F j, Y', strtotime($message['created_at'])) ?></span>
                </div>
            <?php endif; ?>
                <div class="message <?= $message['is_from_admin'] ? 'message-sent' : 'message-received' ?>">
                    <?php if (!empty($message['image_path'])): ?>
                        <div class="message-image">
                            <img src="/uploads/<?= htmlspecialchars($message['image_path']) ?>" alt="Attached image">
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($message['content'])): ?>
                        <div class="message-content"><?= nl2br(htmlspecialchars($message['content'])) ?></div>
                    <?php endif; ?>
                    <div class="message-time"><?= date('g:i A', strtotime($message['created_at'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <form method="POST" action="/admin/messages/<?= $user['id'] ?>" class="message-compose">
        <?= $csrf ?>
        <div class="message-input-wrapper">
            <textarea name="content" placeholder="Type your reply..." rows="1" required></textarea>
            <button type="submit" class="btn btn-primary btn-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22,2 15,22 11,13 2,9"/>
                </svg>
            </button>
        </div>
    </form>
</div>

<script>
// Scroll to bottom of thread
document.addEventListener('DOMContentLoaded', function() {
    const thread = document.getElementById('message-thread');
    thread.scrollTop = thread.scrollHeight;
    
    // Auto-expand textarea
    const textarea = document.querySelector('.message-compose textarea');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 150) + 'px';
    });
});
</script>