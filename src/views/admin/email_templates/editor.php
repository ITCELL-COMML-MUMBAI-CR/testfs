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
                    {
                        id: 'text',
                        label: 'Text',
                        content: '<div data-gjs-type="text">Insert your text here</div>',
                        category: 'Basic',
                    },
                    {
                        id: 'image',
                        label: 'Image',
                        select: true,
                        content: { type: 'image' },
                        category: 'Basic',
                        activate: true,
                    }
                ]
            },
        });

        // Load existing template data if available
        const templateData = <?= $template_json ?>;
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
