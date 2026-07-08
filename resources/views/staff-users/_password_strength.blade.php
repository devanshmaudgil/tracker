{{-- Live password strength meter — include after password input field --}}
<div id="passwordStrength" class="pwd-strength" hidden aria-live="polite">
    <div class="pwd-strength__head">
        <span class="pwd-strength__label">Password strength</span>
        <span class="pwd-strength__value" id="passwordStrengthLabel">—</span>
    </div>
    <div class="pwd-strength__bar" role="progressbar" aria-valuemin="0" aria-valuemax="4" aria-valuenow="0" id="passwordStrengthBar">
        <span class="pwd-strength__segment"></span>
        <span class="pwd-strength__segment"></span>
        <span class="pwd-strength__segment"></span>
        <span class="pwd-strength__segment"></span>
    </div>
    <ul class="pwd-strength__checks" id="passwordStrengthChecks">
        <li data-check="length">At least 8 characters</li>
        <li data-check="uppercase">One uppercase letter (A–Z)</li>
        <li data-check="number">One number (0–9)</li>
        <li data-check="special">One special character (!@#$…)</li>
    </ul>
</div>

<script>
(function () {
    const input = document.getElementById('password');
    const meter = document.getElementById('passwordStrength');
    const labelEl = document.getElementById('passwordStrengthLabel');
    const barEl = document.getElementById('passwordStrengthBar');
    const checksEl = document.getElementById('passwordStrengthChecks');

    if (!input || !meter) return;

    function analyze(password) {
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password),
        };
        const met = Object.values(checks).filter(Boolean).length;
        let score = 1;
        let label = 'Weak';

        if (met < 2 || password.length < 6) {
            score = 1;
            label = 'Weak';
        } else if (met === 2 || (met === 3 && password.length < 8)) {
            score = 2;
            label = 'Fair';
        } else if (met === 3 || (met === 4 && password.length < 12)) {
            score = 3;
            label = 'Good';
        } else {
            score = 4;
            label = 'Strong';
        }

        return { score, label, checks };
    }

    function render() {
        const value = input.value;
        if (!value) {
            meter.hidden = true;
            return;
        }

        meter.hidden = false;
        const result = analyze(value);

        labelEl.textContent = result.label;
        labelEl.dataset.level = result.label.toLowerCase();
        barEl.dataset.level = result.label.toLowerCase();
        barEl.setAttribute('aria-valuenow', String(result.score));

        barEl.querySelectorAll('.pwd-strength__segment').forEach((seg, i) => {
            seg.classList.toggle('is-active', i < result.score);
        });

        checksEl.querySelectorAll('li').forEach(li => {
            const key = li.dataset.check;
            li.classList.toggle('is-met', !!result.checks[key]);
        });
    }

    input.addEventListener('input', render);
    input.addEventListener('focus', render);
    if (input.value) render();
})();
</script>
