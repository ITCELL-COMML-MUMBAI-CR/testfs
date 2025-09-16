<?php

require_once 'BaseModel.php';

class EmailTemplateModel extends BaseModel {
    
    protected $table = 'email_templates';
    protected $fillable = [
        'name',
        'template_json',
        'template_html',
    ];

    /**
     * Get all templates, but only return id and name.
     */
    public function getAllTemplates()
    {
        $sql = "SELECT id, name FROM {$this->table} ORDER BY updated_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get a single template's full data.
     */
    public function getTemplate($id)
    {
        return $this->find($id);
    }

    /**
     * Save a template (create or update).
     */
    public function saveTemplate($data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            // Update
            $id = $data['id'];
            unset($data['id']);
            $this->update($id, $data);
            return $id;
        } else {
            // Create
            return $this->create($data);
        }
    }
}
