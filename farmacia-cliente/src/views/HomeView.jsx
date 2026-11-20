import { Link } from 'react-router-dom';
import usePeticion from '../hooks/usePeticion';
import { getInformacionSecciones } from '../api/clientePublicoService';
import ProductCard from '../components/ProductCard';
import ImagenSegura from '../components/ImagenSegura';
import { SkeletonGrid } from '../components/Skeletons';
import { EstadoError, EstadoVacio } from '../components/Estados';
import { formatoPrecio } from '../utils/formato';

/** Vista Landing: hero + contadores + avisos + categorías + destacados (datos dinámicos). */
export default function HomeView() {
  const { datos, cargando, error, recargar } = usePeticion(getInformacionSecciones);
  const r = datos?.resumen ?? {};

  return (
    <>
      <section className="hero">
        <div className="contenedor hero-interno">
          <div className="hero-texto">
            <span className="eyebrow">Tu farmacia de confianza</span>
            <h1>Cuidar tu salud empieza por <em>encontrar</em> lo que necesitas.</h1>
            <p>Explora medicamentos, vitaminas, material de curación y equipo médico. Precios y existencias en tiempo real, sin registrarte.</p>
            <div className="hero-acciones">
              <Link to="/catalogo" className="btn btn-primario">Ver catálogo</Link>
              <Link to="/nosotros" className="btn btn-fantasma">Conócenos</Link>
            </div>
          </div>

          <div className="hero-stats">
            {cargando ? (
              Array.from({ length: 4 }, (_, i) => <div key={i} className="stat sk" />)
            ) : (
              <>
                <Stat valor={r.total_productos ?? 0} etiqueta="productos en catálogo" />
                <Stat valor={r.disponibles ?? 0} etiqueta="con existencias" />
                <Stat valor={r.total_categorias ?? 0} etiqueta="categorías activas" />
                <Stat valor={r.precio_minimo != null ? formatoPrecio(r.precio_minimo) : '—'} etiqueta="precio desde" />
              </>
            )}
          </div>
        </div>
      </section>

      {error && (
        <div className="contenedor seccion">
          <EstadoError error={error} onReintentar={recargar} />
        </div>
      )}

      {!error && datos?.avisos?.length > 0 && (
        <section className="avisos">
          <div className="contenedor avisos-pista">
            {datos.avisos.map((a, i) => (
              <Link key={i} to={a.producto_id ? `/producto/${a.producto_id}` : '/catalogo'} className={`aviso aviso-${a.tipo}`}>
                <strong>{a.titulo}</strong>
                <span>{a.mensaje}</span>
              </Link>
            ))}
          </div>
        </section>
      )}

      {!error && (
        <section className="contenedor seccion">
          <div className="seccion-cabecera">
            <h2>Categorías</h2>
            <Link to="/catalogo" className="enlace">Todo el catálogo →</Link>
          </div>
          {cargando ? (
            <div className="grid-categorias">
              {Array.from({ length: 4 }, (_, i) => <div key={i} className="categoria sk" />)}
            </div>
          ) : datos.categorias.length === 0 ? (
            <EstadoVacio titulo="Aún no hay categorías activas" mensaje="Cuando se registren productos aparecerán aquí." />
          ) : (
            <div className="grid-categorias">
              {datos.categorias.map((c) => (
                <Link key={c.nombre} to={`/catalogo?categoria=${encodeURIComponent(c.nombre)}`} className="categoria">
                  <ImagenSegura src={c.imagenUrl} alt={c.nombre} />
                  <div className="categoria-info">
                    <h3>{c.nombre}</h3>
                    <span>{c.total} producto{c.total === 1 ? '' : 's'} · {c.disponibles} disponible{c.disponibles === 1 ? '' : 's'}</span>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </section>
      )}

      {!error && (
        <section className="contenedor seccion">
          <div className="seccion-cabecera">
            <h2>Destacados</h2>
            <span className="nota">Los productos con mayor disponibilidad</span>
          </div>
          {cargando ? (
            <SkeletonGrid cantidad={4} />
          ) : datos.destacados.length === 0 ? (
            <EstadoVacio />
          ) : (
            <div className="grid-productos">
              {datos.destacados.map((p, i) => <ProductCard key={p.id} producto={p} indice={i} />)}
            </div>
          )}
        </section>
      )}
    </>
  );
}

function Stat({ valor, etiqueta }) {
  return (
    <div className="stat">
      <span className="stat-valor">{valor}</span>
      <span className="stat-etiqueta">{etiqueta}</span>
    </div>
  );
}
