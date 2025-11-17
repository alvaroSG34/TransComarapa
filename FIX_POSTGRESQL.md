# 🔧 Solución: Habilitar Extensión PostgreSQL en PHP

## ❌ Problema

```
could not find driver (Connection: pgsql)
```

PHP no tiene habilitada la extensión PostgreSQL necesaria para conectarse a la base de datos.

## ✅ Solución

### Paso 1: Abrir php.ini

Tu archivo php.ini está en: **`C:\xampp\php\php.ini`**

1. Abre este archivo con un editor de texto (como Notepad++ o VS Code)
2. Busca estas dos líneas (usa Ctrl + F para buscar):

```ini
;extension=pdo_pgsql
;extension=pgsql
```

### Paso 2: Descomentar las Extensiones

Elimina el punto y coma `;` al inicio de ambas líneas:

**ANTES:**
```ini
;extension=pdo_pgsql
;extension=pgsql
```

**DESPUÉS:**
```ini
extension=pdo_pgsql
extension=pgsql
```

### Paso 3: Guardar y Reiniciar

1. **Guarda** el archivo php.ini
2. **Reinicia Apache** desde el panel de control de XAMPP
3. O si estás usando solo PHP CLI, simplemente cierra y abre una nueva terminal

### Paso 4: Verificar

Ejecuta en PowerShell:

```powershell
php -m | Select-String -Pattern "pgsql"
```

Deberías ver:
```
pdo_pgsql
pgsql
```

### Paso 5: Ejecutar Migraciones

Una vez habilitadas las extensiones:

```powershell
php artisan migrate
```

---

## 🚨 Si no encuentras las líneas en php.ini

Si las líneas `extension=pdo_pgsql` y `extension=pgsql` no existen en tu php.ini:

1. Busca la sección `[extension]` o donde veas otras extensiones como:
   ```ini
   extension=curl
   extension=mbstring
   ```

2. Agrega estas dos líneas en esa sección:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

---

## 🔍 Verificar que los DLL existan

Los archivos DLL deben existir en: `C:\xampp\php\ext\`

Verifica que existan:
- `php_pdo_pgsql.dll`
- `php_pgsql.dll`

Si **NO existen**, necesitas:
1. Descargar la versión correcta de PHP desde https://windows.php.net/download/
2. O actualizar tu instalación de XAMPP a una versión más reciente

---

## 📝 Notas Adicionales

- **XAMPP por defecto NO incluye drivers PostgreSQL** en algunas versiones
- Si los DLL no existen, considera usar **SQLite** temporalmente o instalar **PostgreSQL drivers** manualmente
- Alternativa: Usar **Laragon** que incluye PostgreSQL drivers por defecto

---

## 🔄 Alternativa: Usar SQLite para Desarrollo

Si tienes problemas con PostgreSQL, puedes usar SQLite temporalmente:

1. En tu archivo `.env`, cambia:
   ```env
   DB_CONNECTION=sqlite
   # Comenta las líneas de PostgreSQL
   # DB_HOST=127.0.0.1
   # DB_PORT=5432
   # DB_DATABASE=transcomarapa
   # DB_USERNAME=postgres
   # DB_PASSWORD=
   ```

2. Crea el archivo de base de datos:
   ```powershell
   New-Item -Path "database\database.sqlite" -ItemType File -Force
   ```

3. Ejecuta las migraciones:
   ```powershell
   php artisan migrate
   ```

**Nota**: SQLite es solo para desarrollo. Para producción, usa PostgreSQL como estaba planeado.
