<!-- start page title -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                @if (isset($li_1))
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">{{ $li_1 }}</a></li>
                            @if (isset($title))
                                <li class="breadcrumb-item active">{{ $title }}</li>
                            @endif
                        </ol>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- end page title -->
