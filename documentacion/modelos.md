# Documentación de Modelos — MARTS

Todos los modelos extienden la conexión PDO definida en `config/database.php`.
Usan consultas preparadas con `bindParam` / `bindValue` para prevenir SQL injection.

---

## Database (`config/database.php`)

Clase singleton de conexión PDO.

```php
new Database()->connect()
// Retorna: PDO con ERRMODE_EXCEPTION, FETCH_ASSOC, sin emulación de prepares
// Host: 127.0.0.1 | Puerto: 3320 | BD: bdinventario
```

---

## Usuario

| Método | Parámetros | Retorna | Descripción |
|--------|-----------|---------|-------------|
| `login($correo, $password)` | string, string | array\|false | Valida con `password_verify()`, retorna usuario + rol |
| `getUsuarios()` | — | array | Todos los usuarios con JOIN a rol |
| `getRoles()` | — | array | Lista de roles disponibles |
| `getById($id)` | int | array\|false | Usuario por ID |
| `getByCorreo($correo)` | string | array\|false | Busca por email (para validar unicidad) |
| `crear($nombre,$correo,$password,$id_rol)` | string×4 | bool | Hash con `password_hash(PASSWORD_DEFAULT)` |
| `actualizar($id,$nombre,$correo,$id_rol)` | int+string×3 | bool | Actualiza datos básicos |
| `cambiarPassword($id,$nuevaPassword)` | int, string | bool | Rehashea y guarda |
| `eliminar($id)` | int | bool | Borrado lógico (`estado=0`) |
| `reactivar($id)` | int | bool | Reactiva cuenta (`estado=1`) |
| `contarActivos()` | — | int | Total de usuarios activos |

**Nota:** La columna de contraseña es `password` (no `contraseña`).

---

## Producto

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `listarProductos()` | array | JOIN con categoría, solo `estado=1` |
| `obtenerCategorias()` | array | Para selectores en formularios |
| `registrar(nombre,precio,stock,id_cat,imagen)` | bool | Crea producto con `stock_minimo=5` por defecto |
| `obtenerPorId($id)` | array\|false | Producto por ID |
| `actualizar($id,nombre,precio,id_cat,imagen)` | bool | `imagen` es opcional, si es null no la actualiza |
| `eliminar($id)` | bool | Borrado lógico + elimina movimientos (transacción) |
| `buscar($termino)` | array | LIKE en nombre y categoría |
| `obtenerStockCritico($limite=5)` | array | Productos con stock < límite |

**Campos extra en BD:** `precio_compra`, `precio_venta`, `tamano`, `codigo_barras`, `descripcion`

---

## Movimiento

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `obtenerTipos()` | array | Lista de tipos ordenados por operación |
| `listarMovimientos($limite=50)` | array | JOIN con producto, usuario y tipo. Usa `COALESCE(tm.operacion, m.tipo)` para compatibilidad con datos legacy |
| `registrar($id_prod,$id_tipo,$cant,$motivo,$id_usr,$detalles)` | bool | Transacción: valida tipo → inserta movimiento → inserta detalle opcional → actualiza stock |
| `obtenerReporte($fi,$ff,$id_tipo,$id_prod)` | array | Filtros opcionales con WHERE dinámico |
| `obtenerResumenSemanal()` | array | Agrupado por día, últimos 7 días para gráficas |

---

## Venta

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `registrar($id_usr,$id_caja,$metodo,$items,$obs)` | int\|false | Transacción completa: valida stock → crea venta → detalle → descuenta stock → registra movimiento → registra en caja si efectivo |
| `listar($limite=50)` | array | Ventas recientes con nombre de operador |
| `getById($id)` | array\|false | Venta por ID |
| `getDetalle($id_venta)` | array | Items de la venta con nombre de producto |
| `totalHoy()` | float | Suma de ventas del día actual |
| `contarHoy()` | int | Número de transacciones del día |
| `reportePorPeriodo($desde,$hasta)` | array | Para reportes filtrados |

**Validación crítica:** Antes de insertar, verifica que cada producto tenga stock suficiente. Si falla uno, hace `rollBack()`.

---

## Compra

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `registrar($id_usr,$proveedor,$items,$obs)` | int\|false | Transacción: crea compra → detalle → aumenta stock → registra movimiento tipo "entrada" |
| `listar($limite=50)` | array | Compras recientes |
| `getById($id)` | array\|false | Compra por ID |
| `getDetalle($id_compra)` | array | Items de la compra |
| `reportePorPeriodo($desde,$hasta)` | array | Para reportes |

---

## Caja

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `getCajaAbierta()` | array\|false | La caja con `estado='abierta'`, o false |
| `abrir($id_usuario,$saldo_inicial)` | bool | Verifica que no haya caja abierta. Crea caja con `saldo_inicial = saldo_teorico` |
| `cerrar($id_caja,$saldo_final,$justificacion)` | bool | Calcula diferencia, guarda fecha_cierre, cambia estado a 'cerrada' |
| `registrarMovimiento($id_caja,$tipo,$monto,$concepto,$id_venta)` | bool | Transacción: inserta en `movimiento_caja` + actualiza `saldo_teorico` |
| `movimientos($id_caja)` | array | Movimientos del día para mostrar en la vista |
| `historial($limite=20)` | array | Historial de cajas cerradas |

---

## Devolucion

| Método | Retorna | Descripción |
|--------|---------|-------------|
| `registrar($id_venta,$id_usuario,$motivo,$items)` | int\|false | Transacción: valida venta → valida cantidades → crea devolución → reintegra stock → movimiento → egreso de caja si era efectivo |
| `listar($limite=50)` | array | Devoluciones recientes |
| `getById($id)` | array\|false | Devolución por ID |
| `getDetalle($id_dev)` | array | Items de la devolución |

**Validación crítica:** No permite devolver más unidades de las que aparecen en el `detalle_venta` original.

---

## Log

| Método | Descripción |
|--------|-------------|
| `registrar($id_usuario,$accion,$entidad,$detalles)` | Guarda una entrada de auditoría |
| `listar()` | Retorna logs + ejecuta limpieza de registros > 7 días |
| `limpiarAntiguos()` | DELETE automático de registros con más de 7 días |
