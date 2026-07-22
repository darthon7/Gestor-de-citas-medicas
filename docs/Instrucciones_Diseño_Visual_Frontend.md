# 🎨 Instrucciones de Diseño Visual — Sistema de Gestión de Citas Médicas

> **Propósito**: Este documento describe todas las pantallas y componentes visuales del frontend, tanto **Web** como **Móvil**, del Sistema de Gestión de Citas Médicas. Cada sección está redactada como un **prompt listo para usar en Google Stitch** para generar mockups y diseños de alta fidelidad.

---

## 🎨 Paleta de Colores y Sistema de Diseño

> **Fundamento UX/UI — Teoría del Color aplicada a Salud:**
> - El **azul** transmite confianza, profesionalismo y calma — esencial en entornos médicos donde el paciente necesita sentir seguridad.
> - El **verde azulado (teal)** se asocia con sanación, bienestar y frescura — refuerza la identidad de salud sin ser genérico.
> - Los **blancos y grises claros** como fondos generan limpieza visual y legibilidad — principio de espacio negativo.
> - Los **acentos cálidos** (ámbar/naranja suave) se usan exclusivamente para llamadas a la acción y estados de alerta — contraste complementario que guía la atención del usuario.
> - El **rojo suave** se reserva para errores y cancelaciones — asociación universal de precaución sin generar ansiedad.

### Colores Principales

| Token | Hex | Uso |
|---|---|---|
| `Primary` | `#1B6B93` | Encabezados, navbar, botones principales, enlaces activos |
| `Primary Dark` | `#0F4C6B` | Hover de botones, sidebar activo, estados pressed |
| `Primary Light` | `#A8D5E2` | Fondos de tarjetas, estados seleccionados suaves, badges informativos |
| `Secondary` | `#2A9D8F` | Iconos de éxito, estados "disponible", badges de confirmación |
| `Secondary Light` | `#B5E8D5` | Fondos de secciones de historial, backgrounds de cards positivas |
| `Accent` | `#E9A319` | Botones CTA secundarios, estados "pendiente", notificaciones |
| `Accent Warm` | `#F4A261` | Badges de advertencia, estados "en espera", alertas moderadas |
| `Danger` | `#E76F51` | Botón cancelar, estados "cancelada", alertas de error |
| `Danger Light` | `#FADED4` | Fondo de alertas de error, badges de cancelación |
| `Background` | `#F7F9FC` | Fondo general de toda la aplicación |
| `Surface` | `#FFFFFF` | Tarjetas, modales, formularios, contenedores de contenido |
| `Text Primary` | `#1A1A2E` | Títulos, encabezados, texto principal |
| `Text Secondary` | `#4A5568` | Subtítulos, descripciones, texto de apoyo |
| `Text Muted` | `#A0AEC0` | Placeholders, texto deshabilitado, metadatos |
| `Border` | `#E2E8F0` | Bordes de inputs, separadores, líneas divisorias |
| `Overlay` | `rgba(26,26,46,0.5)` | Fondos de modales y diálogos overlay |

### Tipografía

| Elemento | Fuente | Peso | Tamaño |
|---|---|---|---|
| Títulos principales (H1) | Inter | Bold (700) | 28px web / 24sp móvil |
| Subtítulos (H2) | Inter | Semibold (600) | 22px web / 20sp móvil |
| Encabezados de sección (H3) | Inter | Medium (500) | 18px web / 16sp móvil |
| Cuerpo de texto | Inter | Regular (400) | 16px web / 14sp móvil |
| Texto pequeño / captions | Inter | Regular (400) | 13px web / 12sp móvil |
| Botones | Inter | Semibold (600) | 15px web / 14sp móvil |

### Bordes y Sombras

| Propiedad | Valor |
|---|---|
| Border radius (botones) | `8px` |
| Border radius (tarjetas) | `12px` |
| Border radius (inputs) | `8px` |
| Border radius (avatares) | `50%` (circular) |
| Sombra de tarjeta | `0 2px 12px rgba(27,107,147,0.08)` |
| Sombra de modal | `0 8px 32px rgba(26,26,46,0.15)` |
| Sombra hover de tarjeta | `0 4px 20px rgba(27,107,147,0.14)` |

---

---

# 🖥️ PARTE WEB (Panel Administrativo y Médico)

> **Usuarios objetivo**: Administrador, Recepcionista, Doctor.
> **Estilo general**: Dashboard profesional, limpio, tipo SaaS de salud. Sidebar izquierdo + área de contenido principal. Diseño responsivo pero optimizado para pantallas de escritorio (1280px+).

---

## WEB-01 — Pantalla de Login

**Prompt para Google Stitch:**

```
Design a modern, clean medical appointment system login page for a healthcare clinic web application. 

Layout: Centered login card on a subtle gradient background going from #F7F9FC to #A8D5E2 (light blue). The card is white (#FFFFFF) with border-radius 12px and a soft shadow (0 8px 32px rgba(26,26,46,0.10)).

Card contents from top to bottom:
- A medical cross icon or stethoscope icon in color #1B6B93 at the top center
- Application name "Agenda Médica" in Inter Bold 28px, color #1A1A2E
- Subtitle "Sistema de Gestión de Citas" in Inter Regular 14px, color #4A5568
- Spacer 32px
- Email input field with placeholder "Correo Electrónico", border 1px solid #E2E8F0, border-radius 8px, height 48px, with a mail icon inside in #A0AEC0
- Password input field with placeholder "Contraseña", same style as email, with a lock icon inside in #A0AEC0 and an eye toggle icon
- Spacer 8px
- "¿Olvidaste tu contraseña?" link text in #1B6B93, aligned right, Inter Regular 13px
- Spacer 24px
- "Ingresar" button, full width, background #1B6B93, text white Inter Semibold 15px, border-radius 8px, height 48px, with subtle hover glow effect
- Spacer 16px
- Footer text "© 2026 Agenda Médica — Centro de Salud" in #A0AEC0, 12px

Color palette: Primary blue #1B6B93, background #F7F9FC, white surfaces, teal accent #2A9D8F. 
Typography: Inter font family. 
Style: Modern, professional, healthcare-oriented, minimalist with subtle depth. No decorative clutter. The design should inspire trust and calmness.
```

---

## WEB-02 — Pantalla de Recuperación de Contraseña

**Prompt para Google Stitch:**

```
Design a password recovery page for a medical appointment web system.

Layout: Same centered card style as the login page. Background gradient #F7F9FC to #A8D5E2.

White card (border-radius 12px, soft shadow) contents:
- Back arrow icon ← in #1B6B93 at top-left of the card
- Lock reset icon centered, color #E9A319 (amber accent)
- Title "Recuperar Contraseña" in Inter Bold 24px, color #1A1A2E
- Description text "Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña." in Inter Regular 14px, color #4A5568, centered
- Spacer 24px
- Email input field with placeholder "Correo Electrónico registrado", border 1px solid #E2E8F0, border-radius 8px, mail icon in #A0AEC0
- Spacer 16px
- "Enviar enlace de recuperación" button, full width, background #1B6B93, white text, border-radius 8px
- Spacer 12px
- "Volver al inicio de sesión" link in #1B6B93, Inter Regular 13px, centered

Style: Same medical professional aesthetic. Clean, reassuring, simple flow.
```

