@include('components.label_details._single-print', [
    'label'             => $label,
    'labelDetails'      => $labelDetails ?? null,
    'client'            => $client ?? null,
])
@if (!empty($event) && !empty($clients))
    @include('components.label_details._multi-print', [
        'label'         => $label,
        'client'        => $client,
        'clients'       => $clients,
        'labelDetails'  => $labelDetails ?? null,
    ])
@endif
@include('components.label_details.style', [
    'label'             => $label
])
