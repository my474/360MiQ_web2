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

    function loadIdeas() {
        var query = contextCode ? contextCode : '';
        window.MIQAccount.request('public_ideas', targetIdea ? { idea_id: targetIdea } : { context_key: query }, 'GET').then(function (body) {
            var ideas = body.ideas || [];
            if (targetIdea) ideas = ideas.filter(function (idea) { return String(idea.id) === String(targetIdea); });
            if (!ideas.length) { list.innerHTML = '<div class="miq-empty-state">No published ideas yet. Be the first to share a thoughtful view.</div>'; return; }
            list.innerHTML = ideas.map(function (idea) {
                return '<article class="miq-idea-card"><h3>' + escapeHtml(idea.title) + '</h3><div class="miq-idea-meta"><span class="miq-idea-direction miq-idea-direction-' + escapeHtml(idea.direction) + '">' + escapeHtml(idea.direction) + '</span>' + escapeHtml(idea.code || 'Market') + ' · ' + escapeHtml(idea.timeframe || 'Unspecified timeframe') + ' · by ' + escapeHtml(idea.display_name) + '</div><p>' + escapeHtml(idea.thesis) + '</p><details><summary>More context</summary><p><strong>Catalyst:</strong> ' + escapeHtml(idea.catalyst || 'Not provided') + '</p><p><strong>Risk:</strong> ' + escapeHtml(idea.risk || 'Not provided') + '</p><p><strong>Disclosure:</strong> ' + escapeHtml(idea.disclosure || 'Not provided') + '</p></details><button class="btn btn-sm btn-outline-primary" type="button" data-share-idea="' + escapeHtml(idea.id) + '"><i class="fas fa-link"></i> Copy link</button></article>';
            }).join('');
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
        var button = event.target.closest ? event.target.closest('[data-share-idea]') : null;
        if (!button) return;
        var url = window.location.origin + window.location.pathname + '?idea=' + encodeURIComponent(button.getAttribute('data-share-idea'));
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(url).then(function () { button.textContent = 'Link copied'; });
        else window.prompt('Copy this link', url);
    });
}());
