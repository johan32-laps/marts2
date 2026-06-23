# Guía de Instalación — MARTS

## Requisitos

| Componente | Versión mínima |
|-----------|---------------|
| PHP | 8.1+ |
| MySQL / MariaDB | 5.7+ / 10.4+ |
| Servidor web | Apache (Laragon) |
| Laragon | Cualquier versión reciente |

---

## Instalación Paso a Paso

### 1. Colocar el proyecto

Copia la carpeta `marts2` dentro de:
```
C:\laragon\www\marts2\
```

### 2. Verificar la configuración del servidor

Laragon genera automáticamente un VirtualHost. Verifica en:
```
C:\laragon\etc\apache2\sites-enabled\auto.marts2.test.conf
```

Debe tener:
```apache
<VirtualHost *:8081>
    DocumentRoot "C:/laragon/www/marts2"
    ServerName marts2.test
    ...
</VirtualHost>
```

> Si el DocumentRoot apunta a `/public`, cámbialo a la raíz del proyecto.

### 3. Iniciar Laragon

Abre Laragon y haz clic en **Start All** para iniciar Apache y MySQL.

### 4. Crear la base de datos

Abre **HeidiSQL** o **phpMyAdmin** y ejecuta:

```sql
CREATE DATABASE bdinventario
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar el Setup

Abre el navegador y accede a:
```
http://localhost:8081/marts2/setup.php
```

El setup creará automáticamente:
- Todas las tablas
- Los 2 roles (admin, empleado)
- Los 6 tipos de movimiento
- Los usuarios base
- Las categorías y productos de ejemplo

### 6. Acceder al sistema

```
http://localhost:8081/marts2/views/usuarios/login.php
```

**Credenciales por defecto:**
```
Admin:    admin@marts.com    / admin123
Empleado: empleado@marts.com / empleado123
```

> ⚠️ Cambia las contraseñas después del primer login desde **Usuarios → Seguridad**.

### 7. Tablas adicionales (si el setup no las creó)

Si las tablas de ventas, caja, etc. no existen, ejecuta manualmente en HeidiSQL:

```
Archivo: sql/tablas_nuevas.sql
```

---

## Configuración de la Base de Datos

Si usas un puerto diferente al 3320, edita:

```
c:\laragon\www\marts2\config\database.php
```

```php
private string $host     = '127.0.0.1';
private int    $port     = 3320;          // ← cambiar aquí
private string $db_name  = 'bdinventario';
private string $username = 'root';
private string $password = '';            // ← si tiene contraseña
```

---

## URLs del Sistema

| Página | URL |
|--------|-----|
| Login | `http://localhost:8081/marts2/views/usuarios/login.php` |
| Registro | `http://localhost:8081/marts2/views/usuarios/registro.php` |
| Setup | `http://localhost:8081/marts2/setup.php` |
| Dashboard Admin | `http://localhost:8081/marts2/views/dashboard/admin.php` |
| Dashboard Empleado | `http://localhost:8081/marts2/views/dashboard/empleado.php` |

---

## Estructura de Directorios de Imágenes

Las imágenes de productos se guardan en:
```
public/img/productos/
```

El nombre del archivo es generado automáticamente:
```php
$newName = md5(uniqid('', true)) . '.' . $ext;
// Ejemplo: a3f8c2e1d4b7.jpg
```

El directorio debe tener permisos de escritura (755 en Linux, en Windows Laragon lo maneja automáticamente).

---

## Solución de Problemas Comunes

### "Not Found" al acceder

- Verifica que Laragon esté corriendo
- Verifica que el DocumentRoot en el VirtualHost apunte a `C:/laragon/www/marts2` (sin `/public`)
- Reinicia Apache en Laragon

### Error de conexión a BD

- Verifica que MySQL esté corriendo en Laragon
- Confirma el puerto en `config/database.php` (Laragon usa 3320, no 3306)
- Verifica que la BD `bdinventario` exista

### CSS no carga

- Las rutas del CSS son relativas: `../../public/css/style.css`
- Accede directamente a la vista: `localhost:8081/marts2/views/usuarios/login.php`
- Verifica que `public/css/style.css` existe y tiene contenido

### Error PDO "Invalid parameter number"

Ya corregido en `Caja::abrir()`. Si aparece en otro modelo, verifica que no uses el mismo placeholder dos veces en el mismo `execute()`.
