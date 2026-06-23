# Flujos del Sistema — MARTS

## 1. Flujo de Login

```
Usuario → login.php
    │  POST correo + password
    ▼
AuthController::login()
    │  1. filter_var(email)
    │  2. SELECT u.*, r.nombre FROM usuario u
    │     LEFT JOIN rol r ON u.id_rol=r.id_rol
    │     WHERE correo=? AND estado=1
    │  3. password_verify($password, $user['password'])
    │  4. session_regenerate_id(true)
    │  5. $_SESSION[id_usuario, usuario, rol]
    ▼
Si rol='admin' → dashboard/admin.php
Si rol='empleado' → dashboard/empleado.php
Si falla → login.php con $_SESSION['error']
```

---

## 2. Flujo de Registro

```
Usuario → registro.php
    │  POST nombre, correo, password, confirm, id_rol
    ▼
RegistroController
    │  1. Validar campos obligatorios
    │  2. filter_var(email)
    │  3. strlen(password) >= 6
    │  4. password === confirm_password
    │  5. getByCorreo($correo) → verificar unicidad
    │  6. INSERT INTO usuario (con bcrypt hash)
    │  7. Login automático (misma lógica que AuthController)
    ▼
Redirige al dashboard según rol
```

---

## 3. Flujo de Venta

```
Empleado → ventas/index.php
    │  1. Selecciona productos y cantidades
    │  2. JavaScript calcula total en tiempo real
    │  3. Selecciona método de pago
    │     - Efectivo: requiere caja abierta (bloqueado si no hay)
    │     - Transferencia: siempre disponible
    │  4. POST a VentaController
    ▼
VentaController::registrar()
    │  1. Lee arrays: productos[], cantidades[], precios[]
    │  2. Verifica caja abierta si es efectivo
    │  3. Construye $items
    │  4. Llama Venta::registrar()
    ▼
Venta::registrar() [TRANSACCIÓN]
    │  1. Valida stock de cada producto
    │     → Si falla: Exception → rollBack → false
    │  2. INSERT INTO venta (total calculado)
    │  3. Por cada item:
    │     a. INSERT INTO detalle_venta
    │     b. UPDATE producto SET stock=stock-cantidad
    │     c. INSERT INTO movimiento (tipo='salida', id_tipo=4)
    │  4. Si efectivo y caja:
    │     a. INSERT INTO movimiento_caja (tipo='ingreso')
    │     b. UPDATE caja SET saldo_teorico=saldo_teorico+total
    │  5. commit() → return $id_venta
    ▼
$_SESSION['exito_venta'] → redirect ventas/index.php
```

---

## 4. Flujo de Compra

```
Admin → compras/index.php
    │  POST proveedor, productos[], cantidades[], precios[]
    ▼
CompraController::registrar()
    │  Construye $items, llama Compra::registrar()
    ▼
Compra::registrar() [TRANSACCIÓN]
    │  1. Calcula total
    │  2. INSERT INTO compra
    │  3. Por cada item:
    │     a. INSERT INTO detalle_compra
    │     b. UPDATE producto SET stock=stock+cantidad  ← AUMENTA
    │     c. INSERT INTO movimiento (tipo='entrada', id_tipo=1)
    │  4. commit()
    ▼
Stock actualizado automáticamente
```

---

## 5. Flujo de Caja

```
                  ┌─── Inicio de jornada
                  ▼
        CajaController::abrir()
            │  1. getCajaAbierta() → si existe: ERROR
            │  2. INSERT INTO caja (saldo_inicial, saldo_teorico=saldo_inicial)
            ▼
        Caja en estado 'abierta'
            │
            ├── Venta en efectivo → ingreso automático en movimiento_caja
            │                        + UPDATE caja SET saldo_teorico += total
            │
            ├── Movimiento manual → CajaController::movimiento()
            │                        + ajusta saldo_teorico
            │
            └── Fin de jornada
                    ▼
        CajaController::cerrar()
            │  1. Lee saldo_final (contado físicamente)
            │  2. diferencia = saldo_final - saldo_teorico
            │  3. Si diferencia != 0 y sin justificación: ERROR
            │  4. UPDATE caja SET saldo_final, diferencia, justificacion,
            │     estado='cerrada', fecha_cierre=NOW()
            ▼
        Caja cerrada con arqueo registrado
```

---

## 6. Flujo de Devolución

```
Admin → devoluciones/index.php
    │  1. Selecciona venta existente
    │  2. JavaScript carga productos de esa venta (ventasData JSON)
    │  3. Ajusta cantidades a devolver
    │  4. Ingresa motivo
    │  5. POST a DevolucionController
    ▼
DevolucionController::registrar()
    │  Valida id_venta, motivo, items
    │  Llama Devolucion::registrar()
    ▼
Devolucion::registrar() [TRANSACCIÓN]
    │  1. Verifica que venta existe y es 'completada'
    │  2. Por cada item: valida cant <= cant en detalle_venta original
    │  3. INSERT INTO devolucion
    │  4. Por cada item:
    │     a. INSERT INTO detalle_devolucion
    │     b. UPDATE producto SET stock=stock+cantidad  ← REINTEGRA STOCK
    │     c. INSERT INTO movimiento (tipo='entrada', id_tipo=2)
    │  5. Si venta original fue en efectivo y tiene caja:
    │     a. INSERT INTO movimiento_caja (tipo='egreso')
    │     b. UPDATE caja SET saldo_teorico=saldo_teorico-total
    │  6. commit()
```

---

## 7. Flujo de Movimiento de Inventario (directo)

```
Admin/Empleado → Dashboard → "Nueva Operación" / "Nuevo Movimiento"
    │  POST id_producto, id_tipo_movimiento, cantidad, motivo
    ▼
MovimientoController::registrar()
    │  1. Obtiene operacion (entrada/salida) del tipo_movimiento
    │  2. Si salida: verifica stock suficiente
    │  3. Llama Movimiento::registrar()
    ▼
Movimiento::registrar() [TRANSACCIÓN]
    │  1. INSERT INTO movimiento
    │  2. Si detalles (ubicacion, referencia): INSERT INTO detalle_movimiento
    │  3. UPDATE producto SET stock = stock ± cantidad
    │  4. commit()
```

---

## 8. Flujo de Sidebar Toggle

```
Usuario hace clic en el BRAND (logo MARTS)
    │
    ▼
toggleSidebar() en JavaScript
    │  1. s.classList.toggle('collapsed')
    │     → sidebar: width 260px ↔ 72px (CSS transition)
    │  2. c.classList.toggle('sidebar-collapsed')
    │     → .main-content: margin-left 260px ↔ 72px (CSS transition)
    │  3. document.documentElement.classList.toggle('sidebar-is-collapsed')
    │     → html: para anti-flash en próxima carga
    │  4. localStorage.setItem('sidebarCollapsed', true/false)
    ▼
Al recargar la página:
    │  Script en sidebar.php al inicio del body:
    │  if(localStorage.getItem('sidebarCollapsed')==='true')
    │    document.documentElement.classList.add('sidebar-is-collapsed')
    │
    │  CSS aplica inmediatamente (antes de render visual):
    │  html.sidebar-is-collapsed .main-content { margin-left: 72px !important }
    │  html.sidebar-is-collapsed .sidebar { width: 72px }
    ▼
Sin flash de contenido al recargar
```

---

## 9. Flujo de Reporte

```
Admin → reportes/index.php
    │  GET ?filtrar=1&fecha_inicio=&fecha_fin=&id_tipo=&id_producto=
    ▼
Vista ejecuta Movimiento::obtenerReporte() con filtros dinámicos
    │  WHERE 1=1
    │    [AND DATE(fecha) >= fecha_inicio]
    │    [AND DATE(fecha) <= fecha_fin]
    │    [AND id_tipo_movimiento = id_tipo]
    │    [AND id_producto = id_producto]
    ▼
Muestra tabla de resultados + stat cards de resumen

Exportar CSV:
    GET /controllers/ReporteController.php?action=excel&[mismos filtros]
    → header('Content-Type: text/csv; charset=utf-8')
    → fprintf(BOM UTF-8) + fputcsv()

Exportar PDF:
    GET /controllers/ReporteController.php?action=pdf&[mismos filtros]
    → include views/reportes/imprimir.php
    → Vista optimizada para window.print()
```
