import { useEffect, useState } from 'react';
import { NavLink, Outlet, useLocation } from 'react-router-dom';

export default function Layout() {
  const [abierto, setAbierto] = useState(false);
  const { pathname } = useLocation();

  useEffect(() => {
    setAbierto(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [pathname]);

  return (
    <>
      <header className="nav">
        <div className="contenedor nav-interno">
          <NavLink to="/" className="marca">
            <span className="marca-cruz" aria-hidden="true">+</span>
            <span>Farmacia <em>Vital</em></span>
          </NavLink>
          <button className="nav-toggle" onClick={() => setAbierto((a) => !a)} aria-label="Abrir menú">
            <span /><span /><span />
          </button>
          <nav className={`nav-links ${abierto ? 'abierto' : ''}`}>
            <NavLink to="/" end>Inicio</NavLink>
            <NavLink to="/catalogo">Catálogo</NavLink>
            <NavLink to="/nosotros">Nosotros</NavLink>
          </nav>
        </div>
      </header>

      <main>
        <Outlet />
      </main>

      <footer className="footer">
        <div className="contenedor footer-interno">
          <div>
            <span className="marca"><span className="marca-cruz">+</span> Farmacia <em>Vital</em></span>
            <p>Catálogo público · Consulta sin necesidad de iniciar sesión.</p>
          </div>
          <p className="footer-nota">Actividad 5.3 · Cliente final consumiendo la API REST de Laravel</p>
        </div>
      </footer>
    </>
  );
}
