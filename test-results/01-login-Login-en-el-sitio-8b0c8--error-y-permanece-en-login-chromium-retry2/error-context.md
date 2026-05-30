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

```
Error: Channel closed
```

```
Error: browserContext.close: Target page, context or browser has been closed
```