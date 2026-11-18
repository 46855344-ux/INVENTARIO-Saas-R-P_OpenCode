import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { resolveApiError } from '../../services/errors';
import {
  createProduct,
  listBrands,
  listCategories,
  listProducts,
  updateProduct,
  updateProductStatus,
} from '../../services/catalog';
import type { ProductFilters, ProductInput } from '../../services/catalog';
import type { CatalogItem, CatalogStatus, PaginatedMeta, Product } from '../../types';
import { ProductForm } from './ProductForm';
import type { ProductFormValues } from './ProductForm';
import { VariantsSection } from './VariantsSection';

const PER_PAGE = 10;

const EMPTY_META: PaginatedMeta = {
  current_page: 1,
  last_page: 1,
  per_page: PER_PAGE,
  total: 0,
};

interface ProductsSectionProps {
  canManage: boolean;
}

export function ProductsSection({ canManage }: ProductsSectionProps) {
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<CatalogItem[]>([]);
  const [brands, setBrands] = useState<CatalogItem[]>([]);
  const [meta, setMeta] = useState<PaginatedMeta>(EMPTY_META);
  const [query, setQuery] = useState<ProductFilters>({ page: 1, per_page: PER_PAGE });
  const [reloadToken, setReloadToken] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const [searchInput, setSearchInput] = useState('');
  const [statusInput, setStatusInput] = useState<CatalogStatus | ''>('');
  const [categoryInput, setCategoryInput] = useState<number | ''>('');
  const [brandInput, setBrandInput] = useState<number | ''>('');

  const [formMode, setFormMode] = useState<'create' | 'edit' | null>(null);
  const [editing, setEditing] = useState<Product | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);

  useEffect(() => {
    let active = true;

    Promise.all([
      listCategories({ per_page: 100 }),
      listBrands({ per_page: 100 }),
    ])
      .then(([categoryPage, brandPage]) => {
        if (active) {
          setCategories(categoryPage.data);
          setBrands(brandPage.data);
        }
      })
      .catch(() => {
        if (active) {
          setCategories([]);
          setBrands([]);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    let active = true;

    const load = async () => {
      setLoading(true);
      setError(null);

      try {
        const result = await listProducts(query);

        if (active) {
          setProducts(result.data);
          setMeta(result.meta);
        }
      } catch (requestError) {
        if (active) {
          setError(resolveApiError(requestError).message);
        }
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    };

    void load();

    return () => {
      active = false;
    };
  }, [query, reloadToken]);

  const handleFilter = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    setQuery({
      page: 1,
      per_page: PER_PAGE,
      search: searchInput.trim(),
      status: statusInput,
      category_id: categoryInput,
      brand_id: brandInput,
    });
  };

  const openCreate = () => {
    setEditing(null);
    setFormError(null);
    setFormMode('create');
  };

  const openEdit = (product: Product) => {
    setEditing(product);
    setFormError(null);
    setFormMode('edit');
  };

  const closeForm = () => {
    setFormMode(null);
    setEditing(null);
    setFormError(null);
  };

  const handleSubmit = async (values: ProductFormValues) => {
    setSubmitting(true);
    setFormError(null);

    const input: ProductInput = {
      name: values.name,
      description: values.description,
      category_id: values.category_id,
      brand_id: values.brand_id,
    };

    try {
      if (formMode === 'create') {
        const created = await createProduct(input);
        closeForm();
        setReloadToken((token) => token + 1);
        // El QR se genera por variante: al crear el producto se abre su detalle.
        setSelectedProduct(created);
        return;
      }

      if (editing !== null) {
        await updateProduct(editing.id, input);
      }

      closeForm();
      setReloadToken((token) => token + 1);
    } catch (submitError) {
      setFormError(resolveApiError(submitError).message);
    } finally {
      setSubmitting(false);
    }
  };

  const handleToggleStatus = async (product: Product) => {
    setActionError(null);

    try {
      await updateProductStatus(
        product.id,
        product.status === 'active' ? 'inactive' : 'active',
      );
      setReloadToken((token) => token + 1);
    } catch (statusError) {
      setActionError(resolveApiError(statusError).message);
    }
  };

  if (selectedProduct !== null) {
    return (
      <VariantsSection
        product={selectedProduct}
        canManage={canManage}
        onBack={() => setSelectedProduct(null)}
      />
    );
  }

  return (
    <div className="catalog-section">
      <div className="catalog-section-header">
        <h2>Productos</h2>
        {canManage && (
          <button type="button" className="button-primary" onClick={openCreate}>
            Nuevo producto
          </button>
        )}
      </div>

      {actionError && (
        <p className="form-error" role="alert">
          {actionError}
        </p>
      )}

      <form className="users-filters" onSubmit={handleFilter}>
        <label className="field">
          <span>Buscar</span>
          <input
            name="search"
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            placeholder="Nombre o descripción"
          />
        </label>

        <label className="field">
          <span>Estado</span>
          <select
            name="status"
            value={statusInput}
            onChange={(event) =>
              setStatusInput(event.target.value as CatalogStatus | '')
            }
          >
            <option value="">Todos</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>
        </label>

        <label className="field">
          <span>Categoría</span>
          <select
            name="category_id"
            value={categoryInput}
            onChange={(event) =>
              setCategoryInput(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Todas</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field">
          <span>Marca</span>
          <select
            name="brand_id"
            value={brandInput}
            onChange={(event) =>
              setBrandInput(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Todas</option>
            {brands.map((brand) => (
              <option key={brand.id} value={brand.id}>
                {brand.name}
              </option>
            ))}
          </select>
        </label>

        <button type="submit" className="button-secondary">
          Filtrar
        </button>
      </form>

      {formMode && (
        <ProductForm
          key={`${formMode}-${editing?.id ?? 'new'}`}
          mode={formMode}
          product={editing}
          categories={categories}
          brands={brands}
          submitting={submitting}
          error={formError}
          onSubmit={handleSubmit}
          onCancel={closeForm}
        />
      )}

      {loading && <p className="loading">Cargando productos…</p>}

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      {!loading && !error && products.length === 0 && (
        <p className="empty-state">No hay productos para esta empresa.</p>
      )}

      {!loading && products.length > 0 && (
        <table className="users-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Marca</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {products.map((product) => (
              <tr key={product.id}>
                <td>{product.name}</td>
                <td>{product.category?.name ?? '—'}</td>
                <td>{product.brand?.name ?? '—'}</td>
                <td>
                  <span className={`status-badge status-${product.status}`}>
                    {product.status === 'active' ? 'Activo' : 'Inactivo'}
                  </span>
                </td>
                <td className="users-actions">
                  <button
                    type="button"
                    className="button-link"
                    onClick={() => setSelectedProduct(product)}
                  >
                    Variantes
                  </button>
                  {canManage && (
                    <>
                      <button
                        type="button"
                        className="button-link"
                        onClick={() => openEdit(product)}
                      >
                        Editar
                      </button>
                      <button
                        type="button"
                        className="button-link"
                        onClick={() => {
                          void handleToggleStatus(product);
                        }}
                      >
                        {product.status === 'active' ? 'Desactivar' : 'Activar'}
                      </button>
                    </>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      <nav className="pagination" aria-label="Paginación de productos">
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page <= 1}
          onClick={() =>
            setQuery((current) => ({ ...current, page: Math.max(1, (current.page ?? 1) - 1) }))
          }
        >
          Anterior
        </button>
        <span>
          Página {meta.current_page} de {meta.last_page} · {meta.total} productos
        </span>
        <button
          type="button"
          className="button-secondary"
          disabled={meta.current_page >= meta.last_page}
          onClick={() =>
            setQuery((current) => ({ ...current, page: (current.page ?? 1) + 1 }))
          }
        >
          Siguiente
        </button>
      </nav>
    </div>
  );
}
