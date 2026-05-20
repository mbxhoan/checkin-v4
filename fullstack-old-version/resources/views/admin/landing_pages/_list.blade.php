@php
    $collapsible = $collapsible ?? true;
@endphp

<div class="{{ $collapsible ? 'collapse' : '' }} p-2" @if($collapsible) id="collapseLandingPages" @endif>
    @if (!empty($landingPages) && $landingPages->count())
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Slug (URL)</th>
                        {{-- <th style="width: 120px;">Trạng thái</th> --}}
                        <th class="text-end" style="width: 130px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($landingPages as $index => $landingPage)
                        <tr class="{{ $landingPage->is_default ? 'table-active' : '' }}">
                            <td class="text-center fw-bold">{{ ++$index }}</td>
                            <td>
                                <a class="fst-italic text-decoration-none d-inline-block text-truncate" 
                                   style="max-width: 350px;" 
                                   target="_blank" 
                                   id="lp-link-{{ $landingPage->id }}" 
                                   href="{{ $landingPage->getRegisterUrl() }}">
                                    {{ $landingPage->getRegisterUrl() }}
                                </a>
                            </td>
                            {{-- <td>
                                @include('components.select', [
                                    'fieldName'     => 'status',
                                    'id'            => "status-{$landingPage->id}",
                                    'options'       => $landingPage->getStatues(),
                                    'selected'      => $landingPage->status,
                                    'formClass'     => 'form-select form-select-sm'
                                ])
                            </td> --}}
                            <td class="text-end">
                                <button type="button" class="btn btn-xs btn-outline-secondary" 
                                        data-clipboard-target="#lp-link-{{ $landingPage->id }}" 
                                        title="Copy">
                                    <x-icon name="clipboard" prefix="fa-regular"/>
                                </button>
                                <a href="{{ route('admin.landing_pages.edit', [
                                        'event' => $landingPage->event,
                                        'landing_page' => $landingPage,
                                    ]) }}" target="_blank" 
                                   class="btn btn-xs btn-outline-primary" title="Sửa">
                                    <x-icon name="edit"/>
                                </a>
                                <a href="javascript:void(0)" id="{{ $landingPage->id }}"
                                   class="btn btn-xs btn-outline-danger btn-del-template"
                                   data-id="landing-page-{{ $landingPage->id }}"
                                   data-url="{{ route('admin.landing_pages.destroy', [
                                        'landing_page' => $landingPage
                                    ]) }}" title="Xóa">
                                    <x-icon name="trash"/>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="fst-italic small mb-2">Chưa có</div>
    @endif

    <div class="mt-2">
        <a href="{{ route('admin.landing_pages.create', $event) }}" class="btn btn-sm btn-outline-primary w-100">
            <x-icon name="plus-square" prefix="fa-regular"/>
            Thêm mới
        </a>
    </div>
</div>
@push('admin_js')
    <script>
        $(document).on("click", ".btn-del-template", function(e) {
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
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            Swal.fire("Đã xóa!", "Landing page đã được xóa.", "success")
                                .then(() => {
                                    location.reload(); // ✅ tải lại trang
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
