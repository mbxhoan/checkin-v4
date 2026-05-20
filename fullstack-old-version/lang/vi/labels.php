<?php

return [
    // =======================
    // INDEX PAGE
    // =======================
    'index' => [
        // PAGE
        'page' => [
            'title' => 'Mẫu in',
        ],

        // ACTION
        'action' => [
            'create_button' => 'Thêm mới',
        ],

        // TABLE
        'table' => [
            'title' => 'Danh sách mẫu in',
            'description' => 'Xem, chọn và chỉnh sửa mẫu in tại đây.',
        ],
    ],

    // =======================
    // DETAIL PAGE
    // =======================
    'detail' => [
        // PAGE
        'page' => [
            'title' => 'Chi tiết',
            'breadcrumb1' => 'Mẫu in',
        ],

        // FORM
        'form' => [
            'default_label_switch' => 'Mẫu in mặc định',
            'name_label' => 'Tên',
            'name_placeholder' => 'Tên mẫu in',
            'type_label' => 'Nhóm khách',
            'type_all_option' => '- Tất cả -',
            'width_label' => 'Chiều dài',
            'width_placeholder' => '10',
            'height_label' => 'Chiều cao',
            'height_placeholder' => '10',
            'unit_label' => 'Đơn vị',
            'unit_placeholder' => 'cm',
            'clone_modal_title' => 'Nhân bản mẫu in',
            'clone_modal_event_label' => 'Sự kiện',
            'clone_modal_new_label_info' => 'Thông tin mẫu in mới',
        ],

        // TABLE
        'table' => [
            'attendee_by_type_title' => 'Danh sách Khách tham dự theo loại',
            'attendee_by_type_description' => 'Xem mẫu in cá nhân khách tham dự tại đây.',
            'quantity_label' => 'Số lượng:',
        ],

        // ACTION
        'action' => [
            'create_button' => 'Thêm mới',
            'multi_print_button' => 'In toàn bộ',
            'single_print_button' => 'In thử',
            'clone_confirm_button' => 'Xác nhận nhân bản',
        ],

        // MESSAGE
        'message' => [
            // (reserved for future messages)
        ],
    ],

    // =======================
    // CREATE PAGE
    // =======================
    'create' => [
        // PAGE
        'page' => [
            'title' => 'Tạo mẫu in',
        ],

        // FORM
        'form' => [
            'step_info_label' => 'Thông tin',
            'step_size_label' => 'Kích cỡ',
            'name_label' => 'Tên',
            'name_placeholder' => 'Mẫu in 8x6',
            'event_label' => 'Sự kiện',
            'type_label' => 'Nhóm khách',
            'type_all_option' => '- Tất cả -',
            'width_label' => 'Chiều dài',
            'width_placeholder' => '10',
            'height_label' => 'Chiều cao',
            'height_placeholder' => '10',
            'unit_label' => 'Đơn vị',
            'unit_placeholder' => 'cm',
            'sample_7x3_label' => 'Mẫu 7 x 3',
            'sample_8x6_label' => 'Mẫu 8 x 6',
            'sample_6x4_label' => 'Mẫu 6 x 4',
            'print_form_label' => 'Form in:',
            'clone_modal_title' => 'Nhân bản mẫu in',
            'clone_modal_event_label' => 'Sự kiện',
            'clone_modal_new_label_info' => 'Thông tin mẫu in mới',
        ],

        // ACTION
        'action' => [
            'next_button' => 'Tiếp tục',
            'prev_button' => 'Quay lại',
            'submit_button' => 'Lưu',
            'clone_confirm_button' => 'Xác nhận nhân bản',
        ],

        // MESSAGE
        'message' => [
            // (reserved)
        ],
    ],

    // =======================
    // LABEL DETAIL COMPONENT
    // =======================
    'label_detail' => [
        // PAGE
        'page' => [
            'add_component_title' => 'Thêm mới thành phần:',
            'field_config_title' => 'Cấu hình trường thông tin:',
        ],

        // TABLE
        'table' => [
            'type_column' => 'Loại',
            'field_column' => 'Trường thông tin',
        ],

        // FORM
        'form' => [
            'field_placeholder' => 'Tên',
            'show_label' => 'Hiển thị',
            'color_label' => 'Màu chữ',
            'bold_label' => 'In đậm',
            'italic_label' => 'In nghiêng',
            'uppercase_label' => 'IN HOA',
            'font_label' => 'Font chữ',
            'size_label' => 'Cỡ chữ (%)',
            'width_label' => 'Chiều rộng',
            'height_label' => 'Chiều cao',
            'h_align_label' => 'Canh lề',
            'pos_x_label' => 'Canh ngang',
            'pos_y_label' => 'Canh dọc',
        ],

        // ACTION
        'action' => [
            'save_component_button' => 'Lưu thành phần',
        ],

        // MESSAGE
        'message' => [
            // (reserved for validation / helper text if needed)
        ],
    ],
];
