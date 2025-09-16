<?php

require_once 'BaseController.php';
require_once __DIR__ . '/../models/EmailTemplateModel.php';
require_once __DIR__ . '/../utils/Validator.php';

class EmailTemplateController extends BaseController
{
    private $templateModel;

    public function __construct()
    {
        parent::__construct();
        $this->templateModel = new EmailTemplateModel();
    }

    /**
     * Display the email template editor page.
     */
    public function editor()
    {
        $user = $this->getCurrentUser();
        $templates = $this->templateModel->getAllTemplates();
        
        $template_id = $_GET['id'] ?? null;
        $current_template = null;
        if ($template_id) {
            $current_template = $this->templateModel->getTemplate($template_id);
        }

        $data = [
            'page_title' => 'Email Template Editor',
            'user' => $user,
            'templates' => $templates,
            'current_template' => $current_template,
            'csrf_token' => $this->session->getCSRFToken()
        ];

        $this->view('admin/email_templates/editor', $data);
    }

    /**
     * API endpoint to get all templates (id and name).
     */
    public function listAll()
    {
        try {
            $templates = $this->templateModel->getAllTemplates();
            $this->json(['success' => true, 'templates' => $templates]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to load templates.'], 500);
        }
    }

    /**
     * API endpoint to get a single template's data for the editor.
     */
    public function get($id)
    {
        try {
            $template = $this->templateModel->getTemplate($id);
            if ($template) {
                $this->json(['success' => true, 'template' => $template]);
            } else {
                $this->json(['success' => false, 'message' => 'Template not found.'], 404);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to load template.'], 500);
        }
    }

    /**
     * API endpoint to save a template.
     */
    public function save()
    {
        $this->validateCSRF();
        $data = json_decode(file_get_contents('php://input'), true);

        $validator = new Validator();
        $isValid = $validator->validate($data, [
            'name' => 'required|min:3|max:255',
            'template_json' => 'required',
            'template_html' => 'required',
        ]);

        if (!$isValid) {
            $this->json(['success' => false, 'errors' => $validator->getErrors()], 400);
            return;
        }

        try {
            $templateId = $this->templateModel->saveTemplate($data);
            $this->json(['success' => true, 'message' => 'Template saved successfully', 'id' => $templateId]);
        } catch (Exception $e) {
            error_log("Email Template Save Error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to save template.'], 500);
        }
    }
}
