<?php

return [
    'create' => [
        // PAGE
        'title' => 'Create Landing Page',
        'step1' => 'Event & slug',
        'step2' => 'Template & Language',
        'step3' => 'Images',
        'step4' => 'Credit',
        'slug_label' => 'Name (slug)',
        'slug_placeholder' => 'event-',
        'slug_help' => 'Slug is the tail of the URL path, containing only lowercase letters, numbers and hyphens. For example: <code>event-2025</code> will create link: <code>https://register/event-2025</code>.',
        'event_label' => 'Event',

        // STEP 2
        'language_header' => 'Language:',
        'language_error' => 'Please select at least one language.',
        'template_header' => 'Template:',

        // STEP 3
        'media_header' => 'Images:',
        'copy_button' => 'Copy',
        'download_button' => 'Download',

        // STEP 4
        'credit_header' => 'Credit:',
        'contact_name_label' => 'Representative name',
        'contact_phone_label' => 'Phone number',
        'contact_email_label' => 'Email',
        'contact_address_label' => 'Address',
        'contact_name_placeholder' => 'Full name',
        'contact_phone_placeholder' => 'Phone number',
        'contact_email_placeholder' => 'Email',
        'contact_address_placeholder' => 'Address',
    ],
    'edit' => [
        // PAGE
        'title' => 'Details',
        'create_new' => 'Create new',

        'event_label' => 'Event:',
        'event_none' => 'No event yet',

        // FORM
        'slug_label' => 'Name (slug)',
        'slug_placeholder' => 'slug',

        'link_label' => 'Link:',
        'copy_link' => 'Copy',
        'view' => 'View',
        'scan_qr' => 'Scan QR',
        'qr_title' => 'QR Code',
        'qr_instructions' => 'Scan the code on your phone to access the landing page:',

        'template_label' => 'Template:',
        'language_label' => 'Language',

        // MAIL
        'send_mail' => 'Send mail',
        'no_campaign' => 'You have not set up a mail campaign for this event',
        'create_campaign' => 'Create campaign',

        // CARD
        'card_section' => 'Card/Card',
        'no_card' => 'No card template',
        'create_card' => 'Create card',

        // SETTINGS
        'settings_header' => 'Settings',
        'form_not_open' => 'Form is not open',

        // CREDIT
        'contact_name_label' => 'Representative name',
        'contact_name_placeholder' => 'Full name',
        'contact_phone_label' => 'Phone number',
        'contact_phone_placeholder' => 'Phone number',
        'contact_email_label' => 'Email',
        'contact_email_placeholder' => 'Email',
        'contact_address_label' => 'Address',
        'contact_address_placeholder' => 'Address',
    ],
    'manager' => [
        // PAGE
        'title' => 'Landing Pages',
        'add_new' => 'Add new',

        // LIST
        'list_title' => 'Landing Pages List',
        'list_description' => 'View, select and edit information about registration pages here.',

        // MODAL
        'select_event_title' => 'Select event',
        'select_button' => 'Select',
    ],
];
