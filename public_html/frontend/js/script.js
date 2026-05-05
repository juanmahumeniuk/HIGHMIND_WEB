// ---- VARIABLES ----
let productos = [];

function mostrarErrorProductosGrid(httpStatus, data) {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  const p = document.createElement('p');
  p.className = 'productos-error';
  p.style.textAlign = 'center';
  p.style.color = '#ff8d8d';
  p.style.padding = '2rem';
  let msg = 'No se pudo cargar el catálogo.';
  if (data && data.error) {
    msg += ' Comprobá que MySQL esté en marcha y que .env tenga bien DB_*.';
  }
  msg += ' (HTTP ' + httpStatus + ')';
  p.textContent = msg;
  grid.replaceChildren(p);
}

function safeImgSrc(src) {
  const s = String(src || '').trim();
  if (/^javascript:/i.test(s) || /^data:/i.test(s)) {
    return '';
  }
  return s;
}

// ---- FUNCIONES DE PRODUCTOS Y MODAL ----

// Carga todos los productos (para tienda)
async function cargarProductosTienda() {
  const res = await fetch(apiUrl('productos'));
  const data = await res.json();
  if (!Array.isArray(data)) {
    productos = [];
    mostrarErrorProductosGrid(res.status, data);
    return;
  }
  productos = data;
  renderProductos();
  setupModalYCarrito();
}

// Carga solo 4 productos random (para home)
async function cargarProductosHome() {
  const res = await fetch(apiUrl('productos'));
  const data = await res.json();
  if (!Array.isArray(data)) {
    productos = [];
    mostrarErrorProductosGrid(res.status, data);
    return;
  }
  productos = data;
  productos = productos.sort(() => Math.random() - 0.5).slice(0, 4);
  renderProductos();
  setupModalYCarrito();
}

// Renderiza el grid de productos (sin innerHTML con datos de API)
function renderProductos() {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  grid.replaceChildren();
  productos.forEach(function (p, idx) {
    const card = document.createElement('div');
    card.className = 'card';
    card.setAttribute('data-idx', String(idx));
    card.setAttribute('data-id', String(p.id));
    const img = document.createElement('img');
    const src = safeImgSrc(p.img);
    if (src) img.src = src;
    img.alt = String(p.nombre || '');
    const body = document.createElement('div');
    body.className = 'card-body';
    const h3 = document.createElement('h3');
    h3.textContent = String(p.nombre || '');
    const precioEl = document.createElement('div');
    precioEl.className = 'card-precio';
    precioEl.textContent = '$' + Number(p.precio).toLocaleString('es-AR');
    body.appendChild(h3);
    body.appendChild(precioEl);
    card.appendChild(img);
    card.appendChild(body);
    grid.appendChild(card);
  });
}

// Modal de productos y lógica de agregar al carrito
function setupModalYCarrito() {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  grid.addEventListener('click', function (e) {
    const card = e.target.closest('.card');
    if (!card) return;
    const idx = parseInt(card.getAttribute('data-idx'), 10);
    const p = productos[idx];
    const imgEl = document.getElementById('modal-img');
    const src = safeImgSrc(p.img);
    if (src) imgEl.src = src;
    imgEl.alt = String(p.nombre || '');
    document.getElementById('modal-nombre').textContent = String(p.nombre || '');
    document.getElementById('modal-descripcion').textContent = p.descripcion || '';
    const extra = document.getElementById('modal-extra');
    extra.replaceChildren();
    const bPrecio = document.createElement('b');
    bPrecio.textContent = 'Precio: ';
    extra.appendChild(bPrecio);
    extra.appendChild(
      document.createTextNode('$' + Number(p.precio).toLocaleString('es-AR'))
    );
    extra.appendChild(document.createElement('br'));
    const bStock = document.createElement('b');
    bStock.textContent = 'Stock: ';
    extra.appendChild(bStock);
    extra.appendChild(document.createTextNode(String(p.stock)));
    document.getElementById('modal-producto').setAttribute('data-id', p.id);
    document.getElementById('modal-producto').style.display = 'flex';
  });

  const btnAgregarCarrito = document.getElementById('agregar-carrito');
  if (btnAgregarCarrito) {
    btnAgregarCarrito.onclick = function () {
      const modal = document.getElementById('modal-producto');
      const prodId = modal.getAttribute('data-id');
      agregarAlCarrito(prodId, 1);
      modal.style.display = 'none';
    };
  }

  const cerrarModal = document.getElementById('cerrar-modal');
  if (cerrarModal) {
    cerrarModal.onclick = function () {
      document.getElementById('modal-producto').style.display = 'none';
    };
  }

  const modalProducto = document.getElementById('modal-producto');
  if (modalProducto) {
    modalProducto.onclick = function (e) {
      if (e.target.id === 'modal-producto') {
        modalProducto.style.display = 'none';
      }
    };
  }
}

// ---- CARRITO ----
function agregarAlCarrito(id, qty) {
  qty = qty == null ? 1 : qty;
  getCsrfToken()
    .then(function (csrf) {
      return fetch(apiUrl('carrito'), {
        method: 'POST',
        body: new URLSearchParams({
          action: 'add',
          id: id,
          qty: qty,
          csrf_token: csrf
        }),
        credentials: 'include'
      });
    })
    .then(function (r) {
      return r.json();
    })
    .then(function (resp) {
      if (resp.ok) {
        alert('Producto agregado al carrito');
        if (typeof actualizarBadgeCarrito === 'function') actualizarBadgeCarrito();
        const mc = document.getElementById('modal-carrito');
        if (mc && mc.style.display === 'flex' && typeof cargarCarrito === 'function') {
          cargarCarrito();
        }
      } else {
        if (confirm((resp.msg || 'Debes iniciar sesión para agregar al carrito') + '\n\n¿Ir a iniciar sesión?')) {
          sessionStorage.setItem('postLoginRedirect', window.location.href);
          window.location.href = 'login.html';
        }
      }
    });
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

// ---- LOGIN/LOGOUT EN NAVBAR ----
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

function checkSesionNavbar() {
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
      btn.onclick = function() {
        sessionStorage.setItem('postLoginRedirect', window.location.href);
      };
    }
  });
}

// ---- CONTACTO ----
function setupContacto() {
  const form = document.getElementById('form-contacto');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const respEl = document.getElementById('contacto-respuesta');
    const nombre = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();
    respEl.textContent = 'Enviando…';
    getCsrfToken()
      .then(function (csrf) {
        return fetch(apiUrl('contacto'), {
          method: 'POST',
          body: new URLSearchParams({
            nombre: nombre,
            email: email,
            mensaje: mensaje,
            csrf_token: csrf
          }),
          credentials: 'include'
        });
      })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        respEl.textContent = data.ok
          ? data.msg || 'Mensaje enviado.'
          : data.msg || 'No se pudo enviar.';
        respEl.style.color = data.ok ? '#99e772' : '#ff8d8d';
        if (data.ok) form.reset();
        setTimeout(function () {
          respEl.textContent = '';
        }, 6000);
      })
      .catch(function () {
        respEl.textContent = 'Error de red. Intentá de nuevo.';
        respEl.style.color = '#ff8d8d';
      });
  });
}

// ---- INICIALIZACIÓN ----
document.addEventListener('DOMContentLoaded', function () {
  const pathname = window.location.pathname;

  if (pathname.endsWith('tienda.html')) {
    cargarProductosTienda();
  } else if (document.getElementById('productos-grid')) {
    cargarProductosHome();
  }

  if (document.getElementById('carrito-badge')) {
    actualizarBadgeCarrito();
  }

  checkSesionNavbar();
  setupContacto();
});
