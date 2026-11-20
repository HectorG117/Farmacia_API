export default function Spinner({ texto = 'Cargando…' }) {
  return (
    <div className="spinner-box" role="status" aria-live="polite">
      <span className="spinner" />
      <span>{texto}</span>
    </div>
  );
}
