<?php
return [
    'create' => [
        // PAGE
        'title' => 'Email content',

        // FIELDS
        'name_label' => 'Name',
        'name_placeholder' => 'Name',
        'subject_label' => 'Subject',
        'subject_placeholder' => 'Subject',
    ],
    'toolbar' => [

        // ===== QUICK INSERT =====
        'quick_insert' => 'Quick Insert',
        'qrcode_image' => 'QR Code Image',
        'qrcode_text' => 'QR Code Text',
        'event_information' => 'Event Information',
        'download_qrcode_button' => 'Download QR Code Button',
        'download_invitation_button' => 'Download Invitation Button',

        // ===== EVENT VARIABLE =====
        'event_variables' => 'Data Variables',
        'select_event_variable' => 'Select event variable',
        'no_event_variable' => 'No variables available for this event',
        'choose_or_input_field' => 'Please select or enter a variable field.',
        'insert_variable' => 'variable',
        'drag_drop_variable' => 'Drag & Drop Variable',
        'drag_drop_hint' => 'Click to insert or drag into the editor.',

        // ===== IMAGE TOOL =====
        'selected_image' => 'Selected Image',
        'check_email_compatibility' => 'Check Email Compatibility',

        // ===== EMAIL NOTE =====
        'email_tip' => 'Tip: Email clients behave differently from websites, please test before sending.',
        'compatibility_result' => 'Compatibility Result',

        // ===== EMAIL RULE =====
        'email_display_rules' => 'Email Display Restrictions',
        'rule_no_script' => 'Do not use JavaScript, iframe, embedded video or interactive forms.',
        'rule_limit_css' => 'Avoid advanced CSS such as fixed positioning or complex animations.',
        'rule_layout_width' => 'Recommended layout width is around 600px using inline styles.',
        'rule_image_https' => 'Images should use public HTTPS URLs and define width clearly.',
    ],
];
