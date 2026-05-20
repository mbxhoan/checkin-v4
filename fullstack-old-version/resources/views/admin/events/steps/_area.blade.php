<form id="areas-form" method="POST" action="{{ route('admin.event_areas.store') }}" class="">
    @csrf
    <input type="hidden" name="event_id" value="{{ $event->id }}">
    <div id="areas-list">
        <!-- Existing areas can be listed here if needed -->
    </div>
    <div id="new-area-inputs">
        <h6>Quản lý khu vực</h6>
        <div class="row">
            <div class="mb-2 col-md-4">
                <input type="text" name="areas[0][name]" class="form-control text-sm area-name" placeholder="Khu vực">
            </div>
            <div class="mb-2 col-md-4">
                <input type="text" name="areas[0][desc]" class="form-control text-sm area-desc" placeholder="Mô tả">
            </div>
            <div class="mb-2 col-md-4">
                <input type="text" name="areas[0][note]" class="form-control text-sm area-note" placeholder="Ghi chú">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-sm btn-primary mt-3">Lưu</button>
</form>

<script>
    let areaIndex = 1;

    function addNewAreaInputs() {
        const newAreaInputs = document.createElement('div');
        newAreaInputs.innerHTML = `
            <hr>
            <div class="row">
                <div class="mb-2 col-md-4">
                    <input type="text" name="areas[${areaIndex}][name]" class="form-control text-sm area-name" placeholder="Khu vực">
                </div>
                <div class="mb-2 col-md-4">
                    <input type="text" name="areas[${areaIndex}][desc]" class="form-control text-sm area-desc" placeholder="Mô tả">
                </div>
                <div class="mb-2 col-md-4">
                    <input type="text" name="areas[${areaIndex}][note]" class="form-control text-sm area-note" placeholder="Ghi chú">
                </div>
            </div>
        `;
        document.getElementById('new-area-inputs').appendChild(newAreaInputs);
        areaIndex++;
    }

    document.getElementById('new-area-inputs').addEventListener('input', function(e) {
        // Check if the last set of inputs has any value, then add new inputs
        const inputs = this.querySelectorAll('input');
        const lastSet = Array.from(inputs).slice(-3);
        if (lastSet.some(input => input.value.trim() !== '')) {
            // Prevent adding multiple sets for the same input
            if (inputs.length / 3 === areaIndex) {
                addNewAreaInputs();
            }
        }
    });
</script>
