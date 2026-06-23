# Arquitectura del Sistema — MARTS

## Patrón MVC Manual

MARTS implementa el patrón **Modelo-Vista-Controlador** sin framework, con PHP puro.

```
Navegador
    │
    ▼
Vista PHP (views/)
    │  include layouts/sidebar.php
    │  include layouts/header.php
    │  <form action="../../controllers/XController.php">
    │
    ▼
Controlador (controllers/)
    │  session_start()
    │  Validación de sesión y rol
    │  Instancia el Modelo
    │  Procesa $_POST / $_GET
    │  $_SESSION['mensaje'] = '...'
    │  header("Location: ../views/...")
    │
    ▼
Modelo (models/)
    │  new Database() → PDO
    │  Consultas preparadas
    │  Transacciones cuando aplica
    │  return array | bool | int
    │
    ▼
Base de Datos MySQL (bdinventario)
```

## Flujo de una Solicitud POST

1. El usuario llena un formulario en la **Vista**
2. El form hace `POST` al **Controlador**
3. El controlador valida sesión (`$_SESSION['id_usuario']`)
4. Valida inputs con `trim()`, `filter_var()`, `intval()`
5. Instancia el **Modelo** y llama al método correspondiente
6. El modelo ejecuta una **query preparada** PDO
7. El controlador guarda el resultado en `$_SESSION['exito/error']`
8. Redirige de vuelta a la Vista con `header("Location: ...")`
9. La Vista lee y muestra el mensaje de sesión

## Rutas del Sistema

```
Todas las vistas están en: views/xxx/archivo.php
Los controladores están en: controllers/

Desde una vista (2 niveles de profundidad):
  ../../controllers/AuthController.php   → ir al controlador
  ../../public/css/style.css             → cargar CSS
  ../dashboard/admin.php                 → ir a otra vista
  ../../public/img/productos/xxx.png     → cargar imagen

Desde un controlador (1 nivel):
  ../views/usuarios/login.php            → redirigir
  ../views/dashboard/admin.php           → redirigir
```

## Gestión de Sesiones

```php
// Datos guardados en sesión al hacer login:
$_SESSION['id_usuario'] = 1;
$_SESSION['usuario']    = 'Administrador';
$_SESSION['rol']        = 'admin';  // 'admin' o 'empleado'

// Protección en cada vista:
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

// Protección por rol:
if (!in_array($_SESSION['rol'], ['admin','administrador'])) {
    header("Location: ../dashboard/empleado.php"); exit;
}
```

## Transacciones Atómicas

Las operaciones que modifican múltiples tablas usan transacciones PDO:

```php
// Ejemplo en Venta::registrar()
$this->conn->beginTransaction();
try {
    // 1. Insertar venta
    // 2. Por cada producto: insertar detalle + actualizar stock + movimiento
    // 3. Si pago en efectivo: registrar en caja
    $this->conn->commit();
    return $id_venta;
} catch (Exception $e) {
    $this->conn->rollBack();
    return false;
}
```

Esto garantiza que si falla cualquier paso, todo se revierte.
