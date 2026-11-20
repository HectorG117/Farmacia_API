import { Link } from 'react-router-dom';
import usePeticion from '../hooks/usePeticion';
import { getInformacionSecciones } from '../api/clientePublicoService';
import Spinner from '../components/Spinner';
import { EstadoError, EstadoVacio } from '../components/Estados';
import { formatoPrecio } from '../utils/formato';

/** Vista informativa: datos dinámicos de la API (contadores, categorías, servicios, especialidades). */
export default function AboutView() {
  const { datos, cargando, error, recargar } = usePeticion(getInformacionSecciones);

  return (
    <div className="contenedor seccion">
      <header className="pagina-cabecera">
        <span className="eyebrow">Nosotros</span>
        <h1>Una farmacia con consultorio, pensada para ti</h1>
        <p className="lead">
          En Farmacia Vital combinamos un surtido completo de productos con atención médica de especialistas.
          Todo lo que ves aquí se actualiza directamente desde nuestro sistema.
        </p>
      </header>

      {cargando && <Spinner texto="Consultando información de la farmacia…" />}
      {error && <EstadoError error={error} onReintentar={recargar} />}

      {!cargando && !error && (
        <>
          <section className="cifras">
            <Cifra valor={datos.resumen.total_productos} texto="productos registrados" />
            <Cifra valor={datos.resumen.unidades_en_stock} texto="unidades en inventario" />
            <Cifra valor={datos.resumen.servicios_activos} texto="servicios médicos activos" />
            <Cifra valor={datos.resumen.especialidades} texto="especialidades" />
          </section>

          <section className="seccion">
            <div className="seccion-cabecera"><h2>Disponibilidad por categoría</h2></div>
            {datos.categorias.length === 0 ? (
              <EstadoVacio />
            ) : (
              <ul className="barras">
                {datos.categorias.map((c) => {
                  const pct = c.total ? Math.round((c.disponibles / c.total) * 100) : 0;
                  return (
                    <li key={c.nombre}>
                      <Link to={`/catalogo?categoria=${encodeURIComponent(c.nombre)}`}>{c.nombre}</Link>
                      <div className="barra"><span style={{ width: `${pct}%` }} /></div>
                      <small>{c.disponibles}/{c.total} disponibles</small>
                    </li>
                  );
                })}
              </ul>
            )}
          </section>

          <div className="dos-columnas">
            <section>
              <h2>Servicios médicos</h2>
              {datos.servicios.length === 0 ? (
                <EstadoVacio titulo="Sin servicios activos" mensaje="Pronto anunciaremos nuevos servicios." />
              ) : (
                <ul className="lista-info">
                  {datos.servicios.map((s) => (
                    <li key={s.id}>
                      <div>
                        <strong>{s.nombre}</strong>
                        {s.descripcion && <p>{s.descripcion}</p>}
                      </div>
                      <span className="precio">{formatoPrecio(s.costo)}</span>
                    </li>
                  ))}
                </ul>
              )}
            </section>

            <section>
              <h2>Especialidades</h2>
              {datos.especialidades.length === 0 ? (
                <EstadoVacio titulo="Sin especialidades registradas" mensaje="" />
              ) : (
                <ul className="lista-info">
                  {datos.especialidades.map((e) => (
                    <li key={e.id}>
                      <div>
                        <strong>{e.nombre}</strong>
                        {e.descripcion && <p>{e.descripcion}</p>}
                      </div>
                      <span className="pildora">{e.medicos} médico{e.medicos === 1 ? '' : 's'}</span>
                    </li>
                  ))}
                </ul>
              )}
            </section>
          </div>

          {datos.generadoEn && (
            <p className="nota centrado">Datos actualizados el {new Date(datos.generadoEn).toLocaleString('es-MX')}</p>
          )}
        </>
      )}
    </div>
  );
}

function Cifra({ valor, texto }) {
  return (
    <div className="cifra">
      <span>{valor ?? 0}</span>
      <p>{texto}</p>
    </div>
  );
}
