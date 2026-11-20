import { Link } from 'react-router-dom';

/** Estado de contingencia cuando la API no devuelve registros. */
export function EstadoVacio({
  titulo = 'No hay productos disponibles en este momento',
  mensaje = 'Vuelve más tarde; estamos reabasteciendo nuestro catálogo.',
  children,
}) {
  return (
    <div className="estado estado-vacio">
      <div className="estado-icono">∅</div>
      <h3>{titulo}</h3>
      <p>{mensaje}</p>
      {children}
    </div>
  );
}

/** Estado de error HTTP (404, 500, sin conexión) con opción de reintentar. */
export function EstadoError({ error, onReintentar }) {
  const es404 = error?.status === 404;
  return (
    <div className={`estado estado-error ${es404 ? 'e404' : ''}`}>
      <div className="estado-icono">{es404 ? '404' : error?.status >= 500 ? error.status : '!'}</div>
      <h3>{error?.titulo || 'Algo salió mal'}</h3>
      <p>{error?.message}</p>
      <div className="estado-acciones">
        {!es404 && onReintentar && (
          <button className="btn btn-primario" onClick={onReintentar}>Reintentar</button>
        )}
        <Link className="btn btn-secundario" to="/catalogo">Ir al catálogo</Link>
      </div>
    </div>
  );
}
