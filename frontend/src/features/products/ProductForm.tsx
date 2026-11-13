import { useState } from 'react';
import type { FormEvent } from 'react';
import type { CatalogItem, Product } from '../../types';

export interface ProductFormValues {
  name: string;
  description: string;
  category_id: number | null;
  brand_id: number | null;
}

interface ProductFormProps {
  mode: 'create' | 'edit';
  product: Product | null;
  categories: CatalogItem[];
  brands: CatalogItem[];
  submitting: boolean;
  error: string | null;
  onSubmit: (values: ProductFormValues) => void;
  onCancel: () => void;
}

export function ProductForm({
  mode,
  product,
  categories,
  brands,
  submitting,
  error,
  onSubmit,
  onCancel,
}: ProductFormProps) {
  const [name, setName] = useState(product?.name ?? '');
  const [description, setDescription] = useState(product?.description ?? '');
  const [categoryId, setCategoryId] = useState<number | ''>(product?.category_id ?? '');
  const [brandId, setBrandId] = useState<number | ''>(product?.brand_id ?? '');

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    onSubmit({
      name: name.trim(),
      description: description.trim(),
      category_id: categoryId === '' ? null : categoryId,
      brand_id: brandId === '' ? null : brandId,
    });
  };

  return (
    <form className="user-form" onSubmit={handleSubmit}>
      <h2>{mode === 'create' ? 'Nuevo producto' : `Editar ${product?.name ?? 'producto'}`}</h2>

      {error && (
        <p className="form-error" role="alert">
          {error}
        </p>
      )}

      <div className="user-form-grid">
        <label className="field">
          <span>Nombre</span>
          <input
            name="name"
            value={name}
            onChange={(event) => setName(event.target.value)}
            maxLength={150}
            required
          />
        </label>

        <label className="field">
          <span>Categoría</span>
          <select
            name="category_id"
            value={categoryId}
            onChange={(event) =>
              setCategoryId(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Sin categoría</option>
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
            value={brandId}
            onChange={(event) =>
              setBrandId(event.target.value === '' ? '' : Number(event.target.value))
            }
          >
            <option value="">Sin marca</option>
            {brands.map((brand) => (
              <option key={brand.id} value={brand.id}>
                {brand.name}
              </option>
            ))}
          </select>
        </label>

        <label className="field field-wide">
          <span>Descripción</span>
          <textarea
            name="description"
            value={description}
            onChange={(event) => setDescription(event.target.value)}
            maxLength={2000}
            rows={3}
          />
        </label>
      </div>

      <div className="user-form-actions">
        <button type="submit" className="button-primary" disabled={submitting}>
          {submitting ? 'Guardando…' : 'Guardar'}
        </button>
        <button
          type="button"
          className="button-secondary"
          onClick={onCancel}
          disabled={submitting}
        >
          Cancelar
        </button>
      </div>
    </form>
  );
}
