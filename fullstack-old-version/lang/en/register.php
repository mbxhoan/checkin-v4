<?php

return [
    'hero' => [
        'title' => 'Build your professional check-in system',
        'subtitle' => 'Fill in your company information to activate a trial package and set up events faster.',
    ],

    'steps' => [
        'company_info' => 'Company information',
        'package_terms' => 'Service package & terms',
    ],

    'fields' => [
        'company_name' => 'Company',
        'position' => 'Position',
        'company_type' => 'Event type',
    ],

    'hints' => [
        'company_email' => 'Please use your company email',
    ],

    'captions' => [
        'select_package' => '* Please choose a package for reference:',
        'select_devices' => '* Rental devices:',
    ],

    'package' => [
        'detail' => 'Details',
        'more_information' => 'More information about :name',
    ],

    'plans' => [
        'title' => 'Service plans for your business',
        'subtitle' => 'Pick the plan that fits your current operations. You can upgrade or customize after consultation.',
        'popular' => 'Popular',
        'contact_badge' => 'Demo / Advanced',
        'contact_cta' => 'Contact us',
        'contact_note' => 'For teams that need a demo trial or advanced customization beyond standard plans.',
        'contact_subject' => 'Consultation request for Demo/Advanced plan from registration page',
        'select' => 'Choose this plan',
        'selected' => 'Selected',
        'metrics' => [
            'clients' => 'Data limit',
            'emails' => 'Email quota',
            'events' => 'Event limit',
            'users' => 'User limit',
        ],
        'unlimited' => 'Unlimited',
        'names' => [
            'basic' => 'Starter (Basic)',
            'pro' => 'Professional (Pro)',
            'vip' => 'Contact (Enterprise / Demo)',
        ],
        'descriptions' => [
            'basic' => 'A solid fit for small teams that want quick rollout and simple operations.',
            'pro' => 'Best for businesses that need broader campaigns and stronger automation.',
            'vip' => 'Flexible option for demos, custom integrations, or enterprise workflows.',
        ],
        'features' => [
            'basic' => [
                'Quick event setup and QR check-in',
                'Real-time dashboard overview',
                'Basic role-based user management',
                'Fast configuration for standard events',
            ],
            'pro' => [
                'Everything in the Basic plan',
                'Landing page and campaign email capabilities',
                'Online print templates, invitations, and advanced data handling',
                'Better support for larger attendee volumes',
            ],
            'vip' => [
                'Direct demo consultation for real scenarios',
                'Tailored solution design for your internal process',
                'Advanced integration and custom configuration',
                'Suitable for specialized and multi-layer deployments',
            ],
        ],
    ],

    'actions' => [
        'next' => 'Continue',
        'previous' => 'Back',
    ],

    'comparison' => [
        'title' => 'Quick package comparison',
        'subtitle' => 'Click "Details" to review features and pricing that fit your business.',
        'empty_title' => 'No package details opened yet',
        'empty_subtitle' => 'Click "Details" on a package to show comparison content.',
    ],

    'terms' => [
        'required_label' => 'Required note:',
        'notice' => 'Required: please open and read the terms of use before agreeing.',
        'helper' => 'You can only tick this after pressing "Agree" at the end of the terms.',
        'agreement_prefix' => 'I have read and agree to',
        'agreement_link' => 'the terms of use and image usage consent',
        'agreement_suffix' => 'for users.',
        'modal_title' => 'Terms of use',
        'modal_scroll_hint' => 'Please scroll to the end of the terms to enable the Agree button.',
        'modal_agree' => 'Agree',
        'content_fallback' => '<p class="p1"><b>TERMS OF USE FOR CHECK-IN SOFTWARE SERVICE</b></p><p class="p3">Terms content is being updated.</p>',
        'accept_page_scroll_hint' => 'Please scroll to the end of the terms to enable confirmation.',
        'accept_page_checkbox' => 'I agree to the terms of use and consent to image usage as stated above.',
        'accept_page_submit' => 'Agree and continue',
        'accept_page_aria' => 'Terms of use',
    ],

    'public_terms' => [
        'register' => 'Register',
    ],

    'validation' => [
        'password_regex' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 symbol.',
        'password_not_regex' => 'Password must not contain Vietnamese characters.',
        'accept_terms_accepted' => 'You must accept the terms of use and image consent to register.',
    ],
];
