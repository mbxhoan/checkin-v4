<?php

return [
    'page_title' => 'Công ty',
    'index_title' => 'Danh sách công ty',
    'statuses' => [
        'new' => 'Mới',
        'active' => 'Đang hoạt động',
        'inactive' => 'Ngưng hoạt động',
        'deleted' => 'Đã xóa',
    ],
    'table' => [
        'name' => 'Tên công ty',
        'code' => 'Mã công ty',
        'events' => 'Sự kiện',
        'users' => 'User(s)',
        'status' => 'Trạng thái',
        'limited_clients' => 'Giới hạn data',
        'limited_events' => 'Giới hạn sự kiện',
        'updated_at' => 'Ngày cập nhật',
    ],
    'messages' => [
        'created' => 'Tạo mới thành công',
        'updated' => 'Cập nhật thành công',
        'deleted' => 'Đã xoá thành công',
        'delete_blocked_by_events' => 'Không thể xoá công ty vì đã có sự kiện khởi tạo cùng công ty này',
        'sync_event_settings_success' => 'Đã đồng bộ cấu hình cho sự kiện thuộc công ty :name',
    ],
];
