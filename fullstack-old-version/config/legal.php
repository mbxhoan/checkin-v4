<?php

return [
    'owner' => [
        'company_name' => env('LEGAL_OWNER_COMPANY_NAME', 'CÔNG TY CỔ PHẦN DELFI TECHNOLOGIES'),
        'tax_code' => env('LEGAL_OWNER_TAX_CODE', ''),
        'address' => env('LEGAL_OWNER_ADDRESS', 'A4-E23 Trường Sơn, Phường 2, Quận Tân Bình, TP.HCM'),
        'hotline' => env('LEGAL_OWNER_HOTLINE', '0973382111 - 0903855990'),
    ],

    'pages' => [
        'privacy' => [
            'title_key' => 'legal.pages.privacy.title',
            'description_key' => 'legal.pages.privacy.description',
            'content_paths' => [
                'vi' => 'content/privacy-policy.html',
                'en' => 'content/privacy-policy.en.html',
            ],
        ],
        'payment_refund' => [
            'title_key' => 'legal.pages.payment_refund.title',
            'description_key' => 'legal.pages.payment_refund.description',
            'content_paths' => [
                'vi' => 'content/payment-refund-policy.html',
                'en' => 'content/payment-refund-policy.en.html',
            ],
        ],
    ],
];
