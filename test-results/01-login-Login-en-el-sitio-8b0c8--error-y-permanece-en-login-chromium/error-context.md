# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: 01-login.spec.js >> Login en el sitio (HIGHMIND) >> login inválido: muestra mensaje de error y permanece en login
- Location: e2e/01-login.spec.js:17:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByRole('heading', { name: 'Iniciar sesión' })
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for getByRole('heading', { name: 'Iniciar sesión' })
    - waiting for" http://localhost:8080/frontend/index.html" navigation to finish...
    - navigated to "http://localhost:8080/frontend/index.html"

```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - banner [ref=e2]:
    - navigation [ref=e3]:
      - link "HIGHMIND HIGHMIND" [ref=e4] [cursor=pointer]:
        - /url: "#"
        - img "HIGHMIND" [ref=e5]
        - generic [ref=e6]: HIGHMIND
      - text: ☰
      - list [ref=e7]:
        - listitem [ref=e8]:
          - link "Inicio" [ref=e9] [cursor=pointer]:
            - /url: index.html
        - listitem [ref=e10]:
          - link "Tienda" [ref=e11] [cursor=pointer]:
            - /url: tienda.html
        - listitem [ref=e12]:
          - link "Contacto" [ref=e13] [cursor=pointer]:
            - /url: contacto.html
        - listitem [ref=e14]:
          - link "Iniciar sesión" [ref=e15] [cursor=pointer]:
            - /url: login.html
        - listitem [ref=e16]:
          - link "Carrito" [ref=e17] [cursor=pointer]:
            - /url: "#"
            - img [ref=e19]
  - main [ref=e23]:
    - generic [ref=e25]:
      - heading "Tu mente, tu límite" [level=1] [ref=e26]
      - paragraph [ref=e27]: Tu tienda de ropa online.
      - link "Ver colección" [ref=e28] [cursor=pointer]:
        - /url: tienda.html
    - generic [ref=e29]:
      - heading "Nuevos productos" [level=2] [ref=e30]
      - generic [ref=e31]:
        - generic [ref=e32] [cursor=pointer]:
          - img "Buzo Negro - Smile" [ref=e33]
          - generic [ref=e34]:
            - heading "Buzo Negro - Smile" [level=3] [ref=e35]
            - generic [ref=e36]: $33.000
        - generic [ref=e37] [cursor=pointer]:
          - img "Buzo - Frase" [ref=e38]
          - generic [ref=e39]:
            - heading "Buzo - Frase" [level=3] [ref=e40]
            - generic [ref=e41]: $32.000
        - generic [ref=e42] [cursor=pointer]:
          - img "Hoodie Blackout" [ref=e43]
          - generic [ref=e44]:
            - heading "Hoodie Blackout" [level=3] [ref=e45]
            - generic [ref=e46]: $38.000
        - generic [ref=e47] [cursor=pointer]:
          - img "Remera Oversize Blanca" [ref=e48]
          - generic [ref=e49]:
            - heading "Remera Oversize Blanca" [level=3] [ref=e50]
            - generic [ref=e51]: $15.000
  - contentinfo [ref=e52]:
    - paragraph [ref=e53]: © 2025 HIGHMIND. Todos los derechos reservados.
```

# Test source

```ts
  1  | // @ts-check
  2  | const { test, expect } = require('@playwright/test');
  3  | 
  4  | /**
  5  |  * Credenciales del usuario admin de desarrollo (ver database/migrations/003_usuario_admin.sql).
  6  |  * Se pueden sobreescribir con variables de entorno para otros entornos.
  7  |  */
  8  | const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL || 'admin@admin.com';
  9  | const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD || 'Administrador*1234';
  10 | 
  11 | test.describe('Login en el sitio (HIGHMIND)', () => {
  12 |   test.beforeEach(async ({ page }) => {
  13 |     await page.goto('/login.html');
> 14 |     await expect(page.getByRole('heading', { name: 'Iniciar sesión' })).toBeVisible();
     |                                                                         ^ Error: expect(locator).toBeVisible() failed
  15 |   });
  16 | 
  17 |   test('login inválido: muestra mensaje de error y permanece en login', async ({ page }) => {
  18 |     await page.locator('#login-email').fill(ADMIN_EMAIL);
  19 |     await page.locator('#login-password').fill('ContraseñaIncorrecta123!');
  20 |     await page.locator('#login-form').getByRole('button', { name: 'Ingresar' }).click();
  21 | 
  22 |     const msg = page.locator('#login-msg');
  23 |     await expect(msg).toHaveText('Email o contraseña incorrectos');
  24 |     await expect(page).toHaveURL(/login\.html$/);
  25 |   });
  26 | 
  27 |   test('login válido: mensaje de bienvenida y redirección al inicio', async ({ page }) => {
  28 |     await page.locator('#login-email').fill(ADMIN_EMAIL);
  29 |     await page.locator('#login-password').fill(ADMIN_PASSWORD);
  30 |     await page.locator('#login-form').getByRole('button', { name: 'Ingresar' }).click();
  31 | 
  32 |     const msg = page.locator('#login-msg');
  33 |     await expect(msg).toContainText('Bienvenido');
  34 |     await page.waitForURL(/index\.html$/, { timeout: 10_000 });
  35 |     await expect(page).toHaveURL(/index\.html$/);
  36 |   });
  37 | });
  38 | 
```