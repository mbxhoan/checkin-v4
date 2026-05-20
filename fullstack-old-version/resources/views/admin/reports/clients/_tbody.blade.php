@foreach ($clients as $client)
    <tr
        class="text-xs {{ (!empty($client->checkins) && $client->checkins->count()) ? "table-success" : "" }}"
        data-bs-toggle="collapse"
        data-bs-target="#collapseCheckins-{{ $client->id }}"
        aria-expanded="false"
        aria-controls="collapseCheckins-{{ $client->id }}"
    >
        <td class="fw-bold">
            {{ $client->id }}
        </td>
        <td>
            {{ $client->name }}
        </td>
        <td>
            {{ $client->email }}
        </td>
        <td class="fw-bold text-xs">
            {{ $client->type }}
        </td>
        <td>
            <span class="btn btn-xs {{ $client->getStatusClass() }}">
                {{ $client->getStatusText() }}
            </span>
        </td>
        <td class="fw-bold">
            {{ $client->register_source }}
        </td>
        <td class="">
            {{ $client->updated_by ? $client->user->name : null }}
        </td>
        <td>
            @humanize_date($client->updated_at, 'd/m/Y H:i:s')
        </td>
        <td>
            @humanize_date($client->created_at, 'd/m/Y H:i:s')
        </td>
        @foreach ($customFieldTemplates as $templateName => $templateAttr)
            <td>
                {{ $client->getCustomFieldValue($templateAttr, false) }}
            </td>
        @endforeach
    </tr>
    @if (!empty($client->checkins) && $client->checkins->count())
        @php
            $count = 0;
        @endphp
        <tr class="collapse" id="collapseCheckins-{{ $client->id }}" style="display: ; width: 100%;">
            <td colspan="{{ $countCol }}">
                <table class="table table-borderless mb-0 w-100">
                    <thead>
                        <tr class="text-xs">
                            <th scope="col" class="col-1">#</th>
                            <th scope="col" class="col-1">LOẠI</th>
                            <th scope="col" class="col-1">THỜI GIAN</th>
                            <th scope="col" class="col-1">BỞI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($client->checkins as $checkin)
                            <tr class="text-xs">
                                <th scope="row" style="vertical-align: middle;">
                                    {{ ++$count }}
                                </th>
                                <td>
                                    {{ $checkin->type }}
                                </td>
                                <td>
                                    @humanize_date($checkin->scan_time, 'H:i:s d-m-Y')
                                </td>
                                <td>
                                    {{ $checkin->user_id ? "{$checkin->user->username} - {$checkin->user->email}" : null }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    @endif
@endforeach
