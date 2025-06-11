# 🧠 HIGHMIND – Tienda Online

Tienda de ropa online desarrollada en PHP (backend), HTML, CSS, y JavaScript (frontend), lista para producción en hosting compartido (Hostinger, cPanel, etc.), **totalmente responsive** y compatible con MySQL.

---

## 🚀 Características principales

- **Catálogo de productos**: Carga dinámica desde base de datos (MySQL), con imágenes y descripciones.
- **Carrito de compras persistente**: Soporte para múltiples ítems, cambio de cantidad y subtotal automático.  
- **Modal de producto**: Vista detallada tipo Instagram (desktop y mobile), imagen en grande, info, talles y botón “Agregar al carrito”.
- **Login y registro de usuarios**: Sesiones PHP seguras, validación y registro directo desde la web.
- **Navbar adaptable**:  
  - En desktop: menú horizontal estético estilo Material You.  
  - En mobile: menú hamburguesa fullscreen, con fondo oscuro y centrado, y cierre automático al elegir un link.
- **Badge de carrito en tiempo real**: Actualización automática al agregar/quitar ítems.
- **Modo oscuro predominante**: Colores material, negro, gris y detalles vibrantes.
- **Hero en la home**: Mensaje destacado, llamado a la acción y muestra de productos random cada vez.
- **Fondo animado borroso**: Fondo blur con banner en login y otras páginas.
- **Footer y navbar redondeados**: Estética consistente y moderna, con sombra suave.

---

## 🛠️ Gimmicks & extras

- **Animaciones suaves**: En el menú, modals y botones.
- **Transparencias y blurs**: En formularios y overlays.
- **Navbar con login/logout dinámico**: Cambia automáticamente entre “Iniciar sesión” y “Cerrar sesión (nombre)”.
- **Menú hamburguesa con cierre automático**: Al tocar un link o fuera del menú.
- **Overlay oscuro al abrir menú**: Enfoca la atención al menú móvil.
- **Modal de carrito**: Carrito accesible desde la navbar, con edición y vaciado sin recargar.
- **Carga de productos aleatoria en la home**: Siempre ves 4 productos diferentes.
- **100% mobile-first**: Cards y modals ocupan toda la pantalla en móvil, con tipografía y botones XL.
- **Formularios autovalidados**: Feedback instantáneo y mensajes de éxito/error.

---

## 📦 Estructura del proyecto

```
public_html/
├── backend/ # API PHP para productos, usuarios, carrito
│ ├── api_productos.php
│ ├── usuarios.php
│ └── carrito.php
├── frontend/ # Frontend HTML, CSS, JS, imágenes
│ ├── img/
│ ├── css/
│ │ └── estilo.css
│ ├── js/
│ │ ├── script.js
│ │ ├── login.js
│ │ └── navbar.js
│ ├── index.html
│ ├── tienda.html
│ ├── contacto.html
│ └── login.html
└── .htaccess # (opcional) Redirección de inicio
```


---

## 💡 Cómo probar en local

1. Subí el proyecto a tu servidor o localhost.
2. Importá la base de datos MySQL usando el dump SQL incluido.
3. Configurá el acceso a la DB en los archivos PHP (`backend/`).
4. Accedé a `/frontend/index.html` o configurá un index para redirigir a la tienda.

---

## 🔐 Requerimientos

- PHP 7.4+  
- MySQL/MariaDB  
- Hosting compartido o local (funciona en Hostinger, cPanel, XAMPP, etc.)  


---

## 🙌 Créditos

Desarrollado por panITUS.  
Diseño y código inspirados en Google Material You.

---
