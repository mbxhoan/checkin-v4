@extends('admin.layouts.templates.page-form', [
    'showBtns' => false
])

@if (!empty($file) && ($file->status == $file::STATUS_IMPORTED) || empty($file))
    @section('form-action', route('admin.clients.upload', $event))
@endif

@section('form-back', route('admin.clients.index', $event))
@section('title', 'Nạp file')

@section('primary-content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="">
                    <h4 class="card-title">Nạp danh sách khách mời</h4>
                    <p class="text-xs text-secondary">
                        Nạp danh sách khách mời <em>(.xlsx)</em> của <a href="{{ route('admin.events.edit', $event) }}" class="fw-bold">
                            {{ $event->name }}
                        </a> tại đây.
                    </p>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-6 text-sm">
                    <div class="">
                        <a href="{{ route('admin.clients.export-template-import', $event) }}" class="fst-italic">
                            Tải template
                            <x-icon name="download" />
                        </a>
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'        => "file",
                            'model'     => $model,
                            'type'      => "file",
                            'accept'    => ".xlsx",
                            'formClass' => 'mb-3 col-md-6'
                        ])
                        <div class="col-md-6">
                            @if (session()->has("import_clients_errors_{$event->id}"))
                                <a href=""
                                    class="btn btn-danger btn-sm align-self-center mb-lg-0 mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#errorLogFile"
                                >
                                    Xem lỗi nạp file
                                    <x-icon name="filter"/>
                                </a>
                            @endif
                            @if (!empty($file) && $file->error_log)
                                <a href=""
                                    class="btn btn-danger btn-sm align-self-center mb-lg-0 mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#errorLogFile"
                                >
                                    Xem lỗi nạp file
                                    <x-icon name="filter"/>
                                </a>
                            @endif
                        </div>
                    </div>
                    @include('components.form-groups.input-group', [
                        'id'            => "event_id",
                        'fieldName'     => "event_id",
                        'value'         => $event->id,
                        'type'          => "hidden",
                        'formClass'     => 'd-none',
                    ])
                    <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-sm btn-outline-secondary my-1">
                        <x-icon name="chevron-left" />
                        @lang('forms.actions.back')
                    </a>
                    <button id="" type="submit" class="btn btn-sm btn-outline-primary my-1">
                        <x-icon name="save" />
                        @lang('forms.actions.update')
                    </button>
                    @if (!empty($files) && $files->count())
                        <div class="mt-3">
                            <h6>Các lần nạp file gần đây</h6>
                            <ul class="list-group">
                                @foreach ($files as $process)
                                    <li class="list-group-item d-flex justify-content-between align-items-center text-xs">
                                        <div class="">
                                            <div class="fw-bold">
                                                {{ $process->created_at->format('d/m/Y H:i') }}
                                                @if ($process->status == $process::STATUS_IMPORTED)
                                                    <span class="badge bg-success ms-1">Đã nạp</span>
                                                @elseif ($process->status == $process::STATUS_NEW)
                                                    <span class="badge bg-warning text-dark ms-1">Đang xử lý</span>
                                                @else
                                                    <span class="badge bg-danger ms-1">Lỗi</span>
                                                @endif
                                            </div>
                                            <div class="">
                                                {{ $process->name ?? 'Không rõ tên file' }}
                                            </div>
                                            <div class="">
                                                Số lượng: {{ $process->total_record ?? 0 }}/{{ $process->total_record_before ?? 0 }}
                                            </div>
                                        </div>
                                        @if ($process->error_log)
                                            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#errorLogFile_{{ $process->id }}">
                                                Xem lỗi
                                            </a>
                                            @include('components.modal-content', [
                                                'modalId'           => 'errorLogFile_' . $process->id,
                                                'title'             => "Lỗi nạp file",
                                                'modalClass'        => 'modal-lg modal-dialog-centered modal-dialog-scrollable',
                                                'modalBodyClass'    => 'text-sm',
                                                'content'           => view('components.tables.import-errors', [
                                                    'errors'        => json_decode($process->error_log, true)
                                                ])->render()
                                            ])
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                    <div class="col-md-6">
                    @if (!empty($file) && ($file->status == $file::STATUS_NEW))
                        <h6>
                            Tiến trình tải file
                        </h6>
                        <div id="progress">
                            @include('components._progress', [
                                'total'     => $file->total_record,
                                'completed' => $file->total_record_before,
                                'dataTime'  => 5, // giây
                                'dataEle'   => '#progress',
                                'dataUrl'   => route('admin.imp_exp_files.progress', [
                                    'imp_exp_file' => $file,
                                ]),
                            ])
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="autoShowModal" tabindex="-1" aria-labelledby="autoShowModalLabel" aria-hidden="true"
                            data-bs-backdrop="static"
                            data-bs-keyboard="false"
                        >
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        {{-- <h1 class="modal-title fs-5" id="autoShowModalLabel">

                                        </h1> --}}
                                    </div>
                                    <div class="modal-body">
                                        <div class="fst-italic">
                                            <i class="fa-solid fa-spinner fa-spin"></i>
                                            Vui lòng chờ trong giây lát...
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Single shared error modal for the latest file (button uses #errorLogFile). --}}
    @if (session()->has("import_clients_errors_{$event->id}"))
        @include('components.modal-content', [
            'modalId'           => 'errorLogFile',
            'title'             => "Lỗi nạp file",
            'modalClass'        => 'modal-lg modal-dialog-centered modal-dialog-scrollable',
            'modalBodyClass'    => 'text-sm',
            'content'           => view('components.tables.import-session-errors', [
                'key'           => "import_clients_errors_{$event->id}"
            ])->render()
        ])
    @elseif (!empty($file) && $file->error_log)
        @include('components.modal-content', [
            'modalId'           => 'errorLogFile',
            'title'             => "Lỗi nạp file",
            'modalClass'        => 'modal-lg modal-dialog-centered modal-dialog-scrollable',
            'modalBodyClass'    => 'text-sm',
            'content'           => view('components.tables.import-errors', [
                'errors'        => json_decode($file->error_log, true)
            ])->render()
        ])
    @endif

    @if ($errors->any())
        <div id="import-autoshow-error" data-autoshow="1" class="d-none"></div>
    @endif
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/clients/import.js'
    ])
@endpush
