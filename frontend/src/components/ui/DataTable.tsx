import type { ReactNode } from 'react';

interface DataTableColumn {
  key: string;
  label: string;
}

interface DataTableProps {
  columns: DataTableColumn[];
  rows: Array<Record<string, ReactNode>>;
  rowKey: (row: Record<string, ReactNode>, index: number) => string | number;
  ariaLabel: string;
  empty?: ReactNode;
}

export function DataTable({
  columns,
  rows,
  rowKey,
  ariaLabel,
  empty = 'No hay datos para mostrar.',
}: DataTableProps) {
  if (rows.length === 0) {
    return (
      <p className="empty-state" role="status">
        {empty}
      </p>
    );
  }

  return (
    <table className="users-table" aria-label={ariaLabel}>
      <thead>
        <tr>
          {columns.map((column) => (
            <th key={column.key} scope="col">
              {column.label}
            </th>
          ))}
        </tr>
      </thead>
      <tbody>
        {rows.map((row, index) => (
          <tr key={rowKey(row, index)}>
            {columns.map((column) => (
              <td key={column.key}>{row[column.key]}</td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}
