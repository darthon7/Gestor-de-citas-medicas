/**
 * Helper de autenticación para pruebas E2E con Playwright.
 */

export async function loginComo(page, email, password) {
  await page.goto('/login');
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
}

export async function loginComoAdmin(page) {
  await loginComo(page, 'admin@citasmedicas.com', 'Admin1234!');
}

export async function loginComoDoctor(page, email = 'gogo@doctor.com', password = 'Doctor1234!') {
  await loginComo(page, email, password);
}

export async function loginComoPaciente(page, email = 'carlos.ramirez@paciente.com', password = 'Paciente1234!') {
  await loginComo(page, email, password);
}
