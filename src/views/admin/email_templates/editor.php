<?php
// File: /src/views/admin/email_templates/editor.php

// Simple check to see if we are editing an existing template
$is_editing = isset($current_template) && $current_template;
$template_id = $is_editing ? $current_template['id'] : null;
$template_name = $is_editing ? $current_template['name'] : '';
$template_json = $is_editing ? $current_template['template_json'] : 'null';
ob_start();

?>

<link href="https://cdn.jsdelivr.net/npm/grapesjs@0.21.2/dist/css/grapes.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/grapesjs@0.21.2/dist/grapes.min.js"></script>

<style>
    .editor-container {
        display: flex;
        height: 80vh; /* Or a fixed height */
        border: 3px solid #444;
    }
    #blocks {
        width: 20%;
        border-right: 1px solid #ddd;
    }
    #gjs {
        width: 80%;
    }
</style>

<div class="container-fluid py-3">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4>Email Template Editor</h4>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Load Template
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/email-templates/editor">New Template</a></li>
                    <?php foreach ($templates as $template) : ?>
                        <li><a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/email-templates/editor?id=<?= $template['id'] ?>"><?= htmlspecialchars($template['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <button id="save-template" class="btn btn-primary">Save Template</button>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <input type="text" id="template-name" class="form-control" placeholder="Enter Template Name" value="<?= htmlspecialchars($template_name) ?>">
        </div>
    </div>

    <div class="editor-container">
        <div id="blocks"></div>
        <div id="gjs"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editor = grapesjs.init({
            container: '#gjs',
            fromElement: true,
            height: '100%',
            width: '100%',
            storageManager: false,
            blockManager: {
                appendTo: '#blocks',
                blocks: [
                    // Basic components
                    {
                        id: 'text',
                        label: 'Text',
                        content: '<div data-gjs-type="text" style="padding: 10px;">Insert your text here</div>',
                        category: 'Basic',
                    },
                    {
                        id: 'image',
                        label: 'Image',
                        select: true,
                        content: { type: 'image' },
                        category: 'Basic',
                        activate: true,
                    },
                    {
                        id: 'button',
                        label: 'Button',
                        content: '<a href="#" style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Click Here</a>',
                        category: 'Basic',
                    },
                    {
                        id: 'divider',
                        label: 'Divider',
                        content: '<hr style="border: none; height: 1px; background-color: #ddd; margin: 20px 0;">',
                        category: 'Basic',
                    },

                    // Layout components
                    {
                        id: 'section',
                        label: 'Section',
                        content: '<section style="padding: 20px; margin: 10px 0;"><h2>Section Title</h2><p>Section content goes here...</p></section>',
                        category: 'Layout',
                    },
                    {
                        id: 'container',
                        label: 'Container',
                        content: '<div style="max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff;"></div>',
                        category: 'Layout',
                    },
                    {
                        id: '2-columns',
                        label: '2 Columns',
                        content: '<div style="display: flex; gap: 20px;"><div style="flex: 1; padding: 15px; border: 1px dashed #ddd;">Column 1</div><div style="flex: 1; padding: 15px; border: 1px dashed #ddd;">Column 2</div></div>',
                        category: 'Layout',
                    },
                    {
                        id: '3-columns',
                        label: '3 Columns',
                        content: '<div style="display: flex; gap: 15px;"><div style="flex: 1; padding: 10px; border: 1px dashed #ddd;">Col 1</div><div style="flex: 1; padding: 10px; border: 1px dashed #ddd;">Col 2</div><div style="flex: 1; padding: 10px; border: 1px dashed #ddd;">Col 3</div></div>',
                        category: 'Layout',
                    },

                    // Email-specific components
                    {
                        id: 'header',
                        label: 'Header',
                        content: '<header style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center;"><h1 style="margin: 0; font-size: 28px;">{{app_name}}</h1><p style="margin: 5px 0 0 0; opacity: 0.9;">Support & Mediation Portal</p></header>',
                        category: 'Email',
                    },
                    {
                        id: 'footer',
                        label: 'Footer',
                        content: '<footer style="background-color: #333; color: white; text-align: center; padding: 20px; font-size: 12px;"><p><strong>{{app_name}} Support Team</strong></p><p>This is an automated message. Please do not reply directly to this email.</p></footer>',
                        category: 'Email',
                    },
                    {
                        id: 'greeting',
                        label: 'Greeting',
                        content: '<div style="padding: 20px;"><h2 style="color: #007bff;">Dear {{customer_name}},</h2></div>',
                        category: 'Email',
                    },
                    {
                        id: 'info-box',
                        label: 'Info Box',
                        content: '<div style="background-color: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;"><strong>Information:</strong><br>Important details go here.</div>',
                        category: 'Email',
                    },
                    {
                        id: 'alert-box',
                        label: 'Alert Box',
                        content: '<div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid #ffc107;"><h3 style="margin-top: 0; color: #856404;">⚠️ Attention Required</h3><p style="margin-bottom: 0;">This is an important alert message.</p></div>',
                        category: 'Email',
                    },
                    {
                        id: 'success-box',
                        label: 'Success Box',
                        content: '<div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;"><h2 style="color: #155724; margin-top: 0;">🎉 Success!</h2><p style="font-size: 16px; margin-bottom: 0;">Operation completed successfully.</p></div>',
                        category: 'Email',
                    },

                    // SAMPARK specific components
                    {
                        id: 'ticket-info',
                        label: 'Ticket Info',
                        content: '<div style="background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;"><strong>Ticket Number:</strong> {{complaint_id}}<br><strong>Company:</strong> {{company_name}}</div>',
                        category: 'SAMPARK',
                    },
                    {
                        id: 'action-button',
                        label: 'Action Button',
                        content: '<div style="text-align: center; margin: 30px 0;"><a href="{{view_url}}" style="display: inline-block; background-color: #007bff; color: white; padding: 15px 35px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">View My Ticket</a></div>',
                        category: 'SAMPARK',
                    },
                    {
                        id: 'features-list',
                        label: 'Features List',
                        content: '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;"><h3 style="margin-top: 0; color: #007bff;">You can now:</h3><ul style="margin: 0; padding-left: 20px;"><li style="margin: 8px 0;">Create and track support tickets</li><li style="margin: 8px 0;">View your complete ticket history</li><li style="margin: 8px 0;">Update your profile information</li><li style="margin: 8px 0;">Access all SAMPARK services</li></ul></div>',
                        category: 'SAMPARK',
                    },
                    {
                        id: 'login-buttons',
                        label: 'Login Buttons',
                        content: '<div style="text-align: center; margin: 30px 0;"><a href="{{view_url}}" style="display: inline-block; background-color: #17a2b8; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 5px;">📋 View & Update Ticket</a><a href="{{login_url}}" style="display: inline-block; background-color: #6c757d; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 5px;">🔐 Login First</a></div>',
                        category: 'SAMPARK',
                    }
                ]
            },
        });

        // Load existing template data if available
        const templateData = <?= $template_json ? json_encode(json_decode($template_json, true), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) : 'null' ?>;
        if (templateData) {
            editor.setComponents(templateData.components);
            editor.setStyle(templateData.styles);
        }


        document.getElementById('save-template').addEventListener('click', function() {
            const templateName = document.getElementById('template-name').value;
            if (!templateName) {
                alert('Please enter a template name.');
                return;
            }

            const templateJson = {
                components: editor.getComponents(),
                styles: editor.getStyle(),
            };

            const templateHtml = editor.getHtml();

            const data = {
                id: <?= $template_id ?? 'null' ?>,
                name: templateName,
                template_json: JSON.stringify(templateJson),
                template_html: templateHtml,
                _csrf: '<?= $csrf_token ?>'
            };

            fetch('<?= Config::getAppUrl() ?>/api/email-templates/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $csrf_token ?>'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        if (result.id && !<?= $template_id ?? 'false' ?>) {
                            // If it was a new template, reload to get the new ID in the URL
                            window.location.href = '<?= Config::getAppUrl() ?>/admin/email-templates/editor?id=' + result.id;
                        }
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Save error:', error);
                    alert('An unexpected error occurred.');
                });
        });
    });
</script>
<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
