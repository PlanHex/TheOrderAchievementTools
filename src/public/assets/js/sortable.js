// Repurposed: searchable-list helper
// Provides a minimal helper to filter list items by `data-name` attribute.
(() => {
  window.searchableInit = function(inputSelector, listSelector) {
    const input = document.querySelector(inputSelector);
    const list = document.querySelector(listSelector);
    if (!input || !list) return;

    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      Array.from(list.querySelectorAll('[data-name]')).forEach(li => {
        const name = (li.dataset.name || '').toLowerCase();
        li.style.display = name.includes(q) ? '' : 'none';
      });
    });
  };

  // Keep file available for small helpers; sortable drag-and-drop is deprecated.
})();
