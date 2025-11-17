# Sistema de Temas - TransComarapa

## Descripción General

Sistema de temas dinámico con 3 temas personalizados y modo día/noche automático basado en la hora de Bolivia (America/La_Paz).

## Características

### Temas Disponibles

1. **Niños** 🎨
   - Colores brillantes y divertidos
   - Paleta: Amarillo cálido, naranja, rosa
   - Tipografía juguetona

2. **Jóvenes** 🚀
   - Moderno y dinámico
   - Paleta: Azul cielo, púrpura, cyan
   - Diseño vibrante

3. **Adultos** 💼
   - Elegante y profesional
   - Paleta: Grises, azul oscuro, tonos neutros
   - Diseño minimalista

### Modo Día/Noche Automático

- **Día**: 6:00 AM - 6:00 PM (Bolivia)
- **Noche**: 6:00 PM - 6:00 AM (Bolivia)
- Detección automática en el servidor
- Modo manual disponible para usuarios

## Arquitectura

### Frontend (Vue 3 + Pinia)

```
resources/js/
├── stores/
│   └── theme.js           # Pinia store - estado global del tema
├── composables/
│   └── useTheme.js        # Composable para usar el tema
├── Components/
│   └── ThemeSwitcher.vue  # Componente selector de tema
└── Layouts/
    ├── AuthenticatedLayout.vue  # Layout con tema para usuarios autenticados
    └── GuestLayout.vue          # Layout con tema para invitados
```

### CSS

```
resources/css/
└── themes.css             # Variables CSS para cada tema y modo
```

### Backend (Laravel)

```
app/Http/Middleware/
└── HandleInertiaRequests.php  # Detecta modo día/noche del servidor

routes/
└── web.php                     # Endpoint para guardar preferencias
```

## Uso

### En Componentes Vue

```vue
<script setup>
import { useTheme } from '@/composables/useTheme';

const { 
    currentTheme,      // Tema actual: 'ninos', 'jovenes', 'adultos'
    isDarkMode,        // Estado modo oscuro
    isAutoMode,        // ¿Modo automático activado?
    effectiveMode,     // Modo efectivo: 'light' o 'dark'
    availableThemes,   // Lista de temas disponibles
    setTheme,          // Función para cambiar tema
    toggleDarkMode,    // Función para alternar modo oscuro
    setAutoMode,       // Función para activar/desactivar modo auto
} = useTheme();
</script>
```

### Variables CSS Disponibles

Cada tema define las siguientes variables CSS:

```css
/* Colores principales */
--primary-50 a --primary-900
--secondary-50 a --secondary-900
--accent-50 a --accent-900

/* Colores de superficie */
--bg-primary
--bg-secondary
--bg-tertiary

/* Texto */
--text-primary
--text-secondary
--text-tertiary

/* Bordes y sombras */
--border-primary
--border-secondary
--shadow-sm
--shadow-md
--shadow-lg

/* Efectos de hover */
--hover-overlay
```

### Ejemplo de Uso en CSS

```css
.mi-componente {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    border: 1px solid var(--border-primary);
}

.mi-boton {
    background-color: var(--primary-500);
    color: white;
}

.mi-boton:hover {
    background-color: var(--primary-600);
}
```

## Persistencia de Datos

### localStorage

```json
{
    "theme": "jovenes",
    "darkMode": false,
    "autoMode": true
}
```

### Base de Datos (usuarios table)

```sql
-- Campos relacionados con temas
tema_preferido VARCHAR(20)     -- 'ninos', 'jovenes', 'adultos'
modo_contraste VARCHAR(20)     -- 'normal', 'alto'
tamano_fuente VARCHAR(20)      -- 'pequeño', 'mediano', 'grande' (pendiente)
```

## Flujo de Datos

1. **Inicialización**:
   - Middleware detecta hora en Bolivia y determina `timeMode` (day/night)
   - Se comparte `timeMode` y `auth.user` vía Inertia
   - Layout carga preferencias de usuario y localStorage
   - Se aplica tema automáticamente

2. **Cambio de Tema**:
   - Usuario selecciona tema en ThemeSwitcher
   - Store actualiza estado
   - CSS se actualiza automáticamente (variables CSS reactivas)
   - Preferencias se guardan en localStorage
   - Si usuario autenticado, se envía POST a `/api/user/theme-preferences`

3. **Modo Día/Noche**:
   - Si autoMode activo: usa `serverTimeMode` del middleware
   - Si manual: usa estado `isDarkMode` del usuario
   - Se actualiza en tiempo real al cambiar configuración

## Endpoints API

### POST /api/user/theme-preferences

**Autenticación**: Requerida

**Body**:
```json
{
    "tema_preferido": "jovenes",    // opcional: ninos|jovenes|adultos
    "modo_contraste": "normal"      // opcional: normal|alto
}
```

**Respuesta**:
```json
{
    "success": true
}
```

## Datos Compartidos con Inertia

En cada request, el middleware `HandleInertiaRequests` comparte:

```php
[
    'auth' => [
        'user' => [
            'tema_preferido' => 'jovenes',
            'modo_contraste' => 'normal',
            // ... otros campos
        ]
    ],
    'timeMode' => 'day',          // 'day' o 'night'
    'currentHour' => 14,          // Hora actual en Bolivia
]
```

## Próximas Mejoras

- [ ] Implementar sistema de accesibilidad (tamaño de fuente)
- [ ] Añadir más temas personalizables
- [ ] Permitir usuarios crear temas personalizados
- [ ] Añadir animaciones de transición entre temas
- [ ] Modo de alto contraste mejorado para accesibilidad
- [ ] Detectar preferencia de modo oscuro del sistema operativo

## Testing

### Probar Temas Manualmente

1. Iniciar servidor: `php artisan serve`
2. Iniciar Vite: `npm run dev`
3. Registrarse o iniciar sesión
4. Hacer clic en el icono de paleta en la barra de navegación
5. Seleccionar diferentes temas
6. Alternar modo día/noche manual
7. Verificar persistencia al recargar página

### Probar Modo Automático

1. Modificar hora del sistema para estar antes de 6 AM o después de 6 PM
2. Recargar la aplicación
3. Verificar que se aplique modo oscuro automáticamente
4. Modificar hora del sistema entre 6 AM - 6 PM
5. Recargar la aplicación
6. Verificar que se aplique modo claro automáticamente

## Solución de Problemas

### Los temas no se aplican

1. Verificar que `resources/css/themes.css` esté importado en `app.js`
2. Verificar que Pinia esté correctamente instalado y configurado
3. Revisar consola del navegador por errores
4. Limpiar caché de navegador y localStorage

### Las preferencias no se guardan

1. Verificar que el endpoint `/api/user/theme-preferences` esté en el grupo `auth` middleware
2. Verificar que el usuario esté autenticado
3. Revisar Network tab para ver si la petición POST se envía correctamente
4. Verificar que los campos `tema_preferido` y `modo_contraste` existan en la tabla `usuarios`

### El modo automático no funciona

1. Verificar que el servidor tenga la zona horaria correcta (`America/La_Paz`)
2. Revisar `config/app.php` para confirmar `timezone => 'America/La_Paz'`
3. Verificar que `HandleInertiaRequests` esté compartiendo `timeMode`
4. Revisar que el componente reciba correctamente los props de Inertia

## Recursos

- [Pinia Documentation](https://pinia.vuejs.org/)
- [Vue 3 Composables](https://vuejs.org/guide/reusability/composables.html)
- [CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [Inertia.js Shared Data](https://inertiajs.com/shared-data)
