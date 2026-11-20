import axios from 'axios';

/**
 * Cliente HTTP (Axios) apuntando a los servicios públicos del Área del Cliente.
 * IMPORTANTE: no se agrega ningún encabezado Authorization: Bearer.
 * Estos endpoints están fuera del grupo auth:sanctum en routes/api.php.
 */
export const API_URL = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1/client/').replace(/\/?$/, '/');

// Origen del backend (http://localhost:8000), usado para reconstruir las rutas de imágenes
export const API_ORIGIN = new URL(API_URL).origin;

const http = axios.create({
  baseURL: API_URL,
  timeout: 15000,
  headers: { Accept: 'application/json' },
});

export default http;
