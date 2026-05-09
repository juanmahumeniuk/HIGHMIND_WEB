// ─── FIREBASE CONFIG ──────────────────────────────────────────────────────────
// FIREBASE_CONFIG es inyectado por config.js (window.FIREBASE_CONFIG)
// Se inicializa solo si el SDK está presente (login.html lo carga, otras páginas no)
if (typeof firebase !== 'undefined' && window.FIREBASE_CONFIG) {
  firebase.initializeApp(window.FIREBASE_CONFIG);
}

// ─── BOTÓN GOOGLE LOGIN ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('firebase-google-btn');
  if (!btn) return;

  btn.addEventListener('click', function () {
    btn.disabled = true;
    btn.textContent = 'Conectando...';

    var provider = new firebase.auth.GoogleAuthProvider();

    firebase.auth().signInWithPopup(provider)
      .then(function (result) {
        return result.user.getIdToken();
      })
      .then(function (idToken) {
        return getCsrfToken().then(function (csrf) {
          return fetch(apiUrl('firebase'), {
            method: 'POST',
            body: new URLSearchParams({
              action: 'verify',
              id_token: idToken,
              csrf_token: csrf
            }),
            credentials: 'include'
          });
        });
      })
      .then(function (r) { return r.json(); })
      .then(function (resp) {
        if (resp.ok) {
          showMsg('Bienvenido, ' + (resp.nombre || '') + '!', true);
          var redirect = sessionStorage.getItem('postLoginRedirect') || 'index.html';
          sessionStorage.removeItem('postLoginRedirect');
          setTimeout(function () { window.location.href = redirect; }, 800);
        } else {
          showMsg(resp.msg || 'Error al iniciar sesión', false);
          btn.disabled = false;
          btn.textContent = 'Ingresar con Google';
        }
      })
      .catch(function (err) {
        // El usuario cerró el popup → no mostrar error
        if (err.code === 'auth/popup-closed-by-user' || err.code === 'auth/cancelled-popup-request') {
          btn.disabled = false;
          btn.textContent = 'Ingresar con Google';
          return;
        }
        showMsg('Error: ' + (err.message || err.code), false);
        btn.disabled = false;
        btn.textContent = 'Ingresar con Google';
      });
  });
});

// ─── FEEDBACK EN PANTALLA ─────────────────────────────────────────────────────
function showMsg(msg, ok) {
  var el = document.getElementById('login-msg');
  if (!el) return;
  el.textContent = msg;
  el.style.color = ok ? '#99e772' : '#ff8d8d';
  el.style.fontWeight = ok ? 'bold' : 'normal';
  setTimeout(function () { el.textContent = ''; }, 7000);
}

// ─── NAVBAR: estado de sesión ─────────────────────────────────────────────────
window.checkLogin = function (callback) {
  fetch(apiUrl('usuarios?action=check'), { credentials: 'include' })
    .then(function (r) { return r.json(); })
    .then(function (resp) { callback(resp); });
};

window.logout = function () {
  getCsrfToken()
    .then(function (csrf) {
      return fetch(apiUrl('usuarios'), {
        method: 'POST',
        body: new URLSearchParams({ action: 'logout', csrf_token: csrf }),
        credentials: 'include'
      });
    })
    .then(function (r) { return r.json(); })
    .then(function () {
      // Cerrar sesión de Firebase también (si SDK está disponible)
      if (typeof firebase !== 'undefined') {
        firebase.auth().signOut().catch(function () {});
      }
      resetCsrfTokenCache();
      window.location.reload();
    });
};

document.addEventListener('DOMContentLoaded', function () {
  checkLogin(function (resp) {
    var btn = document.getElementById('sesion-btn');
    if (!btn) return;

    if (resp.ok && resp.id) {
      var first = (resp.nombre || '').split(' ')[0];
      btn.textContent = '';
      var span = document.createElement('span');
      span.textContent = first;
      btn.appendChild(span);
      btn.appendChild(document.createTextNode(' (Cerrar sesión)'));
      btn.href = '#';
      btn.onclick = function (e) {
        e.preventDefault();
        window.logout();
      };
    } else {
      btn.textContent = 'Iniciar sesión';
      btn.href = 'login.html';
      btn.onclick = null;
    }
  });
});
