# Farmacia API (Laravel 12)

Copia completa del proyecto Farmacia migrada de Laravel 10 a Laravel 12.

## Instalación

Requiere PHP 8.2 o superior, Composer y Node.js.

```bash
composer install
npm install
npm run build
php artisan storage:link
php artisan migrate
php artisan serve
```

La base de datos configurada en `.env` es `farmacia_api` (créala en phpMyAdmin antes de migrar).

## Cambios por la migración a Laravel 12

- `app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php` y los providers de rutas/eventos/broadcast ya no existen; su configuración vive en `bootstrap/app.php`.
- Las redirecciones de `Authenticate` y `RedirectIfAuthenticated` (login de doctor / admin) se configuran con `redirectGuestsTo` y `redirectUsersTo` en `bootstrap/app.php`.
- Las rutas de `routes/api.php` se cargan con el prefijo `/api` desde `bootstrap/app.php`. Sanctum 4 sigue disponible para `auth:sanctum`.
- Las migraciones base de Laravel 10 (users, password_reset_tokens, failed_jobs) se sustituyeron por las de Laravel 12 (users + sessions, cache, jobs).

## Acceso administrativo

El panel utiliza el guardia de sesión `admin`, independiente del guardia `web`. Para instalar la tabla de administradores:

```bash
php artisan migrate
```

Crea la primera cuenta desde Tinker; el modelo almacena la contraseña como hash:

```bash
php artisan tinker
```

```php
use App\Models\Administrador;
use Illuminate\Support\Facades\Hash;

Administrador::create([
    'nombre_completo' => 'Nombre del administrador',
    'nombre_usuario' => 'admin',
    'password' => Hash::make('reemplaza-por-una-contrasena-segura'),
    'activo' => true,
]);
```

Abre `/admin/login` para iniciar sesión. El catálogo de productos y sus detalles son públicos; el panel y las operaciones de administración requieren un administrador activo. Para cerrar sesión, utiliza el botón del panel.

En producción, configura HTTPS y no guardes contraseñas reales ni credenciales en el repositorio.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## API v1 — Actividad 4.2 (sin autenticación)

Endpoints públicos bajo `/api/v1` (no usan Sanctum, roles ni permisos). Todas las respuestas tienen la forma:

```json
{ "success": true, "message": "Producto consultado correctamente", "data": { }, "errors": null }
```

| Recurso | Endpoints |
|---|---|
| Productos (con imagen) | `GET/POST /api/v1/productos` · `GET/PUT/PATCH/DELETE /api/v1/productos/{id}` · `POST/DELETE /api/v1/productos/{id}/imagen` |
| Especialidades | `GET/POST /api/v1/especialidades` · `GET/PUT/PATCH/DELETE /api/v1/especialidades/{id}` · `GET /api/v1/especialidades/{id}/medicos` |
| Servicios médicos | `GET/POST /api/v1/servicios-medicos` · `GET/PUT/PATCH/DELETE /api/v1/servicios-medicos/{id}` |

Archivos involucrados:

- `app/Http/Controllers/Api/V1/*` — controladores con `Validator::make()` y `find()`.
- `app/Support/RespuestaApi.php` — formato estándar de éxito y error.
- `app/Http/Middleware/PrepararSolicitudApi.php` — 400 (JSON mal formado), 413 (archivo demasiado grande) y 415 (Content-Type no admitido).
- `bootstrap/app.php` — convierte cualquier excepción (404, 405, 413, 422, 500…) al formato estándar sin exponer detalles internos.

Imágenes: se guardan en `storage/app/public/productos` con nombre `producto_<uuid>.<ext>`; la BD guarda la ruta (`imagen`) y la API devuelve además la URL pública (`imagen_url`). Requiere `php artisan storage:link` y que `APP_URL` en `.env` sea la URL real del proyecto.

Para enviar una imagen al actualizar se usa `POST` con el campo `_method=PATCH` (PHP no procesa archivos en `PUT`/`PATCH` reales), o el endpoint `POST /api/v1/productos/{id}/imagen`.

Colección de pruebas: `FarmaciaAPI-v1.postman_collection.json`.
