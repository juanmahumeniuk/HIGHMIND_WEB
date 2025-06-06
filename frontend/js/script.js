// (Opcional) Código Astro, ignóralo si no lo usás
(function(){
  const postDate = null;
  const currentDate = new Date().setHours(0, 0, 0, 0);
  const postPublishDate = new Date(postDate).setHours(0, 0, 0, 0);
  if (postPublishDate && currentDate < postPublishDate) {
    window.location.replace('/');
  }
})();
(() => {
  var e = async t => { await (await t())() };
  (self.Astro || (self.Astro = {})).only = e;
  window.dispatchEvent(new Event("astro:only"));
})();
(() => {
  var e = async t => { await (await t())() };
  (self.Astro || (self.Astro = {})).load = e;
  window.dispatchEvent(new Event("astro:load"));
})();

// ---- VARIABLES ----
let productos = [];

// ---- FUNCIONES DE PRODUCTOS Y MODAL ----

// Carga todos los productos (para tienda)
async function cargarProductosTienda() {
  const res = await fetch('/backend/api_productos.php');
  productos = await res.json();
  renderProductos();
  setupModalYCarrito();
}

// Carga solo 4 productos random (para home)
async function cargarProductosHome() {
  const res = await fetch('/backend/api_productos.php');
  productos = await res.json();
  // Randomiza y toma solo 4
  productos = productos.sort(() => Math.random() - 0.5).slice(0, 4);
  renderProductos();
  setupModalYCarrito();
}

// Renderiza el grid de productos
function renderProductos() {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  grid.innerHTML = '';
  productos.forEach((p, idx) => {
    const card = document.createElement('div');
    card.className = 'card';
    card.setAttribute('data-idx', idx);
    card.setAttribute('data-id', p.id);
    card.innerHTML = `
      <img src="${p.img}" alt="${p.nombre}">
      <div class="card-body">
        <h3>${p.nombre}</h3>
        <div class="card-precio">$${Number(p.precio).toLocaleString('es-AR')}</div>
      </div>
    `;
    grid.appendChild(card);
  });
}

// Modal de productos y lógica de agregar al carrito
function setupModalYCarrito() {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  grid.addEventListener('click', function (e) {
    let card = e.target.closest('.card');
    if (!card) return;
    const idx = parseInt(card.getAttribute('data-idx'));
    const p = productos[idx];
    document.getElementById('modal-img').src = p.img;
    document.getElementById('modal-img').alt = p.nombre;
    document.getElementById('modal-nombre').textContent = p.nombre;
    document.getElementById('modal-descripcion').textContent = p.descripcion || '';
    document.getElementById('modal-extra').innerHTML =
      `<b>Precio:</b> $${Number(p.precio).toLocaleString('es-AR')}<br>
       <b>Stock:</b> ${p.stock}`;
    document.getElementById('modal-producto').setAttribute('data-id', p.id);
    document.getElementById('modal-producto').style.display = 'flex';
  });

  // Botón agregar al carrito
  const btnAgregarCarrito = document.getElementById('agregar-carrito');
  if (btnAgregarCarrito) {
    btnAgregarCarrito.onclick = function () {
      const modal = document.getElementById('modal-producto');
      const prodId = modal.getAttribute('data-id');
      agregarAlCarrito(prodId, 1);
      modal.style.display = 'none';
    };
  }

  // Cerrar modal con botón
  const cerrarModal = document.getElementById('cerrar-modal');
  if (cerrarModal) {
    cerrarModal.onclick = function () {
      document.getElementById('modal-producto').style.display = 'none';
    };
  }

  // Cerrar modal haciendo click fuera del contenido
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
function agregarAlCarrito(id, qty = 1) {
  fetch('/backend/carrito.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'add', id, qty }),
    credentials: 'include'
  })
    .then(r => r.json())
    .then(resp => {
      if (resp.ok) {
        alert('Producto agregado al carrito');
        if (typeof actualizarBadgeCarrito === 'function') actualizarBadgeCarrito();
      } else {
        alert(resp.msg || 'Debes iniciar sesión para agregar al carrito');
      }
    });
}

function actualizarBadgeCarrito() {
  fetch('/backend/carrito.php?action=get', { credentials: 'include' })
    .then(r => r.json())
    .then(resp => {
      const total = resp.total_items || 0;
      const badge = document.getElementById('carrito-badge');
      if (badge) badge.textContent = total > 0 ? total : '';
    });
}

// ---- LOGIN/LOGOUT EN NAVBAR ----
window.checkLogin = function(callback) {
  fetch('/backend/usuarios.php?action=check', { credentials:'include' })
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

function checkSesionNavbar() {
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
}

// ---- CONTACTO ----
function setupContacto() {
  const form = document.getElementById('form-contacto');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const resp = document.getElementById('contacto-respuesta');
      resp.textContent = "¡Gracias por tu mensaje! Te responderemos a la brevedad.";
      form.reset();
      setTimeout(() => { resp.textContent = ""; }, 6000);
    });
  }
}

// ---- INICIALIZACIÓN ----
document.addEventListener('DOMContentLoaded', function () {
  const pathname = window.location.pathname;

  // Productos: según página
  if (pathname.endsWith('tienda.html')) {
    cargarProductosTienda();
  } else if (document.getElementById('productos-grid')) {
    cargarProductosHome();
  }

  // Badge del carrito
  if (document.getElementById('carrito-badge')) {
    actualizarBadgeCarrito();
  }

  // Botón login/logout en navbar
  checkSesionNavbar();

  // Formulario de contacto (si existe en la página)
  setupContacto();
});
