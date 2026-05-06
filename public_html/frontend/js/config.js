// ─── Firebase config (completar con los valores del proyecto Firebase) ─────────
// Estos valores son PÚBLICOS (van en el frontend). La seguridad la maneja Firebase
// mediante Authorized Domains y reglas de seguridad, no ocultando estas claves.
window.FIREBASE_CONFIG = {
  apiKey: 'AIzaSyBcbnwrRnNY-bU7wP0UvC44IUBqRxi-oXM',
  authDomain: 'highmind-aff15.firebaseapp.com',
  projectId: 'highmind-aff15',
};
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Resuelve la URL de la API respecto de la página actual (siempre bajo /frontend/…).
 * Así funciona en subcarpetas, php -S y distintos hosts sin depender de "/" absoluto.
 */
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
