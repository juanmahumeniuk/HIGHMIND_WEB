// ---- Carrito Modal y Lógica ----

// ABRIR Y CERRAR MODAL
function mostrarModalCarrito() {
  document.getElementById('modal-carrito').style.display = 'flex';
  cargarCarrito();
}

// MOSTRAR CARRITO
function cargarCarrito() {
  fetch('../backend/carrito.php?action=get', {credentials:'include'})
    .then(r => r.json())
    .then(resp => {
      const items = resp.carrito || [];
      const div = document.getElementById('carrito-items');
      div.innerHTML = '';
      if (!items.length) {
        div.innerHTML = '<div style="text-align:center;color:#aaa">El carrito está vacío.</div>';
      }
      items.forEach(item => {
        const fila = document.createElement('div');
        fila.className = 'carrito-item';
        fila.innerHTML = `
          <img src="${item.img}" alt="">
          <div class="carrito-info">
            <div class="carrito-nombre">${item.nombre}</div>
            <div class="carrito-precio">$${Number(item.precio).toLocaleString('es-AR')}</div>
            <div class="carrito-cantidad">
              Cantidad:
              <input type="number" min="1" value="${item.cantidad}" data-id="${item.producto_id}">
            </div>
          </div>
          <button class="carrito-remove" data-id="${item.producto_id}" title="Quitar">&#128465;</button>
        `;
        div.appendChild(fila);
      });
      document.getElementById('carrito-subtotal').textContent =
        '$' + Number(resp.subtotal || 0).toLocaleString('es-AR');
      actualizarBadgeCarrito();
    });
}

// QUITAR ITEM DEL CARRITO
function setupEliminarYActualizar() {
  // Eliminar producto
  document.getElementById('carrito-items').addEventListener('click', function(e) {
    if (e.target.classList.contains('carrito-remove')) {
      const id = e.target.getAttribute('data-id');
      fetch('../backend/carrito.php', {
        method: 'POST',
        body: new URLSearchParams({action:'remove', id}),
        credentials: 'include'
      }).then(() => cargarCarrito());
    }
  });

  // Cambiar cantidad
  document.getElementById('carrito-items').addEventListener('change', function(e) {
    if (e.target.type === 'number') {
      const id = e.target.getAttribute('data-id');
      let qty = parseInt(e.target.value) || 1;
      fetch('../backend/carrito.php', {
        method: 'POST',
        body: new URLSearchParams({action:'update', id, qty}),
        credentials: 'include'
      }).then(() => cargarCarrito());
    }
  });
}

// VACIAR CARRITO
function setupVaciarCarrito() {
  const vaciarBtn = document.getElementById('vaciar-carrito');
  if (vaciarBtn) {
    vaciarBtn.onclick = function() {
      if (confirm('¿Vaciar todo el carrito?')) {
        fetch('../backend/carrito.php', {
          method: 'POST',
          body: new URLSearchParams({action:'clear'}),
          credentials: 'include'
        }).then(() => cargarCarrito());
      }
    };
  }
}

// FINALIZAR COMPRA (demo)
function setupFinalizarCompra() {
  const finalizarBtn = document.getElementById('finalizar-compra');
  if (finalizarBtn) {
    finalizarBtn.onclick = function() {
      alert('Aquí iría el flujo de pago o pedido. (Falta implementar)');
    };
  }
}

// ACTUALIZAR BADGE DEL CARRITO EN NAVBAR
function actualizarBadgeCarrito() {
  fetch('../backend/carrito.php?action=get', {credentials:'include'})
    .then(r => r.json())
    .then(resp => {
      const total = resp.total_items || 0;
      document.getElementById('carrito-badge').textContent = total > 0 ? total : '';
    });
}

// ABRIR MODAL AL HACER CLICK EN EL NAV
function setupAbrirCarrito() {
  const btnCarrito = document.getElementById('carrito-btn');
  if (btnCarrito) {
    btnCarrito.addEventListener('click', function(e) {
      e.preventDefault();
      mostrarModalCarrito();
    });
  }
}

// CERRAR MODAL (BOTÓN Y CLICK FUERA)
function setupCerrarModal() {
  const cerrarBtn = document.getElementById('cerrar-modal-carrito');
  if (cerrarBtn) {
    cerrarBtn.onclick = () =>
      document.getElementById('modal-carrito').style.display = 'none';
  }
  document.getElementById('modal-carrito').onclick = function(e) {
    if (e.target.id === 'modal-carrito') {
      document.getElementById('modal-carrito').style.display = 'none';
    }
  };
}

// ---- INICIALIZAR TODO ----
document.addEventListener('DOMContentLoaded', function() {
  setupAbrirCarrito();
  setupEliminarYActualizar();
  setupVaciarCarrito();
  setupFinalizarCompra();
  setupCerrarModal();
  actualizarBadgeCarrito();
});
