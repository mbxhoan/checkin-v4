@php
    $customFieldTemplates = $event->getCustomFieldTemplates();
    $countCol = 10;
@endphp

<span class="text-xs text-secondary">
    <x-icon name="arrow-left" />
    {{ __('reports.index.media.horizontal_scroll_hint') }}
    <x-icon name="arrow-right" />
</span>

<table class="table text-xs">
    <caption>{{ __('reports.index.media.clients_table_caption') }}</caption>
    <thead>
        <tr>
            <th>ID</th>
            <th scope="col" class="col-2">
                <input type="text" placeholder="{{ __('reports.index.media.clients_table_placeholder_name') }}" class="filter-input" name="name" class="{{ $name ?? null }}">
            </th>
            <th scope="col" class="col-2">
                <input type="text" placeholder="{{ __('reports.index.media.clients_table_placeholder_email') }}" class="filter-input" name="email" class="{{ $email ?? null }}">
            </th>
            <th>{{ __('reports.index.media.table_header_type') }}</th>
            <th>{{ __('reports.index.media.clients_table_header_status') }}</th>
            <th>{{ __('reports.index.media.clients_table_header_source') }}</th>
            <th>{{ __('reports.index.media.clients_table_header_created_by') }}</th>
            <th>{{ __('reports.index.media.clients_table_header_updated_by') }}</th>
            <th>{{ __('reports.index.media.clients_table_header_created_at') }}</th>
            @foreach ($customFieldTemplates as $templateName => $templateAttr)
                @php
                    $countCol++;
                @endphp

                <th>
                    {{ $templateAttr['desc'] ?? strtoupper($templateName) }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody id="clients-table-body">
        @include('admin.reports.clients._tbody', [
            'event'                 => $event,
            'clients'               => $clients,
            'customFieldTemplates'  => $customFieldTemplates,
        ])
    </tbody>
</table>

<div class="d-flex justify-content-center" id="pagination-links">
    {!! $clients->links() !!}
</div>

@push('admin_css')
    <style>
        table {
            overflow: auto;
            height: auto;
        }
        table thead tr th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>
@endpush
