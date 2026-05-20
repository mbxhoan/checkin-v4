<?php

return [
    'index' => [
        // =======================
        // PAGE
        // =======================
        'page' => [
            'title'        => 'Reports',
            'detail_title' => 'Report details',
        ],

        // =======================
        // BANNER / OVERVIEW
        // =======================
        'banner' => [
            'event_report_list_title'        => 'Event report list',
            'event_report_list_description'  => 'View and select event reports here.',
            'overview_summary_title'         => 'SUMMARY',
            'overview_checkin_performance_title' => 'CHECK-IN PERFORMANCE',
            'overview_email_performance_title'   => 'EMAIL PERFORMANCE',
            'email_invite_section_title'     => 'Email invitations',
            'email_overview_title'           => 'Overview',
            'email_recent_sent_title'        => 'Recently sent emails',
            'email_delivery_total_hint'      => 'Emails sent (Delivered/Total)',
            'recent_registrations_title'     => 'Recent registrations',
            'registration_fields_stats_title'       => 'Statistics by registration fields',
            'registration_fields_stats_description' => 'View statistics for selectable fields (select/multichoice/radio).',
            'registration_fields_list_title'        => 'Registration fields:',
        ],

        // =======================
        // MEDIA / TABLES / CHARTS
        // =======================
        'media' => [
            // Charts & check-in widgets
            'guest_group_checkin_stats_title'      => 'Check-ins by guest group',
            'pie_chart_title'                      => 'Pie chart',
            'checkedin_vs_total_invited_title'     => 'Checked in / Total invited',
            'checked_in_title'                     => 'Checked in',
            'realtime_checkin_tracking_title'      => 'Real-time check-in tracking',
            'realtime_checkin_tracking_description'=> 'Real-time reporting during the event',
            'checkin_by_row_title'                 => 'Check-ins by row:',
            'checkin_by_floor_title'               => 'Check-ins by floor:',
            'floor_label'                          => 'Floor',
            'checkin_by_region_title'              => 'Check-ins by region:',
            'checkin_by_channel_title'             => 'Check-ins by channel:',
            'checkin_by_card_type_title'           => 'Check-ins by card type:',

            // Generic table helpers
            'horizontal_scroll_hint'               => 'Hold Shift and scroll to pan horizontally',
            'clients_table_caption'                => 'Summary data',
            'table_header_type'                    => 'TYPE',
            'table_header_registered'              => 'Registered',
            'table_header_checked_in'              => 'Checked in',
            'table_header_registration_source'     => 'Registration source',

            // Table: guests by type
            'table1_title'                         => 'Guest list statistics by type',

            // Table: guests by registration source
            'table2_title'                         => 'Guest statistics by registration source',

            // Shared totals / empty states
            'table_total_guests_label'             => 'Total guests',
            'guest_type_prefix'                    => 'Guest type',
            'unknown_label'                        => 'Unknown',
            'table_empty_by_guest_type'            => 'No data by guest type yet.',
            'table_empty_by_registration_source'   => 'No data by registration source yet.',
            'table_no_data'                        => 'No data',

            // Email stats table
            'email_status_opened'                  => 'Opened',
            'email_status_not_opened'              => 'Not opened',
            'email_table_index'                    => 'No.',
            'email_table_email'                    => 'Email',
            'email_table_attendee'                 => 'Attendee',
            'email_table_sent_at'                  => 'Sent at',
            'email_table_status'                   => 'Status',

            // Clients table
            'clients_table_placeholder_name'       => 'FULL NAME',
            'clients_table_placeholder_email'      => 'EMAIL',
            'clients_table_header_status'          => 'STATUS',
            'clients_table_header_source'          => 'SOURCE',
            'clients_table_header_created_by'      => 'By',
            'clients_table_header_updated_by'      => 'Edited',
            'clients_table_header_created_at'      => 'Created',
            'clients_table_header_info'            => 'Info',
            'clients_table_header_created_date'    => 'Created at',
            'clients_table_header_status_title'    => 'Status',
            'clients_table_header_updated'         => 'Updated',

            // Checkout list
            'checkout_list_title'                  => 'Checked-out guests list',
            'checkout_list_total_label'            => 'Total:',
            'checkout_table_header_name'           => 'Full name',
            'checkout_table_header_email'          => 'Email',
            'checkout_table_header_source'         => 'Registration source',
            'checkout_table_header_type'           => 'Type',
            'checkout_table_header_time'           => 'Time',
            'checkout_table_header_user_id'        => 'User ID',
            'checkout_table_header_username'       => 'Username',
            'checkout_table_empty'                 => 'No checkout records yet.',

            // Not-checked-in list
            'not_checkedin_list_title'             => 'Not yet checked-in guests list',
            'not_checkedin_list_total_label'       => 'Total:',
            'not_checkedin_status_label'           => 'Status',
            'not_checkedin_status_badge'           => 'NOT CHECKED IN',
            'not_checkedin_name_label'             => 'Full name',
            'not_checkedin_email_label'            => 'Email',
            'not_checkedin_source_label'           => 'Registration source',
            'not_checkedin_type_label'             => 'Type',
            'not_checkedin_source_empty'           => 'Empty',
            'not_checkedin_table_empty'            => 'All guests have checked in',

            // Checked-in list
            'checkedin_list_title'                 => 'Checked-in guests list',
            'checkedin_list_total_label'           => 'Total:',
            'checkedin_status_label'               => 'Status',
            'checkedin_name_label'                 => 'Full name',
            'checkedin_email_label'                => 'Email',
            'checkedin_source_label'               => 'Registration source',
            'checkedin_time_label'                 => 'Time',
            'checkedin_user_id_label'              => 'User ID',
            'checkedin_username_label'             => 'Username',
            'checkedin_table_empty'                => 'No check-in records yet.',

            // Pie charts by custom fields
            'tron_no_data_alert'                   => 'No data to display chart.',
            'tron_default_chart_title'             => 'Chart',

            // Filter modal labels
            'filter_field_label'                   => 'Field',
            'filter_from_date_label'               => 'From date',
            'filter_to_date_label'                 => 'To date',
            'filter_company_label'                 => 'Company',
            'filter_province_label'                => 'Province/City',
            'filter_status_label'                  => 'Status',
            'filter_reset_button'                  => 'Reset',
            'filter_close_button'                  => 'Close',
        ],

        // =======================
        // ACTION / BUTTONS / LABELS
        // =======================
        'action' => [
            'edit_button'                  => 'Edit',
            'invited_guest_count_label'    => 'Invited guests:',
            'filter_by_date_label'         => 'Filter by date:',
            'all_dates_option'             => 'All dates',
            'filter_apply_title'           => 'Apply',
            'filter_apply_button'          => 'Filter',
            'today_button'                 => 'Today',
            'today_filter_title_enabled'   => 'Filter for today',
            'today_filter_title_disabled'  => 'Today is not within the event dates',
            'clear_filter_title'           => 'Clear filter',
            'clear_filter_button'          => 'Clear',
            'open_filter_button'           => 'Filters',
            'filter_modal_title'           => 'Filters',
            'filter_modal_submit'          => 'Apply',
            'download_title'               => 'Download',
            'download_qrcodes_button'      => 'Download QR codes',
            'checked_in_label'             => 'Checked in:',
            'export_guest_summary'         => 'Guest summary',
            'export_checkin_details'       => 'Check-in details',
            'export_checkin_summary'       => 'Check-in summary',
            'visits_label'                 => 'Visits',
            'registered_label'             => 'Registered',
            'report_date_label'            => 'Date:',
            'invited_guest_unit'           => 'invited guests',
            'checked_in_guest_unit'        => 'checked-in guests',
            'not_checked_in_guest_unit'    => 'not checked-in guests',
            'peak_label'                   => 'Peak:',
            'last_active_label'            => 'Latest:',
            'checkin_turn_unit'            => 'check-ins',
            'no_email_data_message'        => 'No email sending data for this event yet.',
            'emails_sent_suffix'           => 'sent',
            'email_opened_label'           => 'Opened:',
            'table_none_value'             => 'None',
        ],
    ],
];