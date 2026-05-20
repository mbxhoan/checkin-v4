@extends('admin.layouts.templates.page')

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.email_senders.store'))
@section('form-back', route('admin.email_senders.index'))
@section('title', 'Tạo mới Sender')

@section('buttons')
@endsection

@section('primary-content')
<form id="{{ $formId ?? 'form-create-sender' }}"
      action="@yield('form-action')"
      class="{{ $formClass ?? '' }}"
      method="POST">
    @csrf

    <div class="col-lg-6 col-md-8 mx-auto">
        <x-stepper :steps="[
            ['id'=>1,'label'=>'Thông tin bắt buộc'],
            ['id'=>2,'label'=>'Tuỳ chọn & Xác nhận'],
        ]" :current="$openStep" />

        <input type="hidden" id="current_step" name="current_step" value="{{ $openStep }}">
        <input type="hidden" id="intent" name="intent" value="">

        <x-card>
            {{-- STEP 1:  --}}
            <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
                <div class="col-12 mb-3">
                    @include('components.form-groups.input-group', [
                        'id'                => "from_email",
                        'name'              => "from_email",
                        'type'              => "email",
                        'value'             => old('from_email') ,
                        'label'             => "“From” email",
                        'formClass'         => "",
                        'placeholder'       => 'no-reply@yourdomain.com',
                        'required'          => true,
                    ])
                    <div class="form-text">
                        Email này sẽ hiển thị ở mục From và sẽ nhận được thư xác nhận của Postmark.
                    </div>
                </div>
                <div class="col-12 mb-3">
                    @include('components.form-groups.input-group', [
                        'id'                => "from_name",
                        'name'              => "from_name",
                        'type'              => "text",
                        'value'             => old('from_name') ,
                        'label'             => "“From” name",
                        'formClass'         => "",
                        'placeholder'       => 'Checkin Delfi',
                        'required'          => true,
                    ])
                    <div class="form-text">
                        Ví dụ: “Checkin Delfi” → người nhận sẽ thấy <code>Checkin Delfi &lt;no-reply@yourdomain.com&gt;</code>.
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-primary" data-next>
                        Tiếp tục <i class="fa-solid fa-arrow-right-long ms-1"></i>
                    </button>
                </div>
            </div>

            {{-- STEP 2: --}}
            <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
                <div class="col-12 mb-3">
                    @include('components.form-groups.input-group', [
                        'id'                => "reply_to",
                        'name'              => "reply_to",
                        'type'              => "email",
                        'value'             => old('reply_to') ,
                        'label'             => "“Reply-To” email (tuỳ chọn)",
                        'formClass'         => "",
                        'placeholder'       => 'support@yourdomain.com',
                        'required'          => false,
                    ])
                    <div class="form-text">
                         Nếu người nhận bấm “Trả lời”, email sẽ gửi tới địa chỉ này.
                    </div>
                </div>
                <div class="col-12 mb-3">
                    @include('components.form-groups.input-group', [
                        'id'                => "personal_note",
                        'name'              => "personal_note",
                        'type'              => "text",
                        'value'             => old('personal_note') ,
                        'label'             => "Personal note (tuỳ chọn)",
                        'formClass'         => "",
                        'placeholder'       => "Xin vui lòng xác nhận để hệ thống Checkin Delfi có thể gửi thiệp và QR code. Cảm ơn!",
                        'required'          => false,
                    ])
                    <div class="form-text">
                         Ghi chú cá nhân sẽ xuất hiện trong email xác nhận (branding Postmark).
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <button type="button" class="btn btn-light" data-prev>
                        <i class="fa-solid fa-arrow-left-long me-1"></i> Quay lại
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-submit">
                        <x-icon name="save" />
                        <span>Lưu</span>
                    </button>
                </div>
            </div>

        </x-card>
    </div>
</form>
@endsection

@section('secondary-content')
@endsection

{{-- @push('admin_js')
<script>
(function() {
  const $step1 = document.getElementById('step-1');
  const $step2 = document.getElementById('step-2');
  const $current = document.getElementById('current_step');

  // mini validator cho From email
  function isEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v||'').trim()); }

  // next
  document.querySelectorAll('[data-next]').forEach(btn => {
    btn.addEventListener('click', function(e){
      const fromEmail = document.getElementById('from_email').value;
      if (!isEmail(fromEmail)) {
        alert('Vui lòng nhập “From email” hợp lệ trước khi tiếp tục.');
        document.getElementById('from_email').focus();
        return;
      }
      $step1.classList.add('d-none');
      $step2.classList.remove('d-none');
      if ($current) $current.value = 2;
    });
  });

  // prev
  document.querySelectorAll('[data-prev]').forEach(btn => {
    btn.addEventListener('click', function(e){
      $step2.classList.add('d-none');
      $step1.classList.remove('d-none');
      if ($current) $current.value = 1;
    });
  });
})();
</script>
@endpush --}}
