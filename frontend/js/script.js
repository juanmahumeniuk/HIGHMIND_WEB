// ----- Código Astro (ignóralo si no usas Astro, pero lo dejo por si tu plantilla lo requiere) -----
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

// 1. Productos y modal
let productos = [];

async function cargarProductos() {
  const res = await fetch('../backend/api_productos.php');
  productos = await res.json();
  renderProductos();
}

function renderProductos() {
  const grid = document.getElementById('productos-grid');
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

// 2. Modal lógica y carrito
function setupModalYCarrito() {
  // Delegación: abrir modal
  document.getElementById('productos-grid').addEventListener('click', function (e) {
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

// 3. Función agregar al carrito (usada por el modal)
function agregarAlCarrito(id, qty = 1) {
  fetch('../backend/carrito.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'add', id, qty }),
    credentials: 'include'
  })
    .then(r => r.json())
    .then(resp => {
      if (resp.ok) {
        alert('Producto agregado al carrito');
        // Actualiza badge si existe
        if (typeof actualizarBadgeCarrito === 'function') actualizarBadgeCarrito();
      } else {
        alert(resp.msg || 'Debes iniciar sesión para agregar al carrito');
      }
    });
}

// 4. (Opcional) Badge de carrito en navbar
function actualizarBadgeCarrito() {
  fetch('../backend/carrito.php?action=get', { credentials: 'include' })
    .then(r => r.json())
    .then(resp => {
      const total = resp.total_items || 0;
      const badge = document.getElementById('carrito-badge');
      if (badge) badge.textContent = total > 0 ? total : '';
    });
}

// 5. Formulario de contacto (en contacto.html)
document.addEventListener('DOMContentLoaded', function () {
    
    // Al cargar la página, revisa si hay sesión activa
document.addEventListener('DOMContentLoaded', function() {
  checkSesionNavbar();
});

function checkSesionNavbar() {
  fetch('/backend/usuarios.php?action=check', {credentials: 'include'})
    .then(r => r.json())
    .then(resp => {
      const btn = document.getElementById('sesion-btn');
      if (!btn) return;
      if (resp.ok && resp.usuario) {
        // Si hay usuario logueado, mostrar "Cerrar Sesión"
        btn.textContent = "Cerrar sesión";
        btn.href = "#";
        btn.onclick = function(e) {
          e.preventDefault();
          logoutUsuario();
        };
      } else {
        // Si no hay usuario logueado, mostrar "Iniciar sesión"
        btn.textContent = "Iniciar sesión";
        btn.href = "login.html";
        btn.onclick = null;
      }
    });
}

function logoutUsuario() {
  fetch('/backend/usuarios.php', {
    method: 'POST',
    body: new URLSearchParams({action: 'logout'}),
    credentials: 'include'
  })
  .then(r => r.json())
  .then(resp => {
    // Vuelve a mostrar botón de login
    checkSesionNavbar();
    // Opcional: recarga la página
    location.reload();
  });
}

    
    
    
    
    
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
});

// 6. Inicialización única
document.addEventListener('DOMContentLoaded', function () {
  // Solo para páginas que tienen productos
  if (document.getElementById('productos-grid')) {
    cargarProductos().then(setupModalYCarrito);
  }
  // Actualiza el badge del carrito si existe
  if (document.getElementById('carrito-badge')) {
    actualizarBadgeCarrito();
  }
});

// 7. Otros ejemplos de uso del backend (puedes comentar o eliminar estos si no los usas):
/*
// Registro
fetch('../backend/usuarios.php', {
  method: 'POST',
  body: new URLSearchParams({action:'register', nombre:'Juan', email:'correo@ejemplo.com', password:'123456'})
}).then(r=>r.json()).then(console.log);

// Login
fetch('../backend/usuarios.php', {
  method: 'POST',
  body: new URLSearchParams({action:'login', email:'correo@ejemplo.com', password:'123456'})
}).then(r=>r.json()).then(console.log);

// Check sesión
fetch('../backend/usuarios.php?action=check').then(r=>r.json()).then(console.log);

// Logout
fetch('../backend/usuarios.php', {
  method: 'POST',
  body: new URLSearchParams({action:'logout'})
}).then(r=>r.json()).then(console.log);
*/

// 8. Carrito completo (para futuras páginas del carrito, si lo necesitas)
function obtenerCarrito() {
  fetch('../backend/carrito.php?action=get', { credentials: 'include' })
    .then(r => r.json())
    .then(resp => {
      if (resp.ok) {
        // Renderiza el carrito: resp.carrito (array de items), resp.subtotal, resp.total_items
      } else {
        // No logueado o error
      }
    });
}
function quitarDelCarrito(id) {
  fetch('../backend/carrito.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'remove', id }),
    credentials: 'include'
  }).then(r => r.json()).then(resp => obtenerCarrito());
}
function limpiarCarrito() {
  fetch('../backend/carrito.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'clear' }),
    credentials: 'include'
  }).then(r => r.json()).then(resp => obtenerCarrito());
}
