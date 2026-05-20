{{-- resources/views/admin/reports/report_table/table_5.blade.php --}}
@php
  $rows  = $table_5['listClientNotCheckin'] ?? [];
  $total = is_countable($rows) ? count($rows) : (method_exists($rows, 'count') ? $rows->count() : 0);
@endphp

@if($total > 0)
  <div class="card mb-2" style="min-height: 250px">
    <div class="card-body">
      <h5 class="card-title mb-3">
        {{ __('reports.index.media.not_checkedin_list_title') }}
        <small class="text-danger">— {{ __('reports.index.media.not_checkedin_list_total_label') }} {{ number_format($total) }}</small>
      </h5>

      <div class="table-responsive" style="max-height: {{ $total > 10 ? '400px' : 'auto' }}; overflow-y: auto;">
          {{-- Bảng dữ liệu --}}
        <table class="table table-sm align-middle mb-0 font-size-11">
          <thead>
            <tr>
              <th style="width:56px">#</th>
                        {{-- <th>QR Code</th> --}}
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.not_checkedin_status_label') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.not_checkedin_name_label') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.not_checkedin_email_label') }}</th>
              <th style="min-width: 150px; white-space: nowrap">{{ __('reports.index.media.not_checkedin_source_label') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.not_checkedin_type_label') }}</th>
            </tr>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              @php
                $email = $r->email ?? '';
                $source = trim($r->register_source ?? '');
                $source = ($source === '') ? __('reports.index.media.not_checkedin_source_empty') : $source;
              @endphp
              <tr>
                <td>{{ is_numeric($i) ? $i + 1 : '' }}</td>
                <td>
                  <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-2 py-1">
                      {{ __('reports.index.media.not_checkedin_status_badge') }}
                  </span>
                </td>
                {{-- <td class="font-monospace">{{ $r->qrcode ?? '' }}</td> --}}
                <td>{{ $r->name ?? '' }}</td>
                <td>
                  @if($email !== '')
                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                  @endif
                </td>
                <td>{{ $source }}</td>
                <td>{{ $r->type ?? '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">{{ __('reports.index.media.not_checkedin_table_empty') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endif
