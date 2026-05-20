<?php

return [
    'profile' => 'Tài khoản',
    'public_profile' => 'Tài khoản',
    'settings' => 'Cài đặt',
    'edit' => 'Chỉnh sửa hồ sơ',
    'show' => 'Xem hồ sơ',
    'create' => 'Tạo tài khoản',
    'created' => 'Tài khoản đã được tạo',
    'updated' => 'Tài khoản đã được cập nhật',
    'deleted' => 'Tài khoản đã được xóa',
    'updated' => 'Hồ sơ đã được cập nhật',
    'password_updated' => 'Mật khẩu đã được cập nhật',
    'new_users' => 'người dùng mới|các người dùng mới',
    'count' => ':count người dùng|:count người dùng',
    'security' => 'Bảo mật',
    'not_login_yet' => 'Chưa đăng nhập',

    'attributes' => [
        'company_id' => 'Công ty',
        'event_id' => 'Sự kiện',
        'name' => 'Tên',
        'email' => 'Email',
        'username' => 'Username',
        'current_password' => 'Mật khẩu hiện tại',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'registered_at' => 'Đã đăng ký',
        'roles' => 'Vai trò',
        'status' => 'Trạng thái',
        'type' => 'Loại',
        'updated_at' => 'Cập nhật',
        'created_at' => 'Tạo lúc',
        'updated_by' => 'Cập nhật bởi',
        'created_by' => 'Tạo bởi',
        'last_login_at' => 'Đã đăng nhập',
        'expire_date' => 'HSD',

        'gender' => 'Giới tính',
        'genders' => [
            'male' => 'Nam',
            'female' => 'Nữ',
        ]
    ],

    'placeholder' => [
        'name' => 'Tên của bạn',
        'email' => 'Email của bạn',
        'current_password' => 'Mật khẩu hiện tại của bạn',
        'password' => 'Mật khẩu mới của bạn',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'company_id' => 'Công ty',
        'event_id' => 'Sự kiện',
        'username' => 'Username',
        'registered_at' => 'Đã đăng ký vào',
        'roles' => 'Vai trò',
        'status' => 'Trạng thái',
        'type' => 'Loại',
        'updated_at' => 'Cập nhật',
        'created_at' => 'Tạo lúc',
        'updated_by' => 'Cập nhật bởi',
        'created_by' => 'Tạo bởi',
        'last_login_at' => 'Đăng nhập lần cuối',
        'expire_date' => 'Ngày hết hạn',

        'gender' => 'Giới tính',
        'genders' => [
            'male' => 'Nam',
            'female' => 'Nữ',
        ]
    ],

    'index' => [
        // PAGE
        'page_heading'          => 'Danh sách tài khoản',
        // ACTION
        'action_create'         => 'Thêm mới',
        'filter_button'         => 'Bộ lọc',
        'filter_title'          => 'Bộ lọc',
        'filter_submit'         => 'Lọc',
        // TABLE
        'card_title'            => 'Danh sách Tài khoản',
        // MESSAGE
        'card_description'      => 'Xem, chọn và chỉnh sửa thông tin tài khoản tại đây.',
    ],

    'edit' => [
        // PAGE
        'page_heading'              => 'Chi Tiết',
        'breadcrumb_label'          => 'Tài khoản',
        // ACTION
        'action_create'             => 'Thêm mới',
        // PAGE
        'section_info_heading'      => 'Thông tin',
        'section_password_heading'  => 'Mật khẩu',
        // FORM
        'label_company'             => 'Công ty',
        'label_event'               => 'Sự kiện',
        'label_package'             => 'Gói đang dùng',
        'label_expire_date'         => 'Ngày hết hạn',
        'label_gate'                => 'Quầy/Gian hàng',
        // MESSAGE
        'placeholder_gate'          => 'Khu A...',
    ],

    'create' => [
        // PAGE
        'page_heading'              => 'Tài khoản',
        // FORM
        'step_role'                 => 'Loại tài khoản',
        'step_info'                 => 'Thông tin',
        'toggle_checkout'           => 'Tài khoản checkout',
        'label_company'             => 'Công ty',
        'label_event'               => 'Sự kiện',
        'section_info_heading'      => 'Thông tin',
        'section_password_heading'  => 'Mật khẩu',
        'label_package'             => 'Gói đang dùng',
        'label_expire_date'         => 'Ngày hết hạn',
        'label_gate'                => 'Quầy/Gian hàng',
        'placeholder_gate'          => 'Khu A...',
        // ACTION
        'action_next'               => 'Tiếp tục',
        'action_back'               => 'Quay lại',
        'action_save'               => 'Lưu',
    ],

    '_list' => [
        // TABLE
        'th_index'                  => '#',
        'th_package'                => 'Gói sử dụng',
        'th_company'                => 'Công ty',
        // ACTION
        'action_signout_title'      => 'Đăng xuất tài khoản',
        'action_signout'            => 'Đăng xuất',
        'action_activate_title'     => 'Kích hoạt và gửi mail',
        'action_activate'           => 'Kích hoạt',
        // MESSAGE
        'confirm_activate'          => 'Kích hoạt tài khoản và gửi email xác nhận sử dụng cho user này?',
    ],

    '_form' => [
        // FORM
        'label_company'             => 'Công ty',
        'label_event'               => 'Sự kiện',
        'label_expire_date'         => 'Ngày hết hạn',
        'label_gate'                => 'Quầy/Gian hàng',
        'placeholder_gate'          => 'VIP,...',
    ],

    '_modal-filter' => [
        // FORM
        'label_company'             => 'Công ty',
        'label_event'               => 'Sự kiện',
    ],
];
