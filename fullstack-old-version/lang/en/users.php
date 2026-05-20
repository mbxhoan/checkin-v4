<?php

return [
    'profile' => 'My profile',
    'public_profile' => 'My public profile',
    'settings' => 'Settings',
    'edit' => 'Edit profile',
    'show' => 'Show profile',
    'updated' => 'Profile updated',
    'password_updated' => 'Password updated',
    'new_users' => 'new user|new users',
    'count' => ':count user|:count users',
    'security' => 'Security',

    'attributes' => [
        'name' => 'Name',
        'email' => 'Email',
        'current_password' => 'Current password',
        'password' => 'Password',
        'password_confirmation' => 'Password confirmation',
        'roles' => 'Roles',
        'registered_at' => 'Registered at',
    ],

    'placeholder' => [
        'name' => 'Your name',
        'email' => 'Your email',
        'current_password' => 'Your current password',
        'password' => 'Your new password',
        'password_confirmation' => 'Password confirmation'
    ],

    'index' => [
        // PAGE
        'page_heading'          => 'User list',
        // ACTION
        'action_create'         => 'Create new',
        'filter_button'         => 'Filter',
        'filter_title'          => 'Filter',
        'filter_submit'         => 'Apply',
        // TABLE
        'card_title'            => 'Users',
        // MESSAGE
        'card_description'      => 'View, select, and edit user information here.',
    ],

    'edit' => [
        // PAGE
        'page_heading'              => 'Details',
        'breadcrumb_label'          => 'Account',
        // ACTION
        'action_create'             => 'Create new',
        // PAGE
        'section_info_heading'      => 'Information',
        'section_password_heading'  => 'Password',
        // FORM
        'label_company'             => 'Company',
        'label_event'               => 'Event',
        'label_package'             => 'Current package',
        'label_expire_date'         => 'Expiration date',
        'label_gate'                => 'Gate/Booth',
        // MESSAGE
        'placeholder_gate'          => 'Zone A...',
    ],

    'create' => [
        // PAGE
        'page_heading'              => 'Account',
        // FORM
        'step_role'                 => 'Account type',
        'step_info'                 => 'Information',
        'toggle_checkout'           => 'Checkout account',
        'label_company'             => 'Company',
        'label_event'               => 'Event',
        'section_info_heading'      => 'Information',
        'section_password_heading'  => 'Password',
        'label_package'             => 'Current package',
        'label_expire_date'         => 'Expiration date',
        'label_gate'                => 'Gate/Booth',
        'placeholder_gate'          => 'Zone A...',
        // ACTION
        'action_next'               => 'Continue',
        'action_back'               => 'Back',
        'action_save'               => 'Save',
    ],

    '_list' => [
        // TABLE
        'th_index'                  => '#',
        'th_package'                => 'Package',
        'th_company'                => 'Company',
        // ACTION
        'action_signout_title'      => 'Sign out account',
        'action_signout'            => 'Sign out',
        'action_activate_title'     => 'Activate and send email',
        'action_activate'           => 'Activate',
        // MESSAGE
        'confirm_activate'          => 'Activate the account and send confirmation email for this user?',
    ],

    '_form' => [
        // FORM
        'label_company'             => 'Company',
        'label_event'               => 'Event',
        'label_expire_date'         => 'Expiration date',
        'label_gate'                => 'Gate/Booth',
        'placeholder_gate'          => 'VIP,...',
    ],

    '_modal-filter' => [
        // FORM
        'label_company'             => 'Company',
        'label_event'               => 'Event',
    ],
];