---

## WEB-03 — Dashboard Principal (Vista Recepcionista)

**Prompt para Google Stitch:**

```
Design a professional healthcare dashboard for a medical appointment management system. This is the main view for the Receptionist role.

Layout structure:
- LEFT SIDEBAR (width 260px, background #0F4C6B dark blue):
  - Top: Logo/icon "AM" monogram + "Agenda Médica" text in white, Inter Semibold 18px
  - Navigation menu items, each with an icon + label in white/light blue (#A8D5E2), active item has a left border accent bar in #2A9D8F and background rgba(255,255,255,0.08):
    - 🏠 "Inicio" (active)
    - 👥 "Pacientes"
    - 📅 "Citas"
    - 👨‍⚕️ "Doctores"
    - 📊 "Reportes"
  - Bottom of sidebar: User avatar circle (40px), name "María López", role badge "Recepcionista" in small teal badge, and a logout icon button

- TOP HEADER BAR (height 64px, white background, bottom border 1px #E2E8F0):
  - Left: Page title "Panel Principal" in Inter Semibold 22px, #1A1A2E
  - Right: Notification bell icon with red dot indicator, user avatar circle, and date "Lunes, 21 de Julio 2026" in #4A5568

- MAIN CONTENT AREA (background #F7F9FC, padding 24px):
  - ROW 1 — Four stat cards in a grid (4 columns), each white card with border-radius 12px and subtle shadow:
    - Card 1: Icon calendar in #1B6B93, number "12" large bold, label "Citas del día" in #4A5568
    - Card 2: Icon check-circle in #2A9D8F, number "8", label "Completadas hoy"
    - Card 3: Icon clock in #E9A319, number "3", label "Pendientes"
    - Card 4: Icon x-circle in #E76F51, number "1", label "Canceladas hoy"
  
  - ROW 2 — Two-column layout:
    - LEFT (60%): "Agenda del Día" section, white card with table inside showing columns: Hora | Paciente | Doctor | Especialidad | Estado. States shown as colored badges: "Confirmada" (#2A9D8F green badge), "Pendiente" (#E9A319 amber badge), "En consulta" (#1B6B93 blue badge), "Cancelada" (#E76F51 red badge). 5-6 rows of sample data.
    - RIGHT (40%): "Próximas Citas" section, white card with a mini list of upcoming 3-4 appointments showing time, patient name, and doctor name in a compact card layout.

Color palette: #1B6B93 primary, #0F4C6B sidebar, #2A9D8F success, #E9A319 warning, #E76F51 danger, #F7F9FC background.
Typography: Inter font family throughout.
Style: Modern SaaS dashboard for healthcare. Clean data visualization. Professional and organized.
```

---

## WEB-04 — Módulo de Gestión de Pacientes

**Prompt para Google Stitch:**

```
Design a patient management page for a healthcare web system. This page allows receptionists to view, search, and manage patient records.

Layout: Same sidebar + header structure as the dashboard (sidebar dark blue #0F4C6B, "Pacientes" menu item active with #2A9D8F left border accent).

Main content area (background #F7F9FC):
- Top bar row:
  - Left: Page title "Gestión de Pacientes" in Inter Semibold 22px, #1A1A2E
  - Center: Search bar input (width 400px), placeholder "Buscar por nombre, CURP o expediente...", border #E2E8F0, border-radius 8px, search icon left, height 44px
  - Right: "+ Nuevo Paciente" button, background #1B6B93, white text, border-radius 8px, with a plus icon

- Data table in a white card (border-radius 12px, shadow):
  - Table header row: background #F7F9FC, text #4A5568 Inter Semibold 13px uppercase
  - Columns: # Expediente | Nombre Completo | CURP | Teléfono | Correo | Estado | Acciones
  - 6-7 rows of sample patient data with:
    - "Estado" column showing badges: "Activo" in green (#2A9D8F) badge, "Inactivo" in gray badge
    - "Acciones" column with icon buttons: eye icon (view, #1B6B93), pencil icon (edit, #E9A319), toggle icon (deactivate, #E76F51)
  - Table footer: Pagination controls (Previous, page numbers 1-2-3, Next) in #1B6B93

- Bottom right corner: Floating action hint showing total "47 pacientes registrados" in #4A5568

Color palette and typography: Same system. 
Style: Clean data table with good spacing, professional medical records interface. Easy to scan rows.
```

---

## WEB-05 — Modal / Formulario de Registro de Paciente

**Prompt para Google Stitch:**

```
Design a modal overlay for registering a new patient in a medical appointment system.

Background: Dark overlay (rgba(26,26,46,0.5)) over the patient management page.

Modal card (width 640px, centered, white #FFFFFF, border-radius 12px, shadow 0 8px 32px rgba(26,26,46,0.15)):
- Header: "Registrar Nuevo Paciente" in Inter Semibold 20px, #1A1A2E, with a close X button at top-right in #A0AEC0
- Horizontal divider line #E2E8F0
- Form body with fields in a 2-column grid layout (gap 16px), each field with label above (Inter Medium 13px, #4A5568) and input below (border #E2E8F0, border-radius 8px, height 44px):
  - Row 1: "Nombre Completo" (full width, spans 2 columns)
  - Row 2: "Fecha de Nacimiento" (date picker) | "Sexo" (dropdown: Masculino, Femenino)
  - Row 3: "CURP" (text, 18 chars) | "Teléfono" (phone input)
  - Row 4: "Correo Electrónico" (email) | "Dirección" (text)
- Note text: "El número de expediente se asignará automáticamente." in Inter Regular 12px, #A0AEC0, italic
- Spacer 16px
- Footer buttons row aligned right:
  - "Cancelar" button, outline style, border #E2E8F0, text #4A5568, border-radius 8px
  - "Registrar Paciente" button, solid #1B6B93, white text, border-radius 8px

Style: Clean medical form, well-organized, accessible spacing between fields. Professional and easy to fill.
```

---

## WEB-06 — Perfil / Expediente de Paciente

**Prompt para Google Stitch:**

