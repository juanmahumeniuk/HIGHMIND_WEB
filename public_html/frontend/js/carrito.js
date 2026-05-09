// ---- Carrito Modal y Lógica ----
let mpBrickController = null;
let mpBricksBuilder = null;
let mpPublicKey = '';

function safeImgSrc(src) {
  const s = String(src || '').trim();
  if (/^javascript:/i.test(s) || /^data:/i.test(s)) {
    return '';
  }
  return s;
}

// ABRIR Y CERRAR MODAL
function mostrarModalCarrito() {
  document.getElementById('modal-carrito').style.display = 'flex';
  cargarCarrito();
  applyCheckoutButtonState();
}

// MOSTRAR CARRITO
function cargarCarrito() {
  fetch(apiUrl('carrito?action=get'), { credentials: 'include' })
    .then(function (r) {
      return r.ok ? r.json() : Promise.resolve({ ok: false, carrito: [], subtotal: 0, total_items: 0 });
    })
    .then(function (resp) {
      const div = document.getElementById('carrito-items');
      if (!div) return;
      if (resp.ok === false && resp.msg) {
        const err = document.createElement('div');
        err.style.textAlign = 'center';
        err.style.color = '#ff8d8d';
        err.style.padding = '1rem';
        err.textContent = resp.msg;
        div.replaceChildren(err);
        return;
      }
      const items = resp.carrito || [];
      div.replaceChildren();
      if (!items.length) {
        const empty = document.createElement('div');
        empty.style.textAlign = 'center';
        empty.style.color = '#aaa';
        empty.textContent = 'El carrito está vacío.';
        div.appendChild(empty);
      }
      items.forEach(function (item) {
        const fila = document.createElement('div');
        fila.className = 'carrito-item';
        const img = document.createElement('img');
        const src = safeImgSrc(item.img);
        if (src) img.src = src;
        img.alt = '';
        const info = document.createElement('div');
        info.className = 'carrito-info';
        const nombre = document.createElement('div');
        nombre.className = 'carrito-nombre';
        nombre.textContent = String(item.nombre || '');
        const precio = document.createElement('div');
        precio.className = 'carrito-precio';
        precio.textContent = '$' + Number(item.precio).toLocaleString('es-AR');
        const cantWrap = document.createElement('div');
        cantWrap.className = 'carrito-cantidad';
        cantWrap.appendChild(document.createTextNode('Cantidad: '));
        const input = document.createElement('input');
        input.type = 'number';
        input.min = '1';
        input.value = String(item.cantidad);
        input.setAttribute('data-id', String(item.producto_id));
        cantWrap.appendChild(input);
        info.appendChild(nombre);
        info.appendChild(precio);
        fila.appendChild(img);
        fila.appendChild(info);
        fila.appendChild(cantWrap);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'carrito-remove';
        removeBtn.setAttribute('data-id', String(item.producto_id));
        removeBtn.title = 'Quitar';
        removeBtn.innerHTML = '&#128465;';
        fila.appendChild(removeBtn);
        div.appendChild(fila);
      });
      document.getElementById('carrito-subtotal').textContent =
        '$' + Number(resp.subtotal || 0).toLocaleString('es-AR');
      actualizarBadgeCarrito();
    });
}

// QUITAR ITEM DEL CARRITO
function setupEliminarYActualizar() {
  document.getElementById('carrito-items').addEventListener('click', function (e) {
    if (e.target.classList.contains('carrito-remove')) {
      const id = e.target.getAttribute('data-id');
      getCsrfToken().then(function (csrf) {
        return fetch(apiUrl('carrito'), {
          method: 'POST',
          body: new URLSearchParams({ action: 'remove', id: id, csrf_token: csrf }),
          credentials: 'include'
        });
      }).then(function () {
        cargarCarrito();
      });
    }
  });

  document.getElementById('carrito-items').addEventListener('change', function (e) {
    if (e.target.type === 'number') {
      const id = e.target.getAttribute('data-id');
      let qty = parseInt(e.target.value, 10) || 1;
      getCsrfToken().then(function (csrf) {
        return fetch(apiUrl('carrito'), {
          method: 'POST',
          body: new URLSearchParams({ action: 'update', id: id, qty: qty, csrf_token: csrf }),
          credentials: 'include'
        });
      }).then(function () {
        cargarCarrito();
      });
    }
  });
}

