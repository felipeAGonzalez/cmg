<script>
(function() {
    var form = document.getElementById('triage-form');

    var aNames = ['trauma_score','wound_score','respiratory_difficulty_score',
                  'cyanosis_score','paleness_score','hemorrhage_score','pain_score',
                  'intoxication_score','seizures_score','glasgow_score',
                  'dehydration_score','psychosis_score'];

    var bNames = ['bp_score','hr_score','rr_score','temp_score','glucose_score'];

    function recalculate() {
        var immediateChecks = document.querySelectorAll('.immediate-check');
        var hasImmediate = false;
        immediateChecks.forEach(function(cb) { if (cb.checked) hasImmediate = true; });

        var sumA = 0;
        aNames.forEach(function(name) {
            var checked = document.querySelector('input[name="' + name + '"]:checked');
            var val = checked ? parseInt(checked.value, 10) : 0;
            sumA += val;
            var badge = document.querySelector('[data-points-for="' + name + '"]');
            if (badge) badge.textContent = val;
        });

        var sumB = 0;
        bNames.forEach(function(name) {
            var checked = document.querySelector('input[name="' + name + '"]:checked');
            var val = checked ? parseInt(checked.value, 10) : 0;
            sumB += val;
            var badge = document.querySelector('[data-points-for="' + name + '"]');
            if (badge) badge.textContent = val;
        });

        var total = sumA + sumB;

        var color, colorClass, decision, site;
        if (hasImmediate) {
            color = 'Rojo'; colorClass = 'bg-danger';
            decision = 'Reanimación'; site = 'Choque';
        } else if (total <= 10) {
            color = 'Azul'; colorClass = 'bg-primary';
            decision = 'Sin urgencia'; site = 'Consultorio';
        } else if (total <= 20) {
            color = 'Verde'; colorClass = 'bg-success';
            decision = 'Urgencia menor'; site = 'Primer contacto';
        } else if (total <= 30) {
            color = 'Amarillo'; colorClass = 'bg-warning text-dark';
            decision = 'Urgencia'; site = 'Observación';
        } else if (total <= 40) {
            color = 'Naranja'; colorClass = 'bg-orange';
            decision = 'Emergencia'; site = 'Estabilización';
        } else {
            color = 'Rojo'; colorClass = 'bg-danger';
            decision = 'Reanimación'; site = 'Choque';
        }

        document.getElementById('sum-a').textContent = sumA;
        document.getElementById('sum-b').textContent = sumB;
        document.getElementById('total-score').textContent = total;

        var colorBadge = document.getElementById('color-badge');
        colorBadge.textContent = color;
        colorBadge.className = 'badge fs-3 ' + colorClass;

        document.getElementById('decision-text').textContent = decision;
        document.getElementById('site-text').textContent = site;

        var banner = document.getElementById('immediate-alert-banner');
        if (hasImmediate) {
            banner.classList.remove('d-none');
        } else {
            banner.classList.add('d-none');
        }
    }

    form.addEventListener('change', recalculate);
    recalculate();

    document.getElementById('btn-mark-normal').addEventListener('click', function() {
        if (!confirm('¿Marcar todos los criterios como Ausente/Normal? Esto sobrescribe los valores actuales pero NO los signos vitales ni alertas inmediatas.')) {
            return;
        }

        aNames.forEach(function(name) {
            var radio = document.querySelector('input[name="' + name + '"][value="0"]');
            if (radio) radio.checked = true;
        });

        bNames.forEach(function(name) {
            var radio = document.querySelector('input[name="' + name + '"][data-col-key="normal"]');
            if (radio) radio.checked = true;
        });

        recalculate();
    });
})();
</script>
