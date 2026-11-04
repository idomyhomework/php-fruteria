# PLAN.md — Frutería Online

Aplicación web dinámica para una frutería: catálogo público de productos y gestión de inventario restringida a administradores. Stack: HTML, PHP, MariaDB y Apache sobre Ubuntu en AWS.

---

## 1. Objetivo

Desplegar una aplicación web que:

- Muestre el catálogo de frutas a cualquier visitante (sin login).
- Permita a los **administradores** añadir productos, modificar la cantidad y eliminarlos.
- Permita a los usuarios registrarse e iniciar sesión.
- Quede accesible públicamente en una instancia EC2 de AWS con Ubuntu.

---

## 2. Requisitos funcionales

### 2.1 Usuarios y autenticación

| ID    | Requisito |
|-------|-----------|
| RF-01 | Cualquier visitante puede registrarse con nombre de usuario, email y contraseña. |
| RF-02 | El registro crea siempre un usuario con rol `usuario`. |
| RF-03 | Los usuarios pueden iniciar y cerrar sesión. |
| RF-04 | Existe un rol `admin`, creado mediante seed en la base de datos (no desde la web). |
| RF-05 | Un usuario `usuario` puede ver y buscar el catálogo, sin acciones de modificación. |
| RF-06 | Un usuario `admin` puede además añadir, modificar la cantidad y eliminar productos. |

### 2.2 Página de inicio

| ID    | Requisito |
|-------|-----------|
| RF-07 | Muestra el título "Frutería Online". |
| RF-08 | Enlace al catálogo. |
| RF-09 | Enlace al formulario de inventario (visible y accesible solo para admin). |
| RF-10 | Enlaces de registro e inicio de sesión si el visitante no está logueado; cerrar sesión si lo está. |

### 2.3 Catálogo

| ID    | Requisito |
|-------|-----------|
| RF-11 | Tabla con todas las frutas: nombre, precio, cantidad e imagen. |
| RF-12 | Búsqueda por nombre (filtro sobre la tabla). |
| RF-13 | Admin: campo de texto + botón para eliminar un producto por nombre o id. |
| RF-14 | Admin: cambiar la cantidad de cada fruta (campo numérico + guardar). |
| RF-15 | La base de datos incluye por defecto la fruta **Fruta de la pasión**. |

### 2.4 Inventario (solo admin)

| ID    | Requisito |
|-------|-----------|
| RF-16 | Formulario con: nombre, precio, cantidad y URL de imagen, más botón Guardar. |
| RF-17 | Al enviar, se inserta el producto en la base de datos. |
| RF-18 | Si un usuario no admin accede a la URL, se le redirige al login o se muestra 403. |

---

## 3. Requisitos no funcionales (seguridad)

| ID     | Requisito |
|--------|-----------|
| RNF-01 | Contraseñas almacenadas con `password_hash()` y verificadas con `password_verify()`. |
| RNF-02 | Sesiones con `session_start()` y regeneración del id tras login (`session_regenerate_id()`). |
| RNF-03 | Tokens CSRF en todos los formularios que modifican datos. |
| RNF-04 | Consultas con PDO y sentencias preparadas (prevención de SQL injection). |
| RNF-05 | Escape de salida con `htmlspecialchars()` (prevención de XSS). |
| RNF-06 | Comprobación de rol admin en el servidor en cada acción de escritura, no solo ocultando botones. |
| RNF-07 | Credenciales de BD fuera del código versionado (archivo de configuración no público). |

---

## 4. Modelo de datos

```sql
CREATE DATABASE fruteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE usuarios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  nombre_usuario VARCHAR(50)  NOT NULL UNIQUE,
  email          VARCHAR(120) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  rol            ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE frutas (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  nombre   VARCHAR(100) NOT NULL,
  precio   DECIMAL(8,2) NOT NULL CHECK (precio >= 0),
  cantidad INT          NOT NULL DEFAULT 0 CHECK (cantidad >= 0),
  imagen   VARCHAR(500)
);

-- Datos iniciales
INSERT INTO frutas (nombre, precio, cantidad, imagen)
VALUES ('Fruta de la pasión', 3.50, 10, '');

-- Admin inicial (el hash se genera con password_hash() y se inserta por seed)
INSERT INTO usuarios (nombre_usuario, email, password_hash, rol)
VALUES ('admin', 'admin@fruteria.local', '<HASH_GENERADO_CON_PHP>', 'admin');
```

---

## 5. Estructura de archivos

```
fruteria/
├── index.php              # Página de inicio
├── catalogo.php           # Tabla de frutas, búsqueda, eliminar y cambiar cantidad (admin)
├── inventario.php         # Formulario de nuevo producto (admin)
├── registro.php           # Formulario de registro
├── login.php              # Formulario de inicio de sesión
├── logout.php             # Cierra la sesión
├── accion_fruta.php       # Procesa eliminar / cambiar cantidad / insertar
├── includes/
│   ├── config.php         # Conexión PDO (credenciales fuera del repo)
│   ├── auth.php           # Funciones: requerir sesión, requerir admin
│   ├── csrf.php           # Generar y validar token CSRF
│   └── cabecera.php       # Cabecera HTML y navegación común
├── assets/
│   ├── css/estilo.css
│   └── js/app.js          # JS mínimo (p. ej. confirmar antes de eliminar)
└── sql/
    └── schema.sql         # Tablas, fruta por defecto y admin
```

---

## 6. Fases de trabajo

### Fase 1 — Entorno
1. Instancia EC2 con Ubuntu. Abrir puertos 22 y 80 en el grupo de seguridad.
2. Instalar Apache, PHP (con `php-mysql`) y MariaDB.
3. Crear la base de datos `fruteria` y un usuario de BD con permisos mínimos.
4. Configurar un VirtualHost de Apache apuntando a `/var/www/fruteria`.

### Fase 2 — Base de datos
5. Escribir `sql/schema.sql` con tablas, fruta por defecto y admin.
6. Generar el hash del admin con `php -r "echo password_hash('...', PASSWORD_DEFAULT);"` e insertarlo.

### Fase 3 — Núcleo
7. `includes/config.php` con conexión PDO y manejo de errores.
8. `includes/csrf.php` y `includes/auth.php`.
9. `cabecera.php` con navegación que cambia según sesión y rol.

### Fase 4 — Autenticación
10. `registro.php`: validar datos, comprobar duplicados, guardar con `password_hash()`, rol `usuario`.
11. `login.php`: verificar con `password_verify()`, `session_regenerate_id()`, guardar id y rol en sesión.
12. `logout.php`: destruir la sesión.

### Fase 5 — Catálogo e inventario
13. `catalogo.php`: listar frutas en tabla y buscar por nombre.
14. Controles de admin en el catálogo: eliminar por texto/botón y cambiar cantidad.
15. `inventario.php`: formulario con nombre, precio, cantidad, URL de imagen y token CSRF.
16. `accion_fruta.php`: procesar las tres acciones, validar admin y CSRF, usar sentencias preparadas.

### Fase 6 — Pulido y despliegue
17. Estilos básicos y diseño responsive.
18. JS mínimo para confirmar eliminaciones (opcional).
19. Pruebas manuales: registro, login, acceso denegado a no admin, CRUD como admin, inyección SQL y XSS básicos.
20. Copiar el proyecto a `/var/www/fruteria`, ajustar permisos y comprobar la URL pública.
21. Subir a Aules la URL de la página de inicio.

---

## 7. Criterios de aceptación

- [ ] El catálogo se ve sin iniciar sesión.
- [ ] Un registro nuevo tiene rol `usuario` y no puede acceder a inventario ni a acciones de admin.
- [ ] El admin puede insertar, modificar cantidad y eliminar productos.
- [ ] La fruta de la pasión aparece por defecto.
- [ ] Las contraseñas no se guardan en claro.
- [ ] Los formularios rechazan peticiones sin token CSRF válido.
- [ ] La aplicación es accesible por la IP o dominio público de la instancia AWS.

---

## 8. Entrega

- URL pública de la página de inicio, subida a Aules.
- Repositorio o carpeta con el código, incluyendo `PLAN.md` y `sql/schema.sql`.