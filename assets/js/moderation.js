(function () {
    'use strict';

    var queue = document.getElementById('miq-moderation-queue');
    var reports = document.getElementById('miq-moderation-reports');
    var history = document.getElementById('miq-moderation-history');
    var status = document.getElementById('miq-moderation-status');
    var dashboard = { ideas: [], reports: [], history: [], counts: {} };

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
        });
    }

    function humanDate(value) {
        if (!value) return 'Unknown date';
        var normalized = String(value).replace(' ', 'T') + (String(value).indexOf('Z') === -1 ? 'Z' : '');
        var date = new Date(normalized);
        return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString();
    }

    function reasonLabel(reason) {
        return ({
            spam: 'Spam',
            misleading: 'Misleading or unsupported',
            harassment: 'Harassment',
            undisclosed_conflict: 'Undisclosed conflict',
            other: 'Other'
        })[reason] || reason || 'Other';
    }

    function actionLabel(action) {
        return ({
            publish: 'Published',
            reject: 'Rejected',
            hide: 'Hidden',
            report_dismissed: 'Report dismissed'
        })[action] || action || 'Action';
    }

    function setStatus(message, type) {
        if (!status) return;
        status.hidden = !message;
        status.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-danger');
        status.textContent = message || '';
        if (message) status.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function directionBadge(direction) {
        return '<span class="miq-idea-direction miq-idea-direction-' + escapeHtml(direction) + '">' + escapeHtml(direction) + '</span>';
    }

    function ideaContent(idea, options) {
        options = options || {};
        var ideaId = options.ideaId == null ? idea.id : options.ideaId;
        var ideaStatus = options.status || idea.status;
        var authorName = options.authorName || idea.author_display_name;
        var authorEmail = options.authorEmail || idea.author_email;
        var createdAt = options.createdAt || idea.created_at;
        var updatedAt = options.updatedAt || idea.updated_at;
        return '<div class="miq-moderation-card-header">'
            + '<div><div class="miq-moderation-badges">' + directionBadge(idea.direction) + '<span class="miq-moderation-status-badge">' + escapeHtml(ideaStatus) + '</span></div>'
            + '<h3>' + escapeHtml(idea.title) + '</h3></div>'
            + '<span class="miq-moderation-id">Idea #' + escapeHtml(ideaId) + '</span></div>'
            + '<div class="miq-moderation-meta-grid">'
            + '<div><small>Author</small><strong>' + escapeHtml(authorName) + '</strong><span>' + escapeHtml(authorEmail) + '</span></div>'
            + '<div><small>Subject</small><strong>' + escapeHtml(idea.code || 'Global market') + '</strong><span>' + escapeHtml(idea.timeframe || 'No timeframe supplied') + '</span></div>'
            + '<div><small>Submitted</small><strong>' + escapeHtml(humanDate(createdAt)) + '</strong><span>Updated ' + escapeHtml(humanDate(updatedAt)) + '</span></div>'
            + '</div>'
            + '<div class="miq-moderation-copy"><section><h4>Thesis</h4><p>' + escapeHtml(idea.thesis) + '</p></section>'
            + '<section><h4>Catalyst</h4><p>' + escapeHtml(idea.catalyst || 'Not provided') + '</p></section>'
            + '<section><h4>Risk or invalidation</h4><p>' + escapeHtml(idea.risk || 'Not provided') + '</p></section>'
            + '<section><h4>Disclosure</h4><p>' + escapeHtml(idea.disclosure || 'Not provided') + '</p></section></div>';
    }

    function noteControl(key, placeholder) {
        return '<label class="miq-moderation-note">Moderator note'
            + '<textarea class="form-control" rows="3" maxlength="500" data-moderation-note="' + escapeHtml(key) + '" placeholder="' + escapeHtml(placeholder) + '"></textarea>'
            + '<small>Required for rejection, hiding, and report resolution. Saved in the audit history.</small></label>';
    }

    function renderPending() {
        var ideas = dashboard.ideas || [];
        if (!ideas.length) {
            queue.innerHTML = '<div class="miq-empty-state">No ideas are waiting for review.</div>';
            return;
        }
        queue.innerHTML = ideas.map(function (idea) {
            return '<article class="miq-moderation-card" data-moderation-idea="' + escapeHtml(idea.id) + '">'
                + ideaContent(idea)
                + noteControl('idea-' + idea.id, 'Explain the decision or leave an optional publication note')
                + '<div class="miq-moderation-actions">'
                + '<button class="btn btn-primary" type="button" data-moderate="publish" data-idea-id="' + escapeHtml(idea.id) + '"><i class="fas fa-check"></i> Publish</button>'
                + '<button class="btn btn-outline-secondary" type="button" data-moderate="reject" data-idea-id="' + escapeHtml(idea.id) + '"><i class="fas fa-times"></i> Reject</button>'
                + '<button class="btn btn-outline-danger" type="button" data-moderate="hide" data-idea-id="' + escapeHtml(idea.id) + '"><i class="fas fa-eye-slash"></i> Hide</button>'
                + '</div></article>';
        }).join('');
    }

    function renderReports() {
        var openReports = dashboard.reports || [];
        if (!openReports.length) {
            reports.innerHTML = '<div class="miq-empty-state">No user reports require review.</div>';
            return;
        }
        reports.innerHTML = openReports.map(function (report) {
            return '<article class="miq-moderation-card miq-report-review-card" data-moderation-report="' + escapeHtml(report.report_id) + '">'
                + '<div class="miq-report-summary"><div><span class="miq-account-kicker">Report #' + escapeHtml(report.report_id) + '</span><h3>' + escapeHtml(reasonLabel(report.report_reason)) + '</h3></div>'
                + '<div class="miq-report-reporter"><small>Reported by</small><strong>' + escapeHtml(report.reporter_display_name) + '</strong><span>' + escapeHtml(report.reporter_email) + ' · ' + escapeHtml(humanDate(report.report_created_at)) + '</span></div>'
                + '<p>' + escapeHtml(report.report_details || 'No additional details supplied.') + '</p></div>'
                + ideaContent(report, {
                    ideaId: report.idea_id,
                    status: report.idea_status,
                    authorName: report.author_display_name,
                    authorEmail: report.author_email,
                    createdAt: report.idea_created_at,
                    updatedAt: report.idea_updated_at
                })
                + noteControl('report-' + report.report_id, 'Explain why the report is dismissed or why the idea is hidden')
                + '<div class="miq-moderation-actions">'
                + '<button class="btn btn-outline-secondary" type="button" data-report-decision="dismiss" data-report-id="' + escapeHtml(report.report_id) + '"><i class="fas fa-check-circle"></i> Dismiss report</button>'
                + '<button class="btn btn-danger" type="button" data-report-decision="hide" data-report-id="' + escapeHtml(report.report_id) + '"><i class="fas fa-eye-slash"></i> Hide idea</button>'
                + '</div></article>';
        }).join('');
    }

    function renderHistory() {
        var actions = dashboard.history || [];
        if (!actions.length) {
            history.innerHTML = '<div class="miq-empty-state">No moderation actions have been recorded.</div>';
            return;
        }
        history.innerHTML = '<div class="miq-audit-list">' + actions.map(function (item) {
            return '<article class="miq-audit-row">'
                + '<div><span class="miq-audit-action miq-audit-action-' + escapeHtml(item.action) + '">' + escapeHtml(actionLabel(item.action)) + '</span><strong>' + escapeHtml(item.idea_title) + '</strong><span>' + escapeHtml(item.idea_code || 'Global market') + ' · Idea #' + escapeHtml(item.idea_id) + ' · Current status: ' + escapeHtml(item.idea_status) + '</span></div>'
                + '<div><small>' + escapeHtml(humanDate(item.created_at)) + '</small><span>by ' + escapeHtml(item.moderator_display_name) + ' (' + escapeHtml(item.moderator_email) + ')</span></div>'
                + '<p>' + escapeHtml(item.note || 'No moderator note recorded.') + '</p>'
                + '</article>';
        }).join('') + '</div>';
    }

    function renderCounts() {
        ['pending', 'reports', 'actions'].forEach(function (key) {
            var target = document.querySelector('[data-moderation-count="' + key + '"]');
            if (target) target.textContent = Number(dashboard.counts && dashboard.counts[key] || 0);
        });
    }

    function renderDashboard() {
        renderCounts();
        renderPending();
        renderReports();
        renderHistory();
    }

    function loadDashboard(successMessage) {
        var refresh = document.querySelector('[data-moderation-refresh]');
        if (refresh) refresh.disabled = true;
        return window.MIQAccount.request('moderation_dashboard', {}, 'GET').then(function (body) {
            dashboard = body || { ideas: [], reports: [], history: [], counts: {} };
            renderDashboard();
            setStatus(successMessage || '', 'success');
        }).catch(function (error) {
            setStatus(error.message, 'error');
            queue.innerHTML = '<div class="miq-empty-state">The moderation queue could not be loaded.</div>';
        }).then(function () {
            if (refresh) refresh.disabled = false;
        });
    }

    function selectTab(tab) {
        Array.prototype.forEach.call(document.querySelectorAll('[data-moderation-panel]'), function (panel) {
            panel.hidden = panel.getAttribute('data-moderation-panel') !== tab;
        });
        Array.prototype.forEach.call(document.querySelectorAll('[data-moderation-tab]'), function (button) {
            var active = button.getAttribute('data-moderation-tab') === tab;
            button.classList.toggle('active', active);
            button.classList.toggle('is-active', active);
            if (button.getAttribute('role') === 'tab') button.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState(null, '', url.toString());
        }
    }

    function setCardBusy(card, busy) {
        if (!card) return;
        Array.prototype.forEach.call(card.querySelectorAll('button, textarea'), function (control) {
            control.disabled = busy;
        });
        card.classList.toggle('is-busy', busy);
    }

    function moderateIdea(button) {
        var ideaId = button.getAttribute('data-idea-id');
        var decision = button.getAttribute('data-moderate');
        var card = button.closest('[data-moderation-idea]');
        var note = card ? card.querySelector('[data-moderation-note="idea-' + ideaId + '"]') : null;
        var noteValue = note ? note.value.trim() : '';
        if ((decision === 'reject' || decision === 'hide') && !noteValue) {
            setStatus('Add a moderator note before rejecting or hiding an idea.', 'error');
            if (note) note.focus();
            return;
        }
        var confirmation = decision === 'publish'
            ? 'Publish this idea to the public Community Ideas page?'
            : (decision === 'reject' ? 'Reject this submission?' : 'Hide this idea from public view?');
        if (!window.confirm(confirmation)) return;
        setCardBusy(card, true);
        window.MIQAccount.request('moderate_idea', { idea_id: ideaId, decision: decision, note: noteValue }).then(function () {
            return loadDashboard(actionLabel(decision) + ' idea #' + ideaId + '.');
        }).catch(function (error) {
            setStatus(error.message, 'error');
            setCardBusy(card, false);
        });
    }

    function moderateReport(button) {
        var reportId = button.getAttribute('data-report-id');
        var decision = button.getAttribute('data-report-decision');
        var card = button.closest('[data-moderation-report]');
        var note = card ? card.querySelector('[data-moderation-note="report-' + reportId + '"]') : null;
        var noteValue = note ? note.value.trim() : '';
        if (!noteValue) {
            setStatus('Add a moderator note before resolving a report.', 'error');
            if (note) note.focus();
            return;
        }
        var confirmation = decision === 'dismiss'
            ? 'Dismiss this report and leave the idea public?'
            : 'Hide the reported idea and resolve every open report attached to it?';
        if (!window.confirm(confirmation)) return;
        setCardBusy(card, true);
        window.MIQAccount.request('moderate_report', { report_id: reportId, decision: decision, note: noteValue }).then(function () {
            return loadDashboard(decision === 'dismiss' ? 'Report #' + reportId + ' dismissed.' : 'Reported idea hidden.');
        }).catch(function (error) {
            setStatus(error.message, 'error');
            setCardBusy(card, false);
        });
    }

    document.addEventListener('click', function (event) {
        var tab = event.target.closest ? event.target.closest('[data-moderation-tab]') : null;
        if (tab) {
            selectTab(tab.getAttribute('data-moderation-tab'));
            return;
        }
        var refresh = event.target.closest ? event.target.closest('[data-moderation-refresh]') : null;
        if (refresh) {
            loadDashboard('Moderation data refreshed.');
            return;
        }
        var ideaAction = event.target.closest ? event.target.closest('[data-moderate]') : null;
        if (ideaAction) {
            moderateIdea(ideaAction);
            return;
        }
        var reportAction = event.target.closest ? event.target.closest('[data-report-decision]') : null;
        if (reportAction) moderateReport(reportAction);
    });

    document.addEventListener('DOMContentLoaded', function () {
        var initialTab = new URLSearchParams(window.location.search).get('tab');
        if (['pending', 'reports', 'history'].indexOf(initialTab) === -1) initialTab = 'pending';
        selectTab(initialTab);
        if (window.MIQAccount) loadDashboard();
        else setStatus('The account service is unavailable.', 'error');
    });
}());
