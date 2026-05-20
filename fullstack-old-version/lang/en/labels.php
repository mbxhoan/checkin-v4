<?php

return [
    // =======================
    // INDEX PAGE
    // =======================
    'index' => [
        // PAGE
        'page' => [
            'title' => 'Print templates',
        ],

        // ACTION
        'action' => [
            'create_button' => 'Add new',
        ],

        // TABLE
        'table' => [
            'title' => 'Print template list',
            'description' => 'View, select and edit print templates here.',
        ],
    ],

    // =======================
    // DETAIL PAGE
    // =======================
    'detail' => [
        // PAGE
        'page' => [
            'title' => 'Detail',
            'breadcrumb1' => 'Print templates',
        ],

        // FORM
        'form' => [
            'default_label_switch' => 'Default print template',
            'name_label' => 'Name',
            'name_placeholder' => 'Print template name',
            'type_label' => 'Guest group',
            'type_all_option' => '- All -',
            'width_label' => 'Length',
            'width_placeholder' => '10',
            'height_label' => 'Height',
            'height_placeholder' => '10',
            'unit_label' => 'Unit',
            'unit_placeholder' => 'cm',
            'clone_modal_title' => 'Clone print template',
            'clone_modal_event_label' => 'Event',
            'clone_modal_new_label_info' => 'New template information',
        ],

        // TABLE
        'table' => [
            'attendee_by_type_title' => 'Attendee list by type',
            'attendee_by_type_description' => 'Preview individual attendee print templates here.',
            'quantity_label' => 'Quantity:',
        ],

        // ACTION
        'action' => [
            'create_button' => 'Add new',
            'multi_print_button' => 'Print all',
            'single_print_button' => 'Test print',
            'clone_confirm_button' => 'Confirm clone',
        ],

        // MESSAGE
        'message' => [
            // (reserved for future messages)
        ],
    ],

    // =======================
    // CREATE PAGE
    // =======================
    'create' => [
        // PAGE
        'page' => [
            'title' => 'Create print template',
        ],

        // FORM
        'form' => [
            'step_info_label' => 'Information',
            'step_size_label' => 'Size',
            'name_label' => 'Name',
            'name_placeholder' => '8x6 print template',
            'event_label' => 'Event',
            'type_label' => 'Guest group',
            'type_all_option' => '- All -',
            'width_label' => 'Length',
            'width_placeholder' => '10',
            'height_label' => 'Height',
            'height_placeholder' => '10',
            'unit_label' => 'Unit',
            'unit_placeholder' => 'cm',
            'sample_7x3_label' => 'Template 7 x 3',
            'sample_8x6_label' => 'Template 8 x 6',
            'sample_6x4_label' => 'Template 6 x 4',
            'print_form_label' => 'Print form:',
            'clone_modal_title' => 'Clone print template',
            'clone_modal_event_label' => 'Event',
            'clone_modal_new_label_info' => 'New template information',
        ],

        // ACTION
        'action' => [
            'next_button' => 'Continue',
            'prev_button' => 'Back',
            'submit_button' => 'Save',
            'clone_confirm_button' => 'Confirm clone',
        ],

        // MESSAGE
        'message' => [
            // (reserved)
        ],
    ],

    // =======================
    // LABEL DETAIL COMPONENT
    // =======================
    'label_detail' => [
        // PAGE
        'page' => [
            'add_component_title' => 'Add new component:',
            'field_config_title' => 'Field configuration:',
        ],

        // TABLE
        'table' => [
            'type_column' => 'Type',
            'field_column' => 'Field',
        ],

        // FORM
        'form' => [
            'field_placeholder' => 'Name',
            'show_label' => 'Show',
            'color_label' => 'Text color',
            'bold_label' => 'Bold',
            'italic_label' => 'Italic',
            'uppercase_label' => 'UPPERCASE',
            'font_label' => 'Font',
            'size_label' => 'Font size (%)',
            'width_label' => 'Width',
            'height_label' => 'Height',
            'h_align_label' => 'Horizontal align',
            'pos_x_label' => 'Horizontal offset',
            'pos_y_label' => 'Vertical offset',
        ],

        // ACTION
        'action' => [
            'save_component_button' => 'Save component',
        ],

        // MESSAGE
        'message' => [
            // (reserved for validation / helper text if needed)
        ],
    ],
];
