(function () {
  'use strict';

  window.el = function el(tag, attrs, children) {
    var n = document.createElement(tag);
    if (attrs) {
      Object.keys(attrs).forEach(function (k) {
        if (k === 'text') {
          n.textContent = attrs[k];
        } else if (k === 'html') {
          n.innerHTML = attrs[k];
        } else {
          n.setAttribute(k, attrs[k]);
        }
      });
    }
    if (children) {
      children.forEach(function (c) {
        if (c) n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
      });
    }
    return n;
  };

  window.setFeedback = function setFeedback(elOrId, text, ok, palette) {
    var el = typeof elOrId === 'string' ? document.getElementById(elOrId) : elOrId;
    if (!el) return;
    el.textContent = text || '';
    if (palette) {
      el.style.color = ok ? palette.ok : palette.err;
      if (palette.weight) el.style.fontWeight = ok ? palette.weight.ok : palette.weight.err;
    } else if (ok === true) {
      el.style.color = '#99e772';
      el.style.fontWeight = 'bold';
    } else if (ok === false) {
      el.style.color = '#ff8d8d';
      el.style.fontWeight = 'normal';
    }
  };

  window.formatPrecio = function formatPrecio(n) {
    return '$' + Number(n).toLocaleString('es-AR');
  };

  window.frontendAssetUrl = function frontendAssetUrl(rel) {
    var s = String(rel || '').trim();
    if (!s || /^javascript:/i.test(s) || /^data:/i.test(s)) return '';
    return new URL('../frontend/' + s.replace(/^\//, ''), window.location.href).href;
  };
})();
