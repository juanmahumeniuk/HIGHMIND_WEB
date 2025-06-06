document.addEventListener('DOMContentLoaded', function() {
  const menuBtn = document.getElementById('menu-toggle');
  const navLinks = document.getElementById('nav-links');
  menuBtn.addEventListener('click', function() {
    navLinks.classList.toggle('open');
    document.body.classList.toggle('menu-open');
  });

  // Cerrar al hacer click fuera
  document.body.addEventListener('click', function(e) {
    if (
      navLinks.classList.contains('open') &&
      !navLinks.contains(e.target) &&
      e.target !== menuBtn
    ) {
      navLinks.classList.remove('open');
      document.body.classList.remove('menu-open');
    }
  }, true);
});
