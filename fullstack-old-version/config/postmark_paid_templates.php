<?php

return [
    'notify_to' => env('POSTMARK_PAID_TEMPLATE_NOTIFY_TO', 'admin@delfi.vn'),

    'contact_url' => env('POSTMARK_PAID_TEMPLATE_CONTACT_URL', 'mailto:sales@delfi.vn?subject=Yeu%20cau%20mo%20khoa%20template%20{template_id}&body=Toi%20muon%20mo%20khoa%20template%20{template_id}%20({template_name}).%0ATai%20khoan:%20{user_email}%0ACong%20ty:%20{company_name}'),

    'templates' => [
        43167507 => [
            'is_paid_locked' => true,
            'event_name' => 'Year-End Functional Conference ABinbev',
            'event_time' => '3/2/2026',
            'credit' => 'Year-End Functional Conference ABinbev - 3/2/2026',
        ],
        41723551 => [
            'is_paid_locked' => true,
            'event_name' => 'Tiệc kỷ niệm 10 năm Eurofins Sắc Ký Hải Đăng',
            'event_time' => '10/10/2025',
            'credit' => 'Tiệc kỷ niệm 10 năm Eurofins Sắc Ký Hải Đăng - 10/10/2025',
        ],
        41461290 => [
            'is_paid_locked' => true,
            'event_name' => 'HDBank Hackathon 2025',
            'event_time' => '25/09/2025',
            'credit' => 'HDBank Hackathon 2025 - 25/09/2025',
        ],
        41853263 => [
            'is_paid_locked' => true,
            'event_name' => 'HỘI NGHỊ KHOA HỌC BỆNH PHỔI TOÀN QUỐC LẦN THỨ 11',
            'event_time' => '07-09/11/2025',
            'credit' => 'HỘI NGHỊ KHOA HỌC BỆNH PHỔI TOÀN QUỐC LẦN THỨ 11 - 07-09/11/2025',
        ],
    ],
];
