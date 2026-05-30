(function () {
  'use strict';

  var firebaseReady = false;

  function initFirebase() {
    if (firebaseReady) return;
    if (typeof firebase === 'undefined' || !window.FIREBASE_CONFIG) {
      throw new Error('Firebase SDK no cargado');
    }
    if (!firebase.apps.length) {
      firebase.initializeApp(window.FIREBASE_CONFIG);
    }
    firebaseReady = true;
  }

  function verifyIdTokenWithBackend(idToken) {
    return apiPost('firebase', {
      action: 'verify',
      id_token: idToken,
    }).then(function (r) {
      return r.json;
    });
  }

  function firebaseAuthErrorMessage(err) {
    var code = err && err.code ? String(err.code) : '';
    var messages = {
      'auth/operation-not-allowed':
        'Este método de inicio de sesión no está habilitado en Firebase. ' +
        'Activá el proveedor en Firebase Console → Authentication → Sign-in method.',
      'auth/user-not-found': 'No existe una cuenta con ese email.',
      'auth/wrong-password': 'Contraseña incorrecta.',
      'auth/invalid-email': 'El email no es válido.',
      'auth/email-already-in-use': 'Ese email ya está registrado.',
      'auth/weak-password': 'La contraseña debe tener al menos 6 caracteres.',
      'auth/too-many-requests': 'Demasiados intentos. Esperá unos minutos e intentá de nuevo.',
      'auth/invalid-credential': 'Email o contraseña incorrectos.',
      'auth/popup-closed-by-user': '',
      'auth/cancelled-popup-request': '',
      'auth/account-exists-with-different-credential':
        'Ya existe una cuenta con ese email usando otro método de inicio de sesión.',
    };
    if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') {
      return '';
    }
    if (messages[code]) {
      return messages[code];
    }
    return (err && err.message) ? err.message : 'Error de autenticación desconocido';
  }

  window.firebaseAuthErrorMessage = firebaseAuthErrorMessage;

  window.firebaseSignIn = function firebaseSignIn(email, password) {
    initFirebase();
    return firebase.auth().signInWithEmailAndPassword(email, password)
      .then(function (result) {
        return result.user.getIdToken();
      })
      .then(verifyIdTokenWithBackend)
      .catch(function (err) {
        throw new Error(firebaseAuthErrorMessage(err));
      });
  };

  window.firebaseSignUp = function firebaseSignUp(email, password) {
    initFirebase();
    return firebase.auth().createUserWithEmailAndPassword(email, password)
      .then(function (result) {
        return result.user.getIdToken();
      })
      .then(verifyIdTokenWithBackend)
      .catch(function (err) {
        throw new Error(firebaseAuthErrorMessage(err));
      });
  };

  window.firebaseSignInWithProvider = function firebaseSignInWithProvider(providerId) {
    initFirebase();
    var provider;
    if (providerId === 'google') {
      provider = new firebase.auth.GoogleAuthProvider();
    } else if (providerId === 'github') {
      provider = new firebase.auth.GithubAuthProvider();
    } else {
      return Promise.reject(new Error('Proveedor no soportado: ' + providerId));
    }
    return firebase.auth().signInWithPopup(provider)
      .then(function (result) {
        return result.user.getIdToken();
      })
      .then(verifyIdTokenWithBackend)
      .catch(function (err) {
        var code = err && err.code ? String(err.code) : '';
        if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') {
          return { ok: false, cancelled: true };
        }
        throw new Error(firebaseAuthErrorMessage(err));
      });
  };

  window.firebaseSignOut = function firebaseSignOut() {
    var signOutPromise = Promise.resolve();
    if (typeof firebase !== 'undefined' && firebase.apps && firebase.apps.length) {
      signOutPromise = firebase.auth().signOut().catch(function () {});
    }
    return signOutPromise
      .then(function () {
        return apiPost('usuarios', { action: 'logout' });
      })
      .then(function () {
        resetCsrfTokenCache();
      });
  };

  window.getSession = function getSession() {
    return fetch(apiUrl('usuarios?action=check'), { credentials: 'include' }).then(function (r) {
      return r.json();
    });
  };

  window.checkLogin = function checkLogin(callback) {
    getSession().then(function (resp) {
      callback(resp);
    });
  };

  window.logout = function logout() {
    firebaseSignOut().then(function () {
      window.location.reload();
    });
  };

  window.wireSesionNavbar = function wireSesionNavbar() {
    getSession().then(function (resp) {
      var btn = document.getElementById('sesion-btn');
      if (!btn) return;

      var adminItem = document.getElementById('nav-admin-item');
      if (adminItem) {
        adminItem.style.display = resp.ok && resp.es_admin ? '' : 'none';
      }

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
        btn.onclick = function () {
          sessionStorage.setItem('postLoginRedirect', window.location.href);
        };
      }
    });
  };
})();
