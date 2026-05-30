// ─── Firebase config (completar con los valores del proyecto Firebase) ─────────
window.FIREBASE_CONFIG = {
  apiKey: 'AIzaSyBcbnwrRnNY-bU7wP0UvC44IUBqRxi-oXM',
  authDomain: 'highmind-aff15.firebaseapp.com',
  projectId: 'highmind-aff15',
};

window.apiUrl = function apiUrl(path) {
  const clean = String(path).replace(/^\//, '');
  return new URL('../api/' + clean, window.location.href).href;
};

let _csrfTokenCache = null;

window.getCsrfToken = function getCsrfToken() {
  if (_csrfTokenCache) {
    return Promise.resolve(_csrfTokenCache);
  }
  return fetch(apiUrl('usuarios?action=csrf'), { credentials: 'include' })
    .then(function (r) {
      return r.json();
    })
    .then(function (d) {
      if (!d.ok || !d.csrf_token) {
        throw new Error('CSRF');
      }
      _csrfTokenCache = d.csrf_token;
      return _csrfTokenCache;
    });
};

window.resetCsrfTokenCache = function resetCsrfTokenCache() {
  _csrfTokenCache = null;
};

window.safeImgSrc = function safeImgSrc(src) {
  const s = String(src || '').trim();
  if (/^javascript:/i.test(s) || /^data:/i.test(s)) {
    return '';
  }
  return s;
};

window.actualizarBadgeCarrito = function actualizarBadgeCarrito() {
  return fetch(apiUrl('carrito?action=get'), { credentials: 'include' })
    .then(function (r) {
      return r.ok ? r.json() : Promise.resolve({ total_items: 0 });
    })
    .then(function (resp) {
      const total = resp.total_items || 0;
      const badge = document.getElementById('carrito-badge');
      if (badge) badge.textContent = total > 0 ? String(total) : '';
    });
};
