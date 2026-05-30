(function () {
  'use strict';

  window.parseJsonResponse = function parseJsonResponse(response) {
    return response.json().then(function (json) {
      return { ok: response.ok, status: response.status, json: json };
    });
  };

  window.apiGet = function apiGet(path) {
    return fetch(apiUrl(path), { credentials: 'include' }).then(parseJsonResponse);
  };

  window.apiPost = function apiPost(path, params) {
    return getCsrfToken().then(function (csrf) {
      var body = new URLSearchParams(params || {});
      body.set('csrf_token', csrf);
      return fetch(apiUrl(path), {
        method: 'POST',
        body: body,
        credentials: 'include',
      });
    }).then(parseJsonResponse);
  };

  window.apiPostFormData = function apiPostFormData(path, formData) {
    return getCsrfToken().then(function (csrf) {
      formData.set('csrf_token', csrf);
      return fetch(apiUrl(path), {
        method: 'POST',
        body: formData,
        credentials: 'include',
      });
    }).then(parseJsonResponse);
  };

  window.apiPostCart = function apiPostCart(action, fields) {
    var params = fields ? Object.assign({}, fields) : {};
    params.action = action;
    return apiPost('carrito', params).then(function (r) {
      return r.json;
    });
  };

  window.fetchCarrito = function fetchCarrito() {
    return fetch(apiUrl('carrito?action=get'), { credentials: 'include' })
      .then(function (r) {
        return r.ok
          ? r.json()
          : Promise.resolve({ ok: false, carrito: [], subtotal: 0, total_items: 0 });
      });
  };

  window.actualizarBadgeCarrito = function actualizarBadgeCarrito() {
    return fetchCarrito().then(function (resp) {
      var total = resp.total_items || 0;
      var badge = document.getElementById('carrito-badge');
      if (badge) badge.textContent = total > 0 ? String(total) : '';
    });
  };
})();
