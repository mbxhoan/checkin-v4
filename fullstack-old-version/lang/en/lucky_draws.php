<?php
return [
    'index' => [
        // PAGE
        'page_title'        => 'Online Lucky Draw',
        'page_heading'      => 'Online Lucky Draw',
        // ACTION
        'action_create'     => 'Create new',
    ],

    'detail' => [
        // PAGE
        'page_heading'              => 'Details',
        'breadcrumb_label'          => 'Lucky Draw Template',
        // ACTION
        'action_builder'            => 'Builder',
        'action_display'            => 'Display',
        'action_create'             => 'Create new',
        // FORM
        'label_name'                => 'Name',
        'label_bg_mobile'           => 'Background Mobile',
        'label_bg_desktop'          => 'Background Desktop',
        // TABLE
        'data_list_heading'         => 'Participants:',
        'rewards_list_heading'      => 'Rewards:',
        'winners_table_th_index'    => '#',
        'winners_table_th_qr_name'  => 'QR / Name',
        'winners_table_th_reward'   => 'Won reward',
        'winners_table_th_actions'  => 'Actions',
        // ACTION
        'action_sync_clients'       => 'Sync guest list',
        'label_client_type'         => 'Client group',
        'option_all'                => '- All -',
        'label_group'               => 'Filter',
        'action_reset_assign'       => 'Refresh',
        'action_reset_rewards'      => 'Reset',
        'action_download_template'  => 'Download template',
        'modal_create_reward_title' => 'Add new reward',
        'modal_create_reward_submit'=> 'Add',
        'modal_upload_rewards_title'=> 'Upload rewards list',
        'modal_upload_rewards_submit'=> 'Upload file',
        'action_export_winners'     => 'Export results',
        'action_reset_winners'      => 'Reset winners',
        'action_remove_reward_title'=> 'Remove reward',
        // MESSAGE
        'winners_empty'             => 'No winners yet.',
        'upload_img_label'          => 'Upload image to get link (for img_link column in Excel reward list)',
        'upload_button'             => 'Upload',
        'uploaded_list_label'       => 'Uploaded images',
        'copy_link_title'           => 'Copy link',
        'paste_link_hint'           => 'Paste this link into the img_link column in Excel when uploading rewards.',
        'error_upload'              => 'Upload error',
        'error_connection'          => 'Connection error',
        'toast_copied'              => 'Link copied',
        'reset_winners_confirm'     => 'Are you sure to reset the whole winners list? All assignees will be removed.',
        'remove_reward_confirm'     => 'Remove reward from this person?',
    ],

    'create' => [
        // PAGE
        'page_heading'              => 'Create lucky draw',
        // FORM
        'step_info'                 => 'Information',
        'label_name'                => 'Name',
        'placeholder_name'         => 'Lucky draw template December',
        'label_event'              => 'Event',
        'label_type'               => 'Wheel type',
        // ACTION
        'action_save'               => 'Save',
    ],

    'display' => [
        // MESSAGE
        'confirm_button_title'      => 'Confirm winner and save result',
    ],

    'display-wheel' => [
        // TABLE/PANEL
        'tab_entries'               => 'Entries',
        'tab_results'               => 'Results',
        'label_entries_input'       => 'Enter participants (one name per line)',
        'entries_placeholder'       => "John Doe\nJane Smith\n...",
        // ACTION
        'btn_update'                => 'Update',
        'btn_shuffle'               => 'Shuffle',
        'btn_remove_winner'         => 'Remove winner',
        // FORM
        'bg_title'                  => 'Change background',
        'bg_link_placeholder'       => 'Paste background image link (URL)...',
        'btn_apply_bg'              => 'Apply link',
        'btn_reset_bg'              => 'Use default',
        'bg_upload_select_desktop'  => 'Desktop screen',
        'bg_upload_select_mobile'   => 'Mobile screen',
        'btn_upload_bg'             => 'Upload',
        // MESSAGE
        'results_empty'             => 'No results yet',
        'spin_hint'                 => 'Click the wheel or press Ctrl+Enter',
        'winner_default'            => 'Not spun',
        'toggle_collapse_title'     => 'Collapse to wheel only',
        'toggle_collapse_label_collapse' => 'Collapse',
        'toggle_collapse_label_expand'   => 'Expand',
        'toggle_bg_title'           => 'Change background',
        'bg_drawer_title'           => 'Change screen background',
        'bg_drawer_link_placeholder'=> 'Paste image link (URL)...',
        'btn_drawer_apply'          => 'Apply',
        'btn_drawer_reset'          => 'Default',
        'drawer_upload_desktop'     => 'Desktop background',
        'drawer_upload_mobile'      => 'Mobile background',
        'btn_drawer_upload'         => 'Upload image',
        'results_count_prefix'      => 'Spun:',
        'input_prompt'              => 'Enter the list on the left',
        'status_applying_link'      => 'Background link applied.',
        'status_uploading'          => 'Uploading...',
        'status_updated_bg'         => 'Background updated.',
        'status_upload_error'       => 'Upload error',
        'status_connection_error'   => 'Connection error',
    ],

    'builder' => [
        // PAGE
        'page_title_prefix'         => 'Wheel Designer -',
        // ACTION
        'action_back'               => 'Back',
        'action_preview'            => 'Preview',
        'action_save'               => 'Save',
        // PANEL
        'panel_event_data'          => 'Event data',
        'label_participant_filter'  => 'Filter participants',
        'filter_all'                => 'All',
        'filter_available'          => 'Available',
        'filter_won'                => 'Won',
        'label_core_fields'         => 'Core fields',
        'label_custom_fields'       => 'Custom fields',
        'label_participant_sample'  => 'Participant samples',
        'panel_rewards'             => 'Manage rewards',
        'badge_given'               => 'Given',
        'badge_waiting'             => 'Pending',
        'btn_layout'                => 'Layout',
        'btn_auto'                  => 'Auto',
        'btn_winners'               => 'Winners',
        'properties_title'          => 'Block properties',
        'label_x'                   => 'X',
        'label_y'                   => 'Y',
        'label_width'               => 'Width',
        'label_height'              => 'Height',
        'label_source_field'        => 'Source field',
        'label_font_size'           => 'Font size',
        'label_font_color'          => 'Text color',
        'label_align'               => 'Align',
        'align_left'                => 'Left',
        'align_center'              => 'Center',
        'align_right'               => 'Right',
        'label_image_url'           => 'Image URL',
        'btn_upload'                => 'Upload',
        'label_visible_when'        => 'Visible when',
        'visible_always'            => 'Always',
        'visible_result'            => 'Only when result',
        'label_slot_index'          => 'Slot Index (Group ID)',
        'slot_index_hint'           => 'Blocks with same slot index show info of one person',
        'label_layer'               => 'Layer',
        'action_send_back'          => 'Send back',
        'action_bring_front'        => 'Bring front',
        // MODAL
        'bg_settings_title'         => 'Background settings',
        'bg_type_label'             => 'Background type',
        'bg_type_color'             => 'Solid color',
        'bg_type_image'             => 'Image',
        'bg_type_video'             => 'Video',
        'bg_color_label'            => 'Color',
        'bg_image_url_label'        => 'Image URL',
        'bg_upload_image'           => 'Upload image',
        'modal_cancel'              => 'Cancel',
        'modal_apply'               => 'Apply',
        'preview_title'             => 'Preview',
        'auto_generate_title'       => 'Auto generate slots',
        'auto_alert_info'           => 'Will create :count random slots for this reward',
        'auto_layout_type_label'    => 'Layout type',
        'auto_layout_grid'          => 'Grid',
        'auto_layout_horizontal'    => 'Horizontal',
        'auto_layout_vertical'      => 'Vertical',
        'auto_grid_cols_label'      => 'Grid columns',
        'auto_slot_width_label'     => 'Slot width',
        'auto_slot_height_label'    => 'Slot height',
        'auto_spacing_label'        => 'Spacing',
        'auto_start_x_label'        => 'Start X',
        'auto_start_y_label'        => 'Start Y',
        'auto_random_source_label'  => 'Random field',
        'auto_result_fields_label'  => 'Result fields (optional)',
        'auto_result_basic_label'   => 'Basic fields',
        'auto_warning'              => 'Note: This will delete all existing "random_field" blocks!',
        'auto_confirm'              => 'Generate',
        'create_random_title'       => 'Create random field',
        'create_random_hint'        => 'This field spins when pressing "Spin"',
        'create_result_title'       => 'Result fields (shown when stopped)',
        'create_width_label'        => 'Width',
        'create_height_label'       => 'Random field height',
        'create_offset_label'       => 'Offset to result',
        'create_spacing_label'      => 'Spacing between fields',
        'create_confirm'            => 'Create',
    ],

    '_form' => [
        // PAGE
        'section_info_heading'      => '1. Information:',
        'section_images_heading'    => '2. Images:',
        // FORM
        'label_info'                => 'Information',
        'placeholder_name'          => 'Lucky draw template name',
        'label_type'                => 'Type',
    ],

    'detail-wheel' => [
        // PAGE
        'page_heading'              => 'Lucky Wheel Details',
        'breadcrumb_label'          => 'Lucky Draw Template',
        // ACTION
        'action_display'            => 'Display',
        'action_create'             => 'Create new',
        // FORM/TABLE
        'participants_heading'      => 'Participants:',
        'action_sync'               => 'Sync list',
        'action_reset_clients'      => 'Reset list',
        'label_client_type'         => 'Client group',
        'label_group'               => 'Filter',
        // MODAL
        'modal_reset_title'         => 'Confirm reset participants',
        'modal_reset_body'          => 'Are you sure to remove all :count participants from this wheel?',
        'modal_cancel'              => 'Cancel',
        'modal_confirm'             => 'Confirm reset',
        // PREVIEW
        'preview_heading'           => 'Wheel Preview',
        'preview_hint'              => 'The wheel updates automatically after syncing',
        'no_entries'                => 'No participants yet',
    ],

    'lucky_draw_rewards_list' => [
        // TABLE
        'th_info'                   => 'REWARD INFO',
        'th_assign'                 => 'ASSIGN STRUCTURE',
        'th_image'                  => 'IMAGE',
        // FORM
        'label_order_number'        => 'No:',
        'label_order_name'          => 'Order name:',
        'label_code'                => 'Code:',
        'label_name'                => 'Name:',
        'label_winners_count'       => 'Winners count:',
        // ACTION
        'action_cancel_reward_title'=> 'Cancel reward',
        'action_update_reward'      => 'Update reward',
        'action_delete_reward_title'=> 'Delete reward',
        // MESSAGE
        'confirm_delete_reward'     => 'Are you sure to delete this reward?',
        'empty_text'                => 'No rewards yet',
    ],

    '_modal-upload' => [
        // ACTION
        'action_download_template'  => 'Download template',
        'action_confirm'            => 'Confirm',
        // MESSAGE
        'download_template_hint'    => 'Download template, edit data, then upload the (.xlsx) file below',
        // FORM
        'label_upload_file'         => 'Upload file here <b>(.xlsx)</b>',
    ],

    'modal-upsert' => [
        // FORM
        'placeholder_code'          => 'REWARD0001',
        'label_code'                => 'Reward code',
        'placeholder_name'          => 'First prize - Panasonic fridge / LG 8K TV',
        'label_name'                => 'Reward name',
        'label_winners_count'       => 'Winners count',
        'label_image_link'          => 'Image link',
        'select_uploaded_image'     => 'Select uploaded image',
        'select_other_image'        => 'Use another link...',
        'placeholder_other_image'   => 'Paste image link',
        'label_order'               => 'Reward order',
        // ACTION
        'action_save'               => 'Save',
    ],
];
