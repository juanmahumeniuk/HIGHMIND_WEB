(function () {
  'use strict';

  var resetCsrf = window.resetCsrfTokenCache;

  function setMsg(text, ok) {
    setFeedback('msg', text, ok, { ok: 'var(--color-success)', err: 'var(--color-danger)' });
  }

  function buildToolbar(options) {
    var toolbar = el('div', { class: 'toolbar' });
    var search = el('input', { type: 'search', placeholder: 'Buscar…', 'aria-label': 'Buscar' });
    search.addEventListener('input', function (e) {
      state.filter = e.target.value;
      options.onFilter();
    });
    if (state.filter) search.value = state.filter;
    toolbar.appendChild(search);
    if (options.newLabel && options.onNew) {
      var btnNew = el('button', { class: 'btn btn-primary', type: 'button', text: options.newLabel });
      btnNew.addEventListener('click', options.onNew);
      toolbar.appendChild(btnNew);
    }
    (options.extra || []).forEach(function (node) {
      toolbar.appendChild(node);
    });
    return toolbar;
  }

  function confirmPost(message, path, params, onSuccess) {
    if (!confirm(message)) return;
    apiPost(path, params).then(function (r) {
      setMsg(r.json.msg || (r.json.ok ? 'Listo' : 'Error'), r.json.ok);
      if (r.json.ok && onSuccess) onSuccess();
    });
  }

  function revokeAdminPreviewUrl() {
    if (window.__adminPreviewObjectUrl) {
      URL.revokeObjectURL(window.__adminPreviewObjectUrl);
      window.__adminPreviewObjectUrl = null;
    }
  }

  var state = {
    section: 'productos',
    user: null,
    filter: '',
  };

  function openModal(title) {
    revokeAdminPreviewUrl();
    var backdrop = document.getElementById('modal-backdrop');
    var titleEl = document.getElementById('modal-title');
    var body = document.getElementById('modal-body');
    var actions = document.getElementById('modal-actions');
    titleEl.textContent = title;
    body.innerHTML = '';
    actions.innerHTML = '';
    backdrop.classList.add('open');
    return { body: body, actions: actions, close: closeModal };
  }

  function closeModal() {
    revokeAdminPreviewUrl();
    document.getElementById('modal-backdrop').classList.remove('open');
  }

  function navActive(name) {
    document.querySelectorAll('.admin-sidebar button').forEach(function (b) {
      b.classList.toggle('active', b.getAttribute('data-section') === name);
    });
  }

  function applyFilter(rows, keys) {
    var q = state.filter.trim().toLowerCase();
    if (!q) return rows;
    return rows.filter(function (row) {
      return keys.some(function (k) {
        var v = row[k];
        return v != null && String(v).toLowerCase().indexOf(q) !== -1;
      });
    });
  }

  function renderProductos() {
    var host = document.getElementById('content');
    host.innerHTML = '';
    host.appendChild(
      buildToolbar({
        onFilter: renderProductos,
        newLabel: 'Nuevo producto',
        onNew: function () {
          modalProducto(null);
        },
      })
    );

    apiGet('admin/productos').then(function (res) {
      if (!res.json.ok) {
        setMsg(res.json.msg || 'Error al cargar productos', false);
        return;
      }
      var items = applyFilter(res.json.items || [], ['id', 'nombre', 'precio', 'stock', 'img']);
      var wrap = el('div', { class: 'table-wrap' });
      var table = el('table', { class: 'data' });
      var thead = el('thead');
      var hr = el('tr');
      ['ID', 'Nombre', 'Precio', 'Stock', 'Activo', 'Imagen', 'Acciones'].forEach(function (h) {
        hr.appendChild(el('th', { text: h }));
      });
      thead.appendChild(hr);
      table.appendChild(thead);
      var tb = el('tbody');
      items.forEach(function (p) {
        var tr = el('tr');
        tr.appendChild(el('td', { text: String(p.id) }));
        tr.appendChild(el('td', { text: String(p.nombre || '') }));
        tr.appendChild(el('td', { text: String(p.precio) }));
        tr.appendChild(el('td', { text: String(p.stock != null ? p.stock : '') }));
        var tdAct = el('td');
        var badge = el('span', {
          class: 'badge' + (Number(p.activo) === 1 ? ' on' : ''),
          text: Number(p.activo) === 1 ? 'Sí' : 'No',
        });
        tdAct.appendChild(badge);
        tr.appendChild(tdAct);
        tr.appendChild(el('td', { text: String(p.img || '').slice(0, 40) }));
        var tdActn = el('td', { class: 'actions' });
        var bEdit = el('button', { class: 'btn btn-small', type: 'button', text: 'Editar' });
        bEdit.addEventListener('click', function () {
          modalProducto(p);
        });
        var bDel = el('button', { class: 'btn btn-small btn-danger', type: 'button', text: 'Desactivar' });
        bDel.addEventListener('click', function () {
          confirmPost('¿Desactivar este producto?', 'admin/productos/' + p.id, { action: 'delete' }, renderProductos);
        });
        tdActn.appendChild(bEdit);
        tdActn.appendChild(document.createTextNode(' '));
        tdActn.appendChild(bDel);
        tr.appendChild(tdActn);
        tb.appendChild(tr);
      });
      table.appendChild(tb);
      wrap.appendChild(table);
      host.appendChild(wrap);
    });
  }

  function modalProducto(p) {
    var m = openModal(p ? 'Editar producto' : 'Nuevo producto');
    var hiddenImg = el('input', { type: 'hidden', name: 'img', value: p ? String(p.img || '').trim() : '' });
    var fileInput = el('input', {
      type: 'file',
      name: 'imagen',
      accept: 'image/jpeg,image/png,image/webp,image/gif',
    });
    var previewWrap = el('div', { class: 'admin-product-preview-wrap' });
    var previewImg = el('img', {
      class: 'admin-product-preview',
      alt: 'Vista previa',
    });
    previewWrap.appendChild(previewImg);

    function setPreviewFromFile(file) {
      revokeAdminPreviewUrl();
      if (file) {
        var url = URL.createObjectURL(file);
        window.__adminPreviewObjectUrl = url;
        previewImg.src = url;
      } else {
        previewImg.removeAttribute('src');
        var rel = hiddenImg.value.trim();
        if (rel) {
          previewImg.src = frontendAssetUrl(rel);
        }
      }
    }

    fileInput.addEventListener('change', function () {
      var file = fileInput.files && fileInput.files[0];
      setPreviewFromFile(file || null);
    });

    var f = {
      nombre: el('input', { type: 'text', name: 'nombre', value: p ? String(p.nombre || '') : '' }),
      descripcion: el('textarea', { name: 'descripcion' }),
      precio: el('input', { type: 'text', name: 'precio', value: p ? String(p.precio) : '' }),
      stock: el('input', { type: 'number', name: 'stock', min: '0', value: p && p.stock != null ? String(p.stock) : '0' }),
      activo: el('select', { name: 'activo' }),
    };
    f.descripcion.value = p ? String(p.descripcion || '') : '';
    ;[['1', 'Activo'], ['0', 'Inactivo']].forEach(function (opt) {
      var o = el('option', { value: opt[0], text: opt[1] });
      f.activo.appendChild(o);
    });
    f.activo.value = p && Number(p.activo) === 0 ? '0' : '1';

    if (hiddenImg.value) {
      previewImg.src = frontendAssetUrl(hiddenImg.value);
    }

    function row(label, node) {
      m.body.appendChild(el('label', { text: label }));
      m.body.appendChild(node);
    }
    row('Nombre', f.nombre);
    row('Descripción', f.descripcion);
    row('Precio', f.precio);
    m.body.appendChild(el('label', { text: 'Imagen' }));
    m.body.appendChild(
      el('p', {
        class: 'muted',
        text: 'Elegí un archivo desde tu PC (máx. 6 MB). Al editar, si no cambiás la imagen se mantiene la actual.',
      })
    );
    m.body.appendChild(fileInput);
    m.body.appendChild(previewWrap);
    m.body.appendChild(hiddenImg);
    row('Stock', f.stock);
    row('Estado', f.activo);

    var btnSave = el('button', { class: 'btn btn-primary', type: 'button', text: 'Guardar' });
    var btnCancel = el('button', { class: 'btn', type: 'button', text: 'Cancelar' });
    btnCancel.addEventListener('click', closeModal);
    m.actions.appendChild(btnCancel);
    m.actions.appendChild(btnSave);

    btnSave.addEventListener('click', function () {
      var fd = new FormData();
      fd.set('nombre', f.nombre.value.trim());
      fd.set('descripcion', f.descripcion.value.trim());
      fd.set('precio', f.precio.value.trim());
      fd.set('stock', String(parseInt(f.stock.value, 10) || 0));
      fd.set('activo', f.activo.value);
      if (hiddenImg.value.trim()) {
        fd.set('img', hiddenImg.value.trim());
      }
      var file = fileInput.files && fileInput.files[0];
      if (file) {
        fd.set('imagen', file, file.name);
      }
      var tail = p ? 'admin/productos/' + p.id : 'admin/productos';
      if (p) {
        fd.set('action', 'update');
      }
      apiPostFormData(tail, fd).then(function (r) {
        setMsg(r.json.msg || (r.json.ok ? 'Guardado' : 'Error'), r.json.ok);
        if (r.json.ok) {
          closeModal();
          renderProductos();
        }
      });
    });
  }

  function renderUsuarios() {
    var host = document.getElementById('content');
    host.innerHTML = '';
    host.appendChild(
      buildToolbar({
        onFilter: renderUsuarios,
        newLabel: 'Nuevo usuario',
        onNew: function () {
          modalUsuario(null);
        },
      })
    );

    apiGet('admin/usuarios').then(function (res) {
      if (!res.json.ok) {
        setMsg(res.json.msg || 'Error', false);
        return;
      }
      var items = applyFilter(res.json.items || [], ['id', 'email', 'nombre']);
      var wrap = el('div', { class: 'table-wrap' });
      var table = el('table', { class: 'data' });
      var thead = el('tr');
      ['ID', 'Email', 'Nombre', 'Admin', 'Creado', 'Acciones'].forEach(function (h) {
        thead.appendChild(el('th', { text: h }));
      });
      table.appendChild(el('thead')).appendChild(thead);
      var tb = el('tbody');
      items.forEach(function (u) {
        var tr = el('tr');
        tr.appendChild(el('td', { text: String(u.id) }));
        tr.appendChild(el('td', { text: String(u.email || '') }));
        tr.appendChild(el('td', { text: String(u.nombre || '') }));
        var tdAd = el('td');
        tdAd.appendChild(
          el('span', {
            class: 'badge' + (Number(u.es_admin) === 1 ? ' on' : ''),
            text: Number(u.es_admin) === 1 ? 'Sí' : 'No',
          })
        );
        tr.appendChild(tdAd);
        tr.appendChild(el('td', { text: String(u.creado || '') }));
        var td = el('td', { class: 'actions' });
        var b1 = el('button', { class: 'btn btn-small', type: 'button', text: 'Editar' });
        b1.addEventListener('click', function () {
          modalUsuario(u);
        });
        var b3 = el('button', { class: 'btn btn-small btn-danger', type: 'button', text: 'Eliminar' });
        b3.addEventListener('click', function () {
          confirmPost(
            '¿Eliminar usuario? Debe tener carrito vacío.',
            'admin/usuarios/' + u.id,
            { action: 'delete' },
            renderUsuarios
          );
        });
        td.appendChild(b1);
        td.appendChild(document.createTextNode(' '));
        td.appendChild(b3);
        tr.appendChild(td);
        tb.appendChild(tr);
      });
      table.appendChild(tb);
      wrap.appendChild(table);
      host.appendChild(wrap);
    });
  }

  function modalUsuario(u) {
    var m = openModal(u ? 'Editar usuario' : 'Nuevo usuario');
    var f = {
      email: el('input', { type: 'email', value: u ? String(u.email || '') : '' }),
      nombre: el('input', { type: 'text', value: u ? String(u.nombre || '') : '' }),
      password: el('input', { type: 'password', placeholder: u ? '' : 'Mínimo 6 caracteres' }),
      es_admin: el('select', {}),
    };
    ;[
      ['0', 'No'],
      ['1', 'Sí'],
    ].forEach(function (o) {
      f.es_admin.appendChild(el('option', { value: o[0], text: 'Admin: ' + o[1] }));
    });
    f.es_admin.value = u && Number(u.es_admin) === 1 ? '1' : '0';

    m.body.appendChild(el('label', { text: 'Email' }));
    m.body.appendChild(f.email);
    m.body.appendChild(el('label', { text: 'Nombre' }));
    m.body.appendChild(f.nombre);
    if (!u) {
      m.body.appendChild(el('label', { text: 'Contraseña' }));
      m.body.appendChild(f.password);
    }
    m.body.appendChild(el('label', { text: 'Rol' }));
    m.body.appendChild(f.es_admin);

    var btnSave = el('button', { class: 'btn btn-primary', type: 'button', text: 'Guardar' });
    var btnCancel = el('button', { class: 'btn', type: 'button', text: 'Cancelar' });
    btnCancel.addEventListener('click', closeModal);
    m.actions.appendChild(btnCancel);
    m.actions.appendChild(btnSave);

    btnSave.addEventListener('click', function () {
      if (!u) {
        apiPost('admin/usuarios', {
          email: f.email.value.trim(),
          nombre: f.nombre.value.trim(),
          password: f.password.value,
          es_admin: f.es_admin.value,
        }).then(function (r) {
          setMsg(r.json.msg || (r.json.ok ? 'Creado' : 'Error'), r.json.ok);
          if (r.json.ok) {
            closeModal();
            renderUsuarios();
          }
        });
      } else {
        apiPost('admin/usuarios/' + u.id, {
          action: 'update',
          email: f.email.value.trim(),
          nombre: f.nombre.value.trim(),
          es_admin: f.es_admin.value,
        }).then(function (r) {
          setMsg(r.json.msg || (r.json.ok ? 'Guardado' : 'Error'), r.json.ok);
          if (r.json.ok) {
            closeModal();
            renderUsuarios();
          }
        });
      }
    });
  }

  function renderCarrito() {
    var host = document.getElementById('content');
    host.innerHTML = '';
    var uidInput = el('input', {
      type: 'number',
      min: '1',
      placeholder: 'ID usuario',
      'aria-label': 'ID usuario para vaciar carrito',
      style: 'max-width:120px',
    });
    var btnVac = el('button', { class: 'btn btn-danger', type: 'button', text: 'Vaciar carrito de usuario' });
    btnVac.addEventListener('click', function () {
      var id = parseInt(uidInput.value, 10);
      if (!id) {
        setMsg('Indicá un ID de usuario válido', false);
        return;
      }
      confirmPost(
        '¿Vaciar todo el carrito del usuario ' + id + '?',
        'admin/carrito_items',
        { action: 'vaciar_usuario', usuario_id: String(id) },
        renderCarrito
      );
    });
    host.appendChild(
      buildToolbar({
        onFilter: renderCarrito,
        extra: [uidInput, btnVac],
      })
    );

    apiGet('admin/carrito_items').then(function (res) {
      if (!res.json.ok) {
        setMsg(res.json.msg || 'Error', false);
        return;
      }
      var items = applyFilter(res.json.items || [], [
        'id',
        'usuario_email',
        'producto_nombre',
        'cantidad',
      ]);
      var wrap = el('div', { class: 'table-wrap' });
      var table = el('table', { class: 'data' });
      var thead = el('tr');
      ['ID', 'Usuario', 'Producto', 'Cant.', 'Subtotal', 'Acciones'].forEach(function (h) {
        thead.appendChild(el('th', { text: h }));
      });
      table.appendChild(el('thead')).appendChild(thead);
      var tb = el('tbody');
      items.forEach(function (it) {
        var sub = Number(it.producto_precio) * Number(it.cantidad);
        var tr = el('tr');
        tr.appendChild(el('td', { text: String(it.id) }));
        tr.appendChild(
          el('td', { text: String(it.usuario_email || '') + ' (' + String(it.usuario_id) + ')' })
        );
        tr.appendChild(el('td', { text: String(it.producto_nombre || '') }));
        tr.appendChild(el('td', { text: String(it.cantidad) }));
        tr.appendChild(el('td', { text: sub.toFixed(2) }));
        var td = el('td', { class: 'actions' });
        var bQty = el('button', { class: 'btn btn-small', type: 'button', text: 'Cant.' });
        bQty.addEventListener('click', function () {
          var n = prompt('Nueva cantidad (mín. 1):', String(it.cantidad));
          if (n == null) return;
          var q = parseInt(n, 10);
          if (!q || q < 1) return;
          apiPost('admin/carrito_items/' + it.id, { action: 'update', cantidad: String(q) }).then(function (r) {
            setMsg(r.json.msg || (r.json.ok ? 'Actualizado' : 'Error'), r.json.ok);
            renderCarrito();
          });
        });
        var bDel = el('button', { class: 'btn btn-small btn-danger', type: 'button', text: 'Quitar' });
        bDel.addEventListener('click', function () {
          confirmPost('¿Eliminar esta línea del carrito?', 'admin/carrito_items/' + it.id, { action: 'delete' }, renderCarrito);
        });
        td.appendChild(bQty);
        td.appendChild(document.createTextNode(' '));
        td.appendChild(bDel);
        tr.appendChild(td);
        tb.appendChild(tr);
      });
      table.appendChild(tb);
      wrap.appendChild(table);
      host.appendChild(wrap);
    });
  }

  function renderContacto() {
    var host = document.getElementById('content');
    host.innerHTML = '';
    host.appendChild(buildToolbar({ onFilter: renderContacto }));

    apiGet('admin/contacto_mensajes').then(function (res) {
      if (!res.json.ok) {
        host.appendChild(
          el('p', {
            class: 'muted',
            text:
              res.json.msg ||
              'No se pudieron cargar los mensajes. Verificá la migración 001_contacto_mensajes.sql',
          })
        );
        return;
      }
      var items = applyFilter(res.json.items || [], ['id', 'nombre', 'email', 'mensaje_preview']);
      var wrap = el('div', { class: 'table-wrap' });
      var table = el('table', { class: 'data' });
      var thead = el('tr');
      ['ID', 'Nombre', 'Email', 'Vista previa', 'Fecha', 'Acciones'].forEach(function (h) {
        thead.appendChild(el('th', { text: h }));
      });
      table.appendChild(el('thead')).appendChild(thead);
      var tb = el('tbody');
      items.forEach(function (m) {
        var tr = el('tr');
        tr.appendChild(el('td', { text: String(m.id) }));
        tr.appendChild(el('td', { text: String(m.nombre || '') }));
        tr.appendChild(el('td', { text: String(m.email || '') }));
        tr.appendChild(el('td', { text: String(m.mensaje_preview || '') }));
        tr.appendChild(el('td', { text: String(m.creado || '') }));
        var td = el('td', { class: 'actions' });
        var bV = el('button', { class: 'btn btn-small', type: 'button', text: 'Ver' });
        bV.addEventListener('click', function () {
          apiGet('admin/contacto_mensajes/' + m.id).then(function (r) {
            if (!r.json.ok || !r.json.item) {
              setMsg(r.json.msg || 'Error', false);
              return;
            }
            var modal = openModal('Mensaje #' + m.id);
            modal.body.appendChild(el('p', { class: 'muted', text: r.json.item.email + ' · ' + r.json.item.nombre }));
            var pre = el('pre', {
              style: 'white-space:pre-wrap;word-break:break-word;font-size:0.85rem;',
            });
            pre.textContent = String(r.json.item.mensaje || '');
            modal.body.appendChild(pre);
            var bClose = el('button', { class: 'btn', type: 'button', text: 'Cerrar' });
            bClose.addEventListener('click', closeModal);
            modal.actions.appendChild(bClose);
          });
        });
        var bD = el('button', { class: 'btn btn-small btn-danger', type: 'button', text: 'Eliminar' });
        bD.addEventListener('click', function () {
          confirmPost('¿Eliminar mensaje?', 'admin/contacto_mensajes/' + m.id, { action: 'delete' }, renderContacto);
        });
        td.appendChild(bV);
        td.appendChild(document.createTextNode(' '));
        td.appendChild(bD);
        tr.appendChild(td);
        tb.appendChild(tr);
      });
      table.appendChild(tb);
      wrap.appendChild(table);
      host.appendChild(wrap);
    });
  }

  function renderSection() {
    state.filter = '';
    navActive(state.section);
    if (state.section === 'productos') renderProductos();
    else if (state.section === 'usuarios') renderUsuarios();
    else if (state.section === 'carrito') renderCarrito();
    else if (state.section === 'contacto') renderContacto();
  }

  function showPanel() {
    document.getElementById('gate').style.display = 'none';
    document.getElementById('denied').style.display = 'none';
    var panel = document.getElementById('app-panel');
    panel.classList.add('visible');
    renderSection();
  }

  function showDenied() {
    document.getElementById('gate').style.display = 'none';
    document.getElementById('denied').style.display = 'flex';
    document.getElementById('app-panel').classList.remove('visible');
  }

  function showGate() {
    document.getElementById('gate').style.display = 'flex';
    document.getElementById('denied').style.display = 'none';
    document.getElementById('app-panel').classList.remove('visible');
  }

  function wireNav() {
    document.querySelectorAll('.admin-sidebar button').forEach(function (btn) {
      btn.addEventListener('click', function () {
        state.section = btn.getAttribute('data-section');
        renderSection();
      });
    });
    document.getElementById('gate-form').addEventListener('submit', function (e) {
      e.preventDefault();
      var email = document.getElementById('gate-email').value.trim();
      var password = document.getElementById('gate-password').value;
      resetCsrf();
      firebaseSignIn(email, password).then(function (resp) {
        if (!resp.ok) {
          setMsg(resp.msg || 'Credenciales inválidas', false);
          return;
        }
        getSession().then(function (s) {
          if (!s.ok) return;
          state.user = s;
          if (!s.es_admin) {
            showDenied();
          } else {
            showPanel();
          }
        });
      }).catch(function (err) {
        setMsg(err.message || 'Error al iniciar sesión', false);
      });
    });
  }

  document.getElementById('modal-backdrop').addEventListener('click', function (e) {
    if (e.target.id === 'modal-backdrop') closeModal();
  });

  function init() {
    wireNav();
    getSession().then(function (s) {
      if (!s.ok) {
        showGate();
        return;
      }
      state.user = s;
      if (!s.es_admin) {
        showDenied();
      } else {
        showPanel();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