// VACIAR CARRITO
function setupVaciarCarrito() {
  const vaciarBtn = document.getElementById('vaciar-carrito');
  if (vaciarBtn) {
    vaciarBtn.onclick = function () {
      if (confirm('¿Vaciar todo el carrito?')) {
        getCsrfToken().then(function (csrf) {
          return fetch(apiUrl('carrito'), {
            method: 'POST',
            body: new URLSearchParams({ action: 'clear', csrf_token: csrf }),
            credentials: 'include'
          });
        }).then(function () {
          cargarCarrito();
        });
      }
    };
  }
}

function checkoutFeedback(message, kind) {
  const el = document.getElementById('checkout-feedback');
  if (!el) return;
  el.textContent = message;
  el.classList.remove('is-ok', 'is-pending', 'is-error');
  if (kind) el.classList.add(kind);
}

function ensureCheckoutPanelOpen() {
  const panel = document.getElementById('checkout-panel');
  if (!panel) return false;
  panel.style.display = 'block';
  return true;
}

function syncCheckoutDisabledHint() {
  var btn = document.getElementById('finalizar-compra');
  var el = document.getElementById('checkout-disabled-hint');
  if (!el || !btn) return;
  if (btn.getAttribute('data-checkout-lock') === 'payments_disabled') {
    el.style.display = 'block';
    el.textContent =
      'Por seguridad el pago está desactivado. Solicitá al administrador que lo habilite temporalmente (CHECKOUT_PAYMENTS_ENABLED).';
  } else {
    el.style.display = 'none';
    el.textContent = '';
  }
}

function applyCheckoutButtonState() {
  const btn = document.getElementById('finalizar-compra');
  if (!btn) return;
  fetch(apiUrl('pagos?action=config'), { credentials: 'include' })
    .then(function (r) {
      if (r.status === 401) {
        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
        btn.setAttribute('data-checkout-lock', 'login_required');
        btn.title = 'Iniciá sesión para finalizar la compra.';
        btn.classList.add('btn-checkout-locked');
        return null;
      }
      return r.json();
    })
    .then(function (d) {
      if (!d) return;
      if (d.payments_enabled === false) {
        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
        btn.setAttribute('data-checkout-lock', 'payments_disabled');
        btn.title = d.msg || 'Pagos deshabilitados en este sitio.';
        btn.classList.add('btn-checkout-locked');
        return;
      }
      if (!d.public_key) {
        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
        btn.setAttribute('data-checkout-lock', 'not_configured');
        btn.title = d.msg || 'Pagos no configurados.';
        btn.classList.add('btn-checkout-locked');
        return;
      }
      btn.disabled = false;
      btn.removeAttribute('aria-disabled');
      btn.removeAttribute('data-checkout-lock');
      btn.title = '';
      btn.classList.remove('btn-checkout-locked');
    })
    .catch(function () {})
    .finally(function () {
      syncCheckoutDisabledHint();
    });
}

function setCheckoutPaymentNotice(config) {
  const el = document.getElementById('checkout-payment-notice');
  if (!el) return;
  if (config && config.test_mode === true) {
    el.style.display = 'block';
    el.textContent =
      'Modo prueba: estos pagos son simulados y no debitan dinero real. ' +
      'Si ves esto en un sitio que debería cobrar de verdad, revisá las credenciales Mercado Pago en el servidor.';
  } else {
    el.style.display = 'none';
    el.textContent = '';
  }
}