```
Design a patient profile and medical record detail page for a healthcare web system.

Layout: Same sidebar + header (sidebar with "Pacientes" active).

Main content (background #F7F9FC):
- Top section — Patient info card (white, border-radius 12px):
  - Left: Circular avatar placeholder (80px, background #A8D5E2 with initials "JG" in #1B6B93)
  - Center: Patient name "Juan García Hernández" in Inter Bold 22px #1A1A2E, underneath: CURP, Expediente #0023, Teléfono, Correo in #4A5568. Status badge "Activo" in green #2A9D8F
  - Right: "Editar Paciente" button outline style #1B6B93

- Tab navigation bar below the patient card:
  - Tabs: "Información Personal" | "Historial de Citas" | "Diagnósticos" — active tab has bottom border 3px #1B6B93, text #1B6B93; inactive tabs in #A0AEC0

- Content below tabs (showing "Historial de Citas" active):
  - Timeline-style list of appointments, each as a horizontal card:
    - Left: Date "15 Jul 2026" vertically stacked in a small colored square (#1B6B93 bg, white text)
    - Center: "Dr. Roberto Sánchez — Cardiología" title, "10:30 AM" time, and a status badge "Completada" (#2A9D8F) or "Cancelada" (#E76F51)
    - Right: "Ver detalle" link in #1B6B93
  - 4-5 timeline entries showing different states

Style: Patient-centered design, easy to read medical records. Professional clinical interface.
```

---

## WEB-07 — Módulo de Gestión de Citas (Calendario)

**Prompt para Google Stitch:**

```
Design a medical appointment calendar management page for a healthcare web system. This is the central module for receptionists to schedule and manage appointments.

Layout: Same sidebar + header ("Citas" menu item active with #2A9D8F accent).

Main content area:
- Top control bar (white card, border-radius 12px):
  - Left: View toggle buttons group: "Día" | "Semana" | "Mes" — "Semana" is active with background #1B6B93 and white text, others outline
  - Center: Navigation arrows ← → with current date range "21 — 27 de Julio, 2026" in Inter Semibold 18px
  - Right: Filter dropdown "Todos los doctores ▼" in border style, and "+ Nueva Cita" button solid #1B6B93

- Calendar grid (white card, border-radius 12px, full width):
  - Week view with 7 columns (Lun-Dom) and time slots on the left axis (8:00 AM to 6:00 PM, 30min intervals)
  - Appointment blocks as colored rounded rectangles inside the grid:
    - Blue blocks (#1B6B93 with 15% opacity bg, left border solid 3px #1B6B93): Confirmed appointments, showing "Dr. Sánchez\nJuan García\n10:00-10:30"
    - Teal blocks (#2A9D8F same treatment): Completed
    - Amber blocks (#E9A319): Pending confirmation
    - Red blocks (#E76F51): Cancelled (with strikethrough effect)
  - 8-10 appointment blocks scattered across the week showing realistic distribution
  - Current time indicator: horizontal red dashed line across today's column

- Right sidebar panel (width 300px, white card):
  - Title "Resumen del Día" Inter Semibold 16px
  - Mini stats: "6 Citas programadas", "2 Disponibles", "1 Cancelada"
  - List of today's upcoming appointments as compact cards

Style: Google Calendar inspired but with the medical color palette. Clean time-grid layout. Professional scheduling interface.
```

---

## WEB-08 — Modal de Agendar Nueva Cita

**Prompt para Google Stitch:**

```
Design a modal for scheduling a new medical appointment in a healthcare web system.

Background: Dark overlay (rgba(26,26,46,0.5)).

Modal card (width 560px, white, border-radius 12px, strong shadow):
- Header: Calendar plus icon in #1B6B93 + "Agendar Nueva Cita" Inter Semibold 20px, close X button top-right
- Divider line #E2E8F0
- Step indicator at top: Three dots connected by lines showing a 3-step wizard: "1. Paciente" (active, filled #1B6B93) → "2. Doctor y Horario" (upcoming, outline) → "3. Confirmación" (upcoming, outline)

- Form body (showing Step 2 — Doctor y Horario):
  - Dropdown field "Especialidad" with options visible, border #E2E8F0, border-radius 8px
  - Dropdown field "Doctor" showing "Dr. Roberto Sánchez — Cardiología" selected
  - Date picker field "Fecha de la Cita" showing a mini calendar popup with available dates highlighted in #A8D5E2, unavailable dates grayed out
  - Time slots grid: 6 available time pills in a 3x2 grid, each pill is a rounded rectangle (border-radius 20px):
    - Available slots: border #1B6B93, text #1B6B93, e.g. "9:00 AM", "9:30 AM", "10:00 AM", "11:00 AM", "11:30 AM", "12:00 PM"
    - Selected slot: background #1B6B93, text white ("10:00 AM" selected)
    - Unavailable slots: background #F7F9FC, text #A0AEC0, strikethrough
  - Textarea "Motivo de la consulta" with placeholder, border #E2E8F0, 3 rows height

- Footer:
  - "← Anterior" button outline #4A5568
  - "Confirmar Cita →" button solid #2A9D8F (green, since it's a confirmation action), white text

Style: Step-by-step wizard flow, clean and guided. Time slots as interactive pill buttons. Medical appointment scheduling UX.
```

---

## WEB-09 — Módulo de Gestión de Doctores y Horarios

**Prompt para Google Stitch:**

```
Design a doctor management page for a healthcare web system used by administrators.

Layout: Same sidebar + header ("Doctores" menu item active).

Main content:
- Top bar: "Gestión de Doctores" title, search bar "Buscar doctor por nombre o especialidad...", filter dropdown "Especialidad ▼", and "+ Registrar Doctor" button in #1B6B93

- Doctor cards grid (3 columns):
  - Each doctor card (white, border-radius 12px, shadow, hover shadow increase):
    - Top: Circular avatar (64px, #A8D5E2 background with initials)
    - Name: "Dr. Roberto Sánchez" Inter Semibold 16px, #1A1A2E
    - Specialty badge: "Cardiología" pill badge with background #B5E8D5, text #2A9D8F
    - Cédula: "Céd. Prof. 12345678" in #A0AEC0, 12px
    - Contact icons row: phone icon + email icon in #4A5568
    - Divider line
    - Bottom row: "Disponibilidad" link in #1B6B93 with calendar icon, and "Editar" link in #E9A319 with pencil icon
  - Show 6 doctor cards (2 rows x 3 columns)

- One card should show an "Inactivo" state with a red outline border and grayed-out content

Style: Card-based directory layout. Clean, easy to browse. Medical staff directory aesthetic.
```

---

## WEB-10 — Configuración de Horarios de Doctor

**Prompt para Google Stitch:**

```
Design a doctor schedule configuration page for a medical system administrator.

Layout: Same sidebar + header.

Main content:
- Breadcrumb: "Doctores > Dr. Roberto Sánchez > Horarios" in #4A5568
- Doctor info mini-card at top (horizontal, compact): Avatar + name + specialty badge

- Weekly schedule grid (white card, border-radius 12px):
  - 7 columns for each day of the week (Lunes to Domingo)
  - Each day column header with the day name
  - Inside each column:
    - Time range blocks showing "8:00 AM — 2:00 PM" as filled rectangles in #A8D5E2 (light blue) with "Consulta General" label
    - Break blocks in #F7F9FC (gray) labeled "Descanso"
    - Blocked/vacation blocks in #FADED4 (light red) with diagonal stripes pattern labeled "Bloqueado"
  - Sunday column completely grayed out with "No disponible" label
  - "+ Agregar horario" button at the bottom of each active day column

- Right panel (width 280px):
  - "Bloqueo de Horarios" section
  - Form: Date range picker (Desde — Hasta), Motivo dropdown (Vacaciones, Incapacidad, Personal), "Bloquear Horario" button in #E76F51 with lock icon
  - Warning alert box in #FADED4 background: "⚠️ Al bloquear este horario, 3 citas ya agendadas serán afectadas." in #E76F51 text

Style: Visual schedule builder. Drag-and-drop feel. Color-coded time blocks. Administrative healthcare tool.
```

---

## WEB-11 — Módulo de Reportes

**Prompt para Google Stitch:**

```
Design a reports and analytics page for a medical appointment system administrator dashboard.

Layout: Same sidebar + header ("Reportes" menu item active).

Main content:
- Top filter bar (white card, border-radius 12px):
  - Date range picker: "Desde: 01/07/2026" — "Hasta: 21/07/2026" with calendar icons
  - Dropdown: "Doctor: Todos ▼"
  - Dropdown: "Especialidad: Todas ▼"
  - "Generar Reporte" button solid #1B6B93
  - "Exportar PDF" button outline with PDF icon in #E76F51
  - "Exportar Excel" button outline with spreadsheet icon in #2A9D8F

- ROW 1 — Summary stat cards (4 columns):
  - "Total Agendadas: 156" with calendar icon, card accent border-top 3px #1B6B93
  - "Completadas: 128" with check icon, border-top #2A9D8F
  - "Canceladas: 18" with x icon, border-top #E76F51
  - "Tasa de Asistencia: 82%" with chart icon, border-top #E9A319

- ROW 2 — Two charts side by side:
  - LEFT (55%): Bar chart "Citas por Período" showing bars for each week of July, bars colored #1B6B93 (agendadas), #2A9D8F (completadas), #E76F51 (canceladas). Clean axis labels, legend at top.
  - RIGHT (45%): Donut/pie chart "Especialidades más demandadas" with segments: Cardiología (#1B6B93), Pediatría (#2A9D8F), Dermatología (#E9A319), Traumatología (#F4A261), Otros (#A0AEC0). Center text "156 Total".

- ROW 3 — Full width table:
  - Title "Doctores con Mayor Actividad"
  - Columns: Posición | Doctor | Especialidad | Consultas Realizadas | Tasa de Completadas
  - 5 rows with horizontal bar indicators in each "Consultas" cell showing relative volume
  - Top doctor highlighted with subtle gold (#E9A319) left border

Style: Analytics dashboard with data visualization. Charts clean and readable. Medical business intelligence aesthetic.
```

---

## WEB-12 — Vista del Doctor (Mi Agenda del Día)

**Prompt para Google Stitch:**

```
Design a doctor's daily agenda view for a medical appointment system. This is what doctors see when they log in.

Layout: Same sidebar + header, but sidebar shows doctor-specific menu:
  - 📋 "Mi Agenda" (active)
  - 📝 "Expedientes"
  - 👤 "Mi Perfil"
  - 🚪 "Cerrar Sesión"

Main content:
- Top section: Welcome card (white, border-radius 12px):
  - "Buenos días, Dr. Sánchez" Inter Bold 24px, #1A1A2E
  - "Hoy tienes 8 consultas programadas" Inter Regular 16px, #4A5568
  - Current date "Lunes, 21 de Julio 2026" in #A0AEC0

- Agenda timeline (vertical list, full width):
  - Each appointment as a horizontal card (white, border-radius 12px, left border 4px colored by status):
    - LEFT: Time "10:00 AM" large text in #1B6B93
    - CENTER: Patient name "Juan García Hernández", Exp. #0023, Motivo: "Control de presión arterial" in #4A5568
    - RIGHT: Status badge + Action buttons:
      - "Confirmada" badge #2A9D8F → "Iniciar Consulta" button #1B6B93
      - "En Consulta" badge #1B6B93 with pulsing dot → "Registrar Diagnóstico" button #2A9D8F
      - "Pendiente" badge #E9A319 → "Confirmar Llegada" button outline #E9A319
      - "Completada" badge #2A9D8F muted → "Ver Expediente" link #4A5568
  - 6-7 appointment cards in timeline order showing different states

- Empty slot cards (dashed border #E2E8F0): "11:30 AM — Disponible" in #A0AEC0

Style: Clean medical agenda. Timeline layout with clear status progression. Calming and organized interface for busy doctors.
```

---

## WEB-13 — Formulario de Registro de Diagnóstico (Vista Doctor)

**Prompt para Google Stitch:**

```
Design a medical diagnosis and consultation notes form for a doctor in a healthcare web system.

Layout: Same sidebar + header (Doctor role).

Main content:
- Breadcrumb: "Mi Agenda > Consulta Actual > Registrar Diagnóstico"

- Patient summary mini-card (white, top of page):
  - Avatar + "Juan García Hernández" + Exp. #0023 + "Cita: 10:00 AM — Cardiología" + Age "45 años"

- Form card (white, border-radius 12px, full width):
  - Section 1: "Signos Vitales" (with heart icon #E76F51)
    - 4-column grid: Presión Arterial | Frecuencia Cardíaca | Temperatura | Peso
    - Each as a compact input with unit label (mmHg, bpm, °C, kg)

  - Divider line

  - Section 2: "Diagnóstico" (with clipboard icon #1B6B93)
    - Large textarea "Descripción del diagnóstico" (5 rows, border #E2E8F0)
    
  - Section 3: "Tratamiento Indicado" (with pill/medicine icon #2A9D8F)
    - Large textarea "Tratamiento y recomendaciones" (4 rows)

  - Section 4: "Notas Adicionales" (optional, collapsible)
    - Textarea for additional observations

- Footer buttons:
  - "Guardar como borrador" outline button #4A5568
  - "Completar Consulta" solid button #2A9D8F with check icon

Style: Clinical documentation form. Clear sections for structured medical data. Professional and efficient for fast data entry during consultations.
```

---

---

# 📱 PARTE MÓVIL (Aplicación para Pacientes — Android)

> **Usuario objetivo**: Paciente.
> **Estilo general**: App móvil moderna, accesible, con navegación bottom tab. Diseño Material Design 3 con la paleta de colores del sistema. Optimizada para uso con una mano. Interfaz clara para personas de todas las edades.

---

## MOV-01 — Pantalla de Splash / Carga Inicial

**Prompt para Google Stitch:**

