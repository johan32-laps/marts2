# MARTS — Sistema de Gestión de Inventario

Sistema web profesional de gestión de inventario con diseño dark glassmorphism.

## Stack

- **Frontend:** HTML5, CSS3 (custom dark theme), TailwindCDN, Chart.js
- **Backend:** PHP 8.1+ (MVC manual, PDO)
- **Base de datos:** MySQL (stockcontrol)
- **Servidor:** Laragon / XAMPP

---

## Instalación rápida

### 1. Crear la base de datos

En phpMyAdmin o MySQL CLI:
```sql
CREATE DATABASE stockcontrol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configurar conexión

Edita `config/database.php` si es necesario:
```php
private $host     = "localhost";
private $db_name  = "stockcontrol";
private $username = "root";
private $password = "";
```

### 3. Ejecutar el setup

Abre en el navegador:
```
http://localhost/marts2/setup.php
```

Esto crea todas las tablas, tipos de movimiento y el usuario admin.

### 4. Acceder al sistema

```
http://localhost/marts2/public/index.php
```

**Credenciales por defecto:**
- Email: `admin@marts.com`
- Password: `admin123`

> ⚠️ Cambia la contraseña después del primer login.

---

## Estructura del proyecto

```
marts2/
├── config/
│   └── database.php          # Configuración PDO
├── controllers/
│   ├── AuthController.php    # Login / Logout
│   ├── ProductoController.php
│   ├── MovimientoController.php
│   ├── CategoriaController.php
│   ├── UsuarioController.php
│   ├── TipoMovimientoController.php
│   └── ReporteController.php
├── models/
│   ├── Producto.php
│   ├── Movimiento.php
│   ├── Categoria.php
│   ├── Usuario.php
│   ├── TipoMovimiento.php
│   └── Log.php
├── views/
│   ├── layouts/
│   │   ├── sidebar.php       # Sidebar colapsable
│   │   ├── header.php        # Topbar
│   │   └── footer.php
│   ├── dashboard/
│   │   ├── admin.php         # Dashboard administrador
│   │   ├── empleado.php      # Panel operativo
│   │   ├── adminproductos.php
│   │   └── historial.php
│   ├── usuarios/
│   │   ├── login.php
│   │   └── index.php
│   ├── movimientos/index.php
│   ├── categorias/index.php
│   ├── tipos/index.php
│   └── reportes/
│       ├── index.php
│       └── imprimir.php
├── public/
│   ├── index.php             # Punto de entrada
│   ├── css/style.css         # Tema dark glassmorphism
│   └── img/productos/        # Imágenes de productos
├── sql/stockcontrol.sql      # Schema completo
└── setup.php                 # Instalador (eliminar en producción)
```

---

## Roles

| Rol | Permisos |
|-----|----------|
| **admin** | Acceso total: productos, usuarios, categorías, tipos, reportes, historial |
| **empleado** | Dashboard operativo, registrar movimientos, ver productos |

---

## Funcionalidades

- ✅ Login seguro con `password_hash` / `password_verify`
- ✅ Sidebar colapsable con estado persistente (localStorage)
- ✅ Dashboard con gráficas Chart.js (entradas/salidas semanales)
- ✅ CRUD completo de productos con imágenes
- ✅ Movimientos de inventario (entradas/salidas) con validación de stock
- ✅ Tipos de movimiento configurables
- ✅ Gestión de usuarios y roles
- ✅ Reportes con filtros + exportación CSV + vista de impresión PDF
- ✅ Historial de auditoría (auto-limpieza 7 días)
- ✅ Diseño dark glassmorphism responsive
- ✅ Consultas preparadas PDO (protección SQL injection)
- ✅ Transacciones atómicas en movimientos de stock
