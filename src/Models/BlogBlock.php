<?php

namespace Quidque\Models;

class BlogBlock extends Model
{
    protected static string $table = 'blog_blocks';
    
    public static function getForPost(int $postId): array
    {
        return self::$db->fetchAll(
            "SELECT bb.*, bbt.name as block_type_name, bbt.slug as block_type_slug
             FROM " . static::$table . " bb
             JOIN blog_block_types bbt ON bb.block_type_id = bbt.id
             WHERE bb.post_id = ?
             ORDER BY bb.sort_order",
            [$postId]
        );
    }
    
    public static function createBlock(int $postId, int $blockTypeId, array $data, int $sortOrder = 0): int
    {
        return self::create([
            'post_id' => $postId,
            'block_type_id' => $blockTypeId,
            'data' => json_encode($data),
            'sort_order' => $sortOrder,
        ]);
    }
    
    public static function updateData(int $id, array $data): int
    {
        return self::update($id, ['data' => json_encode($data)]);
    }
    
    public static function getData(int $id): array
    {
        $block = self::find($id);
        if (!$block || !$block['data']) {
            return [];
        }
        return json_decode($block['data'], true) ?? [];
    }
    
    public static function reorder(int $postId, array $blockIds): void
    {
        foreach ($blockIds as $order => $id) {
            self::update($id, ['sort_order' => $order]);
        }
    }
    
    public static function deleteForPost(int $postId): int
    {
        return self::$db->delete(static::$table, 'post_id = ?', [$postId]);
    }
}