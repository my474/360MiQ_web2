(function () {
    'use strict';
    var list = document.getElementById('miq-community-list');
    var listStatus = document.getElementById('miq-community-list-status');
    var form = document.getElementById('miq-idea-form');
    var formStatus = document.getElementById('miq-idea-status');
    var contextCode = new URLSearchParams(window.location.search).get('code') || '';
    var targetIdea = new URLSearchParams(window.location.search).get('idea') || '';

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
        });
    }

    function collect() {
        var output = {};
        Array.prototype.forEach.call(form.querySelectorAll('input, textarea, select'), function (field) { output[field.name] = field.value; });
        return output;
    }

    function save(submit) {
        var data = collect();
        data.submit = submit;
        formStatus.textContent = submit ? 'Submitting for review…' : 'Saving draft…';
        window.MIQAccount.saveIdea(data, submit).then(function (body) {
            formStatus.textContent = submit ? 'Submitted. It will appear after moderation.' : 'Draft saved.';
            if (submit) loadIdeas();
        }).catch(function (error) { formStatus.textContent = error.message; });
    }

    function appendReportControls() {
        Array.prototype.forEach.call(list.querySelectorAll('[data-share-idea]'), function (shareButton) {
            var ideaId = escapeHtml(shareButton.getAttribute('data-share-idea'));
            var article = shareButton.closest ? shareButton.closest('.miq-idea-card') : shareButton.parentNode;
            if (!article) return;
            shareButton.insertAdjacentHTML('afterend', ' <button class="btn btn-sm btn-outline-secondary" type="button" data-report-idea="' + ideaId + '"><i class="fas fa-flag"></i> Report</button>');
            article.insertAdjacentHTML('beforeend',
                '<div class="miq-report-form" data-report-form="' + ideaId + '" hidden>'
                + '<label>Reason<select class="form-control form-control-sm" data-report-reason><option value="misleading">Misleading or unsupported</option><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="undisclosed_conflict">Undisclosed conflict</option><option value="other">Other</option></select></label>'
                + '<label>Details<textarea class="form-control form-control-sm" data-report-details maxlength="500" rows="3" placeholder="Briefly explain the concern"></textarea></label>'
                + '<div class="miq-report-form-actions"><button class="btn btn-sm btn-danger" type="button" data-submit-report="' + ideaId + '">Submit report</button><button class="btn btn-sm btn-outline-secondary" type="button" data-cancel-report="' + ideaId + '">Cancel</button><span class="miq-inline-status" data-report-status aria-live="polite"></span></div>'
                + '</div>'
            );
        });
    }

    function loadIdeas() {
        var query = contextCode ? contextCode : '';
        window.MIQAccount.request('public_ideas', targetIdea ? { idea_id: targetIdea } : { context_key: query }, 'GET').then(function (body) {
            var ideas = body.ideas || [];
            if (targetIdea) ideas = ideas.filter(function (idea) { return String(idea.id) === String(targetIdea); });
            if (!ideas.length) { list.innerHTML = '<div class="miq-empty-state">No published ideas yet. Be the first to share a thoughtful view.</div>'; return; }
            list.innerHTML = ideas.map(function (idea) {
                return '<article class="miq-idea-card"><h3>' + escapeHtml(idea.title) + '</h3><div class="miq-idea-meta"><span class="miq-idea-direction miq-idea-direction-' + escapeHtml(idea.direction) + '">' + escapeHtml(idea.direction) + '</span>' + escapeHtml(idea.code || 'Market') + ' · ' + escapeHtml(idea.timeframe || 'Unspecified timeframe') + ' · by ' + escapeHtml(idea.display_name) + '</div><p>' + escapeHtml(idea.thesis) + '</p><details><summary>More context</summary><p><strong>Catalyst:</strong> ' + escapeHtml(idea.catalyst || 'Not provided') + '</p><p><strong>Risk:</strong> ' + escapeHtml(idea.risk || 'Not provided') + '</p><p><strong>Disclosure:</strong> ' + escapeHtml(idea.disclosure || 'Not provided') + '</p></details><button class="btn btn-sm btn-outline-primary" type="button" data-share-idea="' + escapeHtml(idea.id) + '"><i class="fas fa-link"></i> Copy link</button></article>';
            }).join('');
            appendReportControls();
        }).catch(function (error) { listStatus.textContent = error.message; });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var openButton = document.querySelector('[data-open-idea-form]');
        var formCard = document.querySelector('[data-idea-form]');
        if (openButton && formCard) openButton.addEventListener('click', function () { formCard.hidden = false; formCard.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
        var saveButton = document.querySelector('[data-save-idea-draft]');
        var submitButton = document.querySelector('[data-submit-idea]');
        if (saveButton) saveButton.addEventListener('click', function () { save(false); });
        if (submitButton) submitButton.addEventListener('click', function () { save(true); });
        if (window.MIQAccount) loadIdeas();
    });

    document.addEventListener('click', function (event) {
        var shareButton = event.target.closest ? event.target.closest('[data-share-idea]') : null;
        if (shareButton) {
            var url = window.location.origin + window.location.pathname + '?idea=' + encodeURIComponent(shareButton.getAttribute('data-share-idea'));
            if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(url).then(function () { shareButton.textContent = 'Link copied'; });
            else window.prompt('Copy this link', url);
            return;
        }

        var reportButton = event.target.closest ? event.target.closest('[data-report-idea]') : null;
        if (reportButton) {
            if (!window.MIQAccount || !window.MIQAccount.state.loggedIn) {
                window.location.href = 'account.php?view=login&return_to=' + encodeURIComponent(window.location.pathname + window.location.search);
                return;
            }
            var reportForm = document.querySelector('[data-report-form="' + reportButton.getAttribute('data-report-idea') + '"]');
            if (reportForm) {
                reportForm.hidden = false;
                var reasonField = reportForm.querySelector('[data-report-reason]');
                if (reasonField) reasonField.focus();
            }
            return;
        }

        var cancelButton = event.target.closest ? event.target.closest('[data-cancel-report]') : null;
        if (cancelButton) {
            var cancelForm = document.querySelector('[data-report-form="' + cancelButton.getAttribute('data-cancel-report') + '"]');
            if (cancelForm) cancelForm.hidden = true;
            return;
        }

        var submitButton = event.target.closest ? event.target.closest('[data-submit-report]') : null;
        if (!submitButton || !window.MIQAccount) return;
        var submitForm = document.querySelector('[data-report-form="' + submitButton.getAttribute('data-submit-report') + '"]');
        if (!submitForm) return;
        var reason = submitForm.querySelector('[data-report-reason]');
        var details = submitForm.querySelector('[data-report-details]');
        var reportStatus = submitForm.querySelector('[data-report-status]');
        submitButton.disabled = true;
        reportStatus.textContent = 'Submitting report...';
        window.MIQAccount.request('report_idea', {
            idea_id: submitButton.getAttribute('data-submit-report'),
            reason: reason ? reason.value : 'other',
            details: details ? details.value : ''
        }).then(function () {
            reportStatus.textContent = 'Report submitted for moderator review.';
            submitButton.textContent = 'Reported';
        }).catch(function (error) {
            reportStatus.textContent = error.message;
            submitButton.disabled = false;
        });
    });
}());
