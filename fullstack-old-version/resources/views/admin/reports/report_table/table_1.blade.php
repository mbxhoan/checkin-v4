@php
    // Lấy map số liệu
    $reg = $table_1['registerByType'] ?? [];
    $chk = $table_1['checkinByType'] ?? [];

    // Gom tất cả type xuất hiện ở 2 phía
    $types = array_unique(array_merge(array_keys($reg), array_keys($chk)));

    // Sort theo số đăng ký giảm dần (giữ hàng "Tổng" riêng)
    usort($types, function ($a, $b) use ($reg) {
        return ($reg[$b] ?? 0) <=> ($reg[$a] ?? 0);
    });

    // Tổng toàn bảng
    $totalRegistered = (int) ($table_1['totalClients'] ?? 0);
    $totalChecked    = (int) ($table_1['totalClientCheckedIn'] ?? 0);
@endphp

<div class="card mb-2" style="min-height: 250px">
  <div class="card-body">
    <h5 class="card-title mb-3">{{ __('reports.index.media.table1_title') }}</h5>

    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>{{ __('reports.index.media.table_header_type') }}</th>
            <th class="text-end">{{ __('reports.index.media.table_header_registered') }}</th>
            <th class="text-end">{{ __('reports.index.media.table_header_checked_in') }}</th>
          </tr>
        </thead>

        <tbody>
          {{-- Hàng tổng --}}
          <tr>
            <td><strong>{{ __('reports.index.media.table_total_guests_label') }}</strong></td>
            <td class="text-end"><strong>{{ number_format($totalRegistered) }}</strong></td>
            <td class="text-end"><strong>{{ number_format($totalChecked) }}</strong></td>
          </tr>

          {{-- Các hàng theo type --}}
          @forelse($types as $t)
            <tr>
              <td>{{ __('reports.index.media.guest_type_prefix') }} {{ $t }}</td>
              <td class="text-end">{{ number_format($reg[$t] ?? 0) }}</td>
              <td class="text-end">{{ number_format($chk[$t] ?? 0) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">{{ __('reports.index.media.table_empty_by_guest_type') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
