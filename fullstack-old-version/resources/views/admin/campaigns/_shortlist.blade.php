@if(!empty($campaigns) && $campaigns->count())
    <div class="p-2">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Tên Campaign</th>
                        <th class="text-end" style="width: 130px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $index => $campaign)
                        <tr>
                            <td class="text-center fw-bold">{{ ++$index }}</td>
                            <td class="fst-italic">{{ $campaign->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.campaigns.edit', [
                                        'event' => $event,
                                        'campaign' => $campaign,
                                    ]) }}" target="_blank" 
                                   class="btn btn-xs btn-outline-primary" title="Sửa">
                                    <x-icon name="edit"/>
                                </a>
                                <a href="javascript:void(0)" id="{{ $campaign->id }}"
                                   class="btn btn-xs btn-outline-danger btn-del-campaign"
                                   data-id="campaign-{{ $campaign->id }}"
                                   data-url="{{ route('admin.campaigns.destroy', [
                                        'campaign' => $campaign
                                    ]) }}" title="Xóa">
                                    <x-icon name="trash"/>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            <a href="{{ route('admin.campaigns.create', $event) }}" class="btn btn-sm btn-outline-primary w-100">
                <x-icon name="plus-square" prefix="fa-regular"/>
                Thêm mới
            </a>
        </div>
    </div>
@endif


@push('admin_js')
<script>
    $(document).on("click", ".btn-del-campaign", function(e) {
        e.preventDefault();

        let url = $(this).data("url");

        Swal.fire({
            title: "Bạn có chắc chắn?",
            text: "Hành động này không thể hoàn tác!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Xóa",
            cancelButtonText: "Hủy"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}",
                        confirm: "DELETE"
                    },
                    success: function() {
                        Swal.fire("Đã xóa!", "Campaign đã được xóa.", "success")
                            .then(() => {
                                location.reload();
                            });
                    },
                    error: function() {
                        Swal.fire("Lỗi!", "Không thể xóa. Vui lòng thử lại.", "error");
                    }
                });
            }
        });
    });
</script>
@endpush
