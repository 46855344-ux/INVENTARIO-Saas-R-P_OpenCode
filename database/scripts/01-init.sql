-- =============================================================================
-- Inicialización de base de datos — SAAS Inversiones R&P
-- Se ejecuta automáticamente la primera vez que arranca el contenedor MySQL
-- (docker-entrypoint-initdb.d). El usuario y la base los crea la imagen a
-- partir de las variables MYSQL_* del docker-compose.yml.
-- =============================================================================

-- Codificación estándar: utf8mb4 (soporta emoji y caracteres extendidos).
ALTER DATABASE `saas_inversiones_rp`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Zona horaria de la sesión en UTC; la app formatea a la zona local.
SET time_zone = '+00:00';
