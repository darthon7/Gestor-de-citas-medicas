# Sistema de Gestión de Citas Médicas

---

## 1. Introducción

El Sistema de Gestión de Citas Médicas es una plataforma digital orientada a optimizar el proceso de agendamiento y seguimiento de consultas en una clínica o centro de salud. Permite al personal administrativo y médico gestionar la disponibilidad de doctores, registrar pacientes y controlar el flujo de citas, mientras que los pacientes cuentan con una aplicación móvil para agendar, consultar y cancelar sus citas de forma autónoma, sin necesidad de llamar o acudir físicamente al centro.

**La plataforma consta de dos interfaces:**

- **Sistema Web:** destinado al personal administrativo y médicos para gestionar pacientes, doctores, horarios y expedientes.
- **Aplicación Móvil:** destinada a los pacientes para consultar disponibilidad, agendar citas y revisar su historial de consultas.

### 1.1 Objetivos del Proyecto

- Desarrollar un sistema completo que cubra todos los módulos definidos en este documento.
- Reducir los tiempos de espera y errores en el proceso de agendamiento de citas médicas.
- Centralizar la información de pacientes, doctores y expedientes en una sola plataforma.
- Proveer a los pacientes una herramienta móvil sencilla para gestionar sus citas sin intermediarios.

### 1.2 Alcance del Sistema

El sistema cubrirá el ciclo completo de una cita médica: desde el registro del paciente y la configuración de horarios del doctor, hasta la consulta, el registro del diagnóstico y la generación del expediente. No contempla la integración con sistemas de seguros médicos externos, recetas electrónicas con validez legal ni pagos en línea.

---

## 2. Descripción General del Sistema

El Sistema de Gestión de Citas Médicas es una aplicación cliente-servidor compuesta por un panel web para el personal del centro de salud y una app móvil para los pacientes. Ambas plataformas consumen la misma API REST y comparten la base de datos central.

### 2.1 Tipos de Usuario

| Rol | Plataforma | Descripción |
|---|---|---|
| Administrador | Web | Configura el sistema, gestiona usuarios, doctores y especialidades. |
| Recepcionista | Web | Registra pacientes, agenda citas y gestiona el flujo diario de consultas. |
| Doctor | Web | Consulta su agenda del día, registra diagnósticos y actualiza expedientes. |
| Paciente | Móvil | Agenda, consulta y cancela sus citas; revisa su historial médico básico. |

---

## 3. Módulos del Sistema Web

El sistema web está destinado al personal administrativo y médico del centro de salud. A continuación se describen sus cinco módulos principales.

### Módulo 1: Autenticación y Control de Acceso

**Descripción**

Gestiona el ingreso seguro al sistema web, diferenciando las funciones disponibles según el rol del usuario: Administrador, Recepcionista o Doctor.

**Funciones requeridas**

- Inicio de sesión con correo y contraseña.
- Generación y manejo de token JWT para mantener la sesión activa.
- Control de acceso por rol: cada rol solo puede acceder a las secciones que le corresponden.
- Cierre de sesión con invalidación del token.
- Recuperación de contraseña mediante enlace enviado al correo registrado.
- Bloqueo temporal de cuenta tras 5 intentos fallidos de inicio de sesión.

---

### Módulo 2: Gestión de Pacientes

**Descripción**

Permite al Administrador y a la Recepcionista registrar y administrar la información de los pacientes del centro de salud, manteniendo un expediente básico por cada uno.

**Funciones requeridas**

- Registrar un nuevo paciente con: nombre completo, fecha de nacimiento, sexo, CURP, teléfono, correo y dirección.
- Buscar pacientes por nombre, CURP o número de expediente.
- Ver el perfil completo del paciente, incluyendo su historial de citas y diagnósticos.
- Editar la información personal de un paciente.
- Desactivar un paciente (sin eliminar su historial).
- Asignar un número de expediente único al momento del registro.

**Requerimientos no funcionales**

- El número de expediente y la CURP deben ser únicos en el sistema.
- No se puede desactivar un paciente con citas activas pendientes.

---

### Módulo 3: Gestión de Doctores y Horarios

**Descripción**

Permite al Administrador registrar a los médicos del centro, asignarles especialidades y configurar sus horarios de disponibilidad para que puedan recibir citas.

**Funciones requeridas**

- Registrar un nuevo doctor con: nombre, especialidad, cédula profesional, teléfono y correo.
- Asignar una o más especialidades a cada doctor.
- Configurar el horario semanal de cada doctor: días, hora de inicio, hora de fin y duración de cada consulta.
- Marcar días de descanso o bloquear horarios específicos (vacaciones, ausencias).
- Editar la información y horarios de un doctor existente.
- Listar todos los doctores con filtro por especialidad y disponibilidad.

**Requerimientos no funcionales**

- No se pueden registrar dos horarios solapados para el mismo doctor en el mismo día.
- Al bloquear un horario con citas ya agendadas, el sistema debe alertar al administrador.

---

### Módulo 4: Gestión de Citas

**Descripción**

Es el módulo central del sistema. Permite a la Recepcionista agendar, consultar, reprogramar y cancelar citas médicas, gestionando la disponibilidad en tiempo real.

**Funciones requeridas**

- Agendar una nueva cita seleccionando paciente, doctor, fecha y hora disponible.
- Ver el calendario de citas del día, semana o mes por doctor o por especialidad.
- Reprogramar una cita a otra fecha u hora disponible.
- Cancelar una cita con registro del motivo.
- Registrar la llegada del paciente (check-in) el día de la consulta.
- Ver el historial completo de citas de un paciente específico.
- Filtrar citas por estado: agendada, confirmada, en consulta, completada o cancelada.

**Requerimientos no funcionales**

- No se pueden agendar dos citas al mismo doctor en el mismo horario.
- Solo se pueden agendar citas dentro del horario de disponibilidad configurado para el doctor.
- La cancelación de una cita debe registrar la fecha, hora y usuario que realizó la acción.

---

### Módulo 5: Reportes

**Descripción**

Ofrece al Administrador información consolidada sobre la operación del centro médico para apoyar la toma de decisiones.

**Funciones requeridas**

- Reporte de citas por período: total agendadas, completadas y canceladas.
- Reporte de doctores con mayor número de consultas en un rango de fechas.
- Reporte de especialidades más demandadas.
- Reporte de pacientes con mayor frecuencia de visitas.
- Resumen diario del flujo de citas (agenda del día con estado de cada cita).
- Exportación de reportes en formato PDF y Excel.

**Requerimientos no funcionales**

- Los reportes deben poder filtrarse por rango de fechas, doctor y especialidad.
- La generación de reportes no debe afectar el rendimiento de las operaciones en curso.

---

## 4. Módulos de la Aplicación Móvil

La aplicación móvil está orientada a los pacientes del centro de salud. Su diseño debe ser claro, accesible y fácil de usar para personas de distintas edades.

### Módulo 6: Autenticación Móvil

**Descripción**

Permite al paciente crear su cuenta e iniciar sesión en la app para acceder a todas las funcionalidades personalizadas.

**Funciones requeridas**

- Pantalla de registro con: nombre completo, fecha de nacimiento, CURP, correo y contraseña.
- Inicio de sesión con correo y contraseña.
- Validación de campos en tiempo real (formato de correo, CURP, longitud de contraseña).
- Recuperación de contraseña mediante correo electrónico.
- Mantener la sesión activa entre cierres de la app mediante token almacenado localmente.
- Cerrar sesión desde la pantalla de perfil.

---

### Módulo 7: Búsqueda de Doctores

**Descripción**

Permite al paciente explorar los médicos disponibles en el centro, filtrar por especialidad y consultar los horarios de atención antes de agendar una cita.

**Funciones requeridas**

- Listado de doctores disponibles con nombre, especialidad y foto de perfil.
- Búsqueda de doctores por nombre o especialidad.
- Filtro por especialidad y disponibilidad (con citas disponibles hoy, esta semana).
- Pantalla de detalle del doctor: nombre, especialidad, cédula profesional y horarios de atención.
- Visualización del calendario de disponibilidad del doctor con los horarios libres.

**Requerimientos no funcionales**

- Los horarios bloqueados o sin disponibilidad no deben mostrarse como opciones al paciente.
- El listado debe ordenarse por disponibilidad más próxima de forma predeterminada.

---

### Módulo 8: Agendamiento de Citas

**Descripción**

Permite al paciente seleccionar un doctor, elegir fecha y hora disponible, y confirmar su cita directamente desde la app.

**Funciones requeridas**

- Flujo de agendamiento paso a paso: selección de doctor, fecha, hora y confirmación.
- Mostrar únicamente los horarios disponibles del doctor seleccionado.
- Pantalla de resumen antes de confirmar la cita: doctor, fecha, hora y especialidad.
- Confirmación de la cita con código de referencia generado por el sistema.
- Cancelar una cita agendada desde la app (con restricción de tiempo mínimo antes de la cita).

**Requerimientos no funcionales**

- No se puede agendar una cita si el paciente ya tiene otra cita activa con el mismo doctor el mismo día.
- La cancelación solo está disponible con al menos 2 horas de anticipación a la hora de la cita.
- Al confirmar la cita, el sistema debe enviar un correo electrónico de confirmación al paciente.

---

### Módulo 9: Mis Citas

**Descripción**

Muestra al paciente un resumen de todas sus citas agendadas y el historial de consultas anteriores, con su estado actualizado.

**Funciones requeridas**

- Lista de citas próximas ordenadas por fecha, con nombre del doctor, especialidad, fecha y hora.
- Sección de historial con citas pasadas (completadas y canceladas).
- Indicador visual del estado de cada cita: agendada, confirmada, completada o cancelada.
- Opción para cancelar una cita próxima directamente desde la lista.
- Ver el detalle de una cita: información del doctor, fecha, hora y código de referencia.

**Requerimientos no funcionales**

- Las citas del día deben destacarse visualmente al inicio de la lista.
- Las citas canceladas deben mostrarse con etiqueta visual diferenciada y sin opción de interacción.

---

### Módulo 10: Historial Médico

**Descripción**

Permite al paciente consultar el resumen de sus consultas previas, incluyendo los diagnósticos y tratamientos registrados por el médico.

**Funciones requeridas**

- Listado de consultas anteriores con fecha, doctor y especialidad.
- Ver el detalle de cada consulta: diagnóstico y tratamiento indicado registrado por el doctor.
- Filtrar el historial por doctor o por rango de fechas.
- Visualización en modo solo lectura (el paciente no puede editar la información clínica).

**Requerimientos no funcionales**

- Solo se muestran consultas con estado completado y con diagnóstico registrado por el doctor.
- El historial debe estar disponible, aunque no haya citas próximas agendadas.

---

### Módulo 11: Perfil del Paciente

**Descripción**

Permite al paciente consultar y actualizar sus datos personales dentro de la aplicación.

**Funciones requeridas**

- Ver datos del perfil: nombre, fecha de nacimiento, CURP, correo y teléfono.
- Editar teléfono y foto de perfil.
- Cambiar contraseña desde la app (requiere ingresar la contraseña actual).
- Ver resumen de actividad: total de citas realizadas y próxima cita agendada.
- Cerrar sesión.

---

*Documento generado a partir del Sistema de Gestión de Citas Médicas — 8 páginas originales.*
