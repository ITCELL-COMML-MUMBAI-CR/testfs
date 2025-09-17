<?php
$page_title = 'Test Emails';
include __DIR__ . '/../../layouts/app.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🧪 Test Email Templates</h3>
                </div>
                <div class="card-body">
                    <form id="template-email-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <div class="form-group">
                            <label for="template_code">Email Template</label>
                            <select class="form-control" id="template_code" name="template_code" required>
                                <option value="">Select Template</option>
                                <?php foreach ($email_templates as $template): ?>
                                    <option value="<?= $template['template_code'] ?>"><?= htmlspecialchars($template['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="test_email">Test Email Address</label>
                            <input type="email" class="form-control" id="test_email" name="test_email" placeholder="test@example.com" required>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-info" id="preview-template">👁️ Preview Template</button>
                            <button type="submit" class="btn btn-success">📧 Send Test Email</button>
                        </div>
                    </form>

                    <div id="template-preview" class="mt-4" style="display: none;">
                        <h5>📋 Template Preview</h5>
                        <div class="alert alert-info">
                            <strong>Subject:</strong> <span id="preview-subject"></span>
                        </div>
                        <div class="border p-3" style="max-height: 400px; overflow-y: auto;">
                            <iframe id="preview-iframe" width="100%" height="300" frameborder="0"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📨 Send Custom Email</h3>
                </div>
                <div class="card-body">
                    <form id="send-email-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="body">Body (HTML)</label>
                            <textarea class="form-control" id="body" name="body" rows="5" required></textarea>
                        </div>

                        <div class="form-group">
                            <label>Send To</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_all_customers" value="all_customers" checked>
                                <label class="form-check-label" for="send_to_all_customers">All Customers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_specific_customers" value="specific_customers">
                                <label class="form-check-label" for="send_to_specific_customers">Specific Customers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_all_users" value="all_users">
                                <label class="form-check-label" for="send_to_all_users">All Users</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_user_type" value="user_type">
                                <label class="form-check-label" for="send_to_user_type">Specific User Type</label>
                            </div>
                             <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_division" value="division">
                                <label class="form-check-label" for="send_to_division">Specific Division/Zone/Department</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_specific_users" value="specific_users">
                                <label class="form-check-label" for="send_to_specific_users">Specific Users</label>
                            </div>
                        </div>

                        <div id="specific_customers_div" class="form-group d-none">
                            <label for="specific_customers">Select Customers</label>
                            <select multiple class="form-control" id="specific_customers" name="specific_customers[]">
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['customer_id'] ?>"><?= htmlspecialchars($customer['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="user_type_div" class="form-group d-none">
                            <label for="user_type">Select User Type</label>
                            <select class="form-control" id="user_type" name="user_type">
                                <?php foreach ($user_types as $type): ?>
                                    <option value="<?= $type['role'] ?>"><?= ucfirst($type['role']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="division_div" class="form-group d-none">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="division">Division</label>
                                    <select class="form-control" id="division" name="division">
                                        <option value="">All</option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?= $division['division'] ?>"><?= htmlspecialchars($division['division']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="zone">Zone</label>
                                    <select class="form-control" id="zone" name="zone">
                                        <option value="">All</option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?= $zone['zone'] ?>"><?= htmlspecialchars($zone['zone']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="department">Department</label>
                                    <select class="form-control" id="department" name="department">
                                        <option value="">All</option>
                                        <?php foreach ($departments as $department): ?>
                                            <option value="<?= $department['department'] ?>"><?= htmlspecialchars($department['department']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="specific_users_div" class="form-group d-none">
                            <label for="specific_users">Select Users</label>
                            <select multiple class="form-control" id="specific_users" name="specific_users[]">
                                <?php foreach ($users as $user_item): ?>
                                    <option value="<?= $user_item['id'] ?>"><?= htmlspecialchars($user_item['name']) ?> (<?= $user_item['role'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Send Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('input[name="send_to"]').change(function() {
        $('#specific_customers_div').addClass('d-none');
        $('#user_type_div').addClass('d-none');
        $('#division_div').addClass('d-none');
        $('#specific_users_div').addClass('d-none');

        var selected = $(this).val();
        if (selected === 'specific_customers') {
            $('#specific_customers_div').removeClass('d-none');
        } else if (selected === 'user_type') {
            $('#user_type_div').removeClass('d-none');
        } else if (selected === 'division') {
            $('#division_div').removeClass('d-none');
        } else if (selected === 'specific_users') {
            $('#specific_users_div').removeClass('d-none');
        }
    });

    // Template email functionality
    $('#preview-template').click(function() {
        var templateCode = $('#template_code').val();
        if (!templateCode) {
            Swal.fire('Error', 'Please select a template first.', 'error');
            return;
        }

        $.ajax({
            url: '<?= Config::getAppUrl() ?>/admin/testing/emails/preview',
            type: 'GET',
            data: { template_code: templateCode },
            success: function(response) {
                if (response.success) {
                    $('#preview-subject').text(response.preview.subject);
                    var iframe = document.getElementById('preview-iframe');
                    iframe.srcdoc = response.preview.body_html;
                    $('#template-preview').show();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to preview template.', 'error');
            }
        });
    });

    $('#template-email-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: '<?= Config::getAppUrl() ?>/admin/testing/emails/send-template',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while sending the template email.', 'error');
            }
        });
    });

    $('#send-email-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: '<?= Config::getAppUrl() ?>/admin/testing/emails/send',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred while sending the email.', 'error');
            }
        });
    });
});
</script>