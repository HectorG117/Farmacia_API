import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import usePeticion from '../hooks/usePeticion';
import { getDetalleRecurso } from '../api/clientePublicoService';
import ImagenSegura from '../components/ImagenSegura';
import ProductCard from '../components/ProductCard';
import { SkeletonDetalle } from '../components/Skeletons';
import { EstadoError } from '../components/Estados';
import { PLACEHOLDER } from '../api/imagenes';
import { etiquetaStock, formatoPrecio } from '../utils/formato';

const fecha = (iso) => (iso ? new Date(iso).toLocaleDateString('es-MX', { dateStyle: 'long' }) : '—');

/** Vista de detalle: consulta el recurso por ID y muestra galería, datos completos y relacionados. */
export default function ProductDetailView() {
  const { id } = useParams();
  const { datos, cargando, error, recargar } = usePeticion(() => getDetalleRecurso(id), [id]);
  const [activa, setActiva] = useState(0);
  const [zoom, setZoom] = useState(false);

  useEffect(() => setActiva(0), [id]);

  useEffect(() => {
    const cerrar = (e) => e.key === 'Escape' && setZoom(false);
    window.addEventListener('keydown', cerrar);
    return () => window.removeEventListener('keydown', cerrar);
  }, []);

  if (cargando) return <div className="contenedor seccion"><SkeletonDetalle /></div>;
  if (error) return <div className="contenedor seccion"><EstadoError error={error} onReintentar={recargar} /></div>;

  const { producto: p, relacionados } = datos;
  const galeria = p.galeria.length ? p.galeria : [PLACEHOLDER];
  const stock = etiquetaStock(p.stock);

  return (
    <div className="contenedor seccion">
      <nav className="migas">
        <Link to="/">Inicio</Link> / <Link to="/catalogo">Catálogo</Link> /{' '}
        <Link to={`/catalogo?categoria=${encodeURIComponent(p.categoria)}`}>{p.categoria}</Link> / <span>{p.nombre}</span>
      </nav>

      <div className="detalle">
        <div className="galeria">
          <button className="galeria-principal" onClick={() => setZoom(true)} aria-label="Ampliar imagen">
            <ImagenSegura src={galeria[activa]} alt={p.nombre} />
            <span className="galeria-lupa">⤢ Ampliar</span>
          </button>
          {galeria.length > 1 && (
            <div className="galeria-miniaturas">
              {galeria.map((src, i) => (
                <button key={src} className={i === activa ? 'activa' : ''} onClick={() => setActiva(i)}>
                  <ImagenSegura src={src} alt={`${p.nombre} ${i + 1}`} />
                </button>
              ))}
            </div>
          )}
        </div>

        <div className="detalle-info">
          <span className="card-categoria">{p.categoria}</span>
          <h1>{p.nombre}</h1>
          <div className="detalle-precio">
            <span className="precio grande">{formatoPrecio(p.precio)}</span>
            <span className={`chip-stock ${stock.clase} estatico`}>{stock.texto}</span>
          </div>

          <p className="detalle-descripcion">
            {p.nombre} forma parte de nuestra categoría de <strong>{p.categoria?.toLowerCase()}</strong>.{' '}
            {p.disponible
              ? `Actualmente contamos con ${p.stock} pieza${p.stock === 1 ? '' : 's'} en existencia, lista${p.stock === 1 ? '' : 's'} para surtirse en sucursal.`
              : 'Por el momento no tenemos existencias; vuelve a consultar pronto, nuestro inventario se actualiza constantemente.'}{' '}
            Consulta a tu médico o farmacéutico antes de usar cualquier medicamento.
          </p>

          <dl className="ficha">
            <div><dt>Clave</dt><dd>#{String(p.id).padStart(5, '0')}</dd></div>
            <div><dt>Categoría</dt><dd>{p.categoria}</dd></div>
            <div><dt>Precio unitario</dt><dd>{formatoPrecio(p.precio)}</dd></div>
            <div><dt>Existencias</dt><dd>{p.stock} pzas.</dd></div>
            <div><dt>Estado</dt><dd>{p.disponible ? 'Disponible' : 'Agotado'}</dd></div>
            <div><dt>En catálogo desde</dt><dd>{fecha(p.created_at)}</dd></div>
            <div><dt>Última actualización</dt><dd>{fecha(p.updated_at)}</dd></div>
          </dl>

          <Link to="/catalogo" className="btn btn-secundario">← Volver al catálogo</Link>
        </div>
      </div>

      {relacionados.length > 0 && (
        <section className="seccion">
          <div className="seccion-cabecera"><h2>También en {p.categoria}</h2></div>
          <div className="grid-productos">
            {relacionados.map((r, i) => <ProductCard key={r.id} producto={r} indice={i} />)}
          </div>
        </section>
      )}

      {zoom && (
        <div className="lightbox" onClick={() => setZoom(false)} role="dialog" aria-modal="true">
          <button className="lightbox-cerrar" aria-label="Cerrar">×</button>
          <img
            src={galeria[activa]}
            alt={p.nombre}
            onClick={(e) => e.stopPropagation()}
            onError={(e) => { e.currentTarget.src = PLACEHOLDER; }}
          />
        </div>
      )}
    </div>
  );
}