function fetchCheckoutConfig() {
  return fetch(apiUrl('pagos?action=config'), { credentials: 'include' })
    .then(function (r) {
      if (r.status === 401) {
        throw new Error('Iniciá sesión para pagar.');
      }
      return r.json();
    })
    .then(function (d) {
      if (d.payments_enabled === false) {
        throw new Error(d.msg || 'Los pagos están deshabilitados.');
      }
      if (!d.ok || !d.public_key) {
        throw new Error(d.msg || 'No se pudo inicializar Mercado Pago');
      }
      mpPublicKey = d.public_key;
      return d;
    });
}

function destroyBrickIfAny() {
  if (mpBrickController && typeof mpBrickController.unmount === 'function') {
    return Promise.resolve(mpBrickController.unmount()).catch(function () {});
  }
  return Promise.resolve();
}

function createPaymentWithBackend(formData, csrfToken) {
  return fetch(apiUrl('pagos?action=create'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      csrf_token: csrfToken,
      token: formData.token,
      payment_method_id: formData.payment_method_id,
      installments: formData.installments,
      issuer_id: formData.issuer_id || null,
      payer_email: formData.payer && formData.payer.email ? formData.payer.email : '',
      identification_type: formData.payer && formData.payer.identification ? formData.payer.identification.type : '',
      identification_number: formData.payer && formData.payer.identification ? formData.payer.identification.number : ''
    })
  }).then(function (r) {
    return r.json().then(function (payload) {
      return { http: r.status, data: payload };
    });
  });
}

function mountPaymentBrick(totalAmount) {
  if (typeof window.MercadoPago !== 'function') {
    throw new Error('No se pudo cargar el SDK de Mercado Pago');
  }
  const mp = new window.MercadoPago(mpPublicKey, { locale: 'es-AR' });
  mpBricksBuilder = mp.bricks();

  // Card Payment Brick: solo tarjeta (evita el paso "elegir medio" del Payment Brick genérico).
  return fetch(apiUrl('usuarios?action=check'), { credentials: 'include' })
    .then(function (r) {
      return r.json();
    })
    .then(function (sessionUser) {
      const init = {
        amount: Number(totalAmount)
      };
      if (sessionUser && sessionUser.ok && sessionUser.email) {
        init.payer = { email: sessionUser.email };
      }
      return getCsrfToken().then(function (csrfToken) {
        return mpBricksBuilder.create('cardPayment', 'paymentBrick_container', {
          initialization: init,
          customization: {
            visual: {
              style: {
                theme: 'dark'
              }
            }
          },
          callbacks: {
            onReady: function () {
              checkoutFeedback('Completá los datos de la tarjeta para pagar.', '');
            },
            onSubmit: function (cardData) {
              var formData = cardData && typeof cardData === 'object' ? cardData : {};
              return new Promise(function (resolve, reject) {
                checkoutFeedback('Procesando pago...', 'is-pending');
                createPaymentWithBackend(formData, csrfToken)
                  .then(function (resp) {
                    var data = resp.data || {};
                    if (data.ok && data.status === 'approved') {
                      checkoutFeedback('Pago aprobado. ID: ' + (data.payment_id || '-'), 'is-ok');
                      cargarCarrito();
                      resolve();
                      return;
                    }
                    if (data.ok && (data.status === 'pending' || data.status === 'in_process')) {
                      checkoutFeedback('Pago pendiente. Te avisaremos cuando se confirme.', 'is-pending');
                      resolve();
                      return;
                    }
                    checkoutFeedback(
                      (data.msg || 'Pago rechazado. Podés reintentar sin perder tu carrito.') +
                        (data.status_detail ? ' (' + data.status_detail + ')' : ''),
                      'is-error'
                    );
                    reject(new Error(data.msg || 'rejected'));
                  })
                  .catch(function () {
                    checkoutFeedback('Error de red al procesar el pago. Reintentá.', 'is-error');
                    reject(new Error('network'));
                  });
              });
            },
            onError: function (error) {
              checkoutFeedback(
                'Error en formulario de pago: ' + (error && error.message ? error.message : ''),
                'is-error'
              );
            }
          }
        }).then(function (controller) {
          mpBrickController = controller;
          return controller;
        });
      });
    });
}