```
Design a mobile splash screen for a medical appointment app called "Agenda Médica" on Android.

Screen size: Standard Android phone (360x800dp).

Layout:
- Full screen background: Gradient from #1B6B93 (top) to #0F4C6B (bottom)
- Centered content:
  - Medical cross icon or heartbeat line icon, white, size 80dp, with a subtle glow effect
  - App name "Agenda Médica" in Inter Bold 28sp, white
  - Tagline "Tu salud, a un toque de distancia" in Inter Regular 14sp, #A8D5E2 (light blue)
- Bottom: Circular loading spinner in white, subtle animation indicator
- Very bottom: "v1.0" version text in #A8D5E2, 11sp

Style: Premium mobile splash screen. Medical but modern and welcoming. Smooth gradient background. Clean and confident first impression.
```

---

## MOV-02 — Pantalla de Inicio de Sesión (Login Móvil)

**Prompt para Google Stitch:**

```
Design a mobile login screen for a medical appointment Android app.

Screen size: 360x800dp Android phone.

Layout (scrollable if needed):
- Status bar area (transparent, dark icons)
- Top area (40% of screen): Curved/wave shape background in #1B6B93 with the medical icon + "Agenda Médica" in white centered
- Below the wave:
  - "Bienvenido" Inter Bold 24sp, #1A1A2E
  - "Inicia sesión para continuar" Inter Regular 14sp, #4A5568
  - Spacer 24dp
  - Email input field: Material style outlined text field, label "Correo Electrónico", leading mail icon, border #E2E8F0, focused border #1B6B93, border-radius 8dp
  - Spacer 12dp
  - Password input field: Same style, label "Contraseña", leading lock icon, trailing eye toggle icon
  - "¿Olvidaste tu contraseña?" text link aligned right, #1B6B93, 13sp
  - Spacer 24dp
  - "Ingresar" button: Full width, height 52dp, background #1B6B93, text white Inter Semibold 16sp, border-radius 12dp, with subtle elevation/shadow
  - Spacer 16dp
  - Divider line with "o" text in center
  - Spacer 12dp
  - "Crear cuenta nueva" button: Full width, outline style, border #1B6B93, text #1B6B93, border-radius 12dp

- Bottom safe area padding

Style: Modern Android app login. Material Design 3 inspired. Medical color palette. Friendly and accessible for all ages. Wave/curve decoration adds visual interest without clutter.
```

---

## MOV-03 — Pantalla de Registro de Paciente

**Prompt para Google Stitch:**

```
Design a mobile patient registration screen for a medical appointment Android app.

Screen size: 360x800dp, scrollable form.

Layout:
- Top app bar: Back arrow ← in #1B6B93, title "Crear Cuenta" Inter Semibold 18sp, #1A1A2E
- Progress indicator: 3-step dots, Step 1 active (filled #1B6B93), steps 2-3 outline #E2E8F0. Labels: "Datos Personales" | "Identificación" | "Credenciales"

- Form body (padding 20dp):
  - Step 1 showing:
  - "Nombre Completo" outlined text field with person icon
  - "Fecha de Nacimiento" date field with calendar icon, showing "DD/MM/AAAA" placeholder
  - "Sexo" dropdown field with options Masculino/Femenino
  - "Teléfono" phone field with +52 country prefix and phone icon
  - "Dirección" text field with location icon
  
  - Each field has:
    - Material outlined style, border-radius 8dp
    - Label text in #4A5568
    - Helper/error text area below in 12sp
    - Leading icon in #A0AEC0, focused icon in #1B6B93

  - Inline validation shown on one field: green checkmark ✓ on a correctly filled field, red error text "El teléfono debe tener 10 dígitos" below an incorrect field

- Bottom fixed area:
  - "Siguiente →" button, full width, solid #1B6B93, white text, border-radius 12dp, height 52dp

Style: Clean step-by-step registration wizard. Mobile-first form design with real-time validation feedback. Accessible, large touch targets (48dp minimum).
```

---

## MOV-04 — Pantalla Principal / Home (Paciente)

**Prompt para Google Stitch:**

```
Design the main home screen of a medical appointment mobile app for patients on Android.

Screen size: 360x800dp.

Layout:
- Top section (background #1B6B93, curved bottom edge):
  - Row: "Hola, Juan 👋" Inter Bold 22sp white | Avatar circle 44dp with initials "JG" in white on #2A9D8F
  - Below: "Lunes, 21 de Julio 2026" Inter Regular 13sp, #A8D5E2

- Próxima cita card (white, border-radius 16dp, shadow, overlapping the blue top section by 30dp, margin horizontal 16dp):
  - Small label "Tu próxima cita" in #A0AEC0 12sp
  - Doctor name "Dr. Roberto Sánchez" Inter Semibold 16sp, #1A1A2E
  - Specialty "Cardiología" teal badge pill #2A9D8F
  - Row: Calendar icon + "22 Jul 2026" | Clock icon + "10:00 AM"
  - Status: "Confirmada ✓" green text #2A9D8F
  - Divider
  - "Ver detalle →" link text #1B6B93

- Quick actions grid (2x2, below the card, padding 16dp):
  - Four square action cards (border-radius 12dp, white, shadow, icon + label):
    - 📅 "Agendar Cita" — icon bg #A8D5E2 circle, icon #1B6B93
    - 📋 "Mis Citas" — icon bg #B5E8D5 circle, icon #2A9D8F
    - 🏥 "Doctores" — icon bg #FADED4 circle, icon #E76F51
    - 📄 "Historial" — icon bg light amber circle, icon #E9A319

- Section title "Especialidades Disponibles" Inter Semibold 16sp, #1A1A2E
- Horizontal scrollable chip/pill list:
  - "Cardiología" | "Pediatría" | "Dermatología" | "Traumatología" | "Medicina General"
  - Active chip: bg #1B6B93, text white. Inactive: bg #F7F9FC, border #E2E8F0, text #4A5568

- BOTTOM NAVIGATION BAR (white, elevation shadow, 5 items):
  - 🏠 Inicio (active, #1B6B93 icon and label) | 📅 Citas | 🔍 Doctores | 📋 Historial | 👤 Perfil
  - Inactive items in #A0AEC0

Style: Modern mobile home screen. Card-based layout. Friendly and intuitive for patients of all ages. Medical but not clinical — warm and approachable.
```

---

## MOV-05 — Pantalla de Búsqueda de Doctores

**Prompt para Google Stitch:**

```
Design a doctor search and browse screen for a medical appointment Android app for patients.

Screen size: 360x800dp.

Layout:
- Top app bar: "Doctores Disponibles" title Inter Semibold 18sp, #1A1A2E

- Search bar (below app bar, padding 16dp):
  - Rounded search input (border-radius 24dp, background #F7F9FC, border #E2E8F0), placeholder "Buscar doctor o especialidad...", search icon left in #A0AEC0, height 48dp

- Filter chips row (horizontal scroll):
  - "Todas" | "Cardiología" | "Pediatría" | "Dermatología" | "Traumatología"
  - Active: bg #1B6B93, text white. Inactive: outline #E2E8F0, text #4A5568

- Sort toggle: "Ordenar por: Disponibilidad más próxima ▼" small text #1B6B93, 12sp

- Doctor list (vertical scroll):
  - Each doctor card (white, border-radius 12dp, margin-bottom 12dp, shadow, padding 16dp):
    - Row layout:
      - LEFT: Circular avatar (56dp) with doctor photo placeholder or initials on #A8D5E2 bg
      - CENTER column:
        - "Dr. Roberto Sánchez" Inter Semibold 16sp, #1A1A2E
        - "Cardiología" Inter Regular 13sp, #4A5568
        - "Céd. Prof. 12345678" Inter Regular 11sp, #A0AEC0
        - Row: Star rating ★★★★★ (4.8) in #E9A319 11sp
      - RIGHT column:
        - Green dot + "Disponible" text in #2A9D8F 12sp
        - "Próximo: Hoy 3:00 PM" in #1B6B93 12sp bold
    - Divider inside card
    - Bottom row: "Ver perfil" link #4A5568 | "Agendar cita →" button small, solid #1B6B93, white text, border-radius 20dp

  - Show 4 doctor cards, one showing "Sin disponibilidad esta semana" in #E76F51 with the action button grayed out

- Bottom nav bar (same as MOV-04, "Doctores" tab active)

Style: Browse/search directory for mobile. Easy to scan cards with clear availability indicators. Patient-friendly medical professional directory.
```

---

## MOV-06 — Pantalla de Detalle del Doctor

**Prompt para Google Stitch:**

```
Design a doctor detail/profile screen for a medical appointment Android app.

Screen size: 360x800dp, scrollable.

Layout:
- Top: Back arrow ← and "Perfil del Doctor" title in app bar

- Hero section (background gradient #1B6B93 to #0F4C6B, curved bottom):
  - Centered circular avatar (96dp) with white border ring, initials "RS" on #2A9D8F
  - "Dr. Roberto Sánchez" Inter Bold 22sp, white
  - "Cardiología" pill badge bg rgba(255,255,255,0.2), text white
  - "Cédula Prof. 12345678" Inter Regular 12sp, #A8D5E2

- Info card (white, border-radius 16dp, negative margin overlapping blue section, shadow):
  - Three mini stat boxes in a row:
    - "15 años" label "Experiencia" — icon #1B6B93
    - "4.8 ★" label "Calificación" — icon #E9A319
    - "1,200+" label "Consultas" — icon #2A9D8F

- Section "Horarios de Atención" Inter Semibold 16sp, #1A1A2E:
  - Weekly schedule mini-grid:
    - Days as row headers: Lun, Mar, Mié, Jue, Vie
    - Each row showing time range: "8:00 AM - 2:00 PM" in #4A5568
    - Sáb, Dom: "No disponible" in #A0AEC0
  - Container card with border-radius 12dp, bg #F7F9FC

- Section "Disponibilidad" Inter Semibold 16sp:
  - Mini calendar (current month, 7 columns for days):
    - Available dates highlighted with #A8D5E2 circle background
    - Today marked with #1B6B93 filled circle, white text
    - Unavailable dates in #A0AEC0 text, no background
    - Selected date with #2A9D8F border ring

- Bottom fixed button:
  - "Agendar Cita con Dr. Sánchez →" full width, solid #1B6B93, white text, border-radius 12dp, height 52dp, shadow

Style: Professional doctor profile page. Hero header with gradient. Clean information architecture. Inspires trust and confidence for patients.
```

---

## MOV-07 — Flujo de Agendamiento de Cita (Paso a Paso)

**Prompt para Google Stitch:**

```
Design a multi-step appointment booking flow for a medical appointment Android app. Show all 3 steps as a carousel or side-by-side mobile screens.

Screen size: 360x800dp each.

STEP 1 — "Seleccionar Fecha y Hora":
- Top app bar: Back arrow, "Agendar Cita" title, step indicator "Paso 1 de 3"
- Doctor mini-card at top: Avatar + "Dr. Roberto Sánchez — Cardiología" compact row
- Calendar widget (full month view):
  - Current month "Julio 2026" with ← → arrows
  - 7 columns (D L M M J V S), days grid
  - Available days: #1A1A2E text, tappable
  - Unavailable: #A0AEC0, not tappable
  - Selected day: filled circle #1B6B93, white text
  - Today: outline circle #2A9D8F
- "Horarios disponibles" section below calendar:
  - Time slot pills in a flowing grid (3 per row):
    - Available: outline #1B6B93, text #1B6B93
    - Selected: filled #1B6B93, text white, checkmark
    - Unavailable: bg #F7F9FC, text #A0AEC0
  - Slots: "8:00", "8:30", "9:00", "9:30", "10:00", "10:30", "11:00"
- Bottom: "Siguiente →" button solid #1B6B93

STEP 2 — "Motivo de Consulta":
- Same app bar with step "Paso 2 de 3"
- Summary card showing selected: Date, Time, Doctor
- Textarea "Describe brevemente el motivo de tu consulta" with border #E2E8F0, 4 lines, placeholder text
- Suggested reasons as chips: "Control rutinario" | "Dolor o molestia" | "Seguimiento" | "Primera vez"
- Bottom: "← Anterior" outline | "Siguiente →" solid #1B6B93

STEP 3 — "Confirmación":
- Same app bar with step "Paso 3 de 3"  
- Confirmation summary card (white, border-radius 16dp, shadow):
  - Checkmark circle icon #2A9D8F at top center
  - All details listed with icons:
    - 👨‍⚕️ Doctor: Dr. Roberto Sánchez
    - 🏥 Especialidad: Cardiología
    - 📅 Fecha: 22 de Julio, 2026
    - 🕙 Hora: 10:00 AM
    - 📝 Motivo: Control de presión arterial
  - Each detail row with label in #A0AEC0 and value in #1A1A2E
- Note: "Recibirás un correo de confirmación con tu código de referencia" in #4A5568, 12sp
- Bottom: "Confirmar Cita ✓" button solid #2A9D8F, full width, large

Style: Guided step-by-step booking wizard. Each step clean and focused. Calendar and time slots highly interactive. Confirmation screen gives confidence before committing.
```

---

## MOV-08 — Pantalla de Confirmación de Cita Exitosa

**Prompt para Google Stitch:**

```
Design a successful appointment confirmation screen for a medical appointment Android app.

Screen size: 360x800dp.

Layout (centered content):
- Top: Subtle confetti or checkmark animation area
- Large animated checkmark icon inside a circle (80dp):
  - Circle background: #B5E8D5 (light green)
  - Checkmark: #2A9D8F
  - Subtle pulse/glow animation ring

- "¡Cita Agendada!" Inter Bold 26sp, #1A1A2E
- "Tu cita ha sido registrada exitosamente" Inter Regular 15sp, #4A5568

- Reference code card (white, border-radius 12dp, dashed border #1B6B93):
  - "Código de Referencia" label #A0AEC0, 12sp
  - "REF-2026-0721-0034" Inter Bold 20sp, #1B6B93, monospace style

- Appointment summary compact card (bg #F7F9FC, border-radius 12dp, padding 16dp):
  - Icon + "Dr. Roberto Sánchez — Cardiología"
  - Icon + "22 de Julio, 2026 — 10:00 AM"
  - Icon + "Motivo: Control de presión arterial"

- Spacer 24dp

- Two buttons:
  - "Ver Mis Citas" solid button #1B6B93, full width, border-radius 12dp
  - "Volver al Inicio" outline button #4A5568, full width, border-radius 12dp

Style: Celebration/success screen. Positive feedback with visual confirmation. The reference code stands out clearly. Warm and reassuring.
```

---

## MOV-09 — Pantalla "Mis Citas"

**Prompt para Google Stitch:**

```
Design a "My Appointments" screen for a medical appointment Android app showing upcoming and past appointments.

Screen size: 360x800dp.

Layout:
- Top app bar: "Mis Citas" Inter Semibold 18sp, #1A1A2E

- Tab bar (2 tabs, full width):
  - "Próximas" (active, bottom border 3dp #1B6B93, text #1B6B93) | "Historial" (inactive, text #A0AEC0)

- "Próximas" tab content:
  - TODAY highlight card (accent border-left 4dp #E9A319, white bg, border-radius 12dp):
    - "HOY" badge small, bg #E9A319, text white, border-radius 4dp, 10sp
    - "Dr. Roberto Sánchez — Cardiología" Inter Semibold 15sp, #1A1A2E
    - Row: Calendar icon "22 Jul 2026" | Clock icon "10:00 AM"
    - Status badge "Confirmada ✓" pill bg #B5E8D5, text #2A9D8F
    - Ref: "REF-2026-0721-0034" in #A0AEC0 11sp
    - Swipe hint or "Cancelar" text button in #E76F51 at bottom-right
  
  - UPCOMING cards (2 more, regular style, border-left 4dp #1B6B93):
    - Similar layout but without the "HOY" badge
    - Each with date, doctor, specialty, time, status badge
  
  - Empty state hint at bottom: "No tienes más citas próximas" in #A0AEC0 centered

- Floating action button: "+" icon, circular, bg #1B6B93, white icon, bottom-right, shadow, 56dp

- CANCELLED appointment card (if shown in Historial tab):
  - Border-left 4dp #E76F51
  - Content slightly faded/opacity 0.7
  - "Cancelada ✗" badge bg #FADED4, text #E76F51
  - No action buttons, just "Ver detalle" in #A0AEC0

- Bottom nav bar ("Citas" tab active)

Style: List-based appointment tracker. Today's appointments highlighted prominently. Clear visual state differentiation with colored borders and badges. Easy to scan and manage.
```

---

## MOV-10 — Pantalla de Detalle de Cita

**Prompt para Google Stitch:**

```
Design an appointment detail screen for a medical appointment Android app.

Screen size: 360x800dp.

Layout:
- Top app bar: Back arrow, "Detalle de Cita" title

- Status banner at top:
  - Full width, height 48dp, background #B5E8D5
  - "Estado: Confirmada ✓" centered text in #2A9D8F, Inter Semibold 14sp

- Appointment detail card (white, border-radius 16dp, shadow, margin 16dp):
  - Section "Información de la Cita":
    - Row: Calendar icon #1B6B93 | "Fecha" label #A0AEC0 | "22 de Julio, 2026" value #1A1A2E
    - Row: Clock icon #1B6B93 | "Hora" label | "10:00 AM" value
    - Row: Tag icon #1B6B93 | "Código de Referencia" label | "REF-2026-0721-0034" value bold
    - Row: Note icon #1B6B93 | "Motivo" label | "Control de presión arterial" value
  - Divider line #E2E8F0

  - Section "Doctor Asignado":
    - Avatar (48dp) + "Dr. Roberto Sánchez" name + "Cardiología" specialty badge #2A9D8F
    - "Céd. Prof. 12345678" in #A0AEC0
    - "Consultorio 3, Planta Baja" location in #4A5568

- Action buttons (padding 16dp):
  - "Reprogramar Cita" outline button, border #1B6B93, text #1B6B93, full width, border-radius 12dp
  - Spacer 8dp
  - "Cancelar Cita" outline button, border #E76F51, text #E76F51, full width, border-radius 12dp

- Warning note at bottom (only visible if close to appointment time):
  - Info card bg #FADED4, border-radius 8dp:
  - "⚠️ La cancelación solo está disponible con al menos 2 horas de anticipación." text #E76F51, 12sp

Style: Clean detail view with structured information rows. Status banner gives instant context. Clear action buttons with appropriate color coding (teal for neutral actions, red for destructive).
```

---

## MOV-11 — Pantalla de Historial Médico

**Prompt para Google Stitch:**

```
Design a medical history screen for a patient in a medical appointment Android app. This shows past completed consultations with diagnoses.

Screen size: 360x800dp.

Layout:
- Top app bar: "Mi Historial Médico" Inter Semibold 18sp

- Filter bar (horizontal scroll):
  - Chip pills: "Todos" (active #1B6B93) | "Dr. Sánchez" | "Dra. López" | "Último mes" | "Último año"

- Consultation list (vertical scroll):
  - Each consultation as an expandable card (white, border-radius 12dp, shadow, margin 12dp):
    
    COLLAPSED STATE:
    - Left: Date block (square, bg #F7F9FC, border-radius 8dp): "15" large + "Jul" small + "2026" tiny, stacked
    - Center: "Dr. Roberto Sánchez" Inter Semibold 14sp, "Cardiología" in #4A5568 12sp, "Control rutinario" in #A0AEC0 12sp
    - Right: Expand chevron ▼ in #A0AEC0

    EXPANDED STATE (show one card expanded):
    - Same header info but chevron rotated ▲
    - Divider line
    - "Diagnóstico" section:
      - Clipboard icon #1B6B93
      - "Hipertensión arterial estadio I. Presión arterial 140/90 mmHg." text in #1A1A2E, 14sp
    - "Tratamiento Indicado" section:
      - Medicine icon #2A9D8F
      - "Losartán 50mg cada 24 horas. Dieta baja en sodio. Control en 30 días." text in #1A1A2E, 14sp
    - Read-only indicator: Small lock icon + "Solo lectura" text in #A0AEC0 10sp at bottom

  - Show 4-5 cards, one expanded, rest collapsed

- Empty state (if no history):
  - Centered illustration area: Medical file icon in #A8D5E2
  - "Aún no tienes consultas registradas" Inter Regular 15sp, #4A5568
  - "Agenda tu primera cita" link in #1B6B93

- Bottom nav bar ("Historial" tab active)

Style: Medical records viewer. Expandable cards for progressive disclosure. Read-only feel with lock indicators. Clean clinical data presentation accessible to patients.
```

---

## MOV-12 — Pantalla de Perfil del Paciente

**Prompt para Google Stitch:**

```
Design a patient profile screen for a medical appointment Android app.

Screen size: 360x800dp.

Layout:
- Top section (background #1B6B93, curved bottom, height ~200dp):
  - Large circular avatar (96dp) with white border, centered, showing initials "JG" on #2A9D8F background
  - "Juan García Hernández" Inter Bold 20sp, white
  - "Paciente desde Enero 2025" Inter Regular 12sp, #A8D5E2

- Activity summary card (white, border-radius 16dp, shadow, overlapping blue by 30dp):
  - Two stat boxes side by side:
    - LEFT: "12" large number #1B6B93 + "Citas realizadas" small label
    - Vertical divider
    - RIGHT: "22 Jul, 10:00 AM" text #2A9D8F + "Próxima cita" small label

- Personal info section (white card, border-radius 12dp, margin 16dp):
  - Section header "Datos Personales" Inter Semibold 16sp, #1A1A2E, with pencil edit icon #1B6B93 at right
  - Info rows with icons:
    - Person icon: "Juan García Hernández" (non-editable indicator, lock icon tiny)
    - Calendar icon: "15/03/1981" (non-editable)
    - ID icon: "CURP: GAHJ810315HDFRNN09" (non-editable)
    - Mail icon: "juan.garcia@email.com"
    - Phone icon: "55 1234 5678" (editable indicator, pencil icon)

- Actions section (list items, full width):
  - Each as a tappable row (height 56dp, divider between):
    - 📷 "Cambiar foto de perfil" with right arrow ›
    - 🔒 "Cambiar contraseña" with right arrow ›
    - ℹ️ "Acerca de" with right arrow ›
  - Spacer 16dp
  - 🚪 "Cerrar Sesión" row in #E76F51 text, with logout icon

- Bottom nav bar ("Perfil" tab active)

Style: Modern profile screen with hero header. Card-based layout with clear data display. Settings-style action list at bottom. Personal and warm feel for patients.
```

---

## MOV-13 — Modal de Cancelación de Cita

**Prompt para Google Stitch:**

```
Design a bottom sheet modal for cancelling a medical appointment in an Android app.

Screen size: 360x800dp. The modal rises from the bottom covering ~55% of the screen.

Background: Dimmed overlay rgba(26,26,46,0.5) on the "Mis Citas" screen behind.

Bottom sheet (white, border-radius top-left and top-right 20dp):
- Drag handle bar at top center (40x4dp, bg #E2E8F0, border-radius 2dp)
- Warning icon: Triangle exclamation in #E76F51, size 48dp, centered
- Title "¿Cancelar esta cita?" Inter Bold 20sp, #1A1A2E, centered
- Subtitle "Esta acción no se puede deshacer" Inter Regular 13sp, #4A5568, centered

- Appointment info mini-card (bg #F7F9FC, border-radius 8dp, margin 16dp):
  - "Dr. Roberto Sánchez — Cardiología"
  - "22 Jul 2026 — 10:00 AM"
  - "REF-2026-0721-0034"

- Dropdown/radio field "Motivo de cancelación":
  - Options as radio buttons:
    - ○ "No puedo asistir"
    - ○ "Encontré otro horario"
    - ○ "Ya no necesito la consulta"
    - ○ "Otro motivo"
  - If "Otro motivo" selected: textarea appears for custom reason

- Spacer 16dp
- Two buttons:
  - "Cancelar Cita" full width, solid #E76F51, white text, border-radius 12dp
  - "Volver" full width, outline #4A5568, text #4A5568, border-radius 12dp

Style: Destructive action confirmation. Bottom sheet pattern. Clear warning without being scary. Requires reason selection before proceeding. Medical appointment cancellation UX.
```

---

## MOV-14 — Pantalla de Edición de Perfil

**Prompt para Google Stitch:**

```
Design a profile edit screen for a patient in a medical appointment Android app.

Screen size: 360x800dp.

Layout:
- Top app bar: Back arrow ←, "Editar Perfil" title, "Guardar" text button in #1B6B93 at right

- Avatar section (centered, padding 24dp):
  - Circular avatar (96dp) with current initials "JG" on #2A9D8F
  - Small camera icon button (32dp circle, bg #1B6B93, white camera icon) overlapping bottom-right of avatar
  - "Cambiar foto" link text #1B6B93 12sp below

- Editable fields form (padding 16dp):
  - "Teléfono" outlined text field, pre-filled "55 1234 5678", editable, phone icon, focused state with #1B6B93 border
  - Spacer 12dp
  
- Non-editable fields section:
  - Label "Los siguientes datos no pueden ser modificados" Inter Regular 12sp, #A0AEC0
  - "Nombre Completo" field, pre-filled, grayed out background #F7F9FC, lock icon, disabled
  - "CURP" field, pre-filled, grayed out, lock icon, disabled
  - "Fecha de Nacimiento" field, pre-filled, grayed out, lock icon, disabled
  - "Correo Electrónico" field, pre-filled, grayed out, lock icon, disabled

- Divider

- "Cambiar Contraseña" section card (white, border-radius 12dp):
  - "Contraseña actual" password field with lock icon
  - "Nueva contraseña" password field with key icon
  - "Confirmar nueva contraseña" password field with key icon
  - Password strength indicator bar below the new password field:
    - 4 segments: all gray = none, 1 red = weak, 2 amber = medium, 3-4 green = strong
  - "Actualizar contraseña" button, outline #1B6B93, full width

Style: Profile edit form with clear distinction between editable and locked fields. The disabled fields have a distinct visual treatment so users understand they can't be changed. Clean and accessible form design.
```

---

---

# 📌 Notas Generales para Google Stitch

> **Aplicar en TODOS los prompts:**
> - Tipografía: **Inter** (Google Font) en toda la interfaz
> - Resolución de diseño Web: **1440x900px** (desktop)
> - Resolución de diseño Móvil: **360x800dp** (Android estándar)
> - Todos los diseños deben sentirse cohesivos y parte del mismo sistema visual
> - Usar los colores EXACTOS especificados en la paleta (#1B6B93, #0F4C6B, #2A9D8F, #E9A319, #E76F51, #F7F9FC, etc.)
> - Estilo: **Moderno, profesional, limpio, inspirador de confianza** — tipo SaaS de salud premium
> - Sin marcos de dispositivo (laptop/teléfono) rodeando los diseños — solo la interfaz directa
> - Los iconos deben ser estilo **outline/linear** (tipo Lucide, Phosphor o Material Symbols Outlined)
> - Idioma de toda la interfaz: **Español (México)**
