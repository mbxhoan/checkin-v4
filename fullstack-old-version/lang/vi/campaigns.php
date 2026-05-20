<?php

return [
    'create' => [
        // PAGE
        'title' => 'Tạo chiến dịch gửi mail',

        // STEPS
        'step_information' => 'Thông tin',
        'step_content' => 'Nội dung mail',

        // FIELDS
        'id_label' => 'ID',
        'event_label' => 'Sự kiện',
        'from_email_label' => 'Email gửi',
        'guest_group_label' => 'Nhóm khách cần gửi',
        'all_option' => '- Tất cả -',
        'cc_label' => 'cc',
        'bcc_label' => 'bcc',

        // BUTTONS
        'continue' => 'Tiếp tục',
        'back' => 'Quay lại',
        'save' => 'Lưu',

        // MODAL
        'preview_title' => 'Xem trước',
    ],
    'messages' => [
        'created_success' => 'Tạo chiến dịch gửi mail thành công',
        'created_failed' => 'Tạo campaign thất bại',
        'updated_success' => 'Cập nhật campaign thành công',
        'updated_failed' => 'Cập nhật campaign thất bại',
        'deleted_success' => 'Đã xoá campaign :name',
        'cloned_success' => 'Đã nhân bản campaign :name',
        'cloned_partial' => 'Đã nhân bản campaign :name :detail',
    ],

    'sync' => [
        'no_clients' => 'Không tìm thấy khách hàng nào',
        'update_detail_failed' => 'Lỗi cập nhật Campaign Detail :qrcode',
        'synced_clients' => 'Đã đồng bộ :count khách hàng',
        'synced_templates' => 'Đã đồng bộ :count mẫu nội dung mail từ Postmark',
        'sync_templates' => 'Đồng bộ nội dung mail',
        'synced_senders' => 'Đã đồng bộ :count địa chỉ Email gửi từ Postmark',
        'sync_senders' => 'Đồng bộ Email gửi',
    ],

    'manage' => [
        // PAGE
        'page_title' => 'Danh sách Campaigns',
        'title' => 'Danh sách Campaigns',

        // STATS
        'stats_campaigns' => 'Campaign(s):',
        'stats_sent' => 'Đã gửi',
        'stats_unlimited' => 'Không giới hạn',

        // BUTTONS
        'add_new' => 'Thêm mới',

        // LIST
        'list_title' => 'Danh sách Campaign(s)',
        'list_description' => 'Xem, chọn và chỉnh sửa thông tin các chiến dịch gửi mail tại đây.',
    ],
    'email_template' => [
        // PAGE
        'title' => 'Nội dung mail',
        'add_new_tooltip' => 'Thêm nội dung mail',

        // SYNC
        'syncing' => 'Đang đồng bộ...',

        // CLONE MODAL
        'clone_title' => 'Xác nhận Nhân bản',
        'clone_question' => 'Bạn có chắc chắn muốn nhân bản nội dung mail này?',
        'new_name_label' => 'Tên nội dung mail mới',
        'clone_input_label' => 'VUI LÒNG NHẬP <b>"COPY"</b> ĐỂ XÁC NHẬN NHÂN BẢN',
        'confirm_button' => 'Xác nhận',

        // ACTIONS
        'edit_title' => 'Chỉnh sửa',
        'synced' => 'Đã đồng bộ',
        'sync_error' => 'Không thể đồng bộ',
    ],
    'queue' => [
        'setup_failed_admin' => 'Đã có lỗi xảy ra khi setup email: :error',
        'setup_failed_user' => 'Đã có lỗi xảy ra khi setup email. Vui lòng thử lại sau.',
        'default_success' => 'Email đang được đưa vào hàng đợi.',
        'default_failed' => 'Đã có lỗi xảy ra trong quá trình setup',
        'no_guest_list' => 'Chưa có danh sách khách mời trong campaign.',
        'no_valid_email' => 'Không có email hợp lệ để đưa vào hàng đợi.',
        'limit_exceeded' => 'Đã vượt quá số lượng email cho phép.',
        'queued_success' => 'Đã đưa :count email vào hàng đợi.',
        'scheduled_success' => 'Đã lên lịch gửi :count email lúc :time.',
        'schedule_label' => 'Hẹn giờ gửi (tuỳ chọn)',
        'scheduled_note' => 'Đang hẹn gửi vào: :time',
        'stopped' => 'Đã dừng tiến trình',
    ],

    'status' => [
        'invalid' => 'Trạng thái không hợp lệ',
        'update_success' => 'Cập nhật trạng thái thành công',
        'update_failed' => 'Không thể cập nhật trạng thái thành công',
        'send_failed' => 'Không thể gửi email.',
    ],

    'validation' => [
        'invalid_email_list' => ':attribute không hợp lệ: :emails',
        'scheduled_at_not_past' => 'Thời gian hẹn gửi không được ở quá khứ.',
        'attributes' => [
            'event_id' => 'Sự kiện',
            'template_id' => 'Nội dung email',
            'name' => 'Tên chiến dịch',
            'type' => 'Nhóm khách',
            'subject' => 'Tiêu đề',
            'from_email' => 'Email gửi đi',
            'from_name' => 'Tên người gửi',
            'cc' => 'CC',
            'bcc' => 'BCC',
            'message_stream' => 'Message stream',
            'limitation_per_time' => 'Giới hạn gửi trong 1 lần',
            'hold_time' => 'Thời gian giữ lại',
            'scheduled_at' => 'Thời gian hẹn gửi',
            'fixed_attachments' => 'Tệp đính kèm cố định',
        ],
    ],
];