function setupFinalizarCompra() {
  const finalizarBtn = document.getElementById('finalizar-compra');
  if (finalizarBtn) {
    finalizarBtn.onclick = function () {
      if (finalizarBtn.disabled || finalizarBtn.getAttribute('aria-disabled') === 'true') {
        var lock = finalizarBtn.getAttribute('data-checkout-lock') || '';
        if (lock === 'payments_disabled') {
          window.alert(
            'Por seguridad se desactivó el pago en este sitio.\n\n' +
              'Solicitá al administrador que lo habilite de forma temporal cuando corresponda ' +
              '(variable CHECKOUT_PAYMENTS_ENABLED en el servidor).'
          );
        } else if (lock === 'login_required') {
          sessionStorage.setItem('postLoginRedirect', window.location.href);
          sessionStorage.setItem('openCartOnLoad', 'true');
          window.location.href = 'login.html';
        } else if (lock === 'not_configured') {
          window.alert('Los pagos no están configurados. Contactá al administrador del sitio.');
        } else {
          window.alert('No podés finalizar la compra en este momento.');
        }
        return;
      }
      const subtotalText = (document.getElementById('carrito-subtotal') || {}).textContent || '';
      const normalized = subtotalText.replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
      const subtotal = Number(normalized);
      if (!subtotal || subtotal <= 0) {
        checkoutFeedback('Tu carrito está vacío.', 'is-error');
        ensureCheckoutPanelOpen();
        return;
      }

      ensureCheckoutPanelOpen();
      checkoutFeedback('Inicializando checkout...', 'is-pending');

      Promise.all([fetchCheckoutConfig(), destroyBrickIfAny()])
        .then(function (results) {
          setCheckoutPaymentNotice(results[0]);
          return mountPaymentBrick(subtotal);
        })
        .catch(function (err) {
          checkoutFeedback(err && err.message ? err.message : 'No se pudo iniciar el checkout.', 'is-error');
        });
    };
  }
}

function actualizarBadgeCarrito() {
  fetch(apiUrl('carrito?action=get'), { credentials: 'include' })
    .then(function (r) {
      return r.ok ? r.json() : Promise.resolve({ total_items: 0 });
    })
    .then(function (resp) {
      const total = resp.total_items || 0;
      const badge = document.getElementById('carrito-badge');
      if (badge) badge.textContent = total > 0 ? String(total) : '';
    });
}

function setupAbrirCarrito() {
  const btnCarrito = document.getElementById('carrito-btn');
  if (btnCarrito) {
    btnCarrito.addEventListener('click', function (e) {
      e.preventDefault();
      mostrarModalCarrito();
    });
  }
}

function setupCerrarModal() {
  const cerrarBtn = document.getElementById('cerrar-modal-carrito');
  if (cerrarBtn) {
    cerrarBtn.onclick = function () {
      document.getElementById('modal-carrito').style.display = 'none';
    };
  }
  document.getElementById('modal-carrito').onclick = function (e) {
    if (e.target.id === 'modal-carrito') {
      document.getElementById('modal-carrito').style.display = 'none';
    }
  };
}

document.addEventListener('DOMContentLoaded', function () {
  setupAbrirCarrito();
  setupEliminarYActualizar();
  setupVaciarCarrito();
  setupFinalizarCompra();
  setupCerrarModal();
  actualizarBadgeCarrito();
  applyCheckoutButtonState();

  if (sessionStorage.getItem('openCartOnLoad') === 'true') {
    sessionStorage.removeItem('openCartOnLoad');
    setTimeout(function() {
      mostrarModalCarrito();
    }, 300);
  }
});
