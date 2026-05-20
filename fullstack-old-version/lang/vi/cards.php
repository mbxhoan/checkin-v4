<?php

return [
    'index' => [
        // PAGE
        'page_title' => 'Thiệp/Thiệp',
        'page_heading' => 'Danh sách thiệp',
        // ACTION
        'action_create' => 'Thêm mới',
    ],

    'detail' => [
        // PAGE
        'page_heading' => 'Chi tiết',
        'breadcrumb_label' => 'Mẫu thiệp',
        'preview_heading' => 'Xem trước thiệp / thiệp',
        'preview_hint' => 'Kéo thả vị trí, chỉnh màu / font để thấy ngay',
        'quick_guide_heading' => 'Hướng dẫn nhanh:',
        // ACTION
        'action_create' => 'Thêm mới',
        // FORM
        'label_id' => 'ID',
        'label_client_type' => 'Nhóm khách',
        'option_all' => '- Tất cả -',
        'label_background' => 'Background',
        'label_extension' => 'Định dạng',
        'label_file_name' => 'Tên file',
        'placeholder_file_name' => '<qrcode>',
        // MESSAGE
        'example_heading' => 'Ví dụ:',
        'note_heading' => 'Lưu ý:',
        'note_file_name_rule' => 'Tên file có nên đặt theo trường thông tin không có khả năng bị trùng, ví dụ danh sách có cùng thông tin công ty thì không nên dùng trường công ty làm tên file',
        'guide_drag_drop_fields' => 'Kéo thả ô chữ / QR đến vị trí mong muốn (tọa độ đang tính theo % chiều rộng/chiều cao).',
        'guide_font_size_note' => 'Cỡ chữ = font size (% chiều cao nền) ⇒ chữ xuất file sẽ đúng như preview.',
        'guide_qr_image_note' => 'Ảnh QR: đặt width/height (px), canh lề theo lựa chọn trái / giữa / phải.',
        'guide_toggle_visibility' => 'Ẩn/hiện trường: gạt “Hiển thị”; nút “Làm mới xem trước” dùng khi vừa thêm trường mới.',
        // TABLE
        'stat_count_label' => 'Số lượng:',
        'list_title' => 'Danh sách Khách tham dự theo loại',
        'list_description' => 'Xem mẫu in cá nhân khách tham dự tại đây.',
        // ACTION
        'action_refresh_preview' => 'Làm mới xem trước',
        'action_generate_bulk' => 'Tạo thiệp/thiệp hàng loạt',
        'action_download_images' => 'Tải tệp thiệp/thiệp',
        // MESSAGE
        'confirm_generate_title' => 'Bạn có chắc chắn tạo thiệp/thiệp cho :count khách này? Tiến trình sẽ bắt đầu và chạy mất một lúc, bạn vui lòng đợi nhé...',
        'confirm_generate_label' => 'VUI LÒNG NHẬP "OK" ĐỂ XÁC NHẬN GỬI',
    ],

    'create' => [
        // PAGE
        'page_heading' => 'Tạo thiệp',
        // FORM
        'step_info' => 'Thông tin',
        'step_images' => 'Hình ảnh',
        'label_id' => 'ID',
        'placeholder_code' => 'Mẫu thiệp mời tháng 10',
        'label_event' => 'Sự kiện',
        'label_client_type' => 'Nhóm khách',
        'option_all' => '- Tất cả -',
        'label_background' => 'Background',
        'label_output_extension' => 'Định dạng đầu ra',
        'label_file_name' => 'Tên file',
        'placeholder_file_name' => '<qrcode>',
        // MESSAGE
        'example_heading' => 'Ví dụ:',
        'note_heading' => 'Lưu ý:',
        'note_file_name_rule' => 'Tên file có nên đặt theo trường thông tin không có khả năng bị trùng, ví dụ danh sách có cùng thông tin công ty thì không nên dùng trường công ty làm tên file',
        // ACTION
        'action_next' => 'Tiếp tục',
        'action_back' => 'Quay lại',
        'action_save' => 'Lưu',
        // PAGE
        'fields_display_heading' => 'Hiển thị trường thông tin:',
        'action_fullscreen' => 'Xem toàn màn hình',
    ],

    '_shortlist' => [
        // TABLE
        'th_index' => '#',
        'th_card_name' => 'Tên thiệp mời',
        'th_actions' => 'Hành động',
        // ACTION
        'action_edit_title' => 'Sửa',
        'action_delete_title' => 'Xóa',
        'action_view_report' => 'Xem báo cáo',
        // MESSAGE
        'confirm_delete_title' => 'Bạn có chắc chắn?',
        'confirm_delete_text' => 'Hành động này không thể hoàn tác!',
        'confirm_delete_confirm' => 'Xóa',
        'confirm_delete_cancel' => 'Hủy',
        'delete_success_title' => 'Đã xóa!',
        'delete_success_text' => 'card đã được xóa.',
        'delete_error_title' => 'Lỗi!',
        'delete_error_text' => 'Không thể xóa. Vui lòng thử lại.',
    ],

    '_list' => [
        // TABLE
        'th_index' => '#',
        'th_info' => 'Thông tin',
        // MESSAGE
        'empty_text' => 'Chưa có',
        // ACTION
        'action_create' => 'Thêm mới',
    ],

    '_background' => [
        // PAGE
        'sample_name' => 'Nguyễn Văn A',
        'sample_email' => 'email@example.com',
        'sample_phone' => '0909 000 000',
        'sample_company' => 'CÔNG TY ABC',
        'sample_position' => 'Giám đốc',
        'sample_event_name' => 'Tên sự kiện',
        'sample_code' => 'ABC123',
        'sample_table' => 'Bàn VIP 1',
    ],

    '_form' => [
        // FORM
        'label_other_card' => 'Thư/Thiệp mời khác: ',
        'section_info_heading' => '1. Thông tin:',
        'label_code' => 'Thông tin',
        'label_client_type' => 'Nhóm khách',
        'option_all' => '- Tất cả -',
        'section_output_heading' => '2. Background & Output:',
        'label_background' => 'Background',
        'label_file_name' => 'Tên file',
        'label_extension' => 'Định dạng',
        'placeholder_file_name' => '<qrcode>',
    ],

    '_aim' => [
        // PAGE
        'page_title' => 'Chỉnh sửa vị trí',
        'page_heading' => 'Chỉnh sửa vị trí',
        // ACTION
        'action_show_config' => 'Hiển thị thông tin/Chỉnh sửa vị trí',
        // FORM
        'option_right' => 'Phải',
        'option_left' => 'Trái',
        'option_top' => 'Trên',
        'option_bottom' => 'Dưới',
        // MESSAGE
        'loading_text' => 'Loading',
        'stat_count_label' => 'Số lượng:',
        // ACTION
        'action_download_images' => 'Tải tệp thiệp/thiệp',
    ],

    'card_detail' => [
        // TABLE
        'th_type' => 'Loại dữ liệu',
        'th_field' => 'Trường thông tin',
        // FORM
        'placeholder_name' => 'Tên',
        'label_is_show' => 'Hiển thị',
        'label_font' => 'Font chữ',
        'label_font_size' => 'Cỡ chữ',
        'label_color_text' => 'Màu chữ',
        'label_color' => 'Màu',
        'label_width' => 'Chiều rộng',
        'label_height' => 'Chiều cao',
        'label_align' => 'Canh',
        'label_pos_x' => 'Canh ngang',
        'label_pos_y' => 'Canh dọc',
        // MESSAGE
        'tooltip_font_use' => 'Chọn font sẽ dùng khi xuất file',
        'tooltip_font_size_unit' => 'Đơn vị: % chiều cao nền. 50 = 50% chiều cao hình.',
        'tooltip_apply_preview' => 'Áp dụng ngay trên preview và ảnh xuất.',
        'tooltip_px_unit' => 'Đơn vị px trên ảnh xuất.',
        'tooltip_align_hint' => 'Trái / Giữa / Phải dựa trên vị trí đang chọn',
    ],

    'custom_field_templates' => [
        // TABLE
        'th_field' => 'Trường',
        'th_description' => 'Mô tả',
        // FORM
        'placeholder_name' => 'Tên',
        'placeholder_description' => 'Mô tả',
        'label_is_show' => 'Hiển thị',
        'label_bold' => 'Đậm',
        'label_italic' => 'Nghiêng',
        'label_bg' => 'Nền',
        'label_bg_color' => 'Màu nền',
        'label_color' => 'Màu chữ',
        'label_font_size' => 'Cỡ chữ',
        'label_font' => 'Font chữ',
        'label_align' => 'Canh',
        'label_width' => 'Độ rộng',
        'label_pos_x' => 'Canh ngang',
        'label_pos_y' => 'Canh dọc',
    ],
];
