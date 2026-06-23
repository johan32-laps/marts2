# MARTS — Sistema de Gestión de Inventario
## Documentación Técnica v2.1

---

## Tabla de Contenidos

1. [Descripción General](#1-descripción-general)
2. [Tecnologías Utilizadas](#2-tecnologías-utilizadas)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Base de Datos](#4-base-de-datos)
5. [Configuración e Instalación](#5-configuración-e-instalación)
6. [Arquitectura MVC](#6-arquitectura-mvc)
7. [Módulos del Sistema](#7-módulos-del-sistema)
8. [Flujo de Autenticación](#8-flujo-de-autenticación)
9. [Sistema de Diseño](#9-sistema-de-diseño)
10. [Seguridad](#10-seguridad)
11. [Credenciales por Defecto](#11-credenciales-por-defecto)

---

## 1. Descripción General

**MARTS** es un sistema web de gestión de inventario desarrollado para tiendas. Permite controlar productos, stock, ventas, compras, devoluciones y caja mediante una interfaz moderna con diseño verde/beige/dorado.

### Funcionalidades principales
- Autenticación con roles (admin / empleado)
- CRUD completo de productos con imágenes
- Control de inventario con movimientos de entrada/salida
- Registro de ventas con validación de stock en tiempo real
- Registro de compras que actualiza stock automáticamente
- Gestión de caja: apertura, cierre y arqueo con justificación de diferencias
- Devoluciones sobre ventas existentes con reintegro de stock
- Reportes exportables (CSV y vista de impresión)
- Historial de auditoría con retención de 7 días
- Sidebar colapsable con estado persistente en localStorage

---

## 2. Tecnologías Utilizadas

| Capa | Tecnología |
|------|-----------|
| Servidor | PHP 8.1 |
| Base de datos | MySQL / MariaDB (PDO) |
| Frontend | HTML5, CSS3, JavaScript Vanilla |
| Gráficas | Chart.js 4.4 |
| Tipografía | Google Fonts (Inter + Plus Jakarta Sans) |
| Servidor local | Laragon (Apache + MySQL puerto 3320) |

---

## 3. Estructura del Proyecto

```
marts2/
│
├── config/
│   └── database.php          # Conexión PDO a MySQL
│
├── controllers/              # Lógica de negocio y redirecciones
│   ├── AuthController.php    # Login y logout
│   ├── RegistroController.php# Registro de nuevos usuarios
│   ├── ProductoController.php# CRUD de productos + subida de imágenes
│   ├── MovimientoController.php # Movimientos de inventario
│   ├── VentaController.php   # Registro de ventas
│   ├── CompraController.php  # Registro de compras
│   ├── CajaController.php    # Apertura/cierre/movimientos de caja
│   ├── DevolucionController.php # Devoluciones de clientes
│   ├── CategoriaController.php  # CRUD de categorías
│   ├── TipoMovimientoController.php # CRUD de tipos de movimiento
│   ├── UsuarioController.php # Gestión de usuarios (admin)
│   └── ReporteController.php # Exportación CSV y PDF
│
├── models/                   # Acceso a datos (PDO)
│   ├── Usuario.php
│   ├── Producto.php
│   ├── Movimiento.php
│   ├── Venta.php
│   ├── Compra.php
│   ├── Caja.php
│   ├── Devolucion.php
│   ├── Categoria.php
│   ├── TipoMovimiento.php
│   └── Log.php
│
├── views/
│   ├── layouts/              # Componentes reutilizables
│   │   ├── sidebar.php       # Navegación lateral colapsable
│   │   ├── header.php        # Topbar con breadcrumb y perfil
│   │   └── footer.php        # Pie de página + carga de app.js
│   ├── usuarios/
│   │   ├── login.php         # Página de inicio de sesión
│   │   ├── registro.php      # Registro de nuevos usuarios
│   │   └── index.php         # Gestión de usuarios (solo admin)
│   ├── dashboard/
│   │   ├── admin.php         # Dashboard del administrador
│   │   ├── empleado.php      # Panel operativo del empleado
│   │   ├── adminproductos.php# Catálogo de productos con búsqueda
│   │   └── historial.php     # Auditoría del sistema
│   ├── ventas/index.php      # Historial y registro de ventas
│   ├── compras/index.php     # Historial y registro de compras
│   ├── caja/index.php        # Control de caja diaria
│   ├── devoluciones/index.php# Registro de devoluciones
│   ├── movimientos/index.php # Historial de movimientos
│   ├── categorias/index.php  # Gestión de categorías
│   ├── tipos/index.php       # Tipos de movimiento
│   └── reportes/
│       ├── index.php         # Generador de reportes
│       └── imprimir.php      # Vista de impresión/PDF
│
├── public/
│   ├── css/style.css         # Sistema de diseño completo (~32KB)
│   ├── js/app.js             # Scripts globales
│   └── img/
│       ├── icon.png
│       └── productos/        # Imágenes subidas de productos
│
├── sql/
│   ├── bdinventario.sql      # Schema original de la BD
│   ├── tablas_nuevas.sql     # Tablas adicionales (venta, caja, etc.)
│   └── datos_iniciales.sql   # Datos de ejemplo y usuarios base
│
├── index.php                 # Punto de entrada raíz
├── setup.php                 # Instalador del sistema
└── documentacion/            # Esta carpeta
```

---

## 4. Base de Datos

**Nombre:** `bdinventario`  
**Puerto:** `3320` (Laragon)  
**Motor:** InnoDB con charset utf8mb4

### Diagrama de tablas

```
rol ──────────────────┐
  id_rol (PK)         │
  nombre              │
  descripcion         │         usuario
                      └──────── id_usuario (PK)
                                nombre
                                apellido
                                correo (UNIQUE)
                                password (bcrypt)
                                telefono
                                foto
                                id_rol (FK → rol)
                                estado
                                created_at

categoria                       producto
  id_categoria (PK)    ┌──────  id_producto (PK)
  nombre               │        nombre
  descripcion          │        descripcion
  created_at           │        codigo_barras
                       │        precio
                       │        precio_compra
                       │        precio_venta
                       └─────── id_categoria (FK)
                                stock
                                stock_minimo
                                tamano
                                imagen
                                estado
                                created_at

tipo_movimiento                 movimiento
  id_tipo_movimiento (PK) ─┐    id_movimiento (PK)
  nombre                   │    id_producto (FK → producto)
  operacion (entrada/salida)└─── id_tipo_movimiento (FK)
  contexto                      id_usuario (FK → usuario)
                                tipo (entrada/salida)
                                cantidad
                                stock_anterior
                                stock_nuevo
                                motivo
                                fecha

                                detalle_movimiento
                                  id_detalle (PK)
                                  id_movimiento (FK)
                                  comentarios_tecnicos
                                  ubicacion_almacen
                                  referencia_externa

caja                            movimiento_caja
  id_caja (PK)          ┌─────  id_mov_caja (PK)
  id_usuario (FK)       │       id_caja (FK → caja)
  saldo_inicial         │       tipo (ingreso/egreso)
  saldo_teorico         │       monto
  saldo_final           │       concepto
  diferencia            └─────  id_venta (nullable)
  justificacion                 fecha
  estado (abierta/cerrada)
  fecha_apertura
  fecha_cierre

venta                           detalle_venta
  id_venta (PK)          ┌────  id_detalle (PK)
  id_usuario (FK)        │      id_venta (FK → venta)
  id_caja (FK)           │      id_producto (FK)
  metodo_pago            └────  cantidad
  total                         precio_venta
  estado                        subtotal
  observaciones
  fecha

compra                          detalle_compra
  id_compra (PK)         ┌────  id_detalle (PK)
  id_usuario (FK)        │      id_compra (FK → compra)
  proveedor              └────  id_producto (FK)
  total                         cantidad
  observaciones                 precio_compra
  fecha                         subtotal

devolucion                      detalle_devolucion
  id_devolucion (PK)     ┌────  id_detalle (PK)
  id_venta (FK → venta)  │      id_devolucion (FK)
  id_usuario (FK)        └────  id_producto (FK)
  motivo                        cantidad
  total_devolucion              precio_unitario
  fecha                         subtotal

log
  id_log (PK)
  id_usuario (FK)
  accion
  entidad
  detalles
  fecha
```


---

## 5. Configuración e Instalación

Ver [instalacion.md](instalacion.md) para instrucciones detalladas.

**Resumen:**
1. Colocar en `C:\laragon\www\marts2\`
2. Crear BD `bdinventario` en MySQL
3. Abrir `http://localhost:8081/marts2/setup.php`
4. Login: `admin@marts.com / admin123`

---

## 6. Arquitectura MVC

Ver [arquitectura.md](arquitectura.md) para el flujo completo.

```
Vista → Controlador → Modelo → MySQL
         ↑ session  ↑ PDO
```

**Rutas relativas desde vistas:**
```
../../controllers/   →  controladores
../../public/css/    →  estilos
../dashboard/        →  otra vista del mismo nivel
```

---

## 7. Módulos del Sistema

| Módulo | Vista | Controlador | Roles |
|--------|-------|------------|-------|
| Login | `views/usuarios/login.php` | `AuthController.php` | Todos |
| Registro | `views/usuarios/registro.php` | `RegistroController.php` | Todos |
| Dashboard Admin | `views/dashboard/admin.php` | — | Admin |
| Dashboard Empleado | `views/dashboard/empleado.php` | — | Empleado |
| Productos | `views/dashboard/adminproductos.php` | `ProductoController.php` | Todos |
| Ventas | `views/ventas/index.php` | `VentaController.php` | Todos |
| Compras | `views/compras/index.php` | `CompraController.php` | Admin |
| Caja | `views/caja/index.php` | `CajaController.php` | Todos |
| Devoluciones | `views/devoluciones/index.php` | `DevolucionController.php` | Admin |
| Movimientos | `views/movimientos/index.php` | `MovimientoController.php` | Todos |
| Categorías | `views/categorias/index.php` | `CategoriaController.php` | Admin |
| Tipos Mov. | `views/tipos/index.php` | `TipoMovimientoController.php` | Admin |
| Usuarios | `views/usuarios/index.php` | `UsuarioController.php` | Admin |
| Reportes | `views/reportes/index.php` | `ReporteController.php` | Todos |
| Historial | `views/dashboard/historial.php` | — | Admin |

Ver [controladores.md](controladores.md) y [vistas.md](vistas.md) para detalles.

---

## 8. Flujo de Autenticación

```
login.php → POST → AuthController
                       │
                       ├── bcrypt verify OK
                       │   └── $_SESSION[id_usuario, usuario, rol]
                       │       └── redirect dashboard según rol
                       │
                       └── FAIL → $_SESSION['error'] → login.php
```

Ver [flujos.md](flujos.md) para todos los flujos del sistema.

---

## 9. Sistema de Diseño

**Paleta de colores:**
```css
--green-dark: #2d5a3d   /* Verde oscuro — sidebar, botones secundarios */
--gold:       #c8832a   /* Dorado — botón principal, links activos */
--beige:      #f5f0e8   /* Beige — fondo general */
--white:      #ffffff   /* Blanco — cards, paneles */
```

**Componentes CSS principales:**
- `.stat-card` — Tarjetas de estadísticas con animación hover
- `.dark-table-wrap` — Tablas con thead verde
- `.slide-panel` — Paneles laterales deslizantes
- `.btn-primary` (dorado), `.btn-green` (verde), `.btn-secondary` (blanco)
- `.pill-*` — Badges de colores
- `.form-input` — Inputs con fondo beige y foco verde
- `.login-card` — Diseño bicolor login

**Sidebar:**
- Toggle: clic en el logo MARTS
- Estado persistido en `localStorage`
- Anti-flash: CSS aplicado antes del render

---

## 10. Seguridad

Ver [seguridad.md](seguridad.md) para detalles completos.

- ✅ Contraseñas con bcrypt (`password_hash/verify`)
- ✅ Prepared statements PDO (anti SQL injection)
- ✅ `htmlspecialchars()` en todo output (anti XSS)
- ✅ `session_regenerate_id()` al login
- ✅ Protección de rutas por sesión y rol
- ✅ Validación de archivos subidos con `getimagesize()`
- ✅ Transacciones atómicas con rollback

---

## 11. Credenciales por Defecto

| Usuario | Correo | Contraseña | Rol |
|---------|--------|-----------|-----|
| Administrador | admin@marts.com | admin123 | admin |
| Juan Empleado | empleado@marts.com | empleado123 | empleado |

> ⚠️ Cambiar contraseñas después de la instalación.

---

## Archivos de Documentación

| Archivo | Contenido |
|---------|-----------|
| [README.md](README.md) | Visión general y resumen completo |
| [instalacion.md](instalacion.md) | Guía de instalación paso a paso |
| [arquitectura.md](arquitectura.md) | Patrón MVC y flujo de solicitudes |
| [modelos.md](modelos.md) | Métodos de cada modelo con parámetros |
| [controladores.md](controladores.md) | Lógica de cada controlador |
| [vistas.md](vistas.md) | Vistas, componentes y sistema de diseño |
| [flujos.md](flujos.md) | Diagramas de flujo de cada módulo |
| [seguridad.md](seguridad.md) | Implementaciones de seguridad |
