import { test, expect } from '@playwright/test';
import { loginComoAdmin } from './helpers/auth.js';

test.describe('Autenticación y Sesión E2E', () => {
  test('Navegación a la Landing Page', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Agenda Médica/i);
    await expect(page.locator('h1')).toContainText(/salud/i);
  });

  test('Inicio de sesión exitoso como Admin y redirección a Dashboard', async ({ page }) => {
    await loginComoAdmin(page);
    await expect(page).toHaveURL('http://127.0.0.1:8000/');
    await expect(page.locator('body')).toContainText(/Administrador/i);
  });

  test('Cierre de sesión redirige a la Landing Page (/inicio)', async ({ page }) => {
    await loginComoAdmin(page);
    await expect(page).toHaveURL('http://127.0.0.1:8000/');
    
    // Hacer submit al formulario de logout o pulsar el botón de cerrar sesión
    await page.locator('form[action*="logout"] button, button:has-text("Cerrar Sesión"), a:has-text("Cerrar Sesión")').first().click();
    await expect(page).toHaveURL(/\/inicio/);
  });

  test('Inicio de sesión fallido con credenciales inválidas', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'incorrecto@citasmedicas.com');
    await page.fill('input[name="password"]', 'WrongPassword123');
    await page.click('button[type="submit"]');
    await expect(page.locator('body')).toContainText(/credenciales|incorrect|error/i);
  });
});
