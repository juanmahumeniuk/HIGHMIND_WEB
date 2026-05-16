const { test, expect } = require('@playwright/test');

test.describe('Pruebas de API - Highmind Web', () => {
    const baseURL = 'http://localhost/HIGHMIND_WEB/public_html/api';

    test('Verificar obtención de productos activos', async ({ request }) => {
        // En Highmind Web el controller de Producto devuelve la lista de activos
        const response = await request.get(`${baseURL}/producto.php`); // Ajustar según el enrutador real
        
        // En una app real, ajustamos la ruta. Validamos que retorne código OK.
        expect(response.ok()).toBeTruthy();
        expect(response.status()).toBe(200);

        // La respuesta de ProductoController::index() devuelve JSON
        const data = await response.json();
        
        // Verificamos que sea un arreglo
        expect(Array.isArray(data)).toBe(true);
        if (data.length > 0) {
            expect(data[0]).toHaveProperty('id');
            expect(data[0]).toHaveProperty('nombre');
            expect(data[0]).toHaveProperty('precio');
        }
    });

    test('Intentar agregar al carrito sin estar autenticado (401)', async ({ request }) => {
        // El CarritoController valida la sesión antes de procesar
        const response = await request.post(`${baseURL}/carrito.php`, {
            data: {
                action: 'add',
                id: 1,
                qty: 1
            }
        });
        
        expect(response.status()).toBe(401);
        const data = await response.json();
        expect(data.ok).toBe(false);
        expect(data.msg).toBe('No autorizado');
    });
});
