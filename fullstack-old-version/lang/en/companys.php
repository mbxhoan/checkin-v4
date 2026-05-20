<?php

return [
    'page_title' => 'Companies',
    'index_title' => 'Company list',
    'statuses' => [
        'new' => 'New',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'deleted' => 'Deleted',
    ],
    'table' => [
        'name' => 'Company name',
        'code' => 'Company code',
        'events' => 'Events',
        'users' => 'User(s)',
        'status' => 'Status',
        'limited_clients' => 'Data limit',
        'limited_events' => 'Event limit',
        'updated_at' => 'Updated at',
    ],
    'messages' => [
        'created' => 'Created successfully',
        'updated' => 'Updated successfully',
        'deleted' => 'Deleted successfully',
        'delete_blocked_by_events' => 'Cannot delete company because events already exist for this company.',
        'sync_event_settings_success' => 'Synced settings for events under company :name.',
    ],
];
