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

async function fetchProductosActivos() {
  const res = await fetch(apiUrl('productos'));
  const data = await res.json();
  if (!Array.isArray(data)) {
    productos = [];
    mostrarErrorProductosGrid(res.status, data);
    return false;
  }
  productos = data;
  return true;
}

async function cargarProductosTienda() {
  if (await fetchProductosActivos()) {
    renderProductos();
    setupModalYCarrito();
  }
}

async function cargarProductosHome() {
  if (await fetchProductosActivos()) {
    productos = productos.sort(() => Math.random() - 0.5).slice(0, 4);
    renderProductos();
    setupModalYCarrito();
  }
}

function renderProductos() {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  grid.replaceChildren();
  productos.forEach(function (p, idx) {
    const card = document.createElement('div');
    card.className = 'card reveal';
    card.setAttribute('data-idx', String(idx));
    card.setAttribute('data-id', String(p.id));
    card.setAttribute('data-reveal-delay', String((idx % 4) * 90));

    const media = document.createElement('div');
    media.className = 'card-media';
    const img = document.createElement('img');
    const src = safeImgSrc(p.img);
    if (src) img.src = src;
    img.alt = String(p.nombre || '');
    img.loading = 'lazy';

    const overlay = document.createElement('div');
    overlay.className = 'card-overlay';
    const verBtn = document.createElement('span');
    verBtn.className = 'card-ver';
    verBtn.textContent = 'Ver producto';
    overlay.appendChild(verBtn);

    const lowStock = p.stock != null && Number(p.stock) > 0 && Number(p.stock) <= 5;
    if (lowStock) {
      const tag = document.createElement('span');
      tag.className = 'card-tag';
      tag.textContent = '¡Últimas unidades!';
      media.appendChild(tag);
    }

    media.appendChild(img);
    media.appendChild(overlay);

    const body = document.createElement('div');
    body.className = 'card-body';
    const h3 = document.createElement('h3');
    h3.textContent = String(p.nombre || '');
    const precioEl = document.createElement('div');
    precioEl.className = 'card-precio';
    precioEl.textContent = formatPrecio(p.precio);
    body.appendChild(h3);
    body.appendChild(precioEl);

    card.appendChild(media);
    card.appendChild(body);
    grid.appendChild(card);
  });

  if (typeof window.HMRevealRefresh === 'function') {
    window.HMRevealRefresh();
  }
}

function stockBadgeInfo(stock) {
  var n = Number(stock);
  if (!Number.isFinite(n) || n <= 0) {
    return { text: 'Agotado', className: 'is-out' };
  }
  if (n <= 5) {
    return { text: 'Últimas ' + n + ' unidades', className: 'is-low' };
  }
  return { text: 'En stock', className: 'is-ok' };
}

function abrirModalProducto(p) {
  var modal = document.getElementById('modal-producto');
  if (!modal) return;

  var imgEl = document.getElementById('modal-img');
  var src = safeImgSrc(p.img);
  if (src) imgEl.src = src;
  else imgEl.removeAttribute('src');
  imgEl.alt = String(p.nombre || '');

  document.getElementById('modal-nombre').textContent = String(p.nombre || '');
  document.getElementById('modal-precio').textContent = formatPrecio(p.precio);

  var desc = document.getElementById('modal-descripcion');
  var descripcion = (p.descripcion || '').trim();
  desc.textContent = descripcion || 'Sin descripción disponible por ahora.';
  desc.classList.toggle('is-empty', !descripcion);

  var stockEl = document.getElementById('modal-stock');
  var stockInfo = stockBadgeInfo(p.stock);
  stockEl.textContent = stockInfo.text;
  stockEl.className = 'modal-stock-badge ' + stockInfo.className;

  var btnAgregar = document.getElementById('agregar-carrito');
  var sinStock = Number(p.stock) <= 0;
  btnAgregar.disabled = sinStock;
  btnAgregar.textContent = sinStock ? 'Sin stock disponible' : 'Agregar al carrito';
  btnAgregar.classList.toggle('btn-disabled', sinStock);

  modal.setAttribute('data-id', p.id);
  modal.style.display = 'flex';
  document.body.classList.add('modal-open');
  modal.classList.remove('is-closing');
  document.getElementById('cerrar-modal').focus();
}

function cerrarModalProducto() {
  var modal = document.getElementById('modal-producto');
  if (!modal || modal.style.display === 'none') return;
  modal.classList.add('is-closing');
  window.setTimeout(function () {
    modal.style.display = 'none';
    modal.classList.remove('is-closing');
    document.body.classList.remove('modal-open');
  }, 180);
}

function setupModalYCarrito() {
  const grid = document.getElementById('productos-grid');
  if (!grid) return;
  grid.addEventListener('click', function (e) {
    const card = e.target.closest('.card');
    if (!card) return;
    const idx = parseInt(card.getAttribute('data-idx'), 10);
    const p = productos[idx];
    abrirModalProducto(p);
  });

  const btnAgregarCarrito = document.getElementById('agregar-carrito');
  if (btnAgregarCarrito) {
    btnAgregarCarrito.onclick = function () {
      if (btnAgregarCarrito.disabled) return;
      const modal = document.getElementById('modal-producto');
      const prodId = modal.getAttribute('data-id');
      agregarAlCarrito(prodId, 1);
      cerrarModalProducto();
    };
  }

  const cerrarModal = document.getElementById('cerrar-modal');
  if (cerrarModal) {
    cerrarModal.onclick = cerrarModalProducto;
  }

  const modalProducto = document.getElementById('modal-producto');
  if (modalProducto) {
    modalProducto.onclick = function (e) {
      if (e.target.id === 'modal-producto') {
        cerrarModalProducto();
      }
    };
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalProducto.style.display === 'flex') {
        cerrarModalProducto();
      }
    });
  }
}

function agregarAlCarrito(id, qty) {
  apiPostCart('add', { id: id, qty: qty == null ? 1 : qty }).then(function (resp) {
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

function setupContacto() {
  const form = document.getElementById('form-contacto');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const nombre = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();
    setFeedback('contacto-respuesta', 'Enviando…', null);
    apiPost('contacto', { nombre: nombre, email: email, mensaje: mensaje })
      .then(function (r) {
        var data = r.json;
        setFeedback('contacto-respuesta', data.ok ? (data.msg || 'Mensaje enviado.') : (data.msg || 'No se pudo enviar.'), data.ok);
        if (data.ok) form.reset();
        setTimeout(function () {
          setFeedback('contacto-respuesta', '', null);
        }, 6000);
      })
      .catch(function () {
        setFeedback('contacto-respuesta', 'Error de red. Intentá de nuevo.', false);
      });
  });
}

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

  if (typeof wireSesionNavbar === 'function') {
    wireSesionNavbar();
  }
  setupContacto();
});
