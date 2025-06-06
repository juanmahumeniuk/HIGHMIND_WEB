// --- LOGIN ---
const loginForm = document.getElementById('login-form');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    fetch('/backend/usuarios.php', {
      method: 'POST',
      body: new URLSearchParams({action:'login', email, password}),
      credentials: 'include'
    }).then(r => r.json()).then(resp => {
      showMsg(resp.ok ? '¡Bienvenido, ' + (resp.nombre || '') + '!' : (resp.msg || 'Error'), resp.ok);
      if (resp.ok) setTimeout(() => window.location = 'index.html', 800);
    });
  });
}

// --- REGISTRO ---
const registerForm = document.getElementById('register-form');
if (registerForm) {
  registerForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const nombre = document.getElementById('reg-nombre').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    fetch('/backend/usuarios.php', {
      method: 'POST',
      body: new URLSearchParams({action:'register', nombre, email, password}),
      credentials: 'include'
    }).then(r => r.json()).then(resp => {
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
  setTimeout(() => { el.textContent = ''; }, 7000);
}

// --- NAVBAR ---
// Muestra el botón de sesión con el nombre del usuario o "Iniciar sesión"

window.checkLogin = function(callback) {
  fetch('/backend/usuarios.php?action=check', {credentials:'include'})
    .then(r => r.json())
    .then(resp => callback(resp));
};

window.logout = function() {
  fetch('/backend/usuarios.php', {
    method: 'POST',
    body: new URLSearchParams({action:'logout'}),
    credentials: 'include'
  }).then(r => r.json()).then(() => window.location.reload());
};

document.addEventListener('DOMContentLoaded', function() {
  checkLogin(function(resp) {
    const btn = document.getElementById('sesion-btn');
    if (!btn) return;

    if (resp.ok && resp.id) {
      // Mostrando el primer nombre y la opción de cerrar sesión
      btn.innerHTML = `<span>${(resp.nombre || '').split(' ')[0]}</span> (Cerrar sesión)`;
      btn.href = "#";
      btn.onclick = function(e) {
        e.preventDefault();
        window.logout();
      };
    } else {
      btn.textContent = "Iniciar sesión";
      btn.href = "login.html";
      btn.onclick = null;
    }
  });
});
