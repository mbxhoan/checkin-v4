<div class="col-12">
  <h6 class="text-uppercase text-muted fw-bold mb-2" style="letter-spacing:.5px;">Logo</h6>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event->id }}">
        <input type="hidden" name="company_id" value="{{ $event->company_id }}">
        <input type="hidden" name="name" value="{{ $event->name }}">
        <input type="hidden" name="status" value="{{ $event->status }}">

        <div class="row g-3 align-items-center">
          {{-- Input file --}}
          <div class="col-lg-7">
            @include('components.form-groups.input-group', [
              'id'        => 'logo',
              'label'     => 'Thêm logo cho sự kiện <span class="text-muted small">(khuyến nghị 400×400px)</span>',
              'model'     => $event,
              'type'      => 'file',
              'accept'    => '.png, .jpg, .jpeg',
              'formClass' => 'mb-0'
            ])
          </div>

          <div class="col-lg-5">
            <div class="d-flex flex-column align-items-center gap-2">
                <div class="rounded-3 border border-secondary-subtle p-2 bg-light-subtle d-flex justify-content-center align-items-center"
                    style="width: 100px; height: 100px;">
                @php
                    $logo = $event->logoUrl;
                    $logoUrl = $logo?->getUrl()
                        ?? asset(config('info.placeholders.image'));
                @endphp

                <img src="{{ $logoUrl }}"
                    alt="{{ $event->name ?? 'Logo sự kiện' }}"
                    class="img-fluid rounded-2"
                    style="max-height: 140px; object-fit: contain;">
                </div>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-sm btn-outline-primary w-100">
            <x-icon name="upload" /> Lưu
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
