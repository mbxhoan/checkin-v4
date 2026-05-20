@if (!empty($cards) && $cards->count())
    <div class="p-2">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">{{ __('cards._shortlist.th_index') }}</th>
                        <th>{{ __('cards._shortlist.th_card_name') }}</th>
                        {{-- <th>Trạng thái</th> --}}
                        <th class="text-end" style="width: 130px;">{{ __('cards._shortlist.th_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cards as $index => $card)
                        <tr>
                            <td class="text-center fw-bold">{{ ++$index }}</td>
                            <td class="fst-italic">{{ $card->code }}</td>
                            {{-- <td>
                                <span class="badge bg-{{ $card->status === 'ACTIVE' ? 'success' : 'secondary' }}">
                                    {{ $card->status }}
                                </span>
                            </td> --}}
                            <td class="text-end">
                                <a href="{{ route('admin.cards.edit', [
                                        'event' => $event,
                                        'card' => $card,
                                    ]) }}" target="_blank" 
                                   class="btn btn-xs btn-outline-primary" title="{{ __('cards._shortlist.action_edit_title') }}">
                                    <x-icon name="edit"/>
                                </a>
                                <a href="javascript:void(0)" id="card-{{ $card->id }}"
                                   class="btn btn-xs btn-outline-danger btn-del-card"
                                   data-id="card-{{ $card->id }}"
                                   data-url="{{ route('admin.cards.destroy', [
                                        'card' => $card
                                    ]) }}" 
                                   title="{{ __('cards._shortlist.action_delete_title') }}">
                                    <x-icon name="trash"/>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    
        <div class="mt-2">
            <a href="{{ route('admin.cards.create', $event) }}" class="btn btn-sm btn-outline-primary w-100">
                <x-icon name="plus-square" prefix="fa-regular"/> {{ __('cards._shortlist.action_view_report') }}
            </a>
        </div>
    </div>
@endif

@push('admin_js')
<script>
    $(document).on("click", ".btn-del-card", function(e) {
        e.preventDefault();

        let url = $(this).data("url");

        Swal.fire({
            title: "{{ __('cards._shortlist.confirm_delete_title') }}",
            text: "{{ __('cards._shortlist.confirm_delete_text') }}",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "{{ __('cards._shortlist.confirm_delete_confirm') }}",
            cancelButtonText: "{{ __('cards._shortlist.confirm_delete_cancel') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire("{{ __('cards._shortlist.delete_success_title') }}", "{{ __('cards._shortlist.delete_success_text') }}", "success")
                            .then(() => {
                                location.reload(); 
                            });
                    },
                    error: function() {
                        Swal.fire("{{ __('cards._shortlist.delete_error_title') }}", "{{ __('cards._shortlist.delete_error_text') }}", "error");
                    }
                });
            }
        });
    });
</script>
@endpush
