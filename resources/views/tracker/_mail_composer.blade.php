{{-- Mail Composer Modal for Candidate Initialization --}}
<div id="mailComposerModal" class="modal-overlay" onclick="closeOnBackdrop(event, 'mailComposerModal')">
    <div class="modal-box mail-composer-box" onclick="event.stopPropagation()">
        <div class="modal-head mail-composer-head">
            <div class="mail-composer-head__inner">
                <svg class="mail-composer-head__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <h3 class="mail-composer-head__title">
                    <span class="mail-composer-head__label">Initialization Mail</span>
                    <span id="mailComposerCandidateName" class="mail-composer-sub"></span>
                </h3>
            </div>
            <button type="button" class="modal-close" onclick="closeMailComposer()">×</button>
        </div>

        <div class="modal-body mail-composer-body">
            <div class="mail-field-grid">
                <div class="mail-field">
                    <label for="mailFrom">From</label>
                    <input type="email" id="mailFrom" value="{{ $recruiterEmail ?? '' }}" placeholder="name@rinfinite.com">
                </div>
                <div class="mail-field">
                    <label for="mailTo">To</label>
                    <input type="email" id="mailTo" placeholder="candidate@email.com">
                </div>
                <div class="mail-field mail-field--full">
                    <label for="mailCc">CC</label>
                    <select id="mailCc" multiple>
                        @foreach($staffEmails as $staff)
                            @if($staff->email && $staff->email !== ($recruiterEmail ?? ''))
                                <option value="{{ $staff->email }}">{{ $staff->username }} &lt;{{ $staff->email }}&gt;</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mail-field mail-field--full">
                    <label for="mailSubject">Subject</label>
                    <input type="text" id="mailSubject" placeholder="Exciting Opportunity">
                </div>
            </div>

            <div class="mail-editor-wrap">
                <label class="mail-editor-label">Message Preview &amp; Edit</label>
                <div class="mail-toolbar" id="mailToolbar">
                    <button type="button" class="mail-tb-btn" data-cmd="bold" title="Bold"><b>B</b></button>
                    <button type="button" class="mail-tb-btn" data-cmd="italic" title="Italic"><i>I</i></button>
                    <button type="button" class="mail-tb-btn" data-cmd="underline" title="Underline"><u>U</u></button>
                    <span class="mail-tb-sep"></span>
                    <button type="button" class="mail-tb-btn" data-cmd="justifyLeft" title="Align left">&#8676;</button>
                    <button type="button" class="mail-tb-btn" data-cmd="justifyCenter" title="Align center">&#8596;</button>
                    <button type="button" class="mail-tb-btn" data-cmd="justifyRight" title="Align right">&#8677;</button>
                    <span class="mail-tb-sep"></span>
                    <select class="mail-tb-select" id="mailFontSize" title="Font size">
                        <option value="2">Small</option>
                        <option value="3" selected>Normal</option>
                        <option value="4">Large</option>
                        <option value="5">Extra Large</option>
                    </select>
                    <span class="mail-tb-sep"></span>
                    <button type="button" class="mail-tb-btn" data-cmd="insertUnorderedList" title="Bullet list">&#8226; List</button>
                </div>
                <div id="mailBodyEditor" class="mail-body-editor" contenteditable="true"></div>
            </div>

            <p class="mail-hint">
                Click <strong>Open in Outlook</strong> to open one compose window in the Outlook desktop app with To, CC, Subject, and body pre-filled.
                Your Outlook signature is added automatically.
                <button type="button" class="mail-hint-link" onclick="downloadMailEmlDraft()">Download .eml draft</button> if Outlook does not open.
            </p>
        </div>

        <div class="modal-foot mail-composer-foot">
            <button type="button" class="btn-i btn-ghost-i" onclick="closeMailComposer()">Cancel</button>
            <button type="button" class="btn-i btn-accent-i" id="mailDraftBtn" onclick="openMailDraftInOutlook()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Open in Outlook
            </button>
        </div>
    </div>
