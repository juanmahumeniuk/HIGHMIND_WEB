/* anim.js - microinteracciones y scroll-reveal performantes (solo transform/opacity).
   Respeta prefers-reduced-motion y no bloquea el render. */
(function () {
  'use strict';

  var reduceMotion =
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---- Navbar: estado al hacer scroll + link activo ---- */
  function setupNavbar() {
    var header = document.querySelector('header');
    if (header) {
      var onScroll = function () {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
      };
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Marca el enlace de la pagina actual.
    var current = window.location.pathname.split('/').pop() || 'index.html';
    var links = document.querySelectorAll('.nav-links a[href]');
    links.forEach(function (a) {
      var href = (a.getAttribute('href') || '').split('/').pop();
      if (href && href === current) a.classList.add('is-active');
    });
  }

  /* ---- Scroll-reveal escalonado con IntersectionObserver ---- */
  function setupReveal() {
    var els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      els.forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    var io = new IntersectionObserver(
      function (entries, obs) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          // Stagger por grupo: data-reveal-group comparte indice incremental.
          var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
          el.style.setProperty('--reveal-delay', delay + 'ms');
          el.classList.add('is-visible');
          obs.unobserve(el);
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    els.forEach(function (el) {
      io.observe(el);
    });
  }

  /* ---- Tilt/glow sutil en cards (puntero) ---- */
  function setupPointerGlow() {
    if (reduceMotion) return;
    document.addEventListener(
      'pointermove',
      function (e) {
        var card = e.target.closest && e.target.closest('.card');
        if (!card) return;
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', ((e.clientX - r.left) / r.width) * 100 + '%');
        card.style.setProperty('--my', ((e.clientY - r.top) / r.height) * 100 + '%');
      },
      { passive: true }
    );
  }

  function init() {
    setupNavbar();
    setupReveal();
    setupPointerGlow();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Permite re-escanear reveals tras render dinamico (productos via fetch).
  window.HMRevealRefresh = setupReveal;
})();
