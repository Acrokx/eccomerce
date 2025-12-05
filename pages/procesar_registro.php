<?php
// Paso 1: Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: ". $e->getMessage());
}

// Paso 3: Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']); // Limpiar espacios
    $email = trim($_POST['email']);
    $password_plain = $_POST['password'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password_plain)) {
        $error = "Todos los campos son obligatorios";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email no válido";
    } elseif (strlen($password_plain) < 6) {
        $error = "La contraseña debe tener mínimo 6 caracteres";
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Este email ya está registrado";
        } else {
            // Encriptar contraseña y guardar usuario
            $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, tipo_usuario)
            VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$nombre, $email, $password_hash, $tipo_usuario])) {
                $success = "Usuario registrado exitosamente";
            } else {
                $error = "Error al registrar usuario";
            }
        }
    }
}

// Mostrar resultados
if (isset($error)) {
    echo "<p style='color: red;'>$error</p>";
} elseif (isset($success)) {
    echo "<p style='color: green;'>$success</p>";
}
?>