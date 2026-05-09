# Guía Técnica - Highmind Web

## Arquitectura del Sistema
El proyecto está estructurado bajo un patrón arquitectónico **MVC (Modelo-Vista-Controlador)** sin el uso de frameworks de terceros, utilizando PHP puro orientado a objetos.

### Flujo de Ejecución (Routing)
Todas las peticiones a la API convergen en un único punto de entrada: `public_html/api.php`.
1. **api.php** recibe la solicitud y la pasa a `Core\Router`.
2. **Router** evalúa el método HTTP y la ruta, delegando la petición al Controlador correspondiente.
3. El **Controlador** (ej. `CarritoController`) maneja la lógica de negocio, extrae los parámetros y llama al **Modelo** correspondiente.
4. El **Modelo** interactúa con la base de datos a través de `Core\Database`.
5. El **Controlador** devuelve la respuesta serializada a través de `Core\JsonResponse`.

---

## Core y Seguridad
La carpeta `app/Core/` contiene utilidades esenciales para la seguridad:
- **`Input.php`**: Provee métodos estrictos para recuperar variables POST (ej. `postEmail`, `postPlainString`). Todas las entradas de texto plano pasan por `htmlspecialchars` y limpieza de etiquetas para mitigar ataques **XSS**. Además, se incluye validación con Regex para formatos específicos (como nombres).
- **`Csrf.php`**: Implementa tokens Anti-CSRF. Toda petición mutante (`POST`, `PUT`, `DELETE`) exige un token válido enviado en el body como `csrf_token`.
- **`Database.php`**: Gestiona la conexión PDO usando variables de entorno y protege contra inyección SQL al forzar consultas preparadas (`prepare`/`execute`).

---

## Endpoints de la API

A continuación se detallan las acciones disponibles. Todas las peticiones se realizan a `/api.php` mediante consultas GET o POST, dependiendo de la acción.

### Usuarios (`UsuarioController.php`)
| Acción (GET/POST) | Método | Parámetros Requeridos | Descripción |
| --- | --- | --- | --- |
| `?action=csrf` | GET | Ninguno | Retorna un token CSRF para ser usado en futuros POST. |
| `?action=check` | GET | Ninguno | Verifica si el usuario actual tiene una sesión iniciada. |
| `?action=register` | POST | `nombre`, `email`, `password`, `csrf_token` | Crea un nuevo usuario validando credenciales y unicidad de email. |
| `?action=login` | POST | `email`, `password`, `csrf_token` | Inicia sesión y genera las variables de sesión del usuario. |
| `?action=logout` | POST | `csrf_token` | Destruye la sesión actual. |

### Productos (`ProductoController.php`)
| Acción | Método | Parámetros Requeridos | Descripción |
| --- | --- | --- | --- |
| `?action=productos` | GET | Ninguno | Retorna un array JSON con todos los productos activos y su stock actual. |

### Carrito (`CarritoController.php`)
*Requiere estar autenticado. Todas las operaciones POST requieren `csrf_token`.*
| Acción | Método | Parámetros Requeridos | Descripción |
| --- | --- | --- | --- |
| `?action=carrito` (`get`) | GET | Ninguno | Obtiene los ítems del carrito, el subtotal y la cantidad total de artículos. |
| `POST` (`add`) | POST | `action=add`, `id`, `qty`, `csrf_token` | Añade `qty` unidades de un producto. Valida que no se exceda el stock crítico. |
| `POST` (`update`) | POST | `action=update`, `id`, `qty`, `csrf_token` | Sobrescribe la cantidad de un producto (valida stock). |
| `POST` (`remove`) | POST | `action=remove`, `id`, `csrf_token` | Elimina un producto entero del carrito. |
| `POST` (`clear`) | POST | `action=clear`, `csrf_token` | Vacía completamente el carrito del usuario. |

### Pagos (`PagoController.php`)
*Requiere estar autenticado.*
| Acción | Método | Parámetros Requeridos | Descripción |
| --- | --- | --- | --- |
| `?action=pago` (`config`) | GET | `action=config` | Retorna la llave pública de MercadoPago si el checkout está habilitado. |
| `POST` (`create`) | POST | Datos de tarjeta (`token`), email, cuotas, `csrf_token` | Valida que todos los productos del carrito tengan stock disponible. Si es exitoso, envía el pago a Mercado Pago y vacía el carrito. |

### Contacto (`ContactoController.php`)
| Acción | Método | Parámetros Requeridos | Descripción |
| --- | --- | --- | --- |
| `?action=contacto` | POST | `nombre`, `email`, `mensaje`, `csrf_token` | Registra un mensaje de soporte. Aplica Regex estricto en el nombre y XSS filter en el mensaje. |
