# Guía de entorno local con XAMPP

Alternativa a Docker para desarrollar sin permisos de administrador o sin
virtualización disponible. El detalle completo está en
[`DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md`](../../DOCUMENTACION/MANUALES/MANUAL_INSTALACION.md).

---

## 1. Componentes

| Componente | Versión | Puerto |
| --- | --- | --- |
| Apache | 2.4 | 80 / 443 |
| MySQL | 8.0 (MariaDB en algunas instalaciones) | 3306 |
| PHP | 8.3 | — |

> XAMPP incluye PHP; para Laravel 13 se recomienda PHP 8.3 con las extensiones
> `mbstring`, `pdo_mysql`, `openssl`, `intl` y `zip` habilitadas en `php.ini`.

## 2. Base de datos

1. Iniciar **MySQL** desde el Panel de Control de XAMPP.
2. Abrir <http://localhost/phpmyadmin>.
3. Ejecutar:

```sql
CREATE DATABASE saas_inversiones_rp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'saas'@'localhost' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON saas_inversiones_rp.* TO 'saas'@'localhost';
FLUSH PRIVILEGES;
```

4. Ajustar `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_inversiones_rp
DB_USERNAME=saas
DB_PASSWORD=secret
```

## 3. Apache (opcional)

Para servir la API en `http://saas-inversiones-rp.local`:

1. Copiar el bloque `<VirtualHost>` de
   [`../apache/000-default.conf`](../apache/000-default.conf) en
   `C:\xampp\apache\conf\extra\httpd-vhosts.conf`.
2. Agregar en `C:\Windows\System32\drivers\etc\hosts`:

   ```text
   127.0.0.1  saas-inversiones-rp.local
   ```

3. Reiniciar Apache.

> Para desarrollo diario es más simple usar `php artisan serve`.

## 4. Ejecutar el proyecto

```bash
# Terminal 1 — API
cd backend
php artisan serve

# Terminal 2 — frontend
cd frontend
npm run dev
```

- API: <http://localhost:8000/api/health>
- SPA: <http://localhost:5173>

## 5. Notas

- No versionar `infrastructure/xampp/mysql/data/` ni los logs de Apache.
- Si el puerto 3306 está ocupado por otro MySQL, cambiar `DB_PORT` en `.env`.
- Para detener todo, cerrar MySQL y Apache desde el Panel de Control de XAMPP.
