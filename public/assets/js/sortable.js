// Minimal sortable helper that posts reorder payloads to /api/reorder
(() => {
  function postReorder(type, orders, userId) {
    const payload = { type, orders };
    if (userId) payload.user_id = userId;
    const token = window.__CSRF_TOKEN__ || document.querySelector('input[name="csrf_token"]')?.value;
    if (token) payload.csrf_token = token;

    return fetch('/api/reorder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token || ''
      },
      body: JSON.stringify(payload)
    }).then(r => r.json());
  }

  // Example helper usage: call sortableInit with container selector and item selector
  window.sortableInit = function(containerSelector, itemSelector, type, userId) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    let dragEl = null;
    let allowDrag = false;

    container.addEventListener('mousedown', (e) => {
      // only allow drag if mousedown originated on a handle
      allowDrag = !!e.target.closest('.drag-handle');
    });

    container.addEventListener('dragstart', (e) => {
      if (!allowDrag) { e.preventDefault(); return; }
      dragEl = e.target;
      e.dataTransfer.effectAllowed = 'move';
    });

    container.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      const target = e.target.closest(itemSelector);
      if (target && target !== dragEl) {
        const rect = target.getBoundingClientRect();
        const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
        target.parentNode.insertBefore(dragEl, next ? target.nextSibling : target);
      }
    });

    container.addEventListener('drop', () => {
      // build map id => index
      const items = Array.from(container.querySelectorAll(itemSelector));
      const orders = {};
      items.forEach((it, idx) => {
        const id = it.dataset.id;
        if (id) orders[id] = idx + 1;
      });
      postReorder(type, orders, userId).then(function(res){
        console.log(res);
        showToast('Order saved');
      }).catch(function(err){ console.error(err); showToast('Save failed'); });
    });
  };

  function showToast(msg){
    let t = document.querySelector('.toast');
    if(!t){ t = document.createElement('div'); t.className = 'toast'; document.body.appendChild(t); }
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._timeout);
    t._timeout = setTimeout(()=> t.classList.remove('show'), 2500);
  }
})();
