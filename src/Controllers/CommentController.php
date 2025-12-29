<?php

namespace Quidque\Controllers;

use Quidque\Core\Auth;
use Quidque\Models\Project;
use Quidque\Models\Comment;

class CommentController extends Controller
{
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
        
        $content = trim($this->request->post('content', ''));
        $parentId = $this->request->post('parent_id');
        
        if (empty($content)) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
        }
        
        if (strlen($content) > 2000) {
            return $this->json(['error' => 'Comment too long (max 2000 characters)'], 400);
        }
        
        // Only admin can reply to comments
        if ($parentId && !Auth::isAdmin()) {
            return $this->json(['error' => 'Only admin can reply'], 403);
        }
        
        $commentId = Comment::createComment(
            $project['id'],
            Auth::id(),
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
        
        if (!Comment::canEdit($comment['id'])) {
            return $this->json(['error' => 'Cannot edit comment with replies'], 400);
        }
        
        $content = trim($this->request->post('content', ''));
        
        if (empty($content)) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
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
        
        $deletedBy = $isAdmin && !$isOwner ? 'admin' : 'user';
        Comment::softDelete($comment['id'], $deletedBy);
        
        return $this->json(['success' => true]);
    }
}