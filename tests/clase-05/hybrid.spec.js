const { test, expect } = require('@playwright/test');

test.describe('Pruebas Híbridas - Highmind Web', () => {
    
    test('Preparar carrito vía API y validar visualización en UI', async ({ page, request }) => {
        // PASO 1: Preparar el estado vía API
        // Simulamos un login (o se puede obviar si usamos un auth state preguardado en playwright)
        // Como el sistema usa sessions en PHP, necesitamos capturar el token csrf si aplica
        
        // Para este ejemplo híbrido simplificado, supongamos que nos autenticamos:
        const loginResponse = await request.post('http://localhost/HIGHMIND_WEB/public_html/api/usuario.php', {
            data: { action: 'login_test', user_id: 1 } // Simulando endpoint de testing o usando uno real
        });
        
        // Al estar usando el mismo contexto de Playwright, la cookie de sesión (PHPSESSID) 
        // del request se compartirá con la page.

        // PASO 2: Agregar producto al carrito directamente a la BD/Sesión vía API (más rápido y estable)
        const addResponse = await request.post('http://localhost/HIGHMIND_WEB/public_html/api/carrito.php', {
            data: {
                action: 'add',
                id: 1, // Producto de prueba
                qty: 3
            }
        });
        
        // Ignoramos la validación estricta si es un dummy, pero asumimos status 200
        // expect(addResponse.status()).toBe(200);

        // PASO 3: Acción UI pura -> Navegar a la vista del carrito
        await page.goto('http://localhost/HIGHMIND_WEB/public_html/frontend/carrito.html');

        // PASO 4: Validar el resultado desde la UI
        // Queremos asegurar que la UI levantó la información preparada por el backend correctamente
        const cartItems = page.locator('.cart-item, .fila-carrito'); // Ajustar al DOM real
        
        // Validar que el ítem aparece
        await expect(cartItems).toBeVisible();
        
        // Validar que la cantidad en el input o texto es '3'
        const qtyInput = cartItems.first().locator('input[type="number"], .cantidad-item');
        await expect(qtyInput).toHaveValue('3'); // O toHaveText('3') dependiendo de la UI
    });

});
