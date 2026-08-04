// Front-page "Wcześniej pisałam" filter. Shows/hides already-rendered rows by category.
// Progressive enhancement: without JS the chips are plain links to category archives.
(function () {
  var chips = document.querySelectorAll('.filter__chip');
  var list = document.getElementById('rj-thoughts');
  if (!chips.length || !list) return;

  var rows = Array.prototype.slice.call(list.querySelectorAll('[data-category]'));

  function apply(filter) {
    rows.forEach(function (row) {
      var show = filter === '*' || row.getAttribute('data-category') === filter;
      row.hidden = !show;
    });
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function (e) {
      e.preventDefault(); // JS enabled -> filter in place instead of navigating
      chips.forEach(function (c) {
        c.classList.remove('is-active');
        c.removeAttribute('aria-current');
      });
      chip.classList.add('is-active');
      chip.setAttribute('aria-current', 'true');
      apply(chip.getAttribute('data-filter') || '*');
    });
  });
})();
