## SistemaHorarios (UNAP) — Documentación completa

Este repositorio contiene una aplicación para gestionar horarios académicos. Backend en Laravel (API) protegido por Sanctum. Frontend con React dentro de `resources/js` y construcción con Vite.

Contenido de esta documentación
- Requisitos y versiones
- Instalación y configuración (entorno local, PowerShell)
- Migraciones y tablas importantes (Spatie Permissions)
- Seeders (opcional)
- Ejecutar la aplicación (backend y frontend)
- Endpoints principales (API)
- Estructura del proyecto y archivos relevantes
- Desarrollo frontend (build / dev)
- Pruebas y verificación
- Troubleshooting comunes

---

Requisitos

- PHP 8.1+ (8.2 recomendado)
- Composer
- Node 16+ / 18+ y npm
- MySQL / MariaDB (u otro motor soportado por Laravel)

Instalación (rápida)

1. Clona el repositorio y sitúate en la raíz del proyecto.

2. Instala dependencias PHP y JS:

```powershell
composer install
npm install
```

3. Copia y configura `.env` (Windows: copia manualmente si es necesario):

```powershell
Copy-Item .env.example .env
```

Edita `.env` y ajusta los valores de conexión a la base de datos y URL:

```
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_basedatos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000,localhost:8000
SESSION_DOMAIN=127.0.0.1
```

4. Genera la clave de aplicación y ejecuta migraciones:

```powershell
php artisan key:generate
php artisan migrate
```

---

Nota sobre Spatie Permissions

- El proyecto incluye la migración de las tablas necesarias para el paquete Spatie (roles, permissions y pivotes) en `database/migrations`.
- Después de migrar, las tablas `roles`, `permissions`, `model_has_roles`, `model_has_permissions` y `role_has_permissions` estarán disponibles.

Si usas permisos/roles dinámicos o actualizas permisos, limpia la caché de permisos:

```powershell
php artisan permission:cache-reset
```

---

Seeders (opcional)

- Si necesitas poblar permisos y roles iniciales, crea y ejecuta seeders idempotentes. Ejemplo:

```powershell
php artisan db:seed --class=PermissionSeeder
```

- En este proyecto hemos detectado que ya dispones de datos en la BD; por eso el uso de seeders es opcional.

---

Ejecutar la aplicación

- Backend (Laravel):

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

- Frontend (Vite):

```powershell
npm run dev
```

Abre `http://127.0.0.1:8000` en el navegador.

---

API — endpoints principales

Autenticación (Sanctum)

- POST `/api/register` { name, email, password, password_confirmation }
- POST `/api/login` { email, password }
- POST `/api/logout`
- GET `/api/user`

Recursos principales (ejemplos)

- Cursos: GET/POST/PUT/DELETE `/api/cursos` (varias rutas con filtros por `ciclo`)
- Horarios: rutas bajo `/api/horarios` (creación, grid, asignaciones, publicar, validar conflictos)
- Profesores: `/api/profesores` (+ disponibilidad y horarios)
- Salones: `/api/salones` (+ `/api/salones-disponibles`)
- Usuarios: `/api/usuarios` (CRUD)

Roles y permisos (API implementada)

- GET `/api/roles` — lista roles (incluye conteo usuarios y permisos asignados)
- GET `/api/roles/permissions` — lista permisos disponibles
- POST `/api/roles` — crear rol (body: { name, permisos: [] })
- PUT `/api/roles/{id}` — actualizar rol
- DELETE `/api/roles/{id}` — eliminar rol (controla usuarios asignados)

---

Estructura del proyecto (puntos relevantes)

- Backend

  - `app/Http/Controllers/` — controladores: `AuthController`, `UserController`, `HorarioController`, `RoleController` (nuevo)
  - `app/Models/` — modelos (incluye `User` que ya utiliza `HasRoles` de Spatie)
  - `routes/api.php` — definición de rutas API (auth + rutas protegidas en `auth:sanctum`)
  - `config/permission.php` — configuración de Spatie Permission

- Frontend

  - `resources/js/` — React app
    - `services/api.js` — wrapper Axios para llamadas API (incluye `RolesServicio`)
    - `components/seguridad/Roles.jsx` — UI para gestionar roles y permisos (usa la API)
    - `components/admin/*` y `components/seguridad/*` — otras vistas

---

Notas de desarrollo

- En el frontend, la autenticación por Sanctum usa la cookie CSRF (`/sanctum/csrf-cookie`) antes de realizar login.
- Las vistas principales usan TailwindCSS (clases como `text-gray-800`).
- Para ver las rutas registradas:

```powershell
php artisan route:list
```

---

Pruebas y validación

- Ejecutar tests automáticos (si existen):

```powershell
./vendor/bin/phpunit
```

Otras tareas útiles

- Regenerar autoload después de crear seeders o clases nuevas:

```powershell
composer dump-autoload
```

---

Troubleshooting (problemas comunes)

- 401 / 419 en login: revisa `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, usa la misma URL que pones en `.env`.
- Errores 500: revisa `storage/logs/laravel.log` y la conexión DB en `.env`.
- Problemas CORS: editar `config/cors.php` y ejecutar `php artisan config:clear`.
- Permisos no actualizados: ejecutar `php artisan permission:cache-reset`.

---

Cómo contribuir

- Fork / branch / PR. Sigue las convenciones PSR-12 para PHP y ESLint/Prettier si aplican para JS.
- Añade pruebas cuando cambies lógica crítica (controladores, servicios, autorización).

Contacto

- Si tienes dudas sobre cómo ejecutar algo en tu entorno Windows (PowerShell), o quieres que cree seeders/tests/manuales, dímelo y lo implemento.

Licencia

- Proyecto educativo. Basado en Laravel (MIT).
## SistemaHorarios (UNAP) – Laravel + React (Vite)

Aplicación para gestionar horarios académicos. Backend en Laravel con autenticación por Sanctum; frontend en React (Vite) dentro de `resources/js`.

### Funcionalidades clave
- Autenticación con email institucional obligatorio `@unap.edu.pe` para login y registro.
- API de auth (`/api/login`, `/api/register`, `/api/logout`, `/api/user`) protegida con Sanctum.
- SPA con `Dashboard` tras autenticación y pantallas de Login/Registro.
- Sistema de alertas automáticas (success/error/info) con auto-cierre en 5 segundos.
- Redirección automática al Dashboard tras login/registro exitoso.
- Validación de dominio institucional en backend y frontend.
- Manejo de errores mejorado con mensajes específicos.
- Ejemplo de endpoint protegido: `/api/cursos`.

### Requisitos
- PHP 8.2+, Composer
- Node 18+, npm
- MySQL/MariaDB (o el motor que configures)

### Configuración rápida
1) Instala dependencias
```bash
composer install
npm install
```

2) Copia el archivo de entorno y configura la base de datos
```bash
cp .env.example .env  # en Windows copia manualmente si no existe
```
Edita `.env`:
```
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_horarios
DB_USERNAME=root
DB_PASSWORD=tu_password

SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000,localhost:8000
SESSION_DOMAIN=127.0.0.1
```

3) Genera la clave de app y ejecuta migraciones
```bash
php artisan key:generate
php artisan migrate
```

4) Levanta el servidor y el frontend
```bash
php artisan serve
npm run dev
```
Abre `http://127.0.0.1:8000`.

### Endpoints de autenticación
- POST `/api/register` { name, email, password, password_confirmation }
- POST `/api/login` { email, password }
- POST `/api/logout`
- GET `/api/user`

**Respuestas del backend:**
- **Registro exitoso**: `{ success: true, message: "...", user: {...}, token: "..." }`
- **Login exitoso**: `{ success: true, message: "...", user: {...}, token: "..." }`
- **Usuario autenticado**: `{ success: true, user: {...} }`

Notas:
- El email debe terminar en `@unap.edu.pe` (validación aplicada en backend y frontend).
- Se usa cookie de sesión de Sanctum; el frontend llama a `/sanctum/csrf-cookie` automáticamente.
- Las alertas se muestran automáticamente y se cierran en 5 segundos.

### Estructura relevante
- **Backend**
  - `routes/api.php`: rutas de auth y protegidas
  - `app/Http/Controllers/AuthController.php`: login/register/logout/user con validación de dominio
  - `config/sanctum.php`, `config/cors.php`: CORS/Sanctum
- **Frontend**
  - `resources/js/contexts/AuthContext.jsx`: manejo de sesión, alertas y estado global
  - `resources/js/components/auth/*`: Login/Registro/AuthPage con validación de dominio
  - `resources/js/components/Dashboard.jsx`: dashboard principal
  - `resources/js/app.jsx`: arranque de la SPA con sistema de alertas
- **Flujo de autenticación**
  - Registro → Backend valida dominio → Usuario creado → Alerta verde → Dashboard
  - Login → Backend valida credenciales → Usuario autenticado → Alerta verde → Dashboard
  - Logout → Sesión cerrada → Alerta azul → LoginForm

### Solución de problemas
- **401/419 al loguear**: confirma `SANCTUM_STATEFUL_DOMAINS` y `SESSION_DOMAIN`; accede por `127.0.0.1`.
- **500 en login/registro**: revisa `storage/logs/laravel.log` y conexión a BD `.env`.
- **CORS**: revisa `config/cors.php` y ejecuta `php artisan config:clear` tras cambios.
- **No redirección tras login/registro**: verifica que el backend devuelva `success: true` y que `fetchUser()` funcione.
- **Alertas no aparecen**: confirma que el contexto esté actualizado y que las alertas se muestren en `app.jsx`.
- **"Error en el login/registro"**: verifica que el backend devuelva la estructura `{ success: true, user: {...} }`.
- **Usuario se crea pero muestra error**: el backend está funcionando, pero el frontend no maneja bien la respuesta.
- **Validación de dominio**: confirma que tanto backend como frontend validen `@unap.edu.pe`.

### Licencia
Proyecto educativo. Basado en Laravel (MIT).
