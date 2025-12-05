<?php
session_start();

// Paso 1: Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: ". $e->getMessage());
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password_plain = $_POST['password'];

    if (empty($email) || empty($password_plain)) {
        $error = "Email y contraseña son requeridos";
    } else {
        // Buscar usuario en la base de datos
        $stmt = $pdo->prepare("SELECT id, nombre, email, password, tipo_usuario FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password_plain, $usuario['password'])) {
            // Login exitoso - guardar datos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            // Redirigir según el tipo de usuario
            if ($usuario['tipo_usuario'] == 'agricultor') {
                header("Location: dashboard_agricultor.php");
            } else {
                header("Location: dashboard_cliente.php");
            }
            exit;
        } else {
            $error = "Email o contraseña incorrectos";
        }
    }
}

$page_title = 'Inicio de Sesión';
include '../includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-4">
                            <i class="fas fa-sign-in-alt text-success"></i> Inicio de Sesión
                        </h1>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Correo electrónico
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                       placeholder="tu@email.com" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Contraseña
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password"
                                       placeholder="Tu contraseña" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">¿No tienes cuenta?
                                <a href="registro.php" class="text-success fw-bold">Regístrate aquí</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>