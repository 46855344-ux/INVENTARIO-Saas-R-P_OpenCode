import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';
import {
  createBrand,
  createCategory,
  listBrands,
  listCategories,
  updateBrand,
  updateBrandStatus,
  updateCategory,
  updateCategoryStatus,
} from '../../services/catalog';
import { CatalogSection } from './CatalogSection';
import type { CatalogResource } from './CatalogSection';
import { ProductsSection } from './ProductsSection';

const categoryResource: CatalogResource = {
  title: 'Categorías',
  singular: 'categoría',
  list: listCategories,
  create: createCategory,
  update: updateCategory,
  changeStatus: updateCategoryStatus,
};

const brandResource: CatalogResource = {
  title: 'Marcas',
  singular: 'marca',
  list: listBrands,
  create: createBrand,
  update: updateBrand,
  changeStatus: updateBrandStatus,
};

type CatalogTab = 'categories' | 'brands' | 'products';

export function CatalogPage() {
  const { activeCompany, hasPermission } = useAuth();
  const canView = hasPermission('catalog.view');
  const canManage = hasPermission('catalog.manage');
  const [tab, setTab] = useState<CatalogTab>('categories');

  if (!canView) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para ver el catálogo de esta empresa.</p>
      </section>
    );
  }

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Catálogo</p>
          <h1>Catálogo de productos</h1>
          <p className="lead">
            Categorías y marcas de {activeCompany?.name ?? 'la empresa activa'}.
          </p>
        </div>
      </header>

      <nav className="catalog-tabs" aria-label="Secciones del catálogo">
        <button
          type="button"
          className={tab === 'categories' ? 'catalog-tab is-active' : 'catalog-tab'}
          onClick={() => setTab('categories')}
        >
          Categorías
        </button>
        <button
          type="button"
          className={tab === 'brands' ? 'catalog-tab is-active' : 'catalog-tab'}
          onClick={() => setTab('brands')}
        >
          Marcas
        </button>
        <button
          type="button"
          className={tab === 'products' ? 'catalog-tab is-active' : 'catalog-tab'}
          onClick={() => setTab('products')}
        >
          Productos
        </button>
      </nav>

      {tab === 'categories' && (
        <CatalogSection resource={categoryResource} canManage={canManage} />
      )}
      {tab === 'brands' && (
        <CatalogSection resource={brandResource} canManage={canManage} />
      )}
      {tab === 'products' && <ProductsSection canManage={canManage} />}
    </section>
  );
}
