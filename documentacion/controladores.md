# Documentación de Controladores — MARTS

Todos los controladores siguen el mismo patrón:
1. `session_start()` al inicio
2. Verificar `$_SESSION['id_usuario']` (autenticación)
3. Verificar rol si aplica
4. Leer `$_GET['action']` o `$_SERVER['REQUEST_METHOD']`
5. Instanciar modelos necesarios
6. Procesar y guardar mensaje en `$_SESSION`
7. Redirigir con `header("Location: ...")` + `exit`

---

## AuthController

**Archivo:** `controllers/AuthController.php`

| Acción | Método HTTP | Descripción |
|--------|------------|-------------|
| `?action=logout` (GET) | GET | Destruye sesión, borra cookie, redirige a login |
| *(ninguna)* | POST | Ejecuta login |

### Login
```
POST /controllers/AuthController.php
Body: correo, password

1. Valida campos vacíos y formato de email
2. Busca usuario en BD con JOIN a rol (WHERE correo=? AND estado=1)
3. password_verify($password, $user['password'])
4. Si OK: session_regenerate_id(true) + guarda sesión
5. Redirige según rol: admin→admin.php | empleado→empleado.php
6. Si FAIL: $_SESSION['error'] + redirect login
```

---

## RegistroController

**Archivo:** `controllers/RegistroController.php`

```
POST /controllers/RegistroController.php

Valida: nombre, correo (filter_var EMAIL), password>=6, confirmar==password, id_rol>0
Verifica unicidad del correo: getByCorreo($correo)
Detecta columnas disponibles (apellido, telefono) para INSERT dinámico
Si OK: hace login automático y redirige al dashboard
```

---

## ProductoController

**Archivo:** `controllers/ProductoController.php`  
**Redirect:** `views/dashboard/adminproductos.php`

| Acción | Descripción |
|--------|-------------|
| `registrar` | Valida nombre/precio/categoría. Procesa imagen con `getimagesize()` + `move_uploaded_file()`. Nombre de archivo: `md5(uniqid())` |
| `editar` | Igual que registrar pero actualiza. Imagen es opcional |
| `eliminar` | Solo admins. Borrado lógico del producto |

**Seguridad imagen:**
- Verifica extensión (jpg, jpeg, png, gif, webp)
- Verifica tamaño (< 5MB)
- Verifica que sea imagen real con `getimagesize()`
- Guarda en `public/img/productos/`

---

## MovimientoController

**Archivo:** `controllers/MovimientoController.php`

```
POST ?action=registrar

1. Lee id_producto, id_tipo_movimiento, cantidad, motivo
2. Lee $_POST['redirect'] para saber a dónde redirigir
3. Verifica stock si es salida
4. Llama Movimiento::registrar()
5. Registra en Log
6. Redirige a dashboard o movimientos según 'redirect'
```

---

## VentaController

**Archivo:** `controllers/VentaController.php`

```
POST ?action=registrar

1. Lee metodo_pago, observaciones, arrays: productos[], cantidades[], precios[]
2. Si metodo_pago==='efectivo': verifica caja abierta (bloquea si no hay)
3. Construye $items = [['id_producto','cantidad','precio_venta'], ...]
4. Llama Venta::registrar() con transacción atómica
5. Si OK: registra en Log, $_SESSION['exito_venta']
6. Redirige a ventas/index.php
```

---

## CompraController

**Archivo:** `controllers/CompraController.php`  
**Solo admins**

```
POST ?action=registrar

Similar a VentaController pero:
- No requiere caja abierta
- Llama Compra::registrar()
- El modelo aumenta stock automáticamente
```

---

## CajaController

**Archivo:** `controllers/CajaController.php`

| Acción | Descripción |
|--------|-------------|
| `abrir` | Valida saldo_inicial >= 0. Verifica que no haya caja abierta. Llama `Caja::abrir()` |
| `cerrar` | Lee saldo_final y justificacion. Calcula diferencia. Si diferencia != 0 y no hay justificación: error. Llama `Caja::cerrar()` |
| `movimiento` | Valida tipo (ingreso/egreso), monto > 0, concepto no vacío. Llama `Caja::registrarMovimiento()` |

---

## DevolucionController

**Archivo:** `controllers/DevolucionController.php`  
**Solo admins**

```
POST ?action=registrar

1. Valida id_venta > 0, motivo no vacío, productos[] no vacío
2. Construye $items con id_producto, cantidad, precio_unitario
3. Llama Devolucion::registrar() (valida cantidades contra venta original)
4. El modelo reintegra stock y hace egreso de caja si aplica
```

---

## CategoriaController

**Archivo:** `controllers/CategoriaController.php`  
**Solo admins**

| Acción | Descripción |
|--------|-------------|
| `crear` | Valida nombre único (`existeNombre()`). Registra en Log |
| `editar` | Valida nombre único excluyendo el propio ID |
| `eliminar` | Solo si `total_productos == 0` |

---

## UsuarioController

**Archivo:** `controllers/UsuarioController.php`  
**Solo admins**

| Acción | Descripción |
|--------|-------------|
| `crear` | Valida email único, password >= 6. Llama `Usuario::crear()` |
| `editar` | No permite que admin se quite su propio rol |
| `desactivar` | No permite desactivar la propia cuenta |
| `reactivar` | Reactiva usuario inactivo |
| `cambiar_password` | Password >= 6. Rehashea con bcrypt |

---

## ReporteController

**Archivo:** `controllers/ReporteController.php`

| Acción | Descripción |
|--------|-------------|
| `excel` | Genera CSV con BOM UTF-8 para Excel. `Content-Disposition: attachment` |
| `pdf` | Incluye `views/reportes/imprimir.php` para vista de impresión |

---

## TipoMovimientoController

**Archivo:** `controllers/TipoMovimientoController.php`  
**Solo admins**

Gestiona tipos de movimiento (crear/editar/eliminar).
No permite eliminar si el tipo tiene movimientos asociados.
