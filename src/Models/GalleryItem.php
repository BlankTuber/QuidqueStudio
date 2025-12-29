<?php

namespace Quidque\Models;

class GalleryItem extends Model
{
    protected static string $table = 'gallery_items';
    
    public static function getForBlock(int $blockId): array
    {
        return self::$db->fetchAll(
            "SELECT gi.*, m.file_path, m.alt_text, m.mime_type
             FROM " . static::$table . " gi
             JOIN media m ON gi.media_id = m.id
             WHERE gi.block_id = ?
             ORDER BY gi.sort_order",
            [$blockId]
        );
    }
    
    public static function addToBlock(int $blockId, int $mediaId, int $sortOrder = 0): int
    {
        return self::create([
            'block_id' => $blockId,
            'media_id' => $mediaId,
            'sort_order' => $sortOrder,
        ]);
    }
    
    public static function reorder(int $blockId, array $itemIds): void
    {
        foreach ($itemIds as $order => $id) {
            self::update($id, ['sort_order' => $order]);
        }
    }
    
    public static function deleteForBlock(int $blockId): int
    {
        return self::$db->delete(static::$table, 'block_id = ?', [$blockId]);
    }
}