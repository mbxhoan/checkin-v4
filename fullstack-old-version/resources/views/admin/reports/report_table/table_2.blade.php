@php
  $tab2 = $table_2 ?? [];

  // Map số liệu
  $reg = (array) ($tab2['totalClientRegisterBySource'] ?? []);
  $chk = (array) ($tab2['totalClientCheckedInBySource'] ?? []);

  // Gom tất cả nguồn xuất hiện ở 2 phía
  $sources = array_unique(array_merge(array_keys($reg), array_keys($chk)));

  // Sort theo số đăng ký giảm dần
  usort($sources, fn($a, $b) => ($reg[$b] ?? 0) <=> ($reg[$a] ?? 0));

  // Tổng toàn bảng (fallback tính tổng nếu không có key tổng)
  $totalRegistered = (int) ($tab2['totalClients'] ?? array_sum($reg));
  $totalChecked    = (int) ($tab2['totalClientCheckedIn'] ?? array_sum($chk));

  // Label hiển thị cho nguồn rỗng/null
  $srcLabel = function ($s) {
      return ($s === null || $s === '') ? __('reports.index.media.unknown_label') : $s;
  };
@endphp

<div class="card mb-2" style="min-height: 250px">
  <div class="card-body">
    <h5 class="card-title mb-3">{{ __('reports.index.media.table2_title') }}</h5>

    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>{{ __('reports.index.media.table_header_registration_source') }}</th>
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

          {{-- Các hàng theo nguồn --}}
          @forelse($sources as $src)
            <tr>
              <td>{{ $srcLabel($src) }}</td>
              <td class="text-end">{{ number_format($reg[$src] ?? 0) }}</td>
              <td class="text-end">{{ number_format($chk[$src] ?? 0) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">{{ __('reports.index.media.table_empty_by_registration_source') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
