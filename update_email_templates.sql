-- Update email templates to use new styled templates with CSS classes for icons

-- Update ticket created notification
UPDATE email_templates SET
    html_content = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">
                <span class="icon-ticket" style="display: inline-block; width: 24px; height: 24px; background: url(''data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M22,10V6A2,2 0 0,0 20,4H4A2,2 0 0,0 2,6V10C3.11,10 4,10.9 4,12A2,2 0 0,1 2,14V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V14A2,2 0 0,1 20,12A2,2 0 0,1 22,10Z"/></svg>'') center/contain no-repeat; vertical-align: middle;"></span>
                Ticket Created Successfully
            </div>
            <div style="font-size: 14px; opacity: 0.9;">Your support request has been registered</div>
        </div>
        <div style="padding: 30px;">
            <p style="margin: 0 0 20px; font-size: 16px; color: #333;">Dear <strong>{{customer_name}}</strong>,</p>
            <p style="margin: 0 0 20px; color: #555; line-height: 1.6;">
                Thank you for contacting us. Your ticket <strong>#{{complaint_id}}</strong> has been created successfully and assigned to our support team.
            </p>
            <div style="background: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <div style="font-weight: bold; color: #007bff; margin-bottom: 8px;">
                    <span class="icon-info" style="margin-right: 8px;">ℹ️</span>What happens next?
                </div>
                <ul style="margin: 0; padding-left: 20px; color: #555;">
                    <li>Our team will review your request</li>
                    <li>You will receive updates via email</li>
                    <li>Track progress using the link below</li>
                </ul>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{view_url}}" style="display: inline-block; background: #007bff; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
                    <span style="margin-right: 8px;">👁️</span>View Ticket Details
                </a>
            </div>
            <p style="margin: 20px 0 0; color: #666; font-size: 14px;">
                Best regards,<br>
                <strong>{{app_name}} Support Team</strong>
            </p>
        </div>
        <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #e9ecef;">
            This is an automated message. Please do not reply to this email.
        </div>
    </div>
</div>'
WHERE template_key = 'ticket_created';

-- Update info provided notification
UPDATE email_templates SET
    html_content = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">
                <span style="margin-right: 8px;">📄</span>Information Provided
            </div>
            <div style="font-size: 14px; opacity: 0.9;">Customer has submitted additional details</div>
        </div>
        <div style="padding: 30px;">
            <p style="margin: 0 0 20px; font-size: 16px; color: #333;">Dear Team Member,</p>
            <p style="margin: 0 0 20px; color: #555; line-height: 1.6;">
                Customer <strong>{{customer_name}}</strong> has provided additional information for ticket <strong>#{{complaint_id}}</strong>.
            </p>
            <div style="background: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <div style="font-weight: bold; color: #28a745; margin-bottom: 8px;">
                    <span style="margin-right: 8px;">💬</span>Additional Information:
                </div>
                <div style="background: white; padding: 12px; border-radius: 4px; color: #333; font-style: italic;">
                    "{{additional_info}}"
                </div>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{view_url}}" style="display: inline-block; background: #28a745; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
                    <span style="margin-right: 8px;">🔍</span>Review Ticket
                </a>
            </div>
            <p style="margin: 20px 0 0; color: #666; font-size: 14px;">
                Please review and respond promptly to maintain our service quality standards.
            </p>
        </div>
        <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #e9ecef;">
            This is an automated notification from the {{app_name}} system.
        </div>
    </div>
</div>'
WHERE template_key = 'info_provided';

-- Update priority escalation notification
UPDATE email_templates SET
    html_content = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">
                <span style="margin-right: 8px;">⚠️</span>Priority Escalation Alert
            </div>
            <div style="font-size: 14px; opacity: 0.9;">Ticket requires immediate attention</div>
        </div>
        <div style="padding: 30px;">
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                <div style="color: #856404; font-weight: bold; margin-bottom: 8px;">
                    <span style="margin-right: 8px;">🚨</span>URGENT: Priority Escalation
                </div>
                <p style="margin: 0; color: #856404;">
                    Ticket <strong>#{{complaint_id}}</strong> has been escalated to <strong style="color: #dc3545;">{{priority}}</strong> priority.
                </p>
            </div>
            <div style="background: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <div style="font-weight: bold; color: #dc3545; margin-bottom: 8px;">
                    <span style="margin-right: 8px;">📝</span>Escalation Reason:
                </div>
                <div style="color: #333;">{{escalation_reason}}</div>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{view_url}}" style="display: inline-block; background: #dc3545; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
                    <span style="margin-right: 8px;">👁️</span>View Ticket Immediately
                </a>
            </div>
            <p style="margin: 20px 0 0; color: #666; font-size: 14px;">
                <strong>Note:</strong> This ticket requires immediate attention due to priority escalation.
            </p>
        </div>
        <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #e9ecef;">
            Escalation Time: {{escalation_time}} | Division: {{division}} | Department: {{department}}
        </div>
    </div>
