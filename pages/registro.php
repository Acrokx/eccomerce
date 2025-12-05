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
    $telefono = trim($_POST['telefono']);

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

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, tipo_usuario, telefono)
            VALUES (?, ?, ?, ?, ?)");

            if ($stmt->execute([$nombre, $email, $password_hash, $tipo_usuario, $telefono])) {
                $success = "Usuario registrado exitosamente. <a href='login.php'>Iniciar sesión</a>";
            } else {
                $error = "Error al registrar usuario";
            }
        }
    }
}

$page_title = 'Registro de Usuario';
include '../includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-4">
                            <i class="fas fa-user-plus text-success"></i> Registro de Usuario
                        </h1>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php elseif (isset($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form action="registro.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-user"></i> Nombre completo
                                </label>
                                <input type="text" class="form-control form-control-lg" id="nombre" name="nombre"
                                       placeholder="Tu nombre completo" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Correo electrónico
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                       placeholder="tu@email.com" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Contraseña
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password"
                                       placeholder="Mínimo 6 caracteres" required>
                            </div>

                            <div class="mb-3">
                                <label for="tipo_usuario" class="form-label">
                                    <i class="fas fa-users"></i> Tipo de usuario
                                </label>
                                <select class="form-select form-select-lg" id="tipo_usuario" name="tipo_usuario" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="agricultor">Agricultor</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="telefono" class="form-label">
                                    <i class="fas fa-phone"></i> Teléfono (opcional)
                                </label>
                                <input type="tel" class="form-control form-control-lg" id="telefono" name="telefono"
                                       placeholder="Tu número de teléfono">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-user-plus"></i> Registrarse
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">¿Ya tienes cuenta?
                                <a href="login.php" class="text-success fw-bold">Inicia sesión aquí</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>