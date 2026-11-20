import http from './http';
import { ErrorApi, normalizarError } from './errores';
import { resolverGaleria, resolverImagen } from './imagenes';

/**
 * Servicio HTTP del cliente público (Actividad 5.3).
 * Todas las peticiones son GET abiertas, sin token.
 */

// Estandariza un producto de la API para las vistas
function mapearProducto(p) {
  const precio = Number(p.precio ?? 0);
  const stock = Number(p.stock ?? 0);
  return {
    ...p,
    precio,
    stock,
    disponible: stock > 0,
    imagenUrl: resolverImagen(p),
    galeria: resolverGaleria(p),
  };
}

// Valida que la respuesta sea 200 y extrae "data"
function extraerData(respuesta) {
  if (respuesta.status !== 200) {
    throw new ErrorApi(respuesta.status, `Respuesta inesperada (${respuesta.status})`, 'El servidor respondió con un estado no esperado.');
  }
  return respuesta.data?.data;
}

/** GET /catalogo -> lista de productos activos (no eliminados). */
export async function getCatalogoPublico() {
  try {
    const respuesta = await http.get('catalogo');
    const data = extraerData(respuesta);
    return Array.isArray(data) ? data.map(mapearProducto) : [];
  } catch (error) {
    throw normalizarError(error);
  }
}

/** GET /catalogo/{id} -> detalle, galería y relacionados. */
export async function getDetalleRecurso(id) {
  if (!/^\d+$/.test(String(id))) {
    throw new ErrorApi(404, 'No encontrado (404)', 'El identificador del producto no es válido.');
  }
  try {
    const respuesta = await http.get(`catalogo/${id}`);
    const data = extraerData(respuesta);
    if (!data) throw new ErrorApi(404, 'No encontrado (404)', 'El producto no existe.');
    return {
      producto: mapearProducto(data),
      relacionados: (respuesta.data?.relacionados ?? []).map(mapearProducto),
    };
  } catch (error) {
    throw normalizarError(error);
  }
}

/** GET /informacion -> contadores, categorías, destacados, avisos, servicios y especialidades. */
export async function getInformacionSecciones() {
  try {
    const respuesta = await http.get('informacion');
    const data = extraerData(respuesta) ?? {};
    return {
      resumen: data.resumen ?? {},
      categorias: (data.categorias ?? []).map((c) => ({ ...c, imagenUrl: resolverImagen(c) })),
      destacados: (data.destacados ?? []).map(mapearProducto),
      avisos: data.avisos ?? [],
      servicios: data.servicios ?? [],
      especialidades: data.especialidades ?? [],
      generadoEn: data.generado_en ?? null,
    };
  } catch (error) {
    throw normalizarError(error);
  }
}
