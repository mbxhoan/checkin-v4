@php
    $cfDists =($table_tron['cfDists'] ?? null);
@endphp
@if(!$cfDists || empty($cfDists['fields']))
    <div class="alert alert-info mb-0 text-xs">{{ __('reports.index.media.tron_no_data_alert') }}</div>
@else
    <div class="row">
        @foreach($cfDists['fields'] as $i => $field)
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header text-center text-xs fw-bold">
                        {{ $field['field']['label'] ?? __('reports.index.media.tron_default_chart_title') }}
                    </div>
                    <div class="card-body">
                        <canvas id="pieChart{{ $i }}" height="200"></canvas>
                    </div>
                </div>
            </div>
            {{-- 4 biểu đồ --}}
            @if(($i + 1) % 4 === 0)
                </div><div class="row">
            @endif
        @endforeach
    </div>
@endif
@push('admin_js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @foreach($cfDists['fields'] as $i => $field)
            new Chart(document.getElementById('pieChart{{ $i }}'), {
                type: 'pie',
                data: {
                    labels: @json($field['pie']['labels']),
                    datasets: [{
                        data: @json($field['pie']['values']),
                        backgroundColor: [
                            '#4e79a7','#f28e2b','#e15759',
                            '#76b7b2','#59a14f','#edc949',
                            '#af7aa1','#ff9da7','#9c755f',
                            '#1f77b4','#ff7f0e','#2ca02c',
                            '#d62728','#9467bd','#8c564b',
                            '#e377c2','#7f7f7f','#bcbd22','#17becf'
                        ],

                    }]
                },
                options: {
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    let label = ctx.label || '';
                                    let value = ctx.raw || 0;
                                    let pct = {{ json_encode($field['pie']['pcts']) }}[ctx.dataIndex] || 0;
                                    return `${label}: ${value} (${pct}%)`;
                                }
                            }
                        },
                        legend: { position: 'bottom' }
                    }
                }
            });
        @endforeach
    </script>
@endpush


