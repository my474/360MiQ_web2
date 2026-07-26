(function () {
    'use strict';

    var list = document.getElementById('miq-community-list');
    var listStatus = document.getElementById('miq-community-list-status');
    var form = document.getElementById('miq-idea-form');
    var formStatus = document.getElementById('miq-idea-status');
    var params = new URLSearchParams(window.location.search);
    var contextCode = params.get('code') || '';
    var targetIdea = params.get('idea') || '';
    var loadedReplies = {};

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>'"]/g, function (character) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
        });
    }

    function accountRequest(action, payload, method) {
        return window.MIQAccount.request(action, payload || {}, method || 'POST');
    }

    function requireLogin() {
        if (window.MIQAccount && window.MIQAccount.state.loggedIn) return true;
        window.location.href = 'account.php?view=login&return_to=' + encodeURIComponent(window.location.pathname + window.location.search);
        return false;
    }

    function collect() {
        var output = {};
        Array.prototype.forEach.call(form.querySelectorAll('input, textarea, select'), function (field) {
            output[field.name] = field.value;
        });
        return output;
    }

    function save(submit) {
        var data = collect();
        data.submit = submit;
        formStatus.textContent = submit ? 'Submitting for review…' : 'Saving draft…';
        window.MIQAccount.saveIdea(data, submit).then(function () {
            formStatus.textContent = submit ? 'Submitted. It will appear after moderation.' : 'Draft saved.';
            if (submit) loadIdeas();
        }).catch(function (error) {
            formStatus.textContent = error.message;
        });
    }

    function reportForm(ideaId) {
        return '<div class="miq-report-form" data-report-form="' + ideaId + '" hidden>' +
            '<label>Reason<select class="form-control form-control-sm" data-report-reason><option value="misleading">Misleading or unsupported</option><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="undisclosed_conflict">Undisclosed conflict</option><option value="other">Other</option></select></label>' +
            '<label>Details<textarea class="form-control form-control-sm" data-report-details maxlength="500" rows="3" placeholder="Briefly explain the concern"></textarea></label>' +
            '<div class="miq-report-form-actions"><button class="btn btn-sm btn-danger" type="button" data-submit-report="' + ideaId + '">Submit report</button><button class="btn btn-sm btn-outline-secondary" type="button" data-cancel-report="' + ideaId + '">Cancel</button><span class="miq-inline-status" data-report-status aria-live="polite"></span></div></div>';
    }

    function repliesPanel(ideaId) {
        var login = window.MIQAccount && window.MIQAccount.state.loggedIn;
        return '<section class="miq-idea-replies" data-replies-panel="' + ideaId + '" hidden><div data-replies-list>Loading replies…</div>' +
            (login
                ? '<form data-reply-form="' + ideaId + '"><label for="reply-input-' + ideaId + '">Add a reply</label><textarea id="reply-input-' + ideaId + '" class="form-control" name="body" rows="3" maxlength="2000" required></textarea><button class="btn btn-sm btn-primary" type="submit">Post reply</button><span class="miq-inline-status" data-reply-status></span></form>'
                : '<p class="miq-reply-login"><a href="account.php?view=login&amp;return_to=' + encodeURIComponent(window.location.pathname + window.location.search) + '">Sign in to reply</a></p>') +
            '</section>';
    }

    function ideaCard(idea) {
        var ideaId = Number(idea.id);
        var bookmarkLabel = idea.bookmarked ? 'Bookmarked' : 'Bookmark';
        return '<article class="miq-idea-card" id="idea-' + ideaId + '" data-idea-id="' + ideaId + '"><h3>' + escapeHtml(idea.title) +
            '</h3><div class="miq-idea-meta"><span class="miq-idea-direction miq-idea-direction-' + escapeHtml(idea.direction) + '">' +
            escapeHtml(idea.direction) + '</span>' + escapeHtml(idea.code || 'Market') + ' · ' +
            escapeHtml(idea.timeframe || 'Unspecified timeframe') + ' · by ' + escapeHtml(idea.display_name) + '</div><p>' +
            escapeHtml(idea.thesis) + '</p><details><summary>More context</summary><p><strong>Catalyst:</strong> ' +
            escapeHtml(idea.catalyst || 'Not provided') + '</p><p><strong>Risk:</strong> ' + escapeHtml(idea.risk || 'Not provided') +
            '</p><p><strong>Disclosure:</strong> ' + escapeHtml(idea.disclosure || 'Not provided') + '</p></details>' +
            '<div class="miq-idea-card-actions"><button class="btn btn-sm btn-outline-primary" type="button" data-share-idea="' + ideaId +
            '"><i class="fas fa-link"></i> Copy link</button><button class="btn btn-sm btn-outline-primary' + (idea.bookmarked ? ' active' : '') +
            '" type="button" data-bookmark-idea="' + ideaId + '" data-bookmarked="' + (idea.bookmarked ? 'true' : 'false') +
            '"><i class="fas fa-bookmark"></i> ' + bookmarkLabel + '</button><button class="btn btn-sm btn-outline-primary" type="button" data-toggle-replies="' +
            ideaId + '"><i class="fas fa-comment"></i> Replies (' + Number(idea.reply_count || 0) +
            ')</button><button class="btn btn-sm btn-outline-secondary" type="button" data-report-idea="' + ideaId +
            '"><i class="fas fa-flag"></i> Report</button></div>' + reportForm(ideaId) + repliesPanel(ideaId) + '</article>';
    }

    function loadIdeas() {
        var query = contextCode || '';
        listStatus.textContent = '';
        accountRequest('public_ideas', targetIdea ? { idea_id: targetIdea } : { context_key: query }, 'GET').then(function (body) {
            var ideas = body.ideas || [];
            if (targetIdea) ideas = ideas.filter(function (idea) { return String(idea.id) === String(targetIdea); });
            if (!ideas.length) {
                list.innerHTML = '<div class="miq-empty-state">No published ideas yet. Be the first to share a thoughtful view.</div>';
                return;
            }
            list.innerHTML = ideas.map(ideaCard).join('');
            if (targetIdea) {
                var target = document.getElementById('idea-' + targetIdea);
                if (target) target.scrollIntoView({ block: 'start' });
                if (/^#reply-\d+$/.test(window.location.hash)) {
                    var replies = document.querySelector('[data-replies-panel="' + Number(targetIdea) + '"]');
                    if (replies) replies.hidden = false;
                    loadReplies(Number(targetIdea)).then(function () {
                        var replyTarget = document.querySelector(window.location.hash);
                        if (replyTarget) replyTarget.scrollIntoView({ block: 'center' });
                    });
                }
            }
        }).catch(function (error) {
            listStatus.textContent = error.message;
        });
    }

    function renderReplies(ideaId, replies) {
        var panel = document.querySelector('[data-replies-panel="' + ideaId + '"]');
        var replyList = panel && panel.querySelector('[data-replies-list]');
        if (!replyList) return;
        replyList.innerHTML = replies.length ? replies.map(function (reply) {
            return '<article class="miq-community-reply" id="reply-' + Number(reply.id) + '"><div><strong>' +
                escapeHtml(reply.display_name) + '</strong><small>' + escapeHtml(reply.created_at) + '</small></div><p>' +
                escapeHtml(reply.body) + '</p>' + (reply.can_delete ? '<button class="btn btn-sm btn-link text-danger" type="button" data-delete-reply="' +
                Number(reply.id) + '" data-reply-idea="' + ideaId + '">Delete</button>' : '') + '</article>';
        }).join('') : '<div class="miq-empty-state">No replies yet. Start the discussion.</div>';
        var toggle = document.querySelector('[data-toggle-replies="' + ideaId + '"]');
        if (toggle) toggle.innerHTML = '<i class="fas fa-comment"></i> Replies (' + replies.length + ')';
        loadedReplies[ideaId] = replies;
    }

    function loadReplies(ideaId) {
        return accountRequest('list_idea_replies', { idea_id: ideaId }, 'GET').then(function (body) {
            renderReplies(ideaId, body.replies || []);
        }).catch(function (error) {
            var panel = document.querySelector('[data-replies-panel="' + ideaId + '"]');
            var replyList = panel && panel.querySelector('[data-replies-list]');
            if (replyList) replyList.textContent = error.message;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var openButton = document.querySelector('[data-open-idea-form]');
        var formCard = document.querySelector('[data-idea-form]');
        if (openButton && formCard) openButton.addEventListener('click', function () {
            formCard.hidden = false;
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        var saveButton = document.querySelector('[data-save-idea-draft]');
        var submitButton = document.querySelector('[data-submit-idea]');
        if (saveButton) saveButton.addEventListener('click', function () { save(false); });
        if (submitButton) submitButton.addEventListener('click', function () { save(true); });
        if (window.MIQAccount) loadIdeas();
    });

    document.addEventListener('submit', function (event) {
        var replyForm = event.target.closest && event.target.closest('[data-reply-form]');
        if (!replyForm) return;
        event.preventDefault();
        if (!requireLogin()) return;
        var ideaId = Number(replyForm.getAttribute('data-reply-form'));
        var replyStatus = replyForm.querySelector('[data-reply-status]');
        replyStatus.textContent = 'Posting…';
        accountRequest('save_idea_reply', { idea_id: ideaId, body: replyForm.elements.body.value.trim() }).then(function () {
            replyForm.reset();
            replyStatus.textContent = 'Reply submitted for review.';
        }).catch(function (error) {
            replyStatus.textContent = error.message;
        });
    });

    document.addEventListener('click', function (event) {
        var shareButton = event.target.closest && event.target.closest('[data-share-idea]');
        if (shareButton) {
            var url = window.location.origin + window.location.pathname + '?idea=' + encodeURIComponent(shareButton.getAttribute('data-share-idea'));
            if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(url).then(function () { shareButton.textContent = 'Link copied'; });
            else window.prompt('Copy this link', url);
            return;
        }

        var bookmarkButton = event.target.closest && event.target.closest('[data-bookmark-idea]');
        if (bookmarkButton) {
            if (!requireLogin()) return;
            var bookmarked = bookmarkButton.getAttribute('data-bookmarked') !== 'true';
            accountRequest('bookmark_idea', { idea_id: Number(bookmarkButton.getAttribute('data-bookmark-idea')), bookmarked: bookmarked }).then(function () {
                bookmarkButton.setAttribute('data-bookmarked', bookmarked ? 'true' : 'false');
                bookmarkButton.classList.toggle('active', bookmarked);
                bookmarkButton.innerHTML = '<i class="fas fa-bookmark"></i> ' + (bookmarked ? 'Bookmarked' : 'Bookmark');
            }).catch(function (error) { listStatus.textContent = error.message; });
            return;
        }

        var repliesButton = event.target.closest && event.target.closest('[data-toggle-replies]');
        if (repliesButton) {
            var ideaId = Number(repliesButton.getAttribute('data-toggle-replies'));
            var panel = document.querySelector('[data-replies-panel="' + ideaId + '"]');
            if (!panel) return;
            panel.hidden = !panel.hidden;
            if (!panel.hidden && !loadedReplies[ideaId]) loadReplies(ideaId);
            return;
        }

        var deleteReply = event.target.closest && event.target.closest('[data-delete-reply]');
        if (deleteReply) {
            if (!window.confirm('Delete this reply?')) return;
            var replyIdeaId = Number(deleteReply.getAttribute('data-reply-idea'));
            accountRequest('delete_idea_reply', { reply_id: Number(deleteReply.getAttribute('data-delete-reply')) }).then(function () {
                return loadReplies(replyIdeaId);
            }).catch(function (error) { listStatus.textContent = error.message; });
            return;
        }

        var reportButton = event.target.closest && event.target.closest('[data-report-idea]');
        if (reportButton) {
            if (!requireLogin()) return;
            var report = document.querySelector('[data-report-form="' + reportButton.getAttribute('data-report-idea') + '"]');
            if (report) {
                report.hidden = false;
                report.querySelector('[data-report-reason]').focus();
            }
            return;
        }

        var cancelButton = event.target.closest && event.target.closest('[data-cancel-report]');
        if (cancelButton) {
            var cancelForm = document.querySelector('[data-report-form="' + cancelButton.getAttribute('data-cancel-report') + '"]');
            if (cancelForm) cancelForm.hidden = true;
            return;
        }

        var submitReport = event.target.closest && event.target.closest('[data-submit-report]');
        if (!submitReport) return;
        var submitForm = document.querySelector('[data-report-form="' + submitReport.getAttribute('data-submit-report') + '"]');
        if (!submitForm) return;
        var reason = submitForm.querySelector('[data-report-reason]');
        var details = submitForm.querySelector('[data-report-details]');
        var reportStatus = submitForm.querySelector('[data-report-status]');
        submitReport.disabled = true;
        reportStatus.textContent = 'Submitting report…';
        accountRequest('report_idea', {
            idea_id: Number(submitReport.getAttribute('data-submit-report')),
            reason: reason.value,
            details: details.value
        }).then(function () {
            reportStatus.textContent = 'Report submitted for moderator review.';
            submitReport.textContent = 'Reported';
        }).catch(function (error) {
            reportStatus.textContent = error.message;
            submitReport.disabled = false;
        });
    });
}());
