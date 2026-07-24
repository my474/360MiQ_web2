(function () {
    'use strict';
    var queue = document.getElementById('miq-moderation-queue');
    var status = document.getElementById('miq-moderation-status');

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
        });
    }

    function loadQueue() {
        window.MIQAccount.request('moderation_queue', {}, 'GET').then(function (body) {
            var ideas = body.ideas || [];
            if (!ideas.length) { queue.innerHTML = '<div class="miq-empty-state">No ideas are waiting for review.</div>'; return; }
            queue.innerHTML = ideas.map(function (idea) {
                return '<article class="miq-idea-card" data-moderation-idea="' + idea.id + '"><h3>' + escapeHtml(idea.title) + '</h3><div class="miq-idea-meta">' + escapeHtml(idea.code || 'Market') + ' · ' + escapeHtml(idea.direction) + ' · by ' + escapeHtml(idea.display_name) + ' (' + escapeHtml(idea.email) + ')</div><p>' + escapeHtml(idea.thesis) + '</p><p><strong>Catalyst:</strong> ' + escapeHtml(idea.catalyst || 'Not provided') + '</p><p><strong>Risk:</strong> ' + escapeHtml(idea.risk || 'Not provided') + '</p><p><strong>Disclosure:</strong> ' + escapeHtml(idea.disclosure || 'Not provided') + '</p><div class="miq-idea-actions"><button class="btn btn-primary" data-moderate="publish" data-idea-id="' + idea.id + '">Publish</button><button class="btn btn-outline-secondary" data-moderate="reject" data-idea-id="' + idea.id + '">Reject</button><button class="btn btn-outline-danger" data-moderate="hide" data-idea-id="' + idea.id + '">Hide</button></div></article>';
            }).join('');
        }).catch(function (error) { queue.textContent = error.message; });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('[data-moderate]') : null;
        if (!button || !window.MIQAccount) return;
        button.disabled = true;
        window.MIQAccount.request('moderate_idea', { idea_id: button.getAttribute('data-idea-id'), decision: button.getAttribute('data-moderate') }).then(function () { loadQueue(); }).catch(function (error) { status.hidden = false; status.className = 'alert alert-danger'; status.textContent = error.message; button.disabled = false; });
    });

    document.addEventListener('DOMContentLoaded', loadQueue);
}());
