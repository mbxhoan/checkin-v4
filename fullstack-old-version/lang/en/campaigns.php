<?php

return [
    'create' => [
        // PAGE
        'title' => 'Create email campaign',

        // STEPS
        'step_information' => 'Information',
        'step_content' => 'Email content',

        // FIELDS
        'id_label' => 'ID',
        'event_label' => 'Event',
        'from_email_label' => 'From email',
        'guest_group_label' => 'Guest group',
        'all_option' => '- All -',
        'cc_label' => 'cc',
        'bcc_label' => 'bcc',

        // BUTTONS
        'continue' => 'Continue',
        'back' => 'Back',
        'save' => 'Save',

        // MODAL
        'preview_title' => 'Preview',
    ],
    'messages' => [
        'created_success' => 'Email campaign created successfully',
        'created_failed' => 'Failed to create campaign',
        'updated_success' => 'Campaign updated successfully',
        'updated_failed' => 'Failed to update campaign',
        'deleted_success' => 'Campaign :name has been deleted',
        'cloned_success' => 'Campaign :name has been cloned',
        'cloned_partial' => 'Campaign :name has been cloned :detail',
    ],

    'sync' => [
        'no_clients' => 'No clients found',
        'update_detail_failed' => 'Failed to update Campaign Detail :qrcode',
        'synced_clients' => 'Synced :count clients',
        'synced_templates' => 'Synced :count email templates from Postmark',
        'sync_templates' => 'Sync email templates',
        'synced_senders' => 'Synced :count sender emails from Postmark',
        'sync_senders' => 'Sync sender emails',
    ],

    'manage' => [
        // PAGE
        'page_title' => 'Campaigns List',
        'title' => 'Campaigns List',

        // STATS
        'stats_campaigns' => 'Campaign(s):',
        'stats_sent' => 'Sent',
        'stats_unlimited' => 'Unlimited',

        // BUTTONS
        'add_new' => 'Add new',

        // LIST
        'list_title' => 'Campaign(s) List',
        'list_description' => 'View, select and edit information about email campaigns here.',
    ],
    'email_template' => [
        // PAGE
        'title' => 'Email content',
        'add_new_tooltip' => 'Add email content',

        // SYNC
        'syncing' => 'Syncing...',

        // CLONE MODAL
        'clone_title' => 'Confirm Clone',
        'clone_question' => 'Are you sure you want to clone this email content?',
        'new_name_label' => 'New email content name',
        'clone_input_label' => 'PLEASE TYPE <b>"COPY"</b> TO CONFIRM CLONE',
        'confirm_button' => 'Confirm',

        // ACTIONS
        'edit_title' => 'Edit',
        'synced' => 'Synced',
        'sync_error' => 'Unable to sync',
    ],
    'queue' => [
        'setup_failed_admin' => 'An error occurred while setting up emails: :error',
        'setup_failed_user' => 'An error occurred while setting up emails. Please try again later.',
        'default_success' => 'Emails are being queued.',
        'default_failed' => 'An error occurred during setup',
        'no_guest_list' => 'No guest list available in this campaign.',
        'no_valid_email' => 'No valid emails to queue.',
        'limit_exceeded' => 'Email sending limit has been exceeded.',
        'queued_success' => ':count emails have been queued.',
        'scheduled_success' => 'Scheduled :count emails at :time.',
        'schedule_label' => 'Schedule send time (optional)',
        'scheduled_note' => 'Scheduled for: :time',
        'stopped' => 'Process stopped',
    ],

    'status' => [
        'invalid' => 'Invalid status',
        'update_success' => 'Status updated successfully',
        'update_failed' => 'Unable to update status',
        'send_failed' => 'Unable to send email.',
    ],

    'validation' => [
        'invalid_email_list' => 'Invalid :attribute: :emails',
        'scheduled_at_not_past' => 'Scheduled send time cannot be in the past.',
        'attributes' => [
            'event_id' => 'Event',
            'template_id' => 'Email template',
            'name' => 'Campaign name',
            'type' => 'Audience group',
            'subject' => 'Subject',
            'from_email' => 'Sender email',
            'from_name' => 'Sender name',
            'cc' => 'CC',
            'bcc' => 'BCC',
            'message_stream' => 'Message stream',
            'limitation_per_time' => 'Batch send limit',
            'hold_time' => 'Hold time',
            'scheduled_at' => 'Scheduled send time',
            'fixed_attachments' => 'Fixed attachments',
        ],
    ],
];
