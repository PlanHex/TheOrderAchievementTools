// Simple searchable list helper: filters list children with `data-name` attribute
(function(){
  window.searchableInit = function(inputSelector, listSelector){
    const input = document.querySelector(inputSelector);
    const list = document.querySelector(listSelector);
    if(!input || !list) return;
    input.addEventListener('input', function(){
      const q = input.value.trim().toLowerCase();
      Array.from(list.querySelectorAll('[data-name]')).forEach(function(li){
        const name = (li.dataset.name || '').toLowerCase();
        li.style.display = name.includes(q) ? '' : 'none';
      });
    });
  };
})();
