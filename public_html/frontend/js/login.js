// --- LOGIN ---
const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    getCsrfToken()
      .then(function (csrf) {
        return fetch(apiUrl('usuarios'), {
          method: 'POST',
          body: new URLSearchParams({
            action: 'login',
            email: email,
            password: password,
            csrf_token: csrf
          }),
          credentials: 'include'
        });
      })
      .then(function (r) {
        return r.json();
      })
      .then(function (resp) {
        showMsg(resp.ok ? '¡Bienvenido, ' + (resp.nombre || '') + '!' : (resp.msg || 'Error'), resp.ok);
        if (resp.ok) setTimeout(function () { window.location = 'index.html'; }, 800);
      });
  });
}

// --- REGISTRO ---
const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const nombre = document.getElementById('reg-nombre').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    getCsrfToken()
      .then(function (csrf) {
        return fetch(apiUrl('usuarios'), {
          method: 'POST',
          body: new URLSearchParams({
            action: 'register',
            nombre: nombre,
            email: email,
            password: password,
            csrf_token: csrf
          }),
          credentials: 'include'
        });
      })
      .then(function (r) {
        return r.json();
      })
      .then(function (resp) {
        showMsg(resp.ok ? 'Registro exitoso. Ya puedes iniciar sesión.' : (resp.msg || 'Error'), resp.ok);
        if (resp.ok) registerForm.reset();
      });
  });
}

// --- FEEDBACK EN PANTALLA ---
function showMsg(msg, ok) {
  const el = document.getElementById('login-msg');
  if (!el) return;
  el.textContent = msg;
  el.style.color = ok ? '#99e772' : '#ff8d8d';
  el.style.fontWeight = ok ? 'bold' : 'normal';
  setTimeout(function () { el.textContent = ''; }, 7000);
}

// --- NAVBAR ---
// Muestra el botón de sesión con el nombre del usuario o "Iniciar sesión"

window.checkLogin = function (callback) {
  fetch(apiUrl('usuarios?action=check'), { credentials: 'include' })
    .then(function (r) {
      return r.json();
    })
    .then(function (resp) {
      callback(resp);
    });
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
    .then(function (r) {
      return r.json();
    })
    .then(function () {
      resetCsrfTokenCache();
      window.location.reload();
    });
};

document.addEventListener('DOMContentLoaded', function () {
  checkLogin(function (resp) {
    const btn = document.getElementById('sesion-btn');
    if (!btn) return;

    if (resp.ok && resp.id) {
      const first = (resp.nombre || '').split(' ')[0];
      btn.textContent = '';
      const span = document.createElement('span');
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
