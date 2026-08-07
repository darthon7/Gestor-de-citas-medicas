import { test, expect } from '@playwright/test';
import { loginComoAdmin, loginComo } from './helpers/auth.js';

test.describe('Flujo de Registro de Doctor y Validación por Admin', () => {
  const timeId = Date.now().toString().slice(-4);
  const testEmail = `doctor.playwright${timeId}@test.com`;
  // Usamos la cédula 1234567 o 2345678 que están pre-aprobadas en la BD de verificaciones de cédula mock
  const cedulaProf = '1234567';
  const curp = `DOCP900101HDF${timeId}`;

  test('Auto-registro público de nuevo doctor queda en estado pendiente', async ({ page }) => {
    await page.goto('/registro-doctor');
    
    await page.fill('input[name="nombre"]', `Dr. Playwright Test ${timeId}`);
    await page.fill('input[name="email"]', testEmail);
    await page.fill('input[name="password"]', 'Doctor1234!');
    await page.fill('input[name="password_confirmation"]', 'Doctor1234!');
    await page.fill('input[name="cedula_profesional"]', cedulaProf);
    await page.fill('input[name="curp"]', curp);
    await page.fill('input[name="telefono"]', '5512345678');
    await page.selectOption('select[name="especialidades[]"]', { index: 1 });

    await page.click('button[type="submit"]');

    await page.waitForURL(/\/login/);
    await expect(page.locator('body')).toContainText(/solicitud/i);
  });

  test('El doctor pendiente NO puede iniciar sesión hasta ser validado', async ({ page }) => {
    await loginComo(page, testEmail, 'Doctor1234!');
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('body')).toContainText(/validación|pendiente/i);
  });

  test('Admin abre modal de validación y aprueba la solicitud del doctor', async ({ page }) => {
    await loginComoAdmin(page);
    await page.goto('/doctores');

    // Buscar la tarjeta o fila del doctor por su email/nombre
    const doctorRow = page.locator(`div:has-text("${testEmail}")`).first();
    await expect(doctorRow).toBeVisible();

    // Click en el badge o botón de validar doctor
    const btnValidar = doctorRow.locator('button:has-text("Validar"), span:has-text("Pendiente")').first();
    await btnValidar.click();

    // Modal de validación visible
    const modal = page.locator('#modal_validar_doctor');
    await expect(modal).toBeVisible();

    // Click en Aprobar y Validar Doctor
    await modal.locator('button:has-text("Aprobar y Validar Doctor")').click();

    await page.waitForLoadState('networkidle');
    await expect(page.locator('body')).toContainText(/validado correctamente|éxito/i);
  });

  test('El doctor aprobado puede iniciar sesión exitosamente', async ({ page }) => {
    await loginComo(page, testEmail, 'Doctor1234!');
    await expect(page).toHaveURL(/\/dashboard/);
    await expect(page.locator('body')).toContainText(`Dr. Playwright Test ${timeId}`);
  });
});
