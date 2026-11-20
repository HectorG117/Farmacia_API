import { EstadoError } from '../components/Estados';

export default function NotFoundView() {
  return (
    <div className="contenedor seccion">
      <EstadoError error={{ status: 404, titulo: 'Página no encontrada (404)', message: 'La dirección que buscas no existe.' }} />
    </div>
  );
}
