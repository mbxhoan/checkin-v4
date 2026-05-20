<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="{{ $canvasId }}"
    aria-labelledby="{{ $canvasId }}Label">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="{{ $canvasId }}Label">
            Thông tin
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid text-xs">
            @include('admin/clients/detail/_form', [
                'event'                 => $event,
                'model'                 => $client,
                'cfTemplate'            => $cfTemplate,
                'customFieldTemplates'  => $customFieldTemplates,
            ])
            @if (!$client->isNew())
                <div class="mt-4">
                    @if (!empty($checkins))
                        <h6 class="fw-bold">
                            Dữ liệu check-in/out <span class="text-danger fw-bold">({{ $checkins->count() }})</span>:
                        </h6>
                        @foreach ($checkins as $index => $checkin)
                            <div class="border rounded shadow-sm p-2 mb-2">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-md-6">
                                        {{ $checkin->scan_time ? humanize_date($checkin->scan_time, 'd/m/Y H:i:s') : '' }}
                                    </div>
                                    <div class="col-md-6 text-end">
                                        {{ $checkin->user_id ? $checkin->user->name : null }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {{-- <div class="table-responsive mt-4">
                            <table class="table table-striped table-xs align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Thông tin</th>
                                        <th>Thời gian</th>
                                        <th>Người check</th>
                                        <th>Danh mục</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($checkins as $index => $checkin)
                                        <tr>
                                            <td class="text-table">
                                                {{ $checkin->client->name ?? '' }}
                                                <br>
                                                {{ $checkin->client->email ?? '' }}
                                            </td>
                                            <td class="text-table">{{ $checkin->scan_time ? humanize_date($checkin->scan_time, 'd/m/Y H:i:s') : '' }}</td>
                                            <td class="text-table">{{ $checkin->updated_by ? $checkin->user->name : null }}</td>
                                            <td class="text-table">{{ $checkin->type }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> --}}
                    @else
                        <div class="alert alert-info text-xs">
                            Chưa checkin.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
