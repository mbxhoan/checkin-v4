<?php

return [
    'index' => [
        // PAGE
        'page_title' => 'Cards/Invitations',
        'page_heading' => 'Card list',
        // ACTION
        'action_create' => 'Create new',
    ],

    'detail' => [
        // PAGE
        'page_heading' => 'Details',
        'breadcrumb_label' => 'Card template',
        'preview_heading' => 'Preview Card/Invitation',
        'preview_hint' => 'Drag to reposition, adjust color/font to preview instantly',
        'quick_guide_heading' => 'Quick guide:',
        // ACTION
        'action_create' => 'Create new',
        // FORM
        'label_id' => 'ID',
        'label_client_type' => 'Client group',
        'option_all' => '- All -',
        'label_background' => 'Background',
        'label_extension' => 'Format',
        'label_file_name' => 'File name',
        'placeholder_file_name' => '<qrcode>',
        // MESSAGE
        'example_heading' => 'Example:',
        'note_heading' => 'Note:',
        'note_file_name_rule' => 'File name should be based on a unique field. For example, if multiple clients share the same company, do not use company as file name.',
        'guide_drag_drop_fields' => 'Drag text/QR to desired position (coordinates calculated by % width/height).',
        'guide_font_size_note' => 'Font size = font size (% of background height) ⇒ exported text matches preview.',
        'guide_qr_image_note' => 'QR image: set width/height (px), align left/center/right.',
        'guide_toggle_visibility' => 'Toggle field visibility; “Refresh preview” after adding a new field.',
        // TABLE
        'stat_count_label' => 'Quantity:',
        'list_title' => 'Attendees by type',
        'list_description' => 'Preview personal print sample here.',
        // ACTION
        'action_refresh_preview' => 'Refresh preview',
        'action_generate_bulk' => 'Generate cards/invitations in bulk',
        'action_download_images' => 'Download card/invitation files',
        // MESSAGE
        'confirm_generate_title' => 'Are you sure to generate cards/invitations for :count clients? The process will start and take a while, please wait...',
        'confirm_generate_label' => 'PLEASE TYPE "OK" TO CONFIRM',
    ],

    'create' => [
        // PAGE
        'page_heading' => 'Create card',
        // FORM
        'step_info' => 'Information',
        'step_images' => 'Images',
        'label_id' => 'ID',
        'placeholder_code' => 'Invitation template October',
        'label_event' => 'Event',
        'label_client_type' => 'Client group',
        'option_all' => '- All -',
        'label_background' => 'Background',
        'label_output_extension' => 'Output format',
        'label_file_name' => 'File name',
        'placeholder_file_name' => '<qrcode>',
        // MESSAGE
        'example_heading' => 'Example:',
        'note_heading' => 'Note:',
        'note_file_name_rule' => 'File name should be based on a unique field. For example, if multiple clients share the same company, do not use company as file name.',
        // ACTION
        'action_next' => 'Continue',
        'action_back' => 'Back',
        'action_save' => 'Save',
        // PAGE
        'fields_display_heading' => 'Display fields:',
        'action_fullscreen' => 'View fullscreen',
    ],

    '_shortlist' => [
        // TABLE
        'th_index' => '#',
        'th_card_name' => 'Card name',
        'th_actions' => 'Actions',
        // ACTION
        'action_edit_title' => 'Edit',
        'action_delete_title' => 'Delete',
        'action_view_report' => 'View report',
        // MESSAGE
        'confirm_delete_title' => 'Are you sure?',
        'confirm_delete_text' => 'This action cannot be undone!',
        'confirm_delete_confirm' => 'Delete',
        'confirm_delete_cancel' => 'Cancel',
        'delete_success_title' => 'Deleted!',
        'delete_success_text' => 'Card has been deleted.',
        'delete_error_title' => 'Error!',
        'delete_error_text' => 'Unable to delete. Please try again.',
    ],

    '_list' => [
        // TABLE
        'th_index' => '#',
        'th_info' => 'Information',
        // MESSAGE
        'empty_text' => 'No data',
        // ACTION
        'action_create' => 'Create new',
    ],

    '_background' => [
        // PAGE
        'sample_name' => 'John Doe',
        'sample_email' => 'email@example.com',
        'sample_phone' => '0909 000 000',
        'sample_company' => 'ABC COMPANY',
        'sample_position' => 'Director',
        'sample_event_name' => 'Event Name',
        'sample_code' => 'ABC123',
        'sample_table' => 'VIP Table 1',
    ],

    '_form' => [
        // FORM
        'label_other_card' => 'Other Cards/Invitations: ',
        'section_info_heading' => '1. Information:',
        'label_code' => 'Information',
        'label_client_type' => 'Client group',
        'option_all' => '- All -',
        'section_output_heading' => '2. Background & Output:',
        'label_background' => 'Background',
        'label_file_name' => 'File name',
        'label_extension' => 'Format',
        'placeholder_file_name' => '<qrcode>',
    ],

    '_aim' => [
        // PAGE
        'page_title' => 'Edit position',
        'page_heading' => 'Edit position',
        // ACTION
        'action_show_config' => 'Show info/Edit position',
        // FORM
        'option_right' => 'Right',
        'option_left' => 'Left',
        'option_top' => 'Top',
        'option_bottom' => 'Bottom',
        // MESSAGE
        'loading_text' => 'Loading',
        'stat_count_label' => 'Quantity:',
        // ACTION
        'action_download_images' => 'Download card/invitation files',
    ],

    'card_detail' => [
        // TABLE
        'th_type' => 'Data type',
        'th_field' => 'Field',
        // FORM
        'placeholder_name' => 'Name',
        'label_is_show' => 'Show',
        'label_font' => 'Font',
        'label_font_size' => 'Font size',
        'label_color_text' => 'Text color',
        'label_color' => 'Color',
        'label_width' => 'Width',
        'label_height' => 'Height',
        'label_align' => 'Align',
        'label_pos_x' => 'Horizontal align',
        'label_pos_y' => 'Vertical align',
        // MESSAGE
        'tooltip_font_use' => 'Choose font for export',
        'tooltip_font_size_unit' => 'Unit: % of background height. 50 = 50% of image height.',
        'tooltip_apply_preview' => 'Applies immediately to preview and export image.',
        'tooltip_px_unit' => 'Unit in px on exported image.',
        'tooltip_align_hint' => 'Left / Center / Right based on selected position',
    ],

    'custom_field_templates' => [
        // TABLE
        'th_field' => 'Field',
        'th_description' => 'Description',
        // FORM
        'placeholder_name' => 'Name',
        'placeholder_description' => 'Description',
        'label_is_show' => 'Show',
        'label_bold' => 'Bold',
        'label_italic' => 'Italic',
        'label_bg' => 'Background',
        'label_bg_color' => 'Background color',
        'label_color' => 'Text color',
        'label_font_size' => 'Font size',
        'label_font' => 'Font',
        'label_align' => 'Align',
        'label_width' => 'Width',
        'label_pos_x' => 'Horizontal align',
        'label_pos_y' => 'Vertical align',
    ],
];
