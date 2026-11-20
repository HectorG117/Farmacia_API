import { useCallback, useEffect, useState } from 'react';

/**
 * Ejecuta una petición asíncrona y expone { datos, cargando, error, recargar }.
 * Evita actualizar el estado si el componente se desmontó o cambió la dependencia.
 */
export default function usePeticion(funcion, dependencias = []) {
  const [estado, setEstado] = useState({ datos: null, cargando: true, error: null });
  const [intento, setIntento] = useState(0);

  useEffect(() => {
    let activo = true;
    setEstado((e) => ({ ...e, cargando: true, error: null }));

    funcion()
      .then((datos) => activo && setEstado({ datos, cargando: false, error: null }))
      .catch((error) => activo && setEstado({ datos: null, cargando: false, error }));

    return () => {
      activo = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...dependencias, intento]);

  const recargar = useCallback(() => setIntento((n) => n + 1), []);

  return { ...estado, recargar };
}
