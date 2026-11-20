import { useEffect, useState } from 'react';
import { PLACEHOLDER } from '../api/imagenes';

/**
 * <img> con manejo de errores: si la URL del servidor falla o no existe,
 * muestra la imagen placeholder por defecto. Mientras carga muestra un shimmer.
 */
export default function ImagenSegura({ src, alt, className = '', ...props }) {
  const [actual, setActual] = useState(src || PLACEHOLDER);
  const [cargada, setCargada] = useState(false);

  useEffect(() => {
    setActual(src || PLACEHOLDER);
    setCargada(false);
  }, [src]);

  return (
    <div className={`img-segura ${cargada ? 'lista' : ''} ${className}`}>
      <img
        src={actual}
        alt={alt}
        loading="lazy"
        onLoad={() => setCargada(true)}
        onError={() => {
          if (actual !== PLACEHOLDER) setActual(PLACEHOLDER);
          else setCargada(true);
        }}
        {...props}
      />
    </div>
  );
}
