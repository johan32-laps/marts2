# Seguridad del Sistema — MARTS

## 1. Autenticación

### Contraseñas
```php
// Almacenamiento: bcrypt (PASSWORD_DEFAULT, cost=10)
password_hash($password, PASSWORD_DEFAULT)

// Verificación:
password_verify($inputPassword, $storedHash)

// Nunca se almacena la contraseña en texto plano
```

### Sesiones
```php
// Al hacer login exitoso:
session_regenerate_id(true);  // Previene session fixation

// Al hacer logout:
$_SESSION = [];
session_destroy();
// + invalidar cookie de sesión
setcookie(session_name(), '', time() - 42000, ...);
```

### Protección de rutas
```php
// Cada vista y controlador verifica la sesión:
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

// Rutas solo para admin:
if (!in_array($_SESSION['rol'], ['admin','administrador'])) {
    header("Location: ../dashboard/empleado.php"); exit;
}
```

---

## 2. Consultas a Base de Datos

### Prepared Statements (PDO)
```php
// CORRECTO — previene SQL injection:
$stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = :correo");
$stmt->bindParam(':correo', $correo);
$stmt->execute();

// NUNCA hacer:
$conn->query("SELECT * FROM usuario WHERE correo = '$correo'"); // PELIGROSO
```

### Configuración PDO
```php
PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION  // Lanza excepciones
PDO::ATTR_EMULATE_PREPARES   => false                   // Prepared statements reales
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        // Arrays asociativos
```

---

## 3. Protección XSS

```php
// Siempre escapar output:
echo htmlspecialchars($valor);
echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');

// En atributos HTML con JSON:
echo htmlspecialchars(json_encode($array));
```

---

## 4. Validación de Inputs

```php
// Correo:
filter_var($correo, FILTER_VALIDATE_EMAIL)

// Enteros:
intval($_POST['cantidad'])
(int)$_POST['id']

// Flotantes:
(float)$_POST['precio']

// Strings limpios:
trim($_POST['nombre'] ?? '')

// Longitud mínima:
strlen($password) >= 6
```

---

## 5. Subida de Archivos (Imágenes)

```php
// Validaciones en ProductoController:
1. $_FILES['imagen']['error'] === UPLOAD_ERR_OK
2. Extensión en lista blanca: ['jpg','jpeg','png','gif','webp']
3. Tamaño < 5MB
4. getimagesize() verifica que sea imagen real
5. Nombre generado: md5(uniqid('', true)) + extensión
   (previene path traversal y sobreescritura)
6. move_uploaded_file() solo para archivos subidos vía POST
```

---

## 6. Acciones Protegidas

| Acción | Protección |
|--------|-----------|
| Eliminar producto | Solo admin. Borrado lógico, no físico |
| Gestionar usuarios | Solo admin. No puede desactivarse a sí mismo |
| Cambiar propio rol | Bloqueado: admin no puede quitarse su propio rol |
| Abrir caja | Verifica que no haya otra abierta |
| Cerrar caja | Requiere justificación si hay diferencia |
| Devolución | Valida cantidades contra venta original |

---

## 7. Gestión de Errores

```php
// Los modelos usan try/catch y rollBack:
try {
    $this->conn->beginTransaction();
    // ... operaciones ...
    $this->conn->commit();
    return true;
} catch (Exception $e) {
    $this->conn->rollBack();
    error_log('Modelo::metodo — ' . $e->getMessage()); // Log en servidor
    return false; // No expone detalles al usuario
}
```

**Los mensajes de error al usuario son genéricos** — no revelan detalles de la BD.

---

## 8. Recomendaciones para Producción

- Cambiar contraseñas por defecto inmediatamente
- Configurar HTTPS
- Eliminar o restringir acceso a `setup.php` después de instalar
- Configurar `error_reporting(0)` y `display_errors = Off` en `php.ini`
- Implementar rate limiting en el login
- Hacer respaldos periódicos de la BD
