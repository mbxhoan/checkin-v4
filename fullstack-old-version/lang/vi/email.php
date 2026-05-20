<?php
return [
    'create' => [
        // PAGE
        'title' => 'Nội dung mail',

        // FIELDS
        'name_label' => 'Tên',
        'name_placeholder' => 'Tên',
        'subject_label' => 'Tiêu đề',
        'subject_placeholder' => 'Tiêu đề',
    ],
     'toolbar' => [

        // ===== QUICK INSERT =====
        'quick_insert' => 'Chèn nhanh',
        'qrcode_image' => 'Ảnh Qrcode',
        'qrcode_text' => 'Chuỗi Qrcode',
        'event_information' => 'Thông tin sự kiện',
        'download_qrcode_button' => 'Nút tải QR code',
        'download_invitation_button' => 'Nút tải thiệp mời',

        // ===== EVENT VARIABLE =====
        'event_variables' => 'Biến dữ liệu',
        'select_event_variable' => 'Chọn biến sự kiện',
        'no_event_variable' => 'Không có biến cho sự kiện',
        'choose_or_input_field' => 'Vui lòng chọn hoặc nhập trường thông tin.',
        'insert_variable' => 'biến',
        'drag_drop_variable' => 'Biến kéo thả',
        'drag_drop_hint' => 'Bấm để chèn hoặc kéo thả vào editor.',

        // ===== IMAGE TOOL =====
        'selected_image' => 'Ảnh đang chọn',
        'check_email_compatibility' => 'Kiểm tra tương thích email',

        // ===== EMAIL NOTE =====
        'email_tip' => 'Mẹo: email client không giống website, hãy kiểm tra trước khi gửi thật.',
        'compatibility_result' => 'Kết quả kiểm tra',

        // ===== EMAIL RULE =====
        'email_display_rules' => 'Ràng buộc hiển thị email cần lưu ý',
        'rule_no_script' => 'Không dùng JavaScript, iframe, video embed hoặc form tương tác.',
        'rule_limit_css' => 'Hạn chế CSS nâng cao (position fixed, animation phức tạp, external CSS).',
        'rule_layout_width' => 'Nên thiết kế theo khung rộng khoảng 600px, dùng inline style.',
        'rule_image_https' => 'Ảnh nên là URL công khai HTTPS và nên khai báo width rõ ràng.',
    ],
];
