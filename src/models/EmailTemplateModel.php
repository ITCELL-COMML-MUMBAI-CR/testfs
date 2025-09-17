<?php

require_once 'BaseModel.php';

class EmailTemplateModel extends BaseModel {
    
    protected $table = 'email_templates';
    protected $fillable = [
        'name',
        'template_code',
        'is_active',
        'subject',
        'template_json',
        'body_html',
        'body_text',
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

    /**
     * Get template by template_code
     */
    public function getTemplateByCode($templateCode)
    {
        $sql = "SELECT * FROM {$this->table} WHERE template_code = ? AND is_active = 1";
        return $this->db->fetch($sql, [$templateCode]);
    }

    /**
     * Get all active templates with their codes
     */
    public function getActiveTemplates()
    {
        $sql = "SELECT id, name, template_code, subject FROM {$this->table} WHERE is_active = 1 ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Process template variables
     */
    public function processTemplate($templateCode, $variables)
    {
        $template = $this->getTemplateByCode($templateCode);
        if (!$template) {
            throw new Exception("Template not found: {$templateCode}");
        }

        $subject = $this->replaceVariables($template['subject'], $variables);
        $bodyHtml = $this->replaceVariables($template['body_html'], $variables);
        $bodyText = $template['body_text'] ? $this->replaceVariables($template['body_text'], $variables) : '';

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'template' => $template
        ];
    }

    /**
     * Replace template variables with actual values
     */
    private function replaceVariables($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
}
