import { API_ORIGIN } from './http';

/**
 * Mapeo de imágenes de Laravel.
 *
 * La API devuelve:
 *   imagen      -> ruta guardada en la BD   (productos/producto_x.jpg | pictures/Productos/x.webp)
 *   imagen_url  -> URL pública generada con APP_URL (p. ej. http://localhost/storage/productos/...)
 *
 * Si APP_URL no coincide con el puerto real del backend (php artisan serve -> :8000)
 * la URL quedaría rota, así que se reescribe sobre el origen real de la API.
 * Si no se puede resolver nada, se usa la imagen placeholder.
 */
export const PLACEHOLDER = '/placeholder.svg';

const HOSTS_LOCALES = ['localhost', '127.0.0.1', '0.0.0.0'];

export function normalizarUrlImagen(url) {
  if (!url || typeof url !== 'string') return null;

  // Ruta absoluta http(s)
  if (/^https?:\/\//i.test(url)) {
    try {
      const u = new URL(url);
      // Solo se reubican URLs del backend local; las externas (CDN) se respetan
      if (HOSTS_LOCALES.includes(u.hostname)) {
        return `${API_ORIGIN}${u.pathname}${u.search}`;
      }
      return url;
    } catch {
      return null;
    }
  }

  // Ruta relativa: "storage/..." o "/storage/..." o asset público ("pictures/...")
  return `${API_ORIGIN}/${url.replace(/^\/+/, '')}`;
}

/** Devuelve la mejor URL disponible para un producto, o null. */
export function resolverImagen(producto) {
  if (!producto) return null;

  if (producto.imagen_url) return normalizarUrlImagen(producto.imagen_url);

  const ruta = producto.imagen;
  if (!ruta) return null;
  if (/^https?:\/\//i.test(ruta)) return normalizarUrlImagen(ruta);

  // Imágenes subidas con el disco "public" de Laravel -> /storage/<ruta>
  const limpia = ruta.replace(/^\/+/, '');
  if (limpia.startsWith('storage/') || limpia.startsWith('pictures/') || limpia.startsWith('uploads/')) {
    return `${API_ORIGIN}/${limpia}`;
  }
  return `${API_ORIGIN}/storage/${limpia}`;
}

/** Lista de imágenes para la galería del detalle (sin duplicados). */
export function resolverGaleria(producto) {
  const urls = [
    resolverImagen(producto),
    ...(Array.isArray(producto?.galeria) ? producto.galeria.map(normalizarUrlImagen) : []),
  ].filter(Boolean);
  return [...new Set(urls)];
}
