{{-- resources/views/admin/reports/report_table/table_3.blade.php --}}
@php
  $rows = $table_3['listClientCheckout'] ?? [];
  $total = is_countable($rows) ? count($rows) : (method_exists($rows, 'count') ? $rows->count() : 0);
@endphp

@if($total > 0)
  <div class="card mb-2" style="min-height: 250px">
    <div class="card-body">
      <h5 class="card-title mb-3">
        {{ __('reports.index.media.checkout_list_title') }}
        <small class="text-muted">— {{ __('reports.index.media.checkout_list_total_label') }} {{ number_format($total) }}</small>
      </h5>

      <div class="table-responsive" stype="max-height: {{ $total > 10 ? '400px' : 'auto' }}; overflow-y: auto;">
        {{-- Bảng dữ liệu --}}
        <table class="table table-sm align-middle font-size-11">
          <thead>
            <tr>
              <th>#</th>
              {{-- <th>QR Code</th> --}}
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_name') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_email') }}</th>
              <th style="min-width: 150px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_source') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_type') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_time') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_user_id') }}</th>
              <th style="min-width: 100px; white-space: nowrap">{{ __('reports.index.media.checkout_table_header_username') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $i => $r)
              <tr>
                <td>{{ is_numeric($i) ? $i + 1 : '' }}</td>
                {{-- <td class="font-monospace">{{ $r->qrcode ?? '' }}</td> --}}
                <td>{{ $r->name ?? '' }}</td>
                <td>
                  @if(!empty($r->email))
                    <a href="mailto:{{ $r->email }}">{{ $r->email }}</a>
                  @endif
                </td>
                <td>{{ $r->register_source ?? '' }}</td>
                <td>{{ $r->type ?? '' }}</td>
                <td>
                  {{-- nếu đã select alias scan_time thì hiển thị trực tiếp --}}
                  {{ $r->scan_time ?? ($r->created_at ?? '') }}
                </td>
                <td>{{ $r->user_id ?? '' }}</td>
                <td>
                  {{-- nếu query có alias: u.username as staff_name thì có staff_name; nếu không thì dùng username --}}
                  {{ $r->staff_name ?? $r->username ?? '' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted">{{ __('reports.index.media.checkout_table_empty') }}</td>
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
