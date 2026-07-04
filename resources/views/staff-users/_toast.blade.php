@if(session('success') || session('error'))
<div class="users-toast" id="usersToast" role="status">
    <span class="users-toast__icon">
        @if(session('error'))
            <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        @endif
    </span>
    <div class="users-toast__body">{{ session('success') ?? session('error') }}</div>
    <button type="button" class="users-toast__close" id="usersToastClose" aria-label="Dismiss">&times;</button>
</div>
<script>
(function () {
    var toast = document.getElementById('usersToast');
    if (!toast) return;
    if ({{ session('error') ? 'true' : 'false' }}) {
        toast.style.borderLeftColor = '#dc2626';
    }
    requestAnimationFrame(function () { toast.classList.add('is-visible'); });
    var hide = function () {
        toast.classList.add('is-hiding');
        toast.classList.remove('is-visible');
        setTimeout(function () { toast.remove(); }, 400);
    };
    document.getElementById('usersToastClose')?.addEventListener('click', hide);
    setTimeout(hide, 5000);
})();
</script>
@endif
