import { Link } from 'react-router-dom';
import ImagenSegura from './ImagenSegura';
import { etiquetaStock, formatoPrecio, sintesis } from '../utils/formato';

export default function ProductCard({ producto, indice = 0 }) {
  const stock = etiquetaStock(producto.stock);
  return (
    <Link
      to={`/producto/${producto.id}`}
      className={`card ${producto.disponible ? '' : 'card-agotada'}`}
      style={{ '--retraso': `${Math.min(indice, 12) * 45}ms` }}
    >
      <div className="card-media">
        <ImagenSegura src={producto.imagenUrl} alt={producto.nombre} />
        <span className={`chip-stock ${stock.clase}`}>{stock.texto}</span>
      </div>
      <div className="card-body">
        <span className="card-categoria">{producto.categoria}</span>
        <h3 className="card-titulo">{producto.nombre}</h3>
        <p className="card-sintesis">{sintesis(producto)}</p>
        <div className="card-pie">
          <span className="precio">{formatoPrecio(producto.precio)}</span>
          <span className="card-ver">Ver detalle →</span>
        </div>
      </div>
    </Link>
  );
}
