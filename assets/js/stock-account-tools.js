(function () {
    'use strict';

    var root = document.getElementById('miq-stock-account-tools');
    if (!root || !window.MIQAccount || !window.MIQAccount.state.loggedIn) return;

    var state = { code: '', watchlists: [], notes: [], alerts: [] };
    var watchlistSelect = document.getElementById('miq-stock-watchlist-select');
    var watchlistToggle = document.getElementById('miq-stock-watchlist-toggle');
    var noteForm = document.getElementById('miq-stock-note-form');
    var alertForm = document.getElementById('miq-stock-alert-form');

    function stockCode() {
        var code = typeof window.stockcode !== 'undefined' && window.stockcode
            ? window.stockcode
            : root.getAttribute('data-stock-code');
        if (!code && window.__STOCKINFO_PAGE_CONFIG) code = window.__STOCKINFO_PAGE_CONFIG.stockcode;
        return String(code || '').trim().toUpperCase();
    }

    function request(action, payload, method) {
        return window.MIQAccount.action(action, payload || {}, method || 'POST');
    }

    function escapeHtml(value) {
        var node = document.createElement('div');
        node.textContent = value == null ? '' : String(value);
        return node.innerHTML;
    }

    function status(message, isError) {
        var element = root.querySelector('[data-stock-tools-status]');
        if (!element) return;
        element.textContent = message || '';
        element.hidden = !message;
        element.classList.toggle('is-error', !!isError);
    }

    function selectedWatchlist() {
        var id = Number(watchlistSelect && watchlistSelect.value || 0);
        return state.watchlists.find(function (list) { return Number(list.id) === id; }) || null;
    }

    function renderWatchlists(preferredId) {
        if (!watchlistSelect || !watchlistToggle) return;
        watchlistSelect.innerHTML = state.watchlists.map(function (list) {
            return '<option value="' + Number(list.id) + '">' + escapeHtml(list.name) + ' (' + list.items.length + ')</option>';
        }).join('');
        if (preferredId) watchlistSelect.value = String(preferredId);
        if (!watchlistSelect.value && state.watchlists[0]) watchlistSelect.value = String(state.watchlists[0].id);
        var list = selectedWatchlist();
        var contains = !!(list && list.items.some(function (item) { return item.code === state.code; }));
        watchlistToggle.disabled = !list;
        watchlistToggle.textContent = contains ? 'Remove from watchlist' : 'Add to watchlist';
        watchlistToggle.classList.toggle('btn-outline-danger', contains);
        watchlistToggle.classList.toggle('btn-primary', !contains);
    }

    function renderNotes() {
        var list = root.querySelector('[data-stock-note-list]');
        if (!list) return;
        list.innerHTML = state.notes.slice(0, 3).map(function (note) {
            return '<div class="miq-stock-mini-item"><div class="miq-stock-mini-copy"><strong>' + escapeHtml(note.title) +
                '</strong><span>' + escapeHtml(note.updated_at) + '</span></div><div class="miq-stock-mini-actions">' +
                '<button class="btn btn-link" type="button" data-note-edit="' + Number(note.id) + '">Edit</button>' +
                '<button class="btn btn-link text-danger" type="button" data-note-delete="' + Number(note.id) + '">Delete</button></div></div>';
        }).join('') || '<div class="miq-stock-mini-item">No private notes yet.</div>';
    }

    function renderAlerts() {
        var list = root.querySelector('[data-stock-alert-list]');
        if (!list) return;
        list.innerHTML = state.alerts.slice(0, 4).map(function (alert) {
            var nextStatus = alert.status === 'active' ? 'disabled' : 'active';
            var actionLabel = alert.status === 'active' ? 'Pause' : 'Reactivate';
            return '<div class="miq-stock-mini-item"><div class="miq-stock-mini-copy"><strong>' +
                escapeHtml(alert.condition_type === 'above' ? '≥ ' : '≤ ') + escapeHtml(alert.target_price) +
                '</strong><span>' + escapeHtml(alert.status) + (alert.last_price !== null ? ' · last ' + escapeHtml(alert.last_price) : '') +
                '</span></div><div class="miq-stock-mini-actions"><button class="btn btn-link" type="button" data-alert-status="' +
                Number(alert.id) + '" data-next-status="' + nextStatus + '">' + actionLabel +
                '</button><button class="btn btn-link text-danger" type="button" data-alert-delete="' + Number(alert.id) + '">Delete</button></div></div>';
        }).join('') || '<div class="miq-stock-mini-item">No price alerts yet.</div>';
    }

    function refresh(preferredWatchlistId) {
        state.code = stockCode();
        if (!state.code) {
            status('A stock code is required before these tools can load.', true);
            return Promise.resolve();
        }
        return request('watchlist_state', { code: state.code }, 'GET').then(function (body) {
            state.watchlists = body.watchlists || [];
            state.notes = body.notes || [];
            state.alerts = body.alerts || [];
            renderWatchlists(preferredWatchlistId);
            renderNotes();
            renderAlerts();
            status('', false);
        }).catch(function (error) {
            status(error.message, true);
        });
    }

    watchlistSelect.addEventListener('change', function () { renderWatchlists(); });
    watchlistToggle.addEventListener('click', function () {
        var list = selectedWatchlist();
        if (!list) return;
        var contains = list.items.some(function (item) { return item.code === state.code; });
        watchlistToggle.disabled = true;
        request(contains ? 'remove_watchlist_item' : 'add_watchlist_item', {
            watchlist_id: Number(list.id),
            code: state.code
        }).then(function () {
            status(contains ? 'Removed from ' + list.name + '.' : 'Added to ' + list.name + '.', false);
            return refresh(list.id);
        }).catch(function (error) {
            status(error.message, true);
            renderWatchlists(list.id);
        });
    });

    document.getElementById('miq-stock-watchlist-create').addEventListener('submit', function (event) {
        event.preventDefault();
        var form = event.currentTarget;
        var name = form.elements.name.value.trim();
        if (!name) return;
        request('create_watchlist', { name: name }).then(function (body) {
            var id = Number(body.id);
            form.reset();
            return request('add_watchlist_item', { watchlist_id: id, code: state.code }).then(function () {
                status('Created ' + name + ' and added ' + state.code + '.', false);
                return refresh(id);
            });
        }).catch(function (error) { status(error.message, true); });
    });

    noteForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var form = event.currentTarget;
        request('save_note', {
            id: Number(form.elements.id.value || 0),
            stock_code: state.code,
            title: form.elements.title.value.trim(),
            body: form.elements.body.value.trim()
        }).then(function () {
            form.reset();
            form.querySelector('[data-note-cancel]').hidden = true;
            status('Research note saved.', false);
            return refresh();
        }).catch(function (error) { status(error.message, true); });
    });

    noteForm.querySelector('[data-note-cancel]').addEventListener('click', function () {
        noteForm.reset();
        this.hidden = true;
    });

    alertForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var form = event.currentTarget;
        request('save_alert', {
            code: state.code,
            condition_type: form.elements.condition_type.value,
            target_price: form.elements.target_price.value
        }).then(function () {
            form.reset();
            status('Price alert created.', false);
            return refresh();
        }).catch(function (error) { status(error.message, true); });
    });

    root.addEventListener('click', function (event) {
        var edit = event.target.closest('[data-note-edit]');
        var deleteNote = event.target.closest('[data-note-delete]');
        var alertStatus = event.target.closest('[data-alert-status]');
        var deleteAlert = event.target.closest('[data-alert-delete]');
        if (edit) {
            var note = state.notes.find(function (item) { return Number(item.id) === Number(edit.getAttribute('data-note-edit')); });
            if (!note) return;
            noteForm.elements.id.value = note.id;
            noteForm.elements.title.value = note.title;
            noteForm.elements.body.value = note.body;
            noteForm.querySelector('[data-note-cancel]').hidden = false;
            noteForm.elements.title.focus();
        } else if (deleteNote) {
            if (!window.confirm('Delete this private research note?')) return;
            request('delete_note', { id: Number(deleteNote.getAttribute('data-note-delete')) }).then(function () {
                status('Research note deleted.', false);
                return refresh();
            }).catch(function (error) { status(error.message, true); });
        } else if (alertStatus) {
            request('set_alert_status', {
                id: Number(alertStatus.getAttribute('data-alert-status')),
                status: alertStatus.getAttribute('data-next-status')
            }).then(function () { return refresh(); }).catch(function (error) { status(error.message, true); });
        } else if (deleteAlert) {
            if (!window.confirm('Delete this price alert?')) return;
            request('delete_alert', { id: Number(deleteAlert.getAttribute('data-alert-delete')) }).then(function () {
                status('Price alert deleted.', false);
                return refresh();
            }).catch(function (error) { status(error.message, true); });
        }
    });

    refresh();
}());
