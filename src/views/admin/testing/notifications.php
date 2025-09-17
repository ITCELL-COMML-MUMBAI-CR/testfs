<?php
$page_title = 'Test Notifications';
include __DIR__ . '/../../layouts/app.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Send Test Notification</h3>
                </div>
                <div class="card-body">
                    <form id="send-notification-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
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
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_structured" value="structured">
                                <label class="form-check-label" for="send_to_structured">Structured (Zone → Division → Department)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_to" id="send_to_user_type" value="user_type">
                                <label class="form-check-label" for="send_to_user_type">Specific User Type</label>
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

                        <div id="structured_div" class="form-group d-none">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Structured Notification Selection</h6>
                                    <small class="text-muted">Select zone, then division, then department type in hierarchical order</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="structured_zone">1. Select Zone(s)</label>
                                            <select multiple class="form-control" id="structured_zone" name="structured_zone[]">
                                                <option value="">All Zones</option>
                                                <?php foreach ($zones as $zone): ?>
                                                    <option value="<?= $zone['zone'] ?>"><?= htmlspecialchars($zone['zone']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Hold Ctrl to select multiple</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="structured_division">2. Select Division(s)</label>
                                            <select multiple class="form-control" id="structured_division" name="structured_division[]">
                                                <option value="">All Divisions from selected zones</option>
                                                <?php foreach ($divisions as $division): ?>
                                                    <option value="<?= $division['division'] ?>"><?= htmlspecialchars($division['division']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Hold Ctrl to select multiple</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="structured_department">3. Select Department Type(s)</label>
                                            <select multiple class="form-control" id="structured_department" name="structured_department[]">
                                                <option value="">All Departments from selected divisions</option>
                                                <?php foreach ($departments as $department): ?>
                                                    <option value="<?= $department['department'] ?>"><?= htmlspecialchars($department['department']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Hold Ctrl to select multiple</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-12">
                                            <label>Target User Types</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="structured_user_types[]" id="structured_controller" value="controller" checked>
                                                <label class="form-check-label" for="structured_controller">Controllers</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="structured_user_types[]" id="structured_controller_nodal" value="controller_nodal" checked>
                                                <label class="form-check-label" for="structured_controller_nodal">Controller Nodals</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="structured_user_types[]" id="structured_admin" value="admin">
                                                <label class="form-check-label" for="structured_admin">Admins</label>
                                            </div>
                                        </div>
                                    </div>
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

                        <button type="submit" class="btn btn-primary">Send Notification</button>
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
        $('#structured_div').addClass('d-none');
        $('#specific_users_div').addClass('d-none');

        var selected = $(this).val();
        if (selected === 'specific_customers') {
            $('#specific_customers_div').removeClass('d-none');
        } else if (selected === 'user_type') {
            $('#user_type_div').removeClass('d-none');
        } else if (selected === 'structured') {
            $('#structured_div').removeClass('d-none');
        } else if (selected === 'specific_users') {
            $('#specific_users_div').removeClass('d-none');
        }
    });

    $('#send-notification-form').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: '<?= Config::getAppUrl() ?>/admin/testing/notifications/send',
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
                Swal.fire('Error', 'An error occurred while sending the notification.', 'error');
            }
        });
    });
});
</script>

