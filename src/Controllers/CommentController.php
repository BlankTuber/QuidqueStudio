<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Models\Project;
use Quidque\Models\Comment;
use Quidque\Helpers\RateLimiter;
use Quidque\Constants;

class CommentController extends Controller
{
    private const MAX_COMMENTS_PER_MINUTE = 3;
    private const COMMENT_DECAY_SECONDS = 60;
    
    public function store(array $params): string
    {
        $project = Project::findBySlug($params['slug']);
        
        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }
        
        $settings = Project::getSettings($project['id']);
        if (!($settings['comments_enabled'] ?? false)) {
            return $this->json(['error' => 'Comments disabled'], 403);
        }
        
        $userId = Auth::id();
        $rateLimitKey = 'comment:' . $userId;
        
        if (!RateLimiter::attempt($rateLimitKey, self::MAX_COMMENTS_PER_MINUTE, self::COMMENT_DECAY_SECONDS)) {
            return $this->json(['error' => 'Please wait before posting another comment'], 429);
        }
        
        $content = trim($this->request->post('content', ''));
        $parentId = $this->request->post('parent_id');
        
        if (empty($content)) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
        }
        
        if (strlen($content) > Constants::MAX_COMMENT_LENGTH) {
            return $this->json([
                'error' => 'Comment too long (max ' . Constants::MAX_COMMENT_LENGTH . ' characters)'
            ], 400);
        }
        
        if ($parentId && !Auth::isAdmin()) {
            return $this->json(['error' => 'Only admin can reply'], 403);
        }
        
        if ($parentId) {
            $parentComment = Comment::find((int) $parentId);
            if (!$parentComment || $parentComment['project_id'] !== $project['id']) {
                return $this->json(['error' => 'Invalid parent comment'], 400);
            }
        }
        
        $commentId = Comment::createComment(
            $project['id'],
            $userId,
            $content,
            $parentId ? (int) $parentId : null
        );
        
        if ($this->request->isHtmx()) {
            $comments = Comment::getThreaded($project['id']);
            return $this->render('partials/comments', [
                'comments' => $comments,
                'project' => $project,
            ]);
        }
        
        return $this->json(['success' => true, 'id' => $commentId]);
    }
    
    public function update(array $params): string
    {
        $comment = Comment::find((int) $params['id']);
        
        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }
        
        if ($comment['user_id'] !== Auth::id() && !Auth::isAdmin()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($comment['deleted_by'] !== null) {
            return $this->json(['error' => 'Cannot edit deleted comment'], 400);
        }
        
        if (!Comment::canEdit($comment['id'])) {
            return $this->json(['error' => 'Cannot edit comment with replies'], 400);
        }
        
        $content = trim($this->request->post('content', ''));
        
        if (empty($content)) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
        }
        
        if (strlen($content) > Constants::MAX_COMMENT_LENGTH) {
            return $this->json([
                'error' => 'Comment too long (max ' . Constants::MAX_COMMENT_LENGTH . ' characters)'
            ], 400);
        }
        
        Comment::editComment($comment['id'], $content);
        
        return $this->json(['success' => true]);
    }
    
    public function delete(array $params): string
    {
        $comment = Comment::find((int) $params['id']);
        
        if (!$comment) {
            return $this->json(['error' => 'Comment not found'], 404);
        }
        
        $isOwner = $comment['user_id'] === Auth::id();
        $isAdmin = Auth::isAdmin();
        
        if (!$isOwner && !$isAdmin) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }
        
        $deletedBy = $isAdmin && !$isOwner ? Constants::DELETED_BY_ADMIN : Constants::DELETED_BY_USER;
        Comment::softDelete($comment['id'], $deletedBy);
        
        if ($this->request->isHtmx()) {
            return '';
        }
        
        return $this->json(['success' => true]);
    }
}