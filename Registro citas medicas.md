# Registro de Usuarios en Sistemas de Citas Médicas (México)

Este documento explica cómo se registran los distintos roles (paciente, médico, recepcionista) dentro de los sistemas de salud mexicanos —tanto públicos (IMSS, ISSSTE, ISSEMyM) como privados— y cómo se traduce esa lógica al alta general de un sistema web propio de citas médicas.

---

## 1. Conceptos clave usados en México

Antes de ver cada rol, conviene tener claros los identificadores que casi todos los sistemas de salud mexicanos usan como base de validación:

| Identificador | Qué es | Quién lo usa |
|---|---|---|
| **CURP** | Clave Única de Registro de Población | Pacientes, médicos, recepcionistas (todos) |
| **RFC** | Registro Federal de Contribuyentes | Trabajadores del Estado (ISSSTE), facturación |
| **Cédula profesional** | Documento emitido por la SEP que certifica que una persona tiene un título profesional válido | Médicos |
| **NSS** | Número de Seguridad Social | Pacientes derechohabientes del IMSS |
| **INE** | Credencial para votar, usada como validación de identidad | Médicos y pacientes en procesos de verificación facial |

La mayoría de los sistemas mexicanos (IMSS Digital, Citas ISSSTE, plataformas privadas como expedientes clínicos electrónicos) usan la **CURP como llave primaria de identidad**, y luego añaden validaciones específicas según el rol.

---

## 2. Registro de Pacientes

El paciente es el rol con el registro más simple, pero el más regulado en cuanto a protección de datos.

### Datos que normalmente se solicitan
- Nombre completo
- CURP (validación automática contra RENAPO)
- Fecha de nacimiento
- Sexo
- Correo electrónico y/o número celular
- Domicilio
- Número de afiliación (NSS en IMSS, clave ISSEMyM/ISSSTE si aplica en sistemas de gobierno)
- Contacto de emergencia (opcional, buena práctica)

### Flujo típico
1. El paciente ingresa su **CURP** → el sistema valida el formato y, si está conectado a RENAPO, confirma que existe.
2. Si es un sistema institucional (IMSS/ISSSTE), se valida que ya exista un **registro médico previo** en la unidad asignada; si no existe, primero se debe dar de alta como derechohabiente.
3. El paciente crea credenciales de acceso (correo + contraseña, o acceso vía código SMS).
4. Se genera su **expediente clínico electrónico**, obligatorio según la norma **NOM-004-SSA3-2012**, que regula el contenido mínimo del expediente (datos demográficos, alergias, antecedentes, consultas).
5. Se firma un **aviso de privacidad** conforme a la LFPDPPP (Ley Federal de Protección de Datos Personales en Posesión de los Particulares), ya que se manejan datos sensibles de salud.

### Particularidad en sistemas públicos
En plataformas como Citas ISSSTE o ISSEMyM, el registro de citas por internet solo está disponible para quien **ya tiene un registro previo en la Unidad Médica** correspondiente; el sistema web no da de alta pacientes nuevos, solo agenda citas para quienes ya están en la base de datos institucional.

---

## 3. Registro de Médicos

Este es el rol con más pasos de validación, porque el sistema debe **verificar que la persona realmente es un profesional de la salud certificado**, para evitar intrusión laboral (personas ejerciendo medicina sin título).

### Datos y documentos requeridos
- CURP
- Nombre completo
- Correo electrónico institucional o personal
- **Cédula profesional** (número de cédula emitido por la SEP)
- Especialidad médica (si aplica, cédula de especialidad adicional)
- Identificación oficial vigente (INE)

### Flujo típico de validación
1. El médico ingresa su CURP y cédula profesional.
2. El sistema consulta el **Registro Nacional de Profesionistas de la SEP** para confirmar que la cédula existe, corresponde a esa persona y a la carrera declarada (medicina, odontología, enfermería, etc.).
3. Verificación de identidad adicional:
   - Captura de INE.
   - Validación por SMS al celular.
   
4. Una vez validado, se asigna el rol de **médico** con permisos para:
   - Ver y editar expedientes clínicos de sus pacientes.
   - Generar recetas (algunos sistemas exigen firma digital para que la receta sea válida legalmente).
   - Emitir órdenes de laboratorio/imagenología.
5. El sistema genera un **carnet digital / credencial verificable**, para que el paciente pueda confirmar que el médico está certificado.

### Por qué es tan estricto
Existe una problemática real de "intrusismo médico" en México (personas sin cédula ejerciendo medicina), por lo que plataformas serias exigen validación cruzada con la SEP antes de activar la cuenta del médico, no solo con un formulario de autodeclaración.

---

## 4. Registro de Recepcionistas / Personal administrativo

Este rol es interno y **no requiere cédula profesional**, pero sí control de acceso, porque maneja datos sensibles de pacientes y la agenda de los médicos.

### Datos requeridos
- Nombre completo
- CURP (para historial laboral, no para validación profesional)
- Correo institucional
- Número de empleado (si es una institución)
- Turno / unidad médica asignada

### Flujo típico
1. **El alta no la hace el recepcionista mismo**, sino un administrador del sistema (a diferencia del paciente o el médico, que pueden autoregistrarse). Esto es una diferencia clave de diseño.
2. El administrador crea la cuenta y asigna:
   - Rol: recepcionista.
   - Unidad médica / consultorio.
   - Permisos limitados: agendar/cancelar citas, ver datos de contacto del paciente, **sin acceso al expediente clínico completo** (esto es importante para cumplir con la NOM-004, que restringe quién puede ver el historial clínico).
3. El recepcionista recibe una invitación por correo para establecer su contraseña y activar la cuenta.
4. Puede quedar sujeto a autenticación de dos factores si el sistema maneja datos de salud (buena práctica, aunque no siempre obligatoria).

---

## 5. Comparación general de los tres roles

| Aspecto | Paciente | Médico | Recepcionista |
|---|---|---|---|
| ¿Quién lo da de alta? | Se autoregistra | Se autoregistra (con validación) | Lo da de alta un administrador |
| Documento clave | CURP | CURP + Cédula profesional | CURP (solo referencia) |
| Validación externa | RENAPO (opcional) | SEP (obligatoria) | Ninguna, solo interna |
| Acceso al expediente clínico | Solo el propio | Completo (de sus pacientes) | Nulo o muy limitado |
| Puede agendar citas | Sí (las propias) | Ve su agenda | Sí (de cualquier paciente/médico) |
| Puede emitir recetas | No | Sí | No |

---

## 6. Aplicación a un sistema propio (arquitectura sugerida)

Si esto es para tu propio proyecto de gestión de citas médicas (Spring Boot + React), la lógica anterior se traduce en un modelo de **roles y permisos (RBAC)**:

```
Usuario (tabla base)
 ├── id, curp, email, password_hash, rol, estado_cuenta

Paciente extends Usuario
 ├── fecha_nacimiento, nss (opcional), contacto_emergencia
 ├── expediente_clinico (relación 1:1)

Medico extends Usuario
 ├── cedula_profesional, especialidad, estado_validacion
 ├── validado_por (referencia a proceso de verificación)

Recepcionista extends Usuario
 ├── unidad_asignada, creado_por_admin_id
```

Puntos de diseño recomendados:
- **Paciente y médico**: registro público con verificación (paciente: solo formato de CURP; médico: validación real contra cédula, aunque sea simulada/mock si no tienes acceso a la API de la SEP).
- **Recepcionista**: nunca se autoregistra; solo un usuario con rol "admin" puede crear estas cuentas.
- Aplica el principio de **mínimo privilegio**: la recepcionista no debe poder leer el expediente clínico completo, solo datos de agenda y contacto.
- Guarda un **log de auditoría** de quién accede a qué expediente y cuándo (requisito de buenas prácticas en salud, aunque no siempre es exigido legalmente para proyectos académicos).

---

## 7. Referencias normativas relevantes

- **NOM-004-SSA3-2012**: Regula el expediente clínico (qué debe contener, quién puede acceder).
- **LFPDPPP**: Ley Federal de Protección de Datos Personales en Posesión de los Particulares (aplica porque los datos de salud son datos sensibles).
- **Registro Nacional de Profesionistas (SEP)**: fuente oficial para validar cédulas profesionales.
- **RENAPO**: Registro Nacional de Población, fuente oficial para validar CURP.
