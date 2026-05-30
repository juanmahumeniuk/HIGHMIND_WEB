document.addEventListener('DOMContentLoaded', function () {
  var loginForm = document.getElementById('login-form');
  var registerForm = document.getElementById('register-form');
  var showRegister = document.getElementById('show-register');
  var showLogin = document.getElementById('show-login');

  if (showRegister && showLogin) {
    showRegister.addEventListener('click', function (e) {
      e.preventDefault();
      if (loginForm) loginForm.style.display = 'none';
      if (registerForm) registerForm.style.display = '';
    });
    showLogin.addEventListener('click', function (e) {
      e.preventDefault();
      if (registerForm) registerForm.style.display = 'none';
      if (loginForm) loginForm.style.display = '';
    });
  }

  function handleAuthSuccess(resp) {
    if (resp.ok) {
      showMsg('Bienvenido, ' + (resp.nombre || '') + '!', true);
      var redirect = sessionStorage.getItem('postLoginRedirect') || 'index.html';
      sessionStorage.removeItem('postLoginRedirect');
      setTimeout(function () { window.location.href = redirect; }, 800);
      return true;
    }
    showMsg(resp.msg || 'Error al iniciar sesión', false);
    return false;
  }

  function handleAuth(promise, btn, defaultLabel) {
    btn.disabled = true;
    btn.textContent = 'Conectando...';
    promise
      .then(function (resp) {
        if (resp && resp.cancelled) {
          btn.disabled = false;
          btn.textContent = defaultLabel;
          return;
        }
        if (!handleAuthSuccess(resp)) {
          btn.disabled = false;
          btn.textContent = defaultLabel;
        }
      })
      .catch(function (err) {
        showMsg(err.message || 'Error al iniciar sesión', false);
        btn.disabled = false;
        btn.textContent = defaultLabel;
      });
  }

  function wireOAuthButton(btnId, providerId, defaultLabel) {
    var btn = document.getElementById(btnId);
    if (!btn) return;
    btn.addEventListener('click', function () {
      handleAuth(firebaseSignInWithProvider(providerId), btn, defaultLabel);
    });
  }

  wireOAuthButton('firebase-google-btn', 'google', 'Ingresar con Google');
  wireOAuthButton('firebase-github-btn', 'github', 'Ingresar con GitHub');

  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = document.getElementById('login-email').value.trim();
      var password = document.getElementById('login-password').value;
      var btn = document.getElementById('login-submit');
      handleAuth(firebaseSignIn(email, password), btn, 'Ingresar');
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = document.getElementById('register-email').value.trim();
      var password = document.getElementById('register-password').value;
      var btn = document.getElementById('register-submit');
      if (password.length < 6) {
        showMsg('La contraseña debe tener al menos 6 caracteres', false);
        return;
      }
      handleAuth(firebaseSignUp(email, password), btn, 'Crear cuenta');
    });
  }

  wireSesionNavbar();
});

function showMsg(msg, ok) {
  var el = document.getElementById('login-msg');
  if (!el) return;
  el.textContent = msg;
  el.style.color = ok ? '#99e772' : '#ff8d8d';
  el.style.fontWeight = ok ? 'bold' : 'normal';
  setTimeout(function () { el.textContent = ''; }, 7000);
}
