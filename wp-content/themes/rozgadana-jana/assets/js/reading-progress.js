// Reading progress bar for singular views. Width is driven from scroll position,
// throttled to one write per animation frame.
(function () {
  var bar = document.querySelector('.reading-progress__value');
  if (!bar) return;

  var frame = null;

  function update() {
    frame = null;
    var doc = document.documentElement;
    var max = doc.scrollHeight - window.innerHeight;
    var ratio = max > 0 ? window.scrollY / max : 0;
    if (ratio < 0) ratio = 0;
    if (ratio > 1) ratio = 1;
    bar.style.width = (ratio * 100).toFixed(2) + '%';
  }

  function schedule() {
    if (frame === null) frame = window.requestAnimationFrame(update);
  }

  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule, { passive: true });
  update();
})();
