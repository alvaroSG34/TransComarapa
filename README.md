# TransComarapa - Sistema de Gestión de Transporte

Sistema web para la gestión de boletos y encomiendas de transporte, desarrollado con Laravel 12, Vue 3 e Inertia.js.

## 📋 Descripción

TransComarapa es una aplicación web moderna para gestionar:
- **Boletos de transporte**: Reserva y venta de pasajes
- **Encomiendas**: Gestión de paquetes y entregas
- **Rutas y viajes**: Administración de rutas y programación de viajes
- **Vehículos**: Control de flota vehicular
- **Pagos**: Integración con PagoFácil para procesamiento de pagos mediante QR

## 🛠️ Requisitos del Sistema

Antes de comenzar, asegúrate de tener instalado:

- **PHP** >= 8.2 con extensiones:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - SQLite (para desarrollo) o PostgreSQL/MySQL (para producción)
- **Composer** (gestor de dependencias de PHP)
- **Node.js** >= 18.x y **NPM**
- **Base de datos**: SQLite (por defecto), PostgreSQL o MySQL

## 📦 Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd TransComarapa
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de Node.js

```bash
npm install
```

### 4. Configurar el archivo de entorno

Si no existe un archivo `.env`, cópialo desde `.env.example`:

```bash
cp .env.example .env
```

Si no existe `.env.example`, crea un archivo `.env` en la raíz del proyecto con el siguiente contenido mínimo:

```env
APP_NAME=TransComarapa
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Base de datos (SQLite por defecto)
DB_CONNECTION=sqlite
# DB_DATABASE se configurará automáticamente como database/database.sqlite

# Para usar PostgreSQL o MySQL, descomenta y configura:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=transcomarapa
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contraseña

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

APP_TIMEZONE=America/La_Paz
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

# PagoFácil (configura según PAGOFACIL_SETUP.md)
PAGOFACIL_API_URL=https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr
PAGOFACIL_QUERY_URL=https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction
PAGOFACIL_API_TOKEN=tu_token_aqui
PAGOFACIL_CLIENT_CODE_PREFIX=Grupo04SA
PAGOFACIL_CALLBACK_URL=http://localhost/api/pagofacil/callback
```

### 5. Generar la clave de aplicación

```bash
php artisan key:generate
```

### 6. Crear la base de datos (si usas SQLite)

Si usas SQLite, crea el archivo de base de datos:

```bash
# Windows (PowerShell)
New-Item -ItemType File -Path database\database.sqlite

# Linux/Mac
touch database/database.sqlite
```

**Para PostgreSQL o MySQL:**

Crea la base de datos manualmente:

```sql
-- PostgreSQL
CREATE DATABASE transcomarapa;

-- MySQL
CREATE DATABASE transcomarapa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Luego configura las credenciales en tu archivo `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=transcomarapa
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 7. Ejecutar las migraciones

```bash
php artisan migrate
```

Esto creará todas las tablas necesarias en la base de datos.

### 8. Crear el enlace simbólico de almacenamiento (opcional)

Si vas a usar almacenamiento de archivos:

```bash
php artisan storage:link
```

## 🚀 Ejecutar el Proyecto

### Opción 1: Ejecutar todo con un comando (recomendado)

Laravel incluye un script que inicia todos los servidores necesarios:

```bash
composer run dev
```

Este comando iniciará:
- Servidor Laravel (http://localhost:8000)
- Vite dev server (hot reload para frontend)
- Queue worker
- Laravel Pail (logs en tiempo real)

### Opción 2: Ejecutar en terminales separadas

**Terminal 1 - Servidor Laravel:**

```bash
php artisan serve
```

**Terminal 2 - Servidor de desarrollo Vite:**

```bash
npm run dev
```

El servidor Laravel estará disponible en: **http://localhost:8000**

### Opción 3: Usar Laravel Sail (Docker)

Si tienes Docker instalado, puedes usar Laravel Sail:

```bash
./vendor/bin/sail up
```

## 🔧 Comandos Útiles

### Desarrollo

```bash
# Iniciar servidores de desarrollo (Laravel + Vite + Queue + Logs)
composer run dev

# Solo servidor Laravel
php artisan serve

# Solo Vite
npm run dev

# Compilar assets para producción
npm run build
```

### Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar migraciones con seeders
php artisan migrate --seed

# Revertir última migración
php artisan migrate:rollback

# Revertir todas las migraciones
php artisan migrate:reset

# Crear nueva migración
php artisan make:migration nombre_de_la_migracion
```

### Cache y Optimización

```bash
# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de rutas
php artisan route:clear

# Limpiar cache de vistas
php artisan view:clear

# Limpiar todo el cache
php artisan cache:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Testing

```bash
# Ejecutar tests
composer run test
# o
php artisan test
```

## 📁 Estructura del Proyecto

```
TransComarapa/
├── app/
│   ├── Http/Controllers/    # Controladores de la aplicación
│   ├── Models/              # Modelos Eloquent
│   ├── Services/            # Servicios de negocio (PagoFácil, Venta, etc.)
│   ├── Repositories/        # Repositorios para acceso a datos
│   └── Events/              # Eventos de la aplicación
├── database/
│   ├── migrations/          # Migraciones de base de datos
│   ├── seeders/             # Seeders para datos iniciales
│   └── database.sqlite      # Base de datos SQLite (si se usa)
├── resources/
│   ├── js/
│   │   ├── Pages/           # Páginas Vue.js con Inertia
│   │   ├── Components/      # Componentes Vue reutilizables
│   │   ├── Layouts/         # Layouts de la aplicación
│   │   └── app.js           # Punto de entrada de la aplicación
│   └── css/                 # Estilos CSS/Tailwind
├── routes/
│   ├── web.php              # Rutas web
│   └── api.php              # Rutas API
├── public/                  # Archivos públicos (punto de entrada)
└── .env                     # Variables de entorno (no versionado)
```

## 📚 Documentación Adicional

Este proyecto incluye documentación adicional en los siguientes archivos:

- **[PAGOFACIL_SETUP.md](PAGOFACIL_SETUP.md)**: Configuración detallada de la integración con PagoFácil
- **[THEME_SYSTEM.md](THEME_SYSTEM.md)**: Documentación del sistema de temas
- **[TESTING_GUIDE.md](TESTING_GUIDE.md)**: Guía para probar el sistema de temas
- **[CHECKLIST.md](CHECKLIST.md)**: Lista de verificación de funcionalidades
- **[FIX_POSTGRESQL.md](FIX_POSTGRESQL.md)**: Soluciones para problemas comunes con PostgreSQL

## 🔐 Configuración de PagoFácil

Para configurar la integración con PagoFácil, consulta el archivo **[PAGOFACIL_SETUP.md](PAGOFACIL_SETUP.md)** que incluye:

- Variables de entorno necesarias
- Cómo generar códigos QR de pago
- Cómo consultar el estado de transacciones
- Ejemplos de uso del servicio

## 🌐 Tecnologías Utilizadas

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 + Inertia.js
- **Estilos**: Tailwind CSS
- **Build Tool**: Vite
- **Base de datos**: SQLite (desarrollo) / PostgreSQL / MySQL
- **Gestión de estado**: Pinia
- **Iconos**: Heroicons
- **Gráficos**: Chart.js

## 📝 Licencia

Este proyecto está bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## 🤝 Contribuir

Si deseas contribuir al proyecto:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📞 Soporte

Para problemas o preguntas, por favor abre un issue en el repositorio.
