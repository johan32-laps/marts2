/**
 * MARTS - app.js v2.1
 * Scripts globales: toasts, tabs, dropdowns, paginación, helpers
 */
(function () {
  'use strict';

  /* ── Auto-dismiss toasts ──────────────────────────────────── */
  function dismissToasts() {
    document.querySelectorAll('.toast-auto').forEach(function (el) {
      el.style.transition = 'opacity 0.45s ease, transform 0.45s ease';
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(8px)';
      setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 460);
    });
  }
  setTimeout(dismissToasts, 4500);

  /* ── Escape cierra panels y modales ──────────────────────── */
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.slide-panel.active').forEach(function (p) { p.classList.remove('active'); });
    document.querySelectorAll('.panel-overlay.active').forEach(function (o) { o.classList.remove('active'); });
    document.querySelectorAll('.modal-backdrop.active').forEach(function (m) { m.classList.remove('active'); });
    document.querySelectorAll('.dropdown-menu.open').forEach(function (d) { d.classList.remove('open'); });
  });

  /* ── Confirm data-attribute ───────────────────────────────── */
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (el && !confirm(el.dataset.confirm)) {
      e.preventDefault();
      e.stopPropagation();
    }
  });

  /* ── Cerrar dropdowns al click fuera ─────────────────────── */
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu.open').forEach(function (d) {
        d.classList.remove('open');
      });
    }
  });

  /* ── Tabs ─────────────────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.tab-btn');
    if (!btn) return;
    var container = btn.closest('[data-tabs]') || btn.parentElement;
    var target    = btn.dataset.tab;
    if (!target) return;

    // Desactivar todos
    container.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function (c) { c.classList.remove('active'); });

    // Activar seleccionado
    btn.classList.add('active');
    var content = document.getElementById(target);
    if (content) content.classList.add('active');
  });

  /* ── Dropdowns ────────────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-dropdown]');
    if (!trigger) return;
    var menuId = trigger.dataset.dropdown;
    var menu   = document.getElementById(menuId);
    if (!menu) return;
    e.stopPropagation();
    menu.classList.toggle('open');
  });

  /* ── Paginación de tabla ──────────────────────────────────── */
  window.MARTS_Pagination = function (tableId, rowsPerPage) {
    rowsPerPage = rowsPerPage || 15;
    var table   = document.getElementById(tableId);
    if (!table) return;
    var tbody   = table.querySelector('tbody');
    var rows    = Array.from(tbody.querySelectorAll('tr[data-nombre], tr:not([id])'));
    var total   = rows.length;
    var pages   = Math.ceil(total / rowsPerPage);
    var current = 1;

    function render(page) {
      current = page;
      var start = (page - 1) * rowsPerPage;
      var end   = start + rowsPerPage;
      rows.forEach(function (r, i) {
        r.style.display = (i >= start && i < end) ? '' : 'none';
      });
      updateControls();
    }

    function updateControls() {
      var wrap = document.getElementById(tableId + '-pagination');
      if (!wrap) return;
      var info = wrap.querySelector('.pagination-info');
      var ctrl = wrap.querySelector('.pagination-controls');
      if (info) {
        var start = (current - 1) * rowsPerPage + 1;
        var end   = Math.min(current * rowsPerPage, total);
        info.textContent = 'Mostrando ' + start + '–' + end + ' de ' + total;
      }
      if (ctrl) {
        ctrl.innerHTML = '';
        // Prev
        var prev = makePgBtn('‹', current > 1, function () { render(current - 1); });
        ctrl.appendChild(prev);
        // Páginas
        for (var p = 1; p <= pages; p++) {
          (function (pg) {
            var btn = makePgBtn(pg, true, function () { render(pg); });
            if (pg === current) btn.classList.add('active');
            ctrl.appendChild(btn);
          }(p));
        }
        // Next
        var next = makePgBtn('›', current < pages, function () { render(current + 1); });
        ctrl.appendChild(next);
      }
    }

    function makePgBtn(label, enabled, cb) {
      var btn = document.createElement('button');
      btn.className = 'page-btn';
      btn.textContent = label;
      btn.disabled = !enabled;
      if (enabled) btn.addEventListener('click', cb);
      return btn;
    }

    render(1);
    return { goTo: render, total: total, pages: pages };
  };

  /* ── Toast programático ───────────────────────────────────── */
  window.MARTS_Toast = function (message, type, duration) {
    type     = type     || 'info';
    duration = duration || 4000;

    var icons = {
      success: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
      error:   '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
      warning: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
      info:    '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };

    // Crear o reutilizar contenedor
    var container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML =
      '<span class="toast-icon">' + (icons[type] || icons.info) + '</span>' +
      '<span style="flex:1">' + message + '</span>' +
      '<button class="toast-close" onclick="this.parentElement.remove()">✕</button>';

    container.appendChild(toast);

    setTimeout(function () {
      toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      toast.style.opacity    = '0';
      toast.style.transform  = 'translateX(16px)';
      setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 420);
    }, duration);
  };

  /* ── Helpers globales ─────────────────────────────────────── */
  window.MARTS = {
    formatNumber: function (n) {
      return new Intl.NumberFormat('es-MX').format(n);
    },
    formatCurrency: function (n) {
      return new Intl.NumberFormat('es-MX', {
        style: 'currency', currency: 'MXN', minimumFractionDigits: 2
      }).format(n);
    },
    formatDate: function (dateStr) {
      return new Date(dateStr).toLocaleDateString('es-MX', {
        day: '2-digit', month: 'short', year: 'numeric'
      });
    },
    toast: window.MARTS_Toast
  };

  /* ── Progress bars animadas al cargar ────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.progress-fill[data-width]').forEach(function (el) {
      var w = el.dataset.width;
      setTimeout(function () { el.style.width = w; }, 100);
    });
  });

}());
