document.addEventListener('DOMContentLoaded', function() {
  const menuBtn = document.getElementById('menu-toggle');
  const navLinks = document.getElementById('nav-links');
  if (!menuBtn || !navLinks) return;

  function setMenuOpen(open) {
    navLinks.classList.toggle('open', open);
    document.body.classList.toggle('menu-open', open);
    menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menuBtn.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
  }

  menuBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    setMenuOpen(!navLinks.classList.contains('open'));
  });

  // Cerrar al hacer click fuera
  document.body.addEventListener('click', function(e) {
    if (
      navLinks.classList.contains('open') &&
      !navLinks.contains(e.target) &&
      e.target !== menuBtn
    ) {
      setMenuOpen(false);
    }
  }, true);

  var adminItem = document.getElementById('nav-admin-item');
  if (adminItem && typeof window.apiUrl === 'function') {
    fetch(window.apiUrl('usuarios?action=check'), { credentials: 'include' })
      .then(function (r) {
        return r.json();
      })
      .then(function (resp) {
        if (resp.ok && resp.es_admin) {
          adminItem.style.display = '';
        }
      })
      .catch(function () {});
  }
});
