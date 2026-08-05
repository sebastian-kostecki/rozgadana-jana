// Mobile navigation toggle.
document.addEventListener('click', function (e) {
  var btn = e.target.closest('.nav-toggle');
  if (!btn) return;
  var nav = document.querySelector('.main-nav');
  if (!nav) return;
  var open = nav.classList.toggle('is-open');
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
});
