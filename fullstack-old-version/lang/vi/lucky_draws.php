<?php
return [
    'index' => [
        // PAGE
        'page_title'        => 'Quay số Online',
        'page_heading'      => 'Quay số Online',
        // ACTION
        'action_create'     => 'Thêm mới',
    ],

    'detail' => [
        // PAGE
        'page_heading'              => 'Chi tiết',
        'breadcrumb_label'          => 'Mẫu quay số',
        // ACTION
        'action_builder'            => 'Builder',
        'action_display'            => 'Display',
        'action_create'             => 'Thêm mới',
        // FORM
        'label_name'                => 'Tên',
        'label_bg_mobile'           => 'Background Mobile',
        'label_bg_desktop'          => 'Background Desktop',
        // TABLE
        'data_list_heading'         => 'Danh sách tham dự:',
        'rewards_list_heading'      => 'Danh sách giải:',
        'winners_table_th_index'    => '#',
        'winners_table_th_qr_name'  => 'QR / Tên',
        'winners_table_th_reward'   => 'Giải trúng',
        'winners_table_th_actions'  => 'Thao tác',
        // ACTION
        'action_sync_clients'       => 'Đồng bộ danh sách khách mời',
        'label_client_type'         => 'Nhóm khách',
        'option_all'                => '- Tất cả -',
        'label_group'               => 'Lọc',
        'action_reset_assign'       => 'Làm mới',
        'action_reset_rewards'      => 'Reset',
        'action_download_template'  => 'Tải file mẫu',
        'modal_create_reward_title' => 'Thêm mới giải thưởng',
        'modal_create_reward_submit'=> 'Thêm mới',
        'modal_upload_rewards_title'=> 'Nạp danh sách giải thưởng',
        'modal_upload_rewards_submit'=> 'Nạp file',
        'action_export_winners'     => 'Xuất kết quả',
        'action_reset_winners'      => 'Reset danh sách trúng thưởng',
        'action_remove_reward_title'=> 'Bỏ giải',
        // MESSAGE
        'winners_empty'             => 'Chưa có người trúng thưởng.',
        'upload_img_label'          => 'Upload ảnh lấy link (dùng cho cột img_link trong file Excel danh sách giải thưởng)',
        'upload_button'             => 'Upload',
        'uploaded_list_label'       => 'Danh sách ảnh đã upload',
        'copy_link_title'           => 'Sao chép link',
        'paste_link_hint'           => 'Dán link này vào cột img_link trong file Excel khi nạp danh sách giải.',
        'error_upload'              => 'Lỗi upload',
        'error_connection'          => 'Lỗi kết nối',
        'toast_copied'              => 'Đã sao chép link',
        'reset_winners_confirm'     => 'Bạn có chắc muốn reset toàn bộ danh sách trúng thưởng? Mọi người sẽ được bỏ gán giải.',
        'remove_reward_confirm'     => 'Bỏ giải khỏi người này?',
    ],

    'create' => [
        // PAGE
        'page_heading'              => 'Tạo quay số',
        // FORM
        'step_info'                 => 'Thông tin',
        'label_name'                => 'Tên',
        'placeholder_name'         => 'Mẫu quay số tháng 12',
        'label_event'              => 'Sự kiện',
        'label_type'               => 'Loại vòng quay',
        // ACTION
        'action_save'               => 'Lưu',
    ],

    'display' => [
        // MESSAGE
        'confirm_button_title'      => 'Xác nhận người thắng và lưu kết quả',
    ],

    'display-wheel' => [
        // TABLE/PANEL
        'tab_entries'               => 'Danh sách',
        'tab_results'               => 'Kết quả',
        'label_entries_input'       => 'Nhập tên tham dự (mỗi dòng một tên)',
        'entries_placeholder'       => "Nguyễn Văn A\nTrần Thị B\n...",
        // ACTION
        'btn_update'                => 'Cập nhật',
        'btn_shuffle'               => 'Xáo trộn',
        'btn_remove_winner'         => 'Xóa thắng',
        // FORM
        'bg_title'                  => 'Đổi nền',
        'bg_link_placeholder'       => 'Dán link ảnh nền (URL)...',
        'btn_apply_bg'              => 'Áp dụng link',
        'btn_reset_bg'              => 'Dùng nền mặc định',
        'bg_upload_select_desktop'  => 'Màn hình máy tính',
        'bg_upload_select_mobile'   => 'Màn hình điện thoại',
        'btn_upload_bg'             => 'Upload',
        // MESSAGE
        'results_empty'             => 'Chưa có kết quả',
        'spin_hint'                 => 'Click vòng quay hoặc Ctrl+Enter',
        'winner_default'            => 'Chưa quay',
        'toggle_collapse_title'     => 'Thu gọn chỉ còn vòng quay',
        'toggle_collapse_label_collapse' => 'Thu gọn',
        'toggle_collapse_label_expand'   => 'Mở rộng',
        'toggle_bg_title'           => 'Đổi nền',
        'bg_drawer_title'           => 'Đổi nền màn hình',
        'bg_drawer_link_placeholder'=> 'Dán link ảnh (URL)...',
        'btn_drawer_apply'          => 'Áp dụng',
        'btn_drawer_reset'          => 'Mặc định',
        'drawer_upload_desktop'     => 'Nền máy tính',
        'drawer_upload_mobile'      => 'Nền điện thoại',
        'btn_drawer_upload'         => 'Upload ảnh',
        'results_count_prefix'      => 'Đã quay:',
        'input_prompt'              => 'Nhập danh sách bên trái',
        'status_applying_link'      => 'Đã áp dụng link nền.',
        'status_uploading'          => 'Đang tải lên...',
        'status_updated_bg'         => 'Đã cập nhật nền.',
        'status_upload_error'       => 'Lỗi upload',
        'status_connection_error'   => 'Lỗi kết nối',
    ],

    'builder' => [
        // PAGE
        'page_title_prefix'         => 'Thiết kế vòng quay -',
        // ACTION
        'action_back'               => 'Quay lại',
        'action_preview'            => 'Xem trước',
        'action_save'               => 'Lưu',
        // PANEL
        'panel_event_data'          => 'Dữ liệu sự kiện',
        'label_participant_filter'  => 'Lọc người tham gia',
        'filter_all'                => 'Tất cả',
        'filter_available'          => 'Còn tham gia',
        'filter_won'                => 'Đã trúng',
        'label_core_fields'         => 'Trường có sẵn',
        'label_custom_fields'       => 'Trường tùy chỉnh',
        'label_participant_sample'  => 'Mẫu người tham gia',
        'panel_rewards'             => 'Quản lý giải thưởng',
        'badge_given'               => 'Đã trao',
        'badge_waiting'             => 'Chờ',
        'btn_layout'                => 'Bố cục',
        'btn_auto'                  => 'Tự động',
        'btn_winners'               => 'Người thắng',
        'properties_title'          => 'Thuộc tính khối',
        'label_x'                   => 'X',
        'label_y'                   => 'Y',
        'label_width'               => 'Rộng',
        'label_height'              => 'Cao',
        'label_source_field'        => 'Trường nguồn',
        'label_font_size'           => 'Cỡ chữ',
        'label_font_color'          => 'Màu chữ',
        'label_align'               => 'Căn lề',
        'align_left'                => 'Trái',
        'align_center'              => 'Giữa',
        'align_right'               => 'Phải',
        'label_image_url'           => 'URL hình ảnh',
        'btn_upload'                => 'Tải lên',
        'label_visible_when'        => 'Hiển thị khi',
        'visible_always'            => 'Luôn',
        'visible_result'            => 'Chỉ khi có kết quả',
        'label_slot_index'          => 'Slot Index (Group ID)',
        'slot_index_hint'           => 'Các ô cùng slot index sẽ hiển thị thông tin cùng 1 người',
        'label_layer'               => 'Lớp',
        'action_send_back'          => 'Ra sau',
        'action_bring_front'        => 'Ra trước',
        // MODAL
        'bg_settings_title'         => 'Cài đặt nền',
        'bg_type_label'             => 'Loại nền',
        'bg_type_color'             => 'Màu đồng nhất',
        'bg_type_image'             => 'Hình ảnh',
        'bg_type_video'             => 'Video',
        'bg_color_label'            => 'Màu',
        'bg_image_url_label'        => 'URL hình ảnh',
        'bg_upload_image'           => 'Tải ảnh lên',
        'modal_cancel'              => 'Hủy',
        'modal_apply'               => 'Áp dụng',
        'preview_title'             => 'Xem trước',
        'auto_generate_title'       => 'Tự động tạo ô quay',
        'auto_alert_info'           => 'Sẽ tạo :count ô quay ngẫu nhiên cho giải này',
        'auto_layout_type_label'    => 'Kiểu bố trí',
        'auto_layout_grid'          => 'Grid (Lưới)',
        'auto_layout_horizontal'    => 'Horizontal (Ngang)',
        'auto_layout_vertical'      => 'Vertical (Dọc)',
        'auto_grid_cols_label'      => 'Số cột (Grid)',
        'auto_slot_width_label'     => 'Chiều rộng mỗi ô',
        'auto_slot_height_label'    => 'Chiều cao mỗi ô',
        'auto_spacing_label'        => 'Khoảng cách giữa các ô',
        'auto_start_x_label'        => 'Vị trí X bắt đầu',
        'auto_start_y_label'        => 'Vị trí Y bắt đầu',
        'auto_random_source_label'  => 'Trường quay',
        'auto_result_fields_label'  => 'Trường kết quả (tùy chọn)',
        'auto_result_basic_label'   => 'Trường cơ bản',
        'auto_warning'              => 'Chú ý: Thao tác này sẽ xóa tất cả các ô "random_field" hiện có!',
        'auto_confirm'              => 'Tạo tự động',
        'create_random_title'       => 'Tạo ô quay ngẫu nhiên',
        'create_random_hint'        => 'Trường này sẽ quay khi nhấn "Quay"',
        'create_result_title'       => 'Trường kết quả (hiển thị khi dừng)',
        'create_width_label'        => 'Chiều rộng',
        'create_height_label'       => 'Chiều cao ô quay',
        'create_offset_label'       => 'Khoảng cách đến trường kết quả',
        'create_spacing_label'      => 'Khoảng cách giữa các trường',
        'create_confirm'            => 'Tạo',
    ],

    '_form' => [
        // PAGE
        'section_info_heading'      => '1. Thông tin:',
        'section_images_heading'    => '2. Hình ảnh:',
        // FORM
        'label_info'                => 'Thông tin',
        'placeholder_name'          => 'Tên mẫu quay số',
        'label_type'                => 'Loại',
    ],

    'detail-wheel' => [
        // PAGE
        'page_heading'              => 'Chi tiết Lucky Wheel',
        'breadcrumb_label'          => 'Mẫu quay số',
        // ACTION
        'action_display'            => 'Display',
        'action_create'             => 'Thêm mới',
        // FORM/TABLE
        'participants_heading'      => 'Danh sách tham dự:',
        'action_sync'               => 'Đồng bộ danh sách',
        'action_reset_clients'      => 'Reset danh sách',
        'label_client_type'         => 'Nhóm khách',
        'label_group'               => 'Lọc',
        // MODAL
        'modal_reset_title'         => 'Xác nhận Reset danh sách khách',
        'modal_reset_body'          => 'Bạn có chắc chắn muốn xóa toàn bộ :count người tham dự khỏi vòng quay này?',
        'modal_cancel'              => 'Hủy',
        'modal_confirm'             => 'Xác nhận Reset',
        // PREVIEW
        'preview_heading'           => 'Preview Vòng Quay',
        'preview_hint'              => 'Vòng quay sẽ tự động cập nhật khi đồng bộ danh sách',
        'no_entries'                => 'Chưa có danh sách tham dự',
    ],
    'lucky_draw_rewards_list' => [
        // TABLE
        'th_info'                   => 'THÔNG TIN GIẢI',
        'th_assign'                 => 'GÁN CƠ CẤU',
        'th_image'                  => 'ẢNH',
        // FORM
        'label_order_number'        => 'STT:',
        'label_order_name'          => 'Tên thứ tự:',
        'label_code'                => 'Mã:',
        'label_name'                => 'Tên:',
        'label_winners_count'       => 'Số lượng người trúng:',
        // ACTION
        'action_cancel_reward_title'=> 'Huỷ giải',
        'action_update_reward'      => 'Cập nhật giải thưởng',
        'action_delete_reward_title'=> 'Xóa giải',
        // MESSAGE
        'confirm_delete_reward'     => 'Bạn có chắc muốn xóa giải này không?',
        'empty_text'                => 'Chưa có giải thưởng',
    ],

    '_modal-upload' => [
        // ACTION
        'action_download_template'  => 'Tải file mẫu',
        'action_confirm'            => 'Xác nhận',
        // MESSAGE
        'download_template_hint'    => 'Tải file mẫu, sửa nội dung rồi nạp file (.xlsx) bên dưới',
        // FORM
        'label_upload_file'         => 'Nạp file tại đây <b>(.xlsx)</b>',
    ],

    'modal-upsert' => [
        // FORM
        'placeholder_code'          => 'REWARD0001',
        'label_code'                => 'Mã giải',
        'placeholder_name'          => 'Giải nhất - Tủ lạnh Panasonic/TV LG 8K',
        'label_name'                => 'Tên giải',
        'label_winners_count'       => 'Số lượng người trúng',
        'label_image_link'          => 'Link hình ảnh',
        'select_uploaded_image'     => 'Chọn ảnh đã upload',
        'select_other_image'        => 'Nhập link khác...',
        'placeholder_other_image'   => 'Dán link hình ảnh',
        'label_order'               => 'Thứ tự giải',
        // ACTION
        'action_save'               => 'Lưu',
    ],
];