</div>'
WHERE template_key = 'priority_escalated';

-- Update ticket awaiting info notification
UPDATE email_templates SET
    html_content = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">
                <span style="margin-right: 8px;">📋</span>Additional Information Required
            </div>
            <div style="font-size: 14px; opacity: 0.8;">Action required on your ticket</div>
        </div>
        <div style="padding: 30px;">
            <p style="margin: 0 0 20px; font-size: 16px; color: #333;">Dear <strong>{{customer_name}}</strong>,</p>
            <p style="margin: 0 0 20px; color: #555; line-height: 1.6;">
                We need additional information to process your ticket <strong>#{{complaint_id}}</strong> efficiently.
            </p>
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <div style="font-weight: bold; color: #856404; margin-bottom: 8px;">
                    <span style="margin-right: 8px;">📝</span>Information Request:
                </div>
                <div style="background: white; padding: 12px; border-radius: 4px; color: #333;">
                    {{message}}
                </div>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{view_url}}" style="display: inline-block; background: #ffc107; color: #212529; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
                    <span style="margin-right: 8px;">✍️</span>Provide Information
                </a>
            </div>
            <div style="background: #e7f3ff; border: 1px solid #b8daff; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <div style="color: #004085; font-size: 14px;">
                    <span style="margin-right: 8px;">💡</span><strong>Tip:</strong> Providing complete and accurate information helps us resolve your issue faster.
                </div>
            </div>
            <p style="margin: 20px 0 0; color: #666; font-size: 14px;">
                Best regards,<br>
                <strong>{{app_name}} Support Team</strong>
            </p>
        </div>
        <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #e9ecef;">
            <span style="margin-right: 8px;">🔗</span>You can also access your ticket directly: <a href="{{login_url}}" style="color: #007bff;">Login to {{app_name}}</a>
        </div>
    </div>
</div>'
WHERE template_key = 'ticket_awaiting_info';

-- Update ticket closed notification
UPDATE email_templates SET
    html_content = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 20px; text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">
                <span style="margin-right: 8px;">✅</span>Ticket Resolved
            </div>
            <div style="font-size: 14px; opacity: 0.9;">Your issue has been successfully resolved</div>
        </div>
        <div style="padding: 30px;">
            <p style="margin: 0 0 20px; font-size: 16px; color: #333;">Dear <strong>{{customer_name}}</strong>,</p>
            <p style="margin: 0 0 20px; color: #555; line-height: 1.6;">
                Great news! Your ticket <strong>#{{complaint_id}}</strong> has been resolved by our support team.
            </p>
            <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <div style="font-weight: bold; color: #155724; margin-bottom: 8px;">
                    <span style="margin-right: 8px;">🛠️</span>Resolution Details:
                </div>
                <div style="background: white; padding: 12px; border-radius: 4px; color: #333;">
                    {{action_taken}}
                </div>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{view_url}}" style="display: inline-block; background: #28a745; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; margin-right: 10px;">
                    <span style="margin-right: 8px;">👁️</span>View Resolution
                </a>
                <a href="{{feedback_url}}" style="display: inline-block; background: #17a2b8; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
                    <span style="margin-right: 8px;">⭐</span>Rate Our Service
                </a>
            </div>
            <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <div style="color: #495057; font-size: 14px; text-align: center;">
                    <span style="margin-right: 8px;">🙏</span>Thank you for choosing {{app_name}}. Your feedback helps us improve our services.
                </div>
            </div>
        </div>
        <div style="background: #f8f9fa; padding: 15px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #e9ecef;">
            If you have any questions about this resolution, please create a new ticket or contact our support team.
        </div>
    </div>
</div>'
WHERE template_key = 'ticket_closed';