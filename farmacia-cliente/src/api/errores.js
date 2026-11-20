/**
 * Traduce cualquier error de Axios a un objeto uniforme que las vistas
 * pueden mostrar: { status, titulo, mensaje }.
 * Valida explícitamente 404 (no encontrado) y 500 (error del servidor).
 */
export class ErrorApi extends Error {
  constructor(status, titulo, mensaje) {
    super(mensaje);
    this.status = status;
    this.titulo = titulo;
  }
}

export function normalizarError(error) {
  if (error instanceof ErrorApi) return error;

  // El servidor respondió con un código de error
  if (error?.response) {
    const { status, data } = error.response;
    const mensajeApi = data?.message;

    if (status === 404) {
      return new ErrorApi(404, 'No encontrado (404)', mensajeApi || 'El recurso que buscas no existe o ya no está disponible.');
    }
    if (status >= 500) {
      return new ErrorApi(status, `Error del servidor (${status})`, 'El servidor tuvo un problema al procesar la solicitud. Intenta de nuevo en unos minutos.');
    }
    return new ErrorApi(status, `Error ${status}`, mensajeApi || 'La solicitud no pudo completarse.');
  }

  // La petición salió pero no hubo respuesta (servidor apagado, CORS, sin red)
  if (error?.request) {
    if (error.code === 'ECONNABORTED') {
      return new ErrorApi(0, 'Tiempo de espera agotado', 'El servidor tardó demasiado en responder.');
    }
    return new ErrorApi(0, 'Sin conexión con el servidor', 'No se pudo contactar a la API. Verifica que Laravel esté corriendo (php artisan serve).');
  }

  return new ErrorApi(0, 'Error inesperado', error?.message || 'Ocurrió un error desconocido.');
}
