<?php

return [
    'create' => [
        // PAGE
        'title' => 'Tạo Landing Page',
        'step1' => 'Sự kiện & slug',
        'step2' => 'Template & Ngôn ngữ',
        'step3' => 'Hình ảnh',
        'step4' => 'Credit',
        'slug_label' => 'Tên (slug)',
        'slug_placeholder' => 'su-kien-',
        'slug_help' => '“Slug là phần đuôi của đường dẫn URL, chỉ gồm chữ thường, số và dấu gạch ngang. Ví dụ: <code> su-kien-2025 </code> sẽ tạo link: <code>https://register/su-kien-2025</code>.',
        'event_label' => 'Sự kiện',

        // STEP 2
        'language_header' => 'Ngôn ngữ:',
        'language_error' => 'Vui lòng chọn ít nhất một ngôn ngữ.',
        'template_header' => 'Template:',

        // STEP 3
        'media_header' => 'Hình ảnh:',
        'copy_button' => 'Sao chép',
        'download_button' => 'Tải xuống',

        // STEP 4
        'credit_header' => 'Credit:',
        'contact_name_label' => 'Tên đại diện',
        'contact_phone_label' => 'Số điện thoại',
        'contact_email_label' => 'Email',
        'contact_address_label' => 'Địa chỉ',
        'contact_name_placeholder' => 'Họ tên',
        'contact_phone_placeholder' => 'Số điện thoại',
        'contact_email_placeholder' => 'Email',
        'contact_address_placeholder' => 'Địa chỉ',
    ],
    'edit' => [
        // PAGE
        'title' => 'Chi tiết',
        'create_new' => 'Tạo mới',

        'event_label' => 'Sự kiện:',
        'event_none' => 'Chưa có sự kiện',

        // FORM
        'slug_label' => 'Tên (slug)',
        'slug_placeholder' => 'slug',

        'link_label' => 'Link:',
        'copy_link' => 'Copy',
        'view' => 'Xem',
        'scan_qr' => 'Quét QR',
        'qr_title' => 'QR Code',
        'qr_instructions' => 'Quét mã trên điện thoại để truy cập landing page:',

        'template_label' => 'Template:',
        'language_label' => 'Ngôn ngữ',

        // MAIL
        'send_mail' => 'Gửi mail',
        'no_campaign' => 'Bạn chưa setup campaign gửi mail cho sự kiện này',
        'create_campaign' => 'Tạo campaign',

        // CARD
        'card_section' => 'Thiệp/Thiệp',
        'no_card' => 'Chưa có mẫu thiệp/thiệp',
        'create_card' => 'Tạo thiệp',

        // SETTINGS
        'settings_header' => 'Cài đặt',
        'form_not_open' => 'Bạn chưa mở form',

        // CREDIT
        'contact_name_label' => 'Tên đại diện',
        'contact_name_placeholder' => 'Họ tên',
        'contact_phone_label' => 'Số điện thoại',
        'contact_phone_placeholder' => 'Số điện thoại',
        'contact_email_label' => 'Email',
        'contact_email_placeholder' => 'Email',
        'contact_address_label' => 'Địa chỉ',
        'contact_address_placeholder' => 'Địa chỉ',
    ],
    'manager' => [
        // PAGE
        'title' => 'Quản lý Landing page(s)',
        'add_new' => 'Thêm mới',

        // LIST
        'list_title' => 'Danh sách Landing page(s)',
        'list_description' => 'Xem, chọn và chỉnh sửa thông tin các trang đăng ký tại đây.',

        // MODAL
        'select_event_title' => 'Chọn sự kiện',
        'select_button' => 'Chọn',
    ],
];
