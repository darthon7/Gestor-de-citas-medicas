import { test, expect } from '@playwright/test';
import { loginComoAdmin } from './helpers/auth.js';

test.describe('Gestión de Pacientes E2E', () => {
  test.beforeEach(async ({ page }) => {
    await loginComoAdmin(page);
    await page.goto('/pacientes');
  });

  test('Muestra el listado de pacientes correctamente', async ({ page }) => {
    await expect(page.locator('h2, h1')).toContainText(/Gestión de Pacientes/i);
    await expect(page.locator('table')).toBeVisible();
  });

  test('Alternar estado de activación (desactivar / activar) de un paciente', async ({ page }) => {
    const pacienteRow = page.locator('tbody tr').first();
    await expect(pacienteRow).toBeVisible();

    // Guardar el estado previo
    const statusBadge = pacienteRow.locator('td span:has-text("Activo"), td span:has-text("Inactivo")').first();
    const estadoInicial = await statusBadge.innerText();

    // Escuchar la confirmación de diálogo si aparece
    page.once('dialog', dialog => dialog.accept());

    // Click en el botón de toggle status
    const toggleBtn = pacienteRow.locator('button[title*="paciente"]').first();
    await toggleBtn.click();

    await page.waitForLoadState('networkidle');

    // Verificar que el estado cambió
    const nuevoBadge = pacienteRow.locator('td span:has-text("Activo"), td span:has-text("Inactivo")').first();
    const estadoNuevo = await nuevoBadge.innerText();
    expect(estadoNuevo).not.toBe(estadoInicial);
  });
});
