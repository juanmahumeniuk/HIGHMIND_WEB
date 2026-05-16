const { test, expect } = require('@playwright/test');

test.describe('Pruebas con Mocking/Intercepción - Highmind Web', () => {
    
    test('Simular error 500 al intentar cargar productos y validar UI', async ({ page }) => {
        // Interceptamos la llamada a la API de productos usando page.route()
        await page.route('**/api/producto*', async route => {
            await route.fulfill({
                status: 500,
                contentType: 'application/json',
                body: JSON.stringify({ error: 'Error interno del servidor simulado' })
            });
        });

        // Navegamos a la página de inicio del frontend
        await page.goto('http://localhost/HIGHMIND_WEB/public_html/frontend/index.html');

        // Validamos que la UI maneje el error en vez de "romperse"
        // Este selector debe coincidir con el mecanismo de notificación de tu UI
        const errorMsg = page.locator('.error-message, .alert-danger, #toast-container'); 
        await expect(errorMsg).toBeVisible({ timeout: 5000 });
        
        // Opcionalmente podemos chequear si muestra una lista vacía
        const productsList = page.locator('.producto-card');
        await expect(productsList).toHaveCount(0);
    });

    test('Mockear lista de productos vacía para validar el "empty state"', async ({ page }) => {
        await page.route('**/api/producto*', async route => {
            // Simulamos que la BD no tiene productos, retornando array vacío
            await route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify([]) 
            });
        });

        await page.goto('http://localhost/HIGHMIND_WEB/public_html/frontend/index.html');

        // Verificamos que la página indique explícitamente que no hay stock/productos
        // en lugar de dejar un espacio en blanco confuso
        const noProductsMsg = page.locator('.no-products, #empty-state');
        await expect(noProductsMsg).toBeVisible();
    });

});
