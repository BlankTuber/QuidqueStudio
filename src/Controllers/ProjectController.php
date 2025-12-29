<?php

namespace Quidque\Controllers;

class ProjectController extends Controller
{
    public function index(array $params): string
    {
        $status = $this->request->get('status');
        $tag = $this->request->get('tag');
        
        $sql = "SELECT p.*, GROUP_CONCAT(t.name) as tags 
                FROM projects p 
                LEFT JOIN project_tags pt ON p.id = pt.project_id 
                LEFT JOIN tags t ON pt.tag_id = t.id 
                WHERE 1=1";
        $bindings = [];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $bindings[] = $status;
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.updated_at DESC";
        
        $projects = $this->db->fetchAll($sql, $bindings);
        
        return $this->render('projects/index', [
            'projects' => $projects,
        ]);
    }
    
    public function show(array $params): string
    {
        $project = $this->db->fetch(
            "SELECT * FROM projects WHERE slug = ?",
            [$params['slug']]
        );
        
        if (!$project) {
            return $this->notFound();
        }
        
        $techStack = $this->db->fetchAll(
            "SELECT ts.* FROM tech_stack ts 
             JOIN project_tech_stack pts ON ts.id = pts.tech_id 
             WHERE pts.project_id = ? 
             ORDER BY ts.tier, ts.name",
            [$project['id']]
        );
        
        $blocks = $this->db->fetchAll(
            "SELECT pb.*, bt.slug as block_type 
             FROM project_blocks pb 
             JOIN block_types bt ON pb.block_type_id = bt.id 
             WHERE pb.project_id = ? 
             ORDER BY pb.sort_order",
            [$project['id']]
        );
        
        return $this->render('projects/show', [
            'project' => $project,
            'techStack' => $techStack,
            'blocks' => $blocks,
        ]);
    }
}