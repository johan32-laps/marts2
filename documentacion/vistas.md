# Documentación de Vistas — MARTS

---

## Layouts (Componentes Reutilizables)

### sidebar.php

Sidebar colapsable con navegación por rol.

**Variables que usa:**
- `$_SESSION['rol']` — determina qué secciones mostrar
- `$_SESSION['usuario']` — nombre en el footer del sidebar
- `$currentPage` y `$currentDir` — para marcar el link activo

**Colapso:**
- Clic en el **brand MARTS** (logo + texto) → `toggleSidebar()`
- Estado persistido en `localStorage.getItem('sidebarCollapsed')`
- Anti-flash: script al inicio añade `sidebar-is-collapsed` al `<html>` antes de render
- CSS: `html.sidebar-is-collapsed .main-content { margin-left: 72px }`

**Menú por rol:**
```
Admin ve:    Dashboard, Ventas, Caja, Productos, Movimientos, Categorías,
             Compras, Devoluciones, Tipos Mov., Usuarios, Reportes, Historial
Empleado ve: Dashboard, Ventas, Caja, Productos, Movimientos, Categorías
```

### header.php

Topbar fijo con breadcrumb, notificaciones, fecha y perfil.

- Detecta el título de la página con `basename($_SERVER['PHP_SELF'])` y `dirname()`
- Botón hamburguesa visible en pantallas < 900px (móvil)
- Botón de logout rápido en la barra

### footer.php

Pie de página + carga de `public/js/app.js` al final del `<body>`.

---

## Vistas de Usuarios

### login.php

Diseño dividido en dos paneles:
- **Izquierdo** (verde oscuro): Bienvenida, logo, mensaje de seguridad
- **Derecho** (blanco): Formulario con validación en tiempo real

**Validación JavaScript:**
```javascript
// Regex email en tiempo real
/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)

// Password mínimo 6 caracteres
input.value.length >= 6

// Submit: deshabilita botón y muestra "Verificando..."
```

**Campos del form:**
- `correo` → tipo email, autocomplete="email"
- `password` → tipo password con toggle ver/ocultar
- `remember` → checkbox (no implementado en backend, visual)

**Action:** `../../controllers/AuthController.php` (POST)

### registro.php

Formulario de dos columnas con indicador de fortaleza de contraseña:

```javascript
// Niveles: 1=muy débil, 2=débil, 3=regular, 4=buena, 5=muy segura
// Factores: longitud>=6, >=10, mayúsculas, números, especiales
```

**Campos:** nombre, apellido, correo, telefono, password, confirm_password, id_rol (select)

### usuarios/index.php

Solo admin. Muestra tabla con 3 stat cards (Total, Activos, Inactivos).

**Panels laterales:** Crear, Editar, Cambiar Contraseña
**Acciones:** Editar datos, cambiar contraseña, activar/desactivar (no a sí mismo)

---

## Dashboard Admin (`dashboard/admin.php`)

**8 stat cards** en `grid-template-columns:repeat(4,1fr)`:
1. Ventas Hoy (total en $)
2. Caja (estado + saldo teórico)
3. Productos (total en catálogo)
4. Stock Total (unidades)
5. Categorías
6. Usuarios activos
7. Stock Crítico (< 5 uds.)
8. Agotados (stock = 0)

**Gráfica:** Line chart con Chart.js, entradas (verde) vs salidas (rojo)

**Panel Stock Crítico:** Lista de productos con stock bajo, con indicador de color

**Tabla:** Últimos 8 movimientos de inventario

**Panel lateral:** Registrar movimiento de inventario

---

## Dashboard Empleado (`dashboard/empleado.php`)

**6 stat cards** en `grid-template-columns:repeat(3,1fr)`:
1. Ventas Hoy
2. Caja (estado)
3. Catálogo (número de productos)
4. Stock Global
5. Stock Crítico
6. Acceso Rápido (botones: Nueva Venta, Caja)

**Gráfica:** Bar chart con Chart.js

**Panel lateral:** Registrar movimiento

---

## Módulos Operativos

### ventas/index.php

**3 stat cards:** Ventas Hoy (count), Total Hoy ($), Estado Caja

**Alerta:** Si no hay caja abierta, muestra aviso y deshabilita opción de efectivo

**Panel lateral "Nueva Venta":**
- Select de método de pago (efectivo bloqueado sin caja)
- Filas dinámicas de productos con JavaScript:
  ```javascript
  // addProductRow() clona <template> y añade al DOM
  // updatePrice() auto-completa precio al seleccionar producto
  // calcTotal() suma subtotales en tiempo real
  ```
- `<template id="productRowTpl">` para clonar filas
- Total calculado en tiempo real

### compras/index.php

Similar a ventas pero sin validación de caja. Filas dinámicas con `addCompraRow()`.

### caja/index.php

**3 secciones:**
1. **Caja activa** (card verde con saldo teórico, si existe)
2. **Movimientos del día** (tabla de ingresos/egresos)
3. **Historial de cajas** (tabla con estado, fechas, diferencias)

**3 Panels laterales:**
- **Abrir caja:** Input de saldo inicial
- **Cerrar caja:** Input de saldo final + cálculo de diferencia en tiempo real + justificación condicional
- **Movimiento manual:** Tipo (ingreso/egreso), monto, concepto

### devoluciones/index.php

**Panel "Nueva Devolución":**
- Select de venta existente
- Al seleccionar venta: carga automáticamente los productos del `detalle_venta` via `ventasData` (JSON embebido)
- Permite ajustar cantidades a devolver (max = cantidad original)
- Total calculado en tiempo real

---

## adminproductos.php

**Búsqueda en tiempo real** sin recargar la página:
```javascript
// Filtra filas con data-nombre y data-categoria
function searchProducts(term) {
  allRows.forEach(row => {
    var match = nombre.includes(q) || categoria.includes(q);
    row.style.display = match ? '' : 'none';
  });
}
```

**Dos panels laterales:** Crear producto y Editar producto

**Preview de imagen:** FileReader API para mostrar preview antes de subir

---

## reportes/index.php

**Filtros:** fecha_inicio, fecha_fin, tipo de movimiento, producto específico

**Al filtrar:** Muestra 3 stat cards de resumen + tabla de resultados

**Exportar:** Links a `ReporteController.php?action=excel` y `?action=pdf`

---

## Sistema de Diseño CSS

El archivo `public/css/style.css` (~32KB) implementa un sistema de diseño completo:

### Variables Globales
```css
--green-dark:  #2d5a3d  /* Verde principal */
--green-mid:   #3a7a52  /* Verde medio */
--gold:        #c8832a  /* Dorado principal */
--gold-hover:  #b5721f  /* Dorado hover */
--beige:       #f5f0e8  /* Fondo principal */
--white:       #ffffff
```

### Clases Principales
| Clase | Descripción |
|-------|-------------|
| `.app-layout` | Flex container del layout principal |
| `.main-content` | Área de contenido, `margin-left` con transición |
| `.sidebar` | Barra lateral con `width` animado |
| `.stat-card` | Tarjeta de estadística con hover |
| `.dark-table-wrap` | Contenedor de tabla con thead verde |
| `.slide-panel` | Panel lateral deslizante |
| `.panel-overlay` | Overlay semi-transparente |
| `.modal-backdrop` | Fondo de modal centrado |
| `.btn-primary` | Botón dorado principal |
| `.btn-green` | Botón verde |
| `.pill-*` | Badges de estado coloreados |
| `.form-input` | Input con fondo beige y foco verde |
| `.alert-*` | Alertas de éxito/error/warning/info |
