import { Route, Routes } from 'react-router-dom';
import Layout from './components/Layout';
import HomeView from './views/HomeView';
import CatalogView from './views/CatalogView';
import ProductDetailView from './views/ProductDetailView';
import AboutView from './views/AboutView';
import NotFoundView from './views/NotFoundView';

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<HomeView />} />
        <Route path="catalogo" element={<CatalogView />} />
        <Route path="producto/:id" element={<ProductDetailView />} />
        <Route path="nosotros" element={<AboutView />} />
        <Route path="*" element={<NotFoundView />} />
      </Route>
    </Routes>
  );
}
