<script>
    (function () {
        const radios = document.querySelectorAll('.status-radio');
        const wrapper = document.getElementById('reasonWrapper');
        const reason = document.getElementById('reason');
        if (!radios.length || !wrapper || !reason) return;

        const apply = () => {
            const selected = document.querySelector('.status-radio:checked');
            const needsReason = selected && selected.value !== 'administered';
            wrapper.style.display = needsReason ? 'block' : 'none';
            reason.required = !!needsReason;
            if (!needsReason) reason.setCustomValidity('');
        };

        radios.forEach((r) => r.addEventListener('change', apply));
        apply();
    })();
</script>
