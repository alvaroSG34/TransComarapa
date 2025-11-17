# 🎨 Sistema de Temas - Guía de Prueba

## ✅ Estado Actual del Proyecto

El sistema de temas está **completamente implementado** e integrado en los layouts. A continuación los pasos para probarlo.

## 📋 Pre-requisitos

Antes de probar el sistema de temas, asegúrate de:

1. **Crear la base de datos PostgreSQL**
2. **Configurar las credenciales en .env**
3. **Ejecutar las migraciones**

## 🚀 Pasos para Probar

### 1. Crear Base de Datos

```powershell
# Conectar a PostgreSQL (usando tu usuario de PostgreSQL)
psql -U postgres

# Crear base de datos
CREATE DATABASE transcomarapa;

# Salir
\q
```

### 2. Configurar .env

Abre el archivo `.env` y configura:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=transcomarapa
DB_USERNAME=postgres
DB_PASSWORD=tu_password_aqui

APP_TIMEZONE=America/La_Paz
APP_LOCALE=es
```

### 3. Ejecutar Migraciones

```powershell
php artisan migrate
```

Esto creará todas las tablas necesarias, incluyendo la tabla `usuarios` con los campos de tema:
- `tema_preferido` (ninos, jovenes, adultos)
- `modo_contraste` (normal, alto)
- `tamano_fuente` (pequeño, mediano, grande)

### 4. Iniciar Servidores

**Terminal 1 - Laravel:**
```powershell
php artisan serve
```

**Terminal 2 - Vite (en otra ventana):**
```powershell
npm run dev
```

### 5. Probar en el Navegador

1. Abre: http://localhost:8000
2. Haz clic en **Register** (Registrarse)
3. Crea una cuenta nueva
4. Inicia sesión

### 6. Probar el Sistema de Temas

#### 🎨 Cambiar Tema

1. En la barra de navegación superior derecha, busca el **icono de paleta** (🎨)
2. Haz clic para abrir el menú de temas
3. Selecciona entre:
   - **Niños** 🎨 - Colores brillantes y divertidos
   - **Jóvenes** 🚀 - Moderno y dinámico (predeterminado)
   - **Adultos** 💼 - Elegante y profesional

#### 🌓 Cambiar Modo Día/Noche

En el mismo menú:
1. Verás el botón de **modo oscuro** con icono de sol/luna
2. Haz clic para alternar entre modo claro y oscuro
3. Al hacer clic manual, se desactiva el modo automático

#### ⚙️ Modo Automático

El modo automático está activado por defecto y cambia según la hora de Bolivia:
- **Día (☀️)**: 6:00 AM - 6:00 PM
- **Noche (🌙)**: 6:00 PM - 6:00 AM

Para reactivar el modo automático después de cambiarlo manualmente:
1. Recarga la página
2. O espera a que cambie la hora

## 🔍 Verificar que Funciona

### Ver Tema Activo en Dashboard

El Dashboard ahora muestra:
- ✅ Nombre del tema activo con icono
- ✅ Modo actual (claro/oscuro) con indicador
- ✅ Si está en modo automático o manual
- ✅ Hora actual del servidor (Bolivia)
- ✅ Paleta de colores del tema activo
- ✅ Acciones rápidas con los colores del tema

### Verificar Persistencia

1. Cambia el tema a "Niños"
2. Cambia el modo a oscuro (manual)
3. Recarga la página (F5)
4. ✅ El tema debe persistir (guardado en localStorage y BD)

### Verificar en Base de Datos

```sql
-- Conectar a PostgreSQL
psql -U postgres -d transcomarapa

-- Ver las preferencias del usuario
SELECT id, nombre, apellido, tema_preferido, modo_contraste 
FROM usuarios;
```

Deberías ver algo como:
```
 id | nombre | apellido | tema_preferido | modo_contraste
----+--------+----------+----------------+----------------
  1 | Juan   | Pérez    | ninos          | alto
```

## 🎨 Temas Disponibles

### Tema Niños 🎨
- **Colores**: Amarillo cálido (#fbbf24), naranja (#fb923c), rosa (#ec4899)
- **Estilo**: Divertido, juguetón, brillante
- **Ideal para**: Interfaz amigable para niños

### Tema Jóvenes 🚀 (Predeterminado)
- **Colores**: Azul cielo (#0ea5e9), púrpura (#a855f7), cyan (#06b6d4)
- **Estilo**: Moderno, vibrante, dinámico
- **Ideal para**: Usuarios jóvenes y modernos

### Tema Adultos 💼
- **Colores**: Grises (#6b7280), azul oscuro (#1e40af), neutros
- **Estilo**: Profesional, elegante, minimalista
- **Ideal para**: Entorno empresarial

## 🐛 Solución de Problemas

### Los colores no cambian

1. Verifica que Vite esté corriendo (`npm run dev`)
2. Limpia la caché del navegador (Ctrl + Shift + Delete)
3. Revisa la consola del navegador (F12) por errores
4. Verifica que `themes.css` esté siendo importado en `app.js`

### El tema no se guarda

1. Verifica que las migraciones se hayan ejecutado correctamente
2. Revisa la tabla `usuarios` para confirmar que existen los campos `tema_preferido` y `modo_contraste`
3. Verifica en la consola del navegador (Network tab) que se envíe la petición POST a `/api/user/theme-preferences`

### Error 500 en la API

1. Verifica que el usuario esté autenticado
2. Revisa los logs de Laravel: `storage/logs/laravel.log`
3. Confirma que el endpoint esté en el grupo `auth` middleware en `routes/web.php`

### El modo automático no funciona

1. Verifica la zona horaria en `config/app.php` (debe ser `America/La_Paz`)
2. Reinicia el servidor de Laravel
3. Verifica que `HandleInertiaRequests.php` esté compartiendo `timeMode` y `currentHour`

## 📱 Probar Responsividad

1. Abre las herramientas de desarrollador (F12)
2. Activa el modo de dispositivo móvil (Ctrl + Shift + M)
3. Verifica que el ThemeSwitcher aparezca en el menú hamburguesa
4. Prueba cambiar temas en móvil

## 🎯 Próximos Pasos

Una vez verificado que el sistema de temas funciona:

1. **Sistema de Accesibilidad**: Implementar tamaño de fuente y alto contraste
2. **Módulos de Negocio**: Crear CRUD para ventas, boletos, encomiendas
3. **Dashboard Mejorado**: Gráficas y estadísticas
4. **Gestión de Imágenes**: Configurar storage para img_url

## 📚 Documentación Adicional

- [THEME_SYSTEM.md](./THEME_SYSTEM.md) - Documentación técnica completa del sistema de temas
- [DataBase.md](./DataBase.md) - Esquema de base de datos

## ✨ Características Implementadas

✅ 3 temas personalizados (Niños, Jóvenes, Adultos)  
✅ Modo día/noche automático basado en hora de Bolivia  
✅ Modo oscuro manual  
✅ Persistencia en localStorage  
✅ Persistencia en base de datos  
✅ API para guardar preferencias  
✅ Componente ThemeSwitcher con Headless UI  
✅ Integración en AuthenticatedLayout  
✅ Integración en GuestLayout  
✅ Dashboard con demostración visual de temas  
✅ Variables CSS reactivas  
✅ Detección de hora del servidor  

## 🤝 Soporte

Si encuentras algún problema, revisa:
1. Los logs de Laravel en `storage/logs/laravel.log`
2. La consola del navegador (F12)
3. El archivo `THEME_SYSTEM.md` para detalles técnicos
