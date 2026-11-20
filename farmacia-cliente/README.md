# Farmacia Vital · Cliente público (Actividad 5.3)

Aplicación **React + Vite** independiente para el **cliente final / público general**.
Consume los endpoints públicos del Área del Cliente de `Farmacia API` (Laravel) con **Axios**,
**sin tokens ni inicio de sesión**.

## Puesta en marcha

1. Backend (carpeta `Farmacia API`):
   ```bash
   php artisan storage:link   # solo la primera vez
   php artisan serve          # http://localhost:8000
   ```
2. Cliente (esta carpeta):
   ```bash
   npm install
   npm run dev                # http://localhost:5173
   ```

La URL base se configura en `.env`:
```
VITE_API_URL=http://localhost:8000/api/v1/client/
```

## Endpoints consumidos (todos GET, sin `Authorization: Bearer`)

| Método del servicio            | Endpoint                         | Uso                                   |
|--------------------------------|----------------------------------|---------------------------------------|
| `getCatalogoPublico()`         | `/api/v1/client/catalogo`        | Cuadrícula del catálogo               |
| `getDetalleRecurso(id)`        | `/api/v1/client/catalogo/{id}`   | Detalle, galería y relacionados       |
| `getInformacionSecciones()`    | `/api/v1/client/informacion`     | Contadores, categorías, avisos, servicios y especialidades |

En `routes/api.php` las tres rutas están en el bloque *Endpoints Públicos* del prefijo `client`,
**fuera** del grupo `Route::middleware('auth:sanctum')`.

## Estructura

```
src/
├─ api/
│  ├─ http.js                     Instancia de Axios (baseURL desde .env, sin token)
│  ├─ clientePublicoService.js    getCatalogoPublico / getDetalleRecurso / getInformacionSecciones
│  ├─ imagenes.js                 Mapeo de rutas de imagen de Laravel -> URL real del backend
│  └─ errores.js                  Normaliza 404 / 500 / sin conexión
├─ hooks/usePeticion.js           Estado { datos, cargando, error, recargar }
├─ components/                    Layout, ProductCard, ImagenSegura, Skeletons, Spinner, Estados
├─ views/
│  ├─ HomeView.jsx                Landing con datos dinámicos
│  ├─ CatalogView.jsx             Catálogo general (grid, búsqueda, filtros, orden)
│  ├─ ProductDetailView.jsx       Detalle por ID con galería y visor ampliado
│  ├─ AboutView.jsx               Información dinámica (cifras, categorías, servicios, especialidades)
│  └─ NotFoundView.jsx
└─ utils/formato.js
```

## Requisitos de la actividad cubiertos

- **Proyecto independiente** con su propio `package.json`, fuera del backend.
- **Servicio HTTP** con los tres métodos solicitados y validación de estado 200.
- **Mapeo de imágenes**: la API arma `imagen_url` con `APP_URL` (`http://localhost`), que no
  coincide con `php artisan serve` (`:8000`). `imagenes.js` reubica esas URLs sobre el origen real
  de la API y reconstruye rutas relativas (`/storage/...`, `pictures/...`).
- **Estados de carga**: skeletons en tarjetas, categorías, cifras y detalle; spinner en Nosotros;
  shimmer en cada imagen mientras descarga.
- **Errores HTTP**: 404 (producto inexistente o ID inválido), 500+ (error del servidor) y
  sin conexión, con botón *Reintentar*.
- **Contingencia**: "No hay productos disponibles en este momento" si el catálogo viene vacío.
- **Imagen placeholder** (`public/placeholder.svg`) cuando la URL falla o no existe.
