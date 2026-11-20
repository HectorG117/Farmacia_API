export function SkeletonCard() {
  return (
    <div className="card skeleton-card" aria-hidden="true">
      <div className="sk sk-img" />
      <div className="card-body">
        <div className="sk sk-line corta" />
        <div className="sk sk-line" />
        <div className="sk sk-line media" />
      </div>
    </div>
  );
}

export function SkeletonGrid({ cantidad = 8 }) {
  return (
    <div className="grid-productos" aria-busy="true" aria-label="Cargando productos">
      {Array.from({ length: cantidad }, (_, i) => <SkeletonCard key={i} />)}
    </div>
  );
}

export function SkeletonDetalle() {
  return (
    <div className="detalle" aria-busy="true">
      <div className="sk sk-galeria" />
      <div className="detalle-info">
        <div className="sk sk-line corta" />
        <div className="sk sk-titulo" />
        <div className="sk sk-line media" />
        <div className="sk sk-line" />
        <div className="sk sk-line" />
      </div>
    </div>
  );
}
