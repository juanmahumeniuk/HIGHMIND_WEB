// login.js

// --- LOGIN ---
document.getElementById('login-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const email = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;
  fetch('../backend/usuarios.php', {
    method: 'POST',
    body: new URLSearchParams({action:'login', email, password}),
    credentials: 'include'
  }).then(r=>r.json()).then(resp => {
    showMsg(resp.ok ? '¡Bienvenido, ' + resp.nombre + '!' : (resp.msg || 'Error'), resp.ok);
    if (resp.ok) setTimeout(() => window.location = 'index.html', 800);
  });
});

// --- REGISTRO ---
document.getElementById('register-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const nombre = document.getElementById('reg-nombre').value.trim();
  const email = document.getElementById('reg-email').value.trim();
  const password = document.getElementById('reg-password').value;
  fetch('../backend/usuarios.php', {
    method: 'POST',
    body: new URLSearchParams({action:'register', nombre, email, password}),
    credentials: 'include'
  }).then(r=>r.json()).then(resp => {
    showMsg(resp.ok ? 'Registro exitoso. Ya puedes iniciar sesión.' : (resp.msg || 'Error'), resp.ok);
    if (resp.ok) document.getElementById('register-form').reset();
  });
});

// --- FEEDBACK EN PANTALLA ---
function showMsg(msg, ok) {
  const el = document.getElementById('login-msg');
  el.textContent = msg;
  el.style.color = ok ? '#99e772' : '#ff8d8d';
  el.style.fontWeight = ok ? 'bold' : 'normal';
  setTimeout(() => { el.textContent = ''; }, 7000);
}

// (Opcional) Función para logout desde cualquier página:
window.logout = function() {
  fetch('../backend/usuarios.php', {
    method: 'POST',
    body: new URLSearchParams({action:'logout'}),
    credentials: 'include'
  }).then(r=>r.json()).then(() => window.location.reload());
};

// (Opcional) Chequeo de sesión para mostrar datos del usuario en cualquier página:
window.checkLogin = function(callback) {
  fetch('../backend/usuarios.php?action=check', {credentials:'include'})
    .then(r=>r.json())
    .then(resp => callback(resp));
};
