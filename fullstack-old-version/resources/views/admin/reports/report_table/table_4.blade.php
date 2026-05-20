@php
  $rows = $table_4['listClientCheckin'] ?? [];
  $total = is_countable($rows) ? count($rows) : (method_exists($rows, 'count') ? $rows->count() : 0);
@endphp
@if($total > 0)
  <div class="card mb-2" style="min-height: 250px">
    <div class="card-body">
      <h5 class="card-title mb-3">
        {{ __('reports.index.media.checkedin_list_title') }}
        <small class="text-danger">— {{ __('reports.index.media.checkedin_list_total_label') }} {{ number_format($total) }}</small>
      </h5>

      <div class="table-responsive" style="max-height: {{ $total > 10 ? '400px' : 'auto' }}; overflow-y: auto;">
        {{-- Bảng dữ liệu --}}
        <table class="table table-sm align-middle mb-0 font-size-11">
          <thead>
            <tr>
              <th>#</th>
              {{-- <th>QR Code</th> --}}
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkedin_status_label') }}</th>
              <th style="min-width: 130px; white-space: nowrap">{{ __('reports.index.media.checkedin_name_label') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkedin_email_label') }}</th>
              <th style="min-width: 150px; white-space: nowrap">{{ __('reports.index.media.checkedin_source_label') }}</th>
              <th style="min-width: 170px; white-space: nowrap">{{ __('reports.index.media.checkedin_time_label') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkedin_user_id_label') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkedin_username_label') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ is_numeric($i) ? $i + 1 : '' }}</td>
                <td>
                  <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1">
                      {{ $r->type ?? '' }}
                  </span>
                </td>
                {{-- <td class="font-monospace">{{ $r->qrcode ?? '' }}</td> --}}
                <td>{{ $r->name ?? '' }}</td>
                <td>
                  @if(!empty($r->email))
                    <a href="mailto:{{ $r->email }}">{{ $r->email }}</a>
                  @endif
                </td>
                <td>{{ $r->register_source ?? '' }}</td>
                <td>
                  {{-- nếu đã select alias scan_time thì hiển thị trực tiếp --}}
                  {{ $r->scan_time ?? ($r->created_at ?? '') }}
                </td>
                <td>{{ $r->username ?? '' }}</td>
                <td>
                  {{-- nếu query có alias: u.username as staff_name thì có staff_name; nếu không thì dùng username --}}
                  {{ $r->staff_name ?? $r->username ?? '' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted">{{ __('reports.index.media.checkedin_table_empty') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Nếu $rows là paginator, có thể hiện phân trang --}}
      @if(method_exists($rows, 'links'))
        <div class="mt-2">
          {{ $rows->links() }}
        </div>
      @endif
    </div>
  </div>
@endif
