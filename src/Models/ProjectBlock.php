<?php

namespace Quidque\Models;

class ProjectBlock extends Model
{
    protected static string $table = 'project_blocks';
    
    public static function getForProject(int $projectId): array
    {
        return self::$db->fetchAll(
            "SELECT pb.*, bt.name as block_type_name, bt.slug as block_type_slug
             FROM " . static::$table . " pb
             JOIN block_types bt ON pb.block_type_id = bt.id
             WHERE pb.project_id = ?
             ORDER BY pb.sort_order",
            [$projectId]
        );
    }
    
    public static function createBlock(int $projectId, int $blockTypeId, array $data, int $sortOrder = 0): int
    {
        return self::create([
            'project_id' => $projectId,
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
    
    public static function reorder(int $projectId, array $blockIds): void
    {
        foreach ($blockIds as $order => $id) {
            self::update($id, ['sort_order' => $order]);
        }
    }
    
    public static function deleteForProject(int $projectId): int
    {
        return self::$db->delete(static::$table, 'project_id = ?', [$projectId]);
    }
}