import { test, expect } from '@playwright/test';

test.describe('Prueba de Inicio de Sesión con Admin Seeder', () => {
  test('Iniciar sesión con las credenciales del Seeder Admin (admin@citasmedicas.com / Admin1234!)', async ({ page }) => {
    // 1. Ir a la página de login
    await page.goto('/login');

    // 2. Llenar credenciales creadas por AdminUserSeeder
    await page.fill('input[name="email"]', 'admin@citasmedicas.com');
    await page.fill('input[name="password"]', 'Admin1234!');

    // 3. Hacer clic en el botón de Ingresar
    await page.click('button[type="submit"]');

    // 4. Verificar que redirige a la vista Dashboard (URL raíz /) y muestra la interfaz de Administrador
    await expect(page).toHaveURL('http://127.0.0.1:8000/');
    await expect(page.locator('body')).toContainText(/Administrador/i);
  });
});
