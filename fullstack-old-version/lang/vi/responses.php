<?php

return [
    'checkin' => [
        'error' => 'Lỗi',
        'errors' => [
            'no_data_found' => 'Không tìm thấy thông tin',
            'event_not_found' => "Không tìm thấy sự kiện :code",
            'client_not_found' => 'Không tìm thấy thông tin :qrcode',
            'duplicate_by_date' => 'Đã checkin cùng ngày',
            'duplicate_by_user' => 'Đã checkin cùng cổng',
            'duplicate_checkin' => 'Đã checkin',
        ],
        'success' => 'Checkin thành công',
        'successes' => [
            'checkin_no_data' => 'Đã checkin không đầu vào',
            'checkin_count' => 'Đã checkin: :count',
        ]
    ],
    'checkout' => [
        'error' => 'Lỗi',
        'errors' => [
            'no_data_found' => 'Không tìm thấy thông tin',
            'event_not_found' => "Không tìm thấy sự kiện :code",
            'client_not_found' => 'Không tìm thấy thông tin :qrcode',
            'duplicate_by_date' => 'Đã checkout cùng ngày',
            'duplicate_by_user' => 'Đã checkout cùng cổng',
            'duplicate_checkout' => 'Đã checkout',
        ],
        'success' => 'Checkout thành công',
        'successes' => [
            'checkout_no_data' => 'Đã checkout không đầu vào',
            'checkout_count' => 'Đã checkout: :count',
        ]
    ],
    'create' => [
        'success' => 'Tạo mới thành công',
    ],
    'recaptcha' => [
        'not_validated' => "Recaptcha chưa được xác thực.",
    ]
];
