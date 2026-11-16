import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from '../features/auth/AuthProvider';
import { LoginPage } from '../features/auth/LoginPage';
import { ProtectedRoute } from '../features/auth/ProtectedRoute';
import { UsersPage } from '../features/identity/UsersPage';
import { CatalogPage } from '../features/products/CatalogPage';
import { InventoryPage } from '../features/inventory/InventoryPage';
import { AccountPage } from '../features/account/AccountPage';
import { CountsPage } from '../features/audit/CountsPage';
import { MetricsPage } from '../features/metrics/MetricsPage';
import { PurchasingPage } from '../features/purchasing/PurchasingPage';
import { ConfigurationPage } from '../features/settings/ConfigurationPage';
import { AppLayout } from '../layouts/AppLayout';
import { HomePage } from './HomePage';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<LoginPage />} />

          <Route element={<ProtectedRoute />}>
            <Route element={<AppLayout />}>
              <Route index element={<HomePage />} />
              <Route path="catalog" element={<CatalogPage />} />
              <Route path="inventory" element={<InventoryPage />} />
              <Route path="purchasing" element={<PurchasingPage />} />
              <Route path="metrics" element={<MetricsPage />} />
              <Route path="counts" element={<CountsPage />} />
              <Route path="account" element={<AccountPage />} />
              <Route path="settings" element={<ConfigurationPage />} />
              <Route path="users" element={<UsersPage />} />
              <Route path="*" element={<Navigate to="/" replace />} />
            </Route>
          </Route>
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}
