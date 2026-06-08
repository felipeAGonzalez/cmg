<script>
    (function () {
        const toggle = (selectId, wrapperId) => {
            const sel = document.getElementById(selectId);
            const wrap = document.getElementById(wrapperId);
            if (!sel || !wrap) return;
            const apply = () => { wrap.style.display = sel.value === 'other' ? 'block' : 'none'; };
            sel.addEventListener('change', apply);
            apply();
        };
        toggle('route', 'routeOtherWrapper');
        toggle('frequency', 'frequencyOtherWrapper');
    })();
</script>
