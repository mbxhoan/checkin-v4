<?php

return [
    'index' => [
        // =======================
        // PAGE
        // =======================
        'page' => [
            'title'        => 'Báo cáo',
            'detail_title' => 'Báo cáo chi tiết',
        ],

        // =======================
        // BANNER / OVERVIEW
        // =======================
        'banner' => [
            'event_report_list_title'        => 'Danh sách báo cáo sự kiện',
            'event_report_list_description'  => 'Xem và chọn xem báo cáo của sự kiện tại đây.',
            'overview_summary_title'         => 'TÓM TẮT',
            'overview_checkin_performance_title' => 'HIỆU SUẤT CHECKIN',
            'overview_email_performance_title'   => 'HIỆU QUẢ EMAIL',
            'email_invite_section_title'     => 'Thiệp qua Email',
            'email_overview_title'           => 'Tổng quan',
            'email_recent_sent_title'        => 'Email đã gửi gần đây',
            'email_delivery_total_hint'      => 'Email đã gửi đi (Delivery/Total)',
            'recent_registrations_title'     => 'Đăng ký gần đây',
            'registration_fields_stats_title'       => 'Thống kê theo các trường thông tin',
            'registration_fields_stats_description' => 'Xem thống kê các trường thông tin theo dạng lựa chọn (select/multichoice/radio).',
            'registration_fields_list_title'        => 'Các trường thông tin đăng ký:',
        ],

        // =======================
        // MEDIA / TABLES / CHARTS
        // =======================
        'media' => [
            // Charts & check-in widgets
            'guest_group_checkin_stats_title'      => 'Thống kê checkin theo nhóm khách',
            'pie_chart_title'                      => 'Pie Chart',
            'checkedin_vs_total_invited_title'     => 'Đã checkin/Tổng số khách mời',
            'checked_in_title'                     => 'Đã checkin',
            'realtime_checkin_tracking_title'      => 'Theo dõi checkin',
            'realtime_checkin_tracking_description'=> 'Báo cáo theo thời gian thực trong thời gian diễn ra sự kiện',
            'checkin_by_row_title'                 => 'Checkin theo Hàng:',
            'checkin_by_floor_title'               => 'Checkin theo Tầng:',
            'floor_label'                          => 'Tầng',
            'checkin_by_region_title'              => 'Checkin theo Miền:',
            'checkin_by_channel_title'             => 'Checkin theo Kênh:',
            'checkin_by_card_type_title'           => 'Checkin theo Thiệp:',

            // Generic table helpers
            'horizontal_scroll_hint'               => 'Giữ Shift và lăn chuột để kéo ngang',
            'clients_table_caption'                => 'Tổng hợp dữ liệu',
            'table_header_type'                    => 'LOẠI',
            'table_header_registered'              => 'Đăng ký',
            'table_header_checked_in'              => 'Đã checkin',
            'table_header_registration_source'     => 'Nguồn đăng ký',

            // Table: guests by type
            'table1_title'                         => 'Thống kê danh sách khách mời theo loại',

            // Table: guests by registration source
            'table2_title'                         => 'Thống kê khách mời theo nguồn đăng ký',

            // Shared totals / empty states
            'table_total_guests_label'             => 'Tổng số khách',
            'guest_type_prefix'                    => 'Loại khách',
            'unknown_label'                        => 'Không rõ',
            'table_empty_by_guest_type'            => 'Chưa có dữ liệu theo loại khách.',
            'table_empty_by_registration_source'   => 'Chưa có dữ liệu theo nguồn đăng ký.',
            'table_no_data'                        => 'Không có dữ liệu',

            // Email stats table
            'email_status_opened'                  => 'Đã mở',
            'email_status_not_opened'              => 'Chưa mở',
            'email_table_index'                    => 'STT',
            'email_table_email'                    => 'Email',
            'email_table_attendee'                 => 'Người tham dự',
            'email_table_sent_at'                  => 'Thời gian gửi',
            'email_table_status'                   => 'Trạng thái',

            // Clients table
            'clients_table_placeholder_name'       => 'HỌ, TÊN',
            'clients_table_placeholder_email'      => 'EMAIL',
            'clients_table_header_status'          => 'TRẠNG THÁI',
            'clients_table_header_source'          => 'NGUỒN',
            'clients_table_header_created_by'      => 'Bởi',
            'clients_table_header_updated_by'      => 'Sửa',
            'clients_table_header_created_at'      => 'Tạo',
            'clients_table_header_info'            => 'Thông tin',
            'clients_table_header_created_date'    => 'Ngày tạo',
            'clients_table_header_status_title'    => 'Trạng Thái',
            'clients_table_header_updated'         => 'Cập Nhật',

            // Checkout list
            'checkout_list_title'                  => 'Danh sách khách đã CHECKOUT',
            'checkout_list_total_label'            => 'Tổng:',
            'checkout_table_header_name'           => 'Họ tên',
            'checkout_table_header_email'          => 'Email',
            'checkout_table_header_source'         => 'Nguồn đăng ký',
            'checkout_table_header_type'           => 'Loại',
            'checkout_table_header_time'           => 'Thời gian',
            'checkout_table_header_user_id'        => 'User ID',
            'checkout_table_header_username'       => 'Username',
            'checkout_table_empty'                 => 'Chưa có bản ghi CHECKOUT.',

            // Not-checked-in list
            'not_checkedin_list_title'             => 'Danh sách khách CHƯA CHECKIN',
            'not_checkedin_list_total_label'       => 'Tổng:',
            'not_checkedin_status_label'           => 'Trạng thái',
            'not_checkedin_status_badge'           => 'CHƯA CHECKIN',
            'not_checkedin_name_label'             => 'Họ tên',
            'not_checkedin_email_label'            => 'Email',
            'not_checkedin_source_label'           => 'Nguồn đăng ký',
            'not_checkedin_type_label'             => 'Loại',
            'not_checkedin_source_empty'           => 'Trống',
            'not_checkedin_table_empty'            => 'Tất cả khách đã checkin',

            // Checked-in list
            'checkedin_list_title'                 => 'Danh sách khách đã CHECKIN',
            'checkedin_list_total_label'           => 'Tổng:',
            'checkedin_status_label'               => 'Trạng thái',
            'checkedin_name_label'                 => 'Họ tên',
            'checkedin_email_label'                => 'Email',
            'checkedin_source_label'               => 'Nguồn đăng ký',
            'checkedin_time_label'                 => 'Thời gian',
            'checkedin_user_id_label'              => 'User ID',
            'checkedin_username_label'             => 'Username',
            'checkedin_table_empty'                => 'Chưa có bản ghi CHECKIN.',

            // Pie charts by custom fields
            'tron_no_data_alert'                   => 'Không có dữ liệu để hiển thị biểu đồ.',
            'tron_default_chart_title'             => 'Biểu đồ',

            // Filter modal labels
            'filter_field_label'                   => 'Trường',
            'filter_from_date_label'               => 'Từ ngày',
            'filter_to_date_label'                 => 'Đến ngày',
            'filter_company_label'                 => 'Công ty',
            'filter_province_label'                => 'Tỉnh/Thành phố',
            'filter_status_label'                  => 'Trạng thái',
            'filter_reset_button'                  => 'Reset',
            'filter_close_button'                  => 'Đóng',
        ],

        // =======================
        // ACTION / BUTTONS / LABELS
        // =======================
        'action' => [
            'edit_button'                  => 'Chỉnh sửa',
            'invited_guest_count_label'    => 'Số lượng khách mời:',
            'filter_by_date_label'         => 'Lọc theo ngày:',
            'all_dates_option'             => 'Tất cả ngày',
            'filter_apply_title'           => 'Áp dụng',
            'filter_apply_button'          => 'Lọc',
            'today_button'                 => 'Hôm nay',
            'today_filter_title_enabled'   => 'Lọc theo hôm nay',
            'today_filter_title_disabled'  => 'Hôm nay không nằm trong ngày diễn ra sự kiện',
            'clear_filter_title'           => 'Bỏ lọc',
            'clear_filter_button'          => 'Bỏ lọc',
            'open_filter_button'           => 'Bộ lọc',
            'filter_modal_title'           => 'Bộ lọc',
            'filter_modal_submit'          => 'Lọc',
            'download_title'               => 'Tải xuống',
            'download_qrcodes_button'      => 'Tải Qrcodes',
            'checked_in_label'             => 'Đã checkin:',
            'export_guest_summary'         => 'Tổng hợp khách mời',
            'export_checkin_details'       => 'Chi tiết checkin',
            'export_checkin_summary'       => 'Tổng hợp checkin',
            'visits_label'                 => 'Lượt truy cập',
            'registered_label'             => 'Đã đăng ký',
            'report_date_label'            => 'Ngày:',
            'invited_guest_unit'           => 'khách mời',
            'checked_in_guest_unit'        => 'khách đã checkin',
            'not_checked_in_guest_unit'    => 'khách chưa checkin',
            'peak_label'                   => 'Cao điểm:',
            'last_active_label'            => 'Gần nhất:',
            'checkin_turn_unit'            => 'lượt',
            'no_email_data_message'        => 'Chưa có dữ liệu gửi email cho sự kiện này.',
            'emails_sent_suffix'           => 'đã gửi',
            'email_opened_label'           => 'Đã mở:',
            'table_none_value'             => 'Không có',
        ],
    ],
];