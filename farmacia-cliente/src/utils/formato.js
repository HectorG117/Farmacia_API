const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
export const formatoPrecio = (n) => moneda.format(Number(n) || 0);

export function etiquetaStock(stock) {
  if (stock <= 0) return { texto: 'Agotado', clase: 'agotado' };
  if (stock <= 5) return { texto: `Últimas ${stock} piezas`, clase: 'pocas' };
  return { texto: 'Disponible', clase: 'disponible' };
}

// Síntesis atractiva generada con los datos del producto (la tabla no tiene descripción)
export function sintesis(p) {
  if (!p) return '';
  const disp = p.stock > 0 ? `${p.stock} piezas en existencia` : 'temporalmente agotado';
  return `${p.nombre}, de nuestra línea de ${String(p.categoria || 'productos').toLowerCase()}. ${disp.charAt(0).toUpperCase() + disp.slice(1)}.`;
}
