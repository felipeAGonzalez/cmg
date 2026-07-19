@php
    $aldreteConfig = config('anesthesia_note_aldrete_scale');
    $totalId = 'aldrete_total_' . str_replace(['.', '[', ']'], '_', $fieldName);
@endphp

<div class="card mb-3">
    <div class="card-header" style="background-color:#fce4ec; color:#880e4f;">
        <strong>{{ $title }}</strong>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($aldreteConfig as $key => $criterion)
                <div class="col-12">
                    <label class="form-label small fw-semibold mb-1">{{ $criterion['label'] }}</label>
                    <select name="{{ $fieldName }}[{{ $key }}]"
                            class="form-select form-select-sm aldrete-select"
                            data-total="{{ $totalId }}"
                            onchange="recalcAldreteTotal('{{ $totalId }}')">
                        <option value="">— No evaluado —</option>
                        @foreach($criterion['options'] as $score => $desc)
                            <option value="{{ $score }}"
                                {{ (string)($currentValues[$key] ?? '') === (string)$score ? 'selected' : '' }}>
                                {{ $score }} — {{ $desc }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
        <div class="mt-2 text-end">
            <strong>Total Aldrete: <span id="{{ $totalId }}" class="badge bg-primary">—</span> / 10</strong>
        </div>
    </div>
</div>

<script>
(function() {
    function recalcAldreteTotal(totalId) {
        var selects = document.querySelectorAll('[data-total="' + totalId + '"]');
        var sum = 0;
        var allFilled = true;
        selects.forEach(function(sel) {
            if (sel.value === '') { allFilled = false; }
            else { sum += parseInt(sel.value, 10); }
        });
        var badge = document.getElementById(totalId);
        if (badge) {
            badge.textContent = allFilled ? sum : '—';
            badge.className = 'badge ' + (allFilled ? (sum >= 9 ? 'bg-success' : sum >= 7 ? 'bg-warning text-dark' : 'bg-danger') : 'bg-primary');
        }
    }
    window.recalcAldreteTotal = window.recalcAldreteTotal || recalcAldreteTotal;
    document.addEventListener('DOMContentLoaded', function() {
        recalcAldreteTotal('{{ $totalId }}');
    });
})();
</script>
