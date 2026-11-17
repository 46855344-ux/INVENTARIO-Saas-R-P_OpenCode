interface TabItem {
  id: string;
  label: string;
}

interface TabsProps {
  items: TabItem[];
  active: string;
  ariaLabel: string;
  onChange: (id: string) => void;
}

export function Tabs({ items, active, ariaLabel, onChange }: TabsProps) {
  return (
    <nav className="catalog-tabs" aria-label={ariaLabel}>
      {items.map((item) => (
        <button
          key={item.id}
          type="button"
          className={active === item.id ? 'catalog-tab is-active' : 'catalog-tab'}
          aria-pressed={active === item.id}
          onClick={() => onChange(item.id)}
        >
          {item.label}
        </button>
      ))}
    </nav>
  );
}