</div>

{{-- Hidden form: posts draft to iframe so Windows can hand .eml straight to Outlook --}}
<iframe name="mailDraftFrame" class="mail-draft-frame" title=""></iframe>
<form id="mailDraftForm" method="POST" action="#" target="mailDraftFrame" class="mail-draft-form">
    @csrf
    <input type="hidden" name="from" id="mailDraftFrom">
    <input type="hidden" name="to" id="mailDraftTo">
    <input type="hidden" name="subject" id="mailDraftSubject">
    <input type="hidden" name="candidate_name" id="mailDraftCandidateName">
    <textarea name="body" id="mailDraftBody" hidden></textarea>
    <input type="hidden" name="download" value="1">
    <div id="mailDraftCcFields"></div>
</form>

<div id="mailComposeToast" class="mail-compose-toast" role="status" aria-live="polite" hidden>
    <div class="mail-compose-toast__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
    </div>
    <div class="mail-compose-toast__text" id="mailComposeToastText"></div>
    <button type="button" class="mail-compose-toast__close" onclick="hideMailToast()" aria-label="Dismiss">×</button>
</div>

<style>
    .mail-composer-box {
        max-width: 780px;
        width: 96vw;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
    }
    .mail-composer-head {
        background: #0a2d29;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px 12px 0 0;
    }
    .mail-composer-head__inner {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }
    .mail-composer-head__icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        color: #f1cd86;
    }
    .mail-composer-head__title {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        margin: 0;
        min-width: 0;
    }
    .mail-composer-head__label {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        line-height: 1.25;
    }
    .mail-composer-sub {
        font-weight: 500;
        font-size: 13px;
        color: rgba(255, 255, 255, .85);
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    .mail-composer-body {
        padding: 18px 20px;
        overflow-y: auto;
        flex: 1;
        background: #fafbfc;
    }
    .mail-field-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 14px;
        margin-bottom: 16px;
    }
    .mail-field--full { grid-column: 1 / -1; }
    .mail-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        margin-bottom: 5px;
    }
    .mail-field input[type="email"],
    .mail-field input[type="text"] {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        font-size: 13px;
        background: #fff;
        color: #111827;
    }
    .mail-field input:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 3px rgba(201, 168, 76, .12);
    }
    .mail-field select { width: 100%; }
    .mail-editor-wrap { margin-top: 4px; }
    .mail-editor-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .mail-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        padding: 8px 10px;
        background: linear-gradient(180deg, #fff 0%, #f8faf9 100%);
        border: 1px solid #e5e7eb;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
    }
    .mail-tb-btn {
        padding: 5px 9px;
        border: 1px solid transparent;
        border-radius: 5px;
        background: transparent;
        cursor: pointer;
        font-size: 13px;
        color: #0a2d29;
        transition: background .12s, border-color .12s;
    }
    .mail-tb-btn:hover {
        background: #f1cd86;
        border-color: #e8c078;
    }
    .mail-tb-sep {
        width: 1px;
        height: 20px;
        background: #e5e7eb;
        margin: 0 4px;
    }
    .mail-tb-select {
        padding: 4px 6px;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        font-size: 12px;
        background: #fff;
        color: #0a2d29;
    }
    .mail-body-editor {
        min-height: 320px;
        max-height: 42vh;
        overflow-y: auto;
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 0 0 8px 8px;
        background: #fff;
        font-family: Calibri, 'Segoe UI', Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        color: #111827;
        outline: none;
    }
    .mail-body-editor:focus {
        border-color: #c9a84c;
        box-shadow: 0 0 0 3px rgba(201, 168, 76, .15);
    }
    .mail-hint {
        margin: 12px 0 0;
        font-size: 12px;
        color: #6b7280;
        line-height: 1.5;
    }
    .mail-hint-link {
        background: none;
        border: none;
        padding: 0;
        margin-left: 4px;
        color: #0a2d29;
        font-size: 12px;
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
    }
    .mail-hint-link:hover { color: #c9a84c; }
    .mail-compose-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 10050;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        max-width: 420px;
        padding: 14px 16px;
        background: #0a2d29;
        color: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,.2);
        border-left: 4px solid #f1cd86;
        animation: mailToastIn .25s ease;
    }
    .mail-compose-toast[hidden] { display: none !important; }
    .mail-compose-toast--warn { border-left-color: #f59e0b; }
    .mail-compose-toast__icon {
        flex-shrink: 0;
        width: 22px;
        height: 22px;
        color: #f1cd86;
        margin-top: 1px;
    }
    .mail-compose-toast__icon svg { width: 22px; height: 22px; }
    .mail-compose-toast__text {
        flex: 1;
        font-size: 13px;
        line-height: 1.5;
    }
    .mail-compose-toast__text strong { color: #f1cd86; }
    .mail-compose-toast__text a.mail-toast-link {
        color: #f1cd86;
        font-weight: 600;
        text-decoration: underline;
    }
    .mail-compose-toast__text a.mail-toast-link:hover { color: #fff; }
    .mail-compose-toast__close {
        background: rgba(255,255,255,.12);
        border: none;
        color: #fff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        flex-shrink: 0;
    }
    @keyframes mailToastIn {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .mail-composer-foot {
        background: linear-gradient(180deg, #fff 0%, #f9fafb 100%);
        border-top: 1px solid #e5e7eb;
    }
    .ap-item-mail { color: #0a2d29; font-weight: 600; }
    .ap-item-mail:hover { background: #ecfdf5; color: #065f46; }
    @media (max-width: 640px) {
        .mail-field-grid { grid-template-columns: 1fr; }
    }
    /* Select2 in mail composer */
    .mail-composer-body .select2-container { width: 100% !important; }
    .mail-composer-body .select2-container--default .select2-selection--multiple {
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        min-height: 40px;
        padding: 2px 6px;
    }
    .mail-draft-frame,
    .mail-draft-form {
        display: none;
        width: 0;
        height: 0;
        border: 0;
        position: absolute;
        left: -9999px;
    }
</style>

<script>
(function () {
    const MAIL_CONFIG = {
        trackerId: {{ $trackerInfo->id }},
        position: @json($trackerInfo->position ?? ''),
        cf: @json($trackerInfo->cf ?? 'USA'),
        location: @json($trackerLocation ?? ''),
        jobDescription: @json($trackerInfo->job_description ?? ''),
        recruiterName: @json($recruiterName ?? ''),
        recruiterEmail: @json($recruiterEmail ?? ''),
        draftUrl: @json(route('tracker.candidates.mail.draft', ['tracker_id' => $trackerInfo->id, 'tracker_candidate_id' => '__TC__'])),
        csrf: @json(csrf_token()),
    };

    let mailTcId = null;
    let mailCandidateName = '';

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function nl2br(text) {
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    function firstName(fullName) {
        const parts = String(fullName || '').trim().split(/\s+/);
        return parts[0] || 'there';
    }

    function formatMailDate(cf) {
        const d = new Date();
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        if (cf === 'Canada') {
            return day + '/' + month + '/' + year;
        }
        return month + '/' + day + '/' + year;
    }

    function buildMailSubject() {
        const role = (MAIL_CONFIG.position || 'Open').trim();
        const dateStr = formatMailDate(MAIL_CONFIG.cf);
        return role + ' Position - RADiiX INFINITEii - ' + dateStr;
    }

    function buildMailTemplate(candidateName) {
        const fname = firstName(candidateName);
        const role = MAIL_CONFIG.position || 'an exciting role';
        const location = MAIL_CONFIG.location || 'our client location';
        const jd = (MAIL_CONFIG.jobDescription || '').trim();

        let jdBlock = '';
        if (jd) {
            jdBlock = '<div style="margin:16px 0;">' + nl2br(jd) + '</div>';
        } else {
            jdBlock = [
                '<p style="margin:12px 0 4px;"><strong>Role:</strong> ' + escapeHtml(role) + '</p>',
                '<p style="margin:4px 0;"><strong>Location:</strong> ' + escapeHtml(location) + '</p>',
            ].join('');
        }

        return [
            '<p style="margin:0 0 14px;">Hi ' + escapeHtml(fname) + ',</p>',
            '<p style="margin:0 0 14px;">I hope you are doing well.</p>',
            '<p style="margin:0 0 14px;">I came across your profile and wanted to connect regarding an exciting opportunity for a <strong>' + escapeHtml(role) + '</strong> role with one of our clients based in <strong>' + escapeHtml(location) + '</strong>.</p>',
            jdBlock,
            '<p style="margin:18px 0 10px;">If you are interested in exploring this opportunity, please share your updated resume along with the following details:</p>',
            '<ul style="margin:0 0 18px;padding-left:22px;">',
            '<li style="margin-bottom:6px;">Current Location:</li>',
            '<li style="margin-bottom:6px;">Work Authorization/Visa Status:</li>',
            '<li style="margin-bottom:6px;">Years of Experience:</li>',
            '<li style="margin-bottom:6px;">Current Man Hour Rate:</li>',
            '<li style="margin-bottom:6px;">Expected Man Hour Rate:</li>',
            '<li style="margin-bottom:6px;">Availability for Interview:</li>',
            '<li style="margin-bottom:6px;">Availability to Join:</li>',
            '</ul>',
        ].join('');
    }

    window.openMailComposer = function (tcId, candidateName, candidateEmail) {
        mailTcId = tcId;
        mailCandidateName = candidateName || '';

        document.getElementById('mailComposerCandidateName').textContent = candidateName;
        document.getElementById('mailTo').value = candidateEmail || '';
        document.getElementById('mailSubject').value = buildMailSubject();
        document.getElementById('mailBodyEditor').innerHTML = buildMailTemplate(candidateName);

        if (typeof jQuery !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            const $cc = $('#mailCc');
            if ($cc.hasClass('select2-hidden-accessible')) {
                $cc.val(null).trigger('change');
            } else {
                $cc.select2({
                    placeholder: 'Select team members to CC…',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#mailComposerModal .mail-composer-box'),
                });
            }
        }

        openModal('mailComposerModal');
        setTimeout(() => document.getElementById('mailBodyEditor').focus(), 200);
    };

    window.closeMailComposer = function () {
        closeModal('mailComposerModal');
        mailTcId = null;
    };

    let mailToastTimer = null;

    function showMailToast(html, warn) {
        const el = document.getElementById('mailComposeToast');
        const text = document.getElementById('mailComposeToastText');
        if (!el || !text) return;
        text.innerHTML = html;
        el.classList.toggle('mail-compose-toast--warn', !!warn);
        el.hidden = false;
        clearTimeout(mailToastTimer);
        mailToastTimer = setTimeout(hideMailToast, 12000);
    }

    window.hideMailToast = function () {
        const el = document.getElementById('mailComposeToast');
        if (el) el.hidden = true;
        clearTimeout(mailToastTimer);
    };

    function htmlToPlainForMail(html) {
        let s = String(html || '')
            .replace(/<br\s*\/?>/gi, '\n')
            .replace(/<\/p>/gi, '\n\n')
            .replace(/<\/div>/gi, '\n')
            .replace(/<li[^>]*>/gi, '\n• ')
            .replace(/<\/li>/gi, '')
            .replace(/<[^>]+>/g, '');
        const div = document.createElement('div');
        div.innerHTML = s;
        return (div.textContent || div.innerText || '')
            .replace(/\u00a0/g, ' ')
            .replace(/\n{3,}/g, '\n\n')
            .trim();
    }

    function fitBodyForMailUrl(bodyPlain) {
        const maxLen = 3500;
        if (bodyPlain.length <= maxLen) return bodyPlain;
        return bodyPlain.substring(0, maxLen - 40) + '\n\n[Content shortened — review before sending.]';
    }

    function buildMailtoUrl(to, cc, subject, bodyPlain) {
        const parts = [];
        if (cc.length) parts.push('cc=' + encodeURIComponent(cc.join(',')));
        parts.push('subject=' + encodeURIComponent(subject));
        if (bodyPlain) parts.push('body=' + encodeURIComponent(bodyPlain));
        return 'mailto:' + to + (parts.length ? '?' + parts.join('&') : '');
    }

    function launchDesktopOutlook(mailtoUrl) {
        const a = document.createElement('a');
        a.href = mailtoUrl;
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    function submitEmlDraftForm(from, to, subject, body, cc) {
        const form = document.getElementById('mailDraftForm');
        form.action = MAIL_CONFIG.draftUrl.replace('__TC__', mailTcId);
        document.getElementById('mailDraftFrom').value = from;
        document.getElementById('mailDraftTo').value = to;
        document.getElementById('mailDraftSubject').value = subject;
        document.getElementById('mailDraftBody').value = body;
        document.getElementById('mailDraftCandidateName').value = mailCandidateName;
        const ccWrap = document.getElementById('mailDraftCcFields');
        ccWrap.innerHTML = '';
        cc.forEach(function (email) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'cc[]';
            inp.value = email;
            ccWrap.appendChild(inp);
        });
        form.submit();
    }

    function collectMailFields() {
        const from = document.getElementById('mailFrom').value.trim();
        const to = document.getElementById('mailTo').value.trim();
        const subject = document.getElementById('mailSubject').value.trim();
        const body = document.getElementById('mailBodyEditor').innerHTML;
        const ccEl = document.getElementById('mailCc');
        const cc = ccEl ? Array.from(ccEl.selectedOptions).map(o => o.value) : [];
        return { from, to, subject, body, cc };
    }

    function validateMailFields(from, to, subject) {
        if (!from || !/@rinfinite\.com$/i.test(from)) {
            alert('A valid @rinfinite.com sender email is required. Update your profile under Users.');
            return false;
        }
        if (!to) {
            alert('Candidate email is missing.');
            return false;
        }
        if (!subject) {
            alert('Please enter a subject.');
            return false;
        }
        return true;
    }

    window.openMailDraftInOutlook = function () {
        if (!mailTcId) return;

        const { from, to, subject, body, cc } = collectMailFields();
        if (!validateMailFields(from, to, subject)) return;

        const btn = document.getElementById('mailDraftBtn');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Opening Outlook…';

        const bodyPlain = fitBodyForMailUrl(htmlToPlainForMail(body));
        const mailtoUrl = buildMailtoUrl(to, cc, subject, bodyPlain);

        launchDesktopOutlook(mailtoUrl);

        closeMailComposer();

        showMailToast(
            '<strong>Opening Outlook…</strong> One compose window with To, CC, Subject &amp; body pre-filled. ' +
            'Your signature is added by Outlook automatically. ' +
            '<a href="' + mailtoUrl + '" class="mail-toast-link">Click here</a> if Outlook did not open.'
        );

        btn.disabled = false;
        btn.innerHTML = orig;
    };

    window.downloadMailEmlDraft = function () {
        if (!mailTcId) return;
        const { from, to, subject, body, cc } = collectMailFields();
        if (!validateMailFields(from, to, subject)) return;
        submitEmlDraftForm(from, to, subject, body, cc);
        showMailToast('Downloading <strong>.eml draft</strong> — open it from Downloads if Outlook does not launch automatically.', true);
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#mailToolbar .mail-tb-btn[data-cmd]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const cmd = this.dataset.cmd;
                document.getElementById('mailBodyEditor').focus();
                document.execCommand(cmd, false, null);
            });
        });

        const sizeSel = document.getElementById('mailFontSize');
        if (sizeSel) {
            sizeSel.addEventListener('change', function () {
                document.getElementById('mailBodyEditor').focus();
                document.execCommand('fontSize', false, this.value);
            });
        }
    });
})();
</script>
