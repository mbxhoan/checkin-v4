@props([
    'steps' => [], // mảng các step: [['id'=>1,'label'=>'Thông tin cơ bản'], ...]
    'current' => 1, // step đang active
])
<div class=" mx-auto">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-center gap-2">
                @foreach ($steps as $index => $step)
                    <div id="badge-step-{{ $step['id'] }}"
                        class="badge {{ $current == $step['id'] ? 'bg-primary' : 'bg-secondary' }}">
                        {{ $step['id'] }}
                    </div>
                    <span id="label-step-{{ $step['id'] }}"
                        class="{{ $current == $step['id'] ? 'fw-semibold' : 'text-muted' }}"> {{ $step['label'] }}
                    </span>
                    @if (!$loop->last)
                        <i class="fa-solid fa-angles-right text-muted mx-2"></i>
                    @endif
                @endforeach
            </div>
            @php
                $ids = collect($steps)->pluck('id')->values();
                $pos = max(1, ($ids->search($current) ?? 0) + 1); // 1..N
                $total = max(count($steps), 1);
                $percent = ($pos / $total) * 100;
            @endphp

            <div class="progress mt-2 mx-auto" style="height:6px; max-width:500px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;"
                    aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const stepEls = Array.from(document.querySelectorAll('[id^="step-"]'));
        const progressBar = document.querySelector('.progress .progress-bar');
        const currentStepInput = document.getElementById('current_step');
        const intentInput = document.getElementById('intent');
        const btnSubmit = document.getElementById('btn-submit');
        const badges = document.querySelectorAll('[id^="badge-step-"]');
        const labels = document.querySelectorAll('[id^="label-step-"]');

        const stepIds = stepEls.map(el => Number(el.id.replace('step-', '')));
        const totalSteps = stepIds.length || 1;

        let currentStep = Number(currentStepInput?.value || stepIds[0] || 1);
        if (!stepIds.includes(currentStep)) currentStep = stepIds[0] || 1;

        const posOf = id => Math.max(0, stepIds.indexOf(id)); // 0..N-1

        const goStep = (n) => {
            if (!stepIds.includes(n)) return;
            currentStep = n;
            if (currentStepInput) currentStepInput.value = String(n);

            // show/hide content
            stepEls.forEach(el => {
                const id = Number(el.id.replace('step-', ''));
                el.classList.toggle('d-none', id !== n);
            });

            // progress
            if (progressBar) {
                const percent = ((posOf(n) + 1) / totalSteps) * 100;
                progressBar.style.width = `${percent}%`;
                progressBar.setAttribute('aria-valuenow', String(percent));
            }

            // intent + submit
            if (intentInput) intentInput.value = (posOf(n) < totalSteps - 1) ? 'save_and_next' :
                'save_finish';
            if (btnSubmit) btnSubmit.classList.toggle('d-none', n !== totalSteps);

            // badges/labels
            badges.forEach(b => {
                const id = Number(b.id.replace('badge-step-', ''));
                const active = id === n;
                b.classList.toggle('bg-primary', active);
                b.classList.toggle('bg-secondary', !active);
            });
            labels.forEach(l => {
                const id = Number(l.id.replace('label-step-', ''));
                const active = id === n;
                l.classList.toggle('fw-semibold', active);
                l.classList.toggle('text-muted', !active);
            });
        };

        // Event delegation cho prev/next
        document.addEventListener('click', (e) => {
            const nextBtn = e.target.closest('[data-next]');
            const prevBtn = e.target.closest('[data-prev]');

            if (nextBtn) {
                const currentEl = document.getElementById(`step-${currentStep}`);
                const requiredFields = currentEl ? currentEl.querySelectorAll(
                    'input[required], textarea[required], select[required]') : [];
                const firstInvalid = Array.from(requiredFields).find(f => !f.checkValidity());
                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                    firstInvalid.classList.add('is-invalid');
                    return;
                }
                const p = posOf(currentStep);
                if (p < totalSteps - 1) goStep(stepIds[p + 1]);
            }

            if (prevBtn) {
                const p = posOf(currentStep);
                if (p > 0) goStep(stepIds[p - 1]);
            }
        });

        goStep(currentStep);
    });
</script>
