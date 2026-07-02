// Front-page "Przemyślenia" filter. Shows/hides already-rendered cards by category.
// Progressive enhancement: without JS the chips are plain links to category archives.
(function () {
  var chips = document.querySelectorAll('.filter__chip');
  var grid = document.getElementById('rj-thoughts');
  if (!chips.length || !grid) return;

  var cards = Array.prototype.slice.call(grid.querySelectorAll('[data-category]'));

  function apply(filter) {
    cards.forEach(function (card) {
      var show = filter === '*' || card.getAttribute('data-category') === filter;
      card.style.display = show ? '' : 'none';
    });
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function (e) {
      e.preventDefault(); // JS enabled → filter in place instead of navigating
      chips.forEach(function (c) { c.classList.remove('is-active'); });
      chip.classList.add('is-active');
      apply(chip.getAttribute('data-filter') || '*');
    });
  });
})();
