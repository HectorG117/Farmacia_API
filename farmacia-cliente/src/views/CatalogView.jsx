import { useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import usePeticion from '../hooks/usePeticion';
import { getCatalogoPublico } from '../api/clientePublicoService';
import ProductCard from '../components/ProductCard';
import { SkeletonGrid } from '../components/Skeletons';
import { EstadoError, EstadoVacio } from '../components/Estados';

const ORDENES = {
  nombre: { etiqueta: 'Nombre A–Z', fn: (a, b) => a.nombre.localeCompare(b.nombre) },
  precioAsc: { etiqueta: 'Precio: menor a mayor', fn: (a, b) => a.precio - b.precio },
  precioDesc: { etiqueta: 'Precio: mayor a menor', fn: (a, b) => b.precio - a.precio },
  stock: { etiqueta: 'Mayor disponibilidad', fn: (a, b) => b.stock - a.stock },
};

/** Vista de Catálogo General: cuadrícula responsiva con filtros del lado del cliente. */
export default function CatalogView() {
  const { datos: productos, cargando, error, recargar } = usePeticion(getCatalogoPublico);
  const [params, setParams] = useSearchParams();
  const [busqueda, setBusqueda] = useState('');
  const [orden, setOrden] = useState('nombre');
  const [soloDisponibles, setSoloDisponibles] = useState(false);
  const categoria = params.get('categoria') || '';

  const categorias = useMemo(
    () => [...new Set((productos ?? []).map((p) => p.categoria).filter(Boolean))].sort(),
    [productos],
  );

  const filtrados = useMemo(() => {
    const q = busqueda.trim().toLowerCase();
    return (productos ?? [])
      .filter((p) => !categoria || p.categoria === categoria)
      .filter((p) => !soloDisponibles || p.disponible)
      .filter((p) => !q || p.nombre.toLowerCase().includes(q) || p.categoria?.toLowerCase().includes(q))
      .sort(ORDENES[orden].fn);
  }, [productos, categoria, soloDisponibles, busqueda, orden]);

  const elegirCategoria = (c) => {
    if (c) setParams({ categoria: c });
    else setParams({});
  };

  return (
    <div className="contenedor seccion">
      <header className="pagina-cabecera">
        <span className="eyebrow">Catálogo general</span>
        <h1>Todo lo que tenemos para ti</h1>
        {!cargando && !error && (
          <p className="nota">{filtrados.length} de {productos.length} productos</p>
        )}
      </header>

      {!error && (
        <div className="filtros">
          <input
            type="search"
            className="buscador"
            placeholder="Buscar por nombre o categoría…"
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
            disabled={cargando}
          />
          <select value={orden} onChange={(e) => setOrden(e.target.value)} disabled={cargando}>
            {Object.entries(ORDENES).map(([k, v]) => <option key={k} value={k}>{v.etiqueta}</option>)}
          </select>
          <label className="check">
            <input type="checkbox" checked={soloDisponibles} onChange={(e) => setSoloDisponibles(e.target.checked)} />
            Solo disponibles
          </label>
        </div>
      )}

      {!cargando && !error && categorias.length > 0 && (
        <div className="chips">
          <button className={`chip ${!categoria ? 'activo' : ''}`} onClick={() => elegirCategoria('')}>Todas</button>
          {categorias.map((c) => (
            <button key={c} className={`chip ${categoria === c ? 'activo' : ''}`} onClick={() => elegirCategoria(c)}>{c}</button>
          ))}
        </div>
      )}

      {cargando && <SkeletonGrid cantidad={8} />}

      {error && <EstadoError error={error} onReintentar={recargar} />}

      {!cargando && !error && productos.length === 0 && <EstadoVacio />}

      {!cargando && !error && productos.length > 0 && filtrados.length === 0 && (
        <EstadoVacio titulo="Sin coincidencias" mensaje="Ningún producto coincide con los filtros seleccionados.">
          <button className="btn btn-secundario" onClick={() => { setBusqueda(''); setSoloDisponibles(false); elegirCategoria(''); }}>
            Limpiar filtros
          </button>
        </EstadoVacio>
      )}

      {!cargando && !error && filtrados.length > 0 && (
        <div className="grid-productos">
          {filtrados.map((p, i) => <ProductCard key={p.id} producto={p} indice={i} />)}
        </div>
      )}
    </div>
  );
}
