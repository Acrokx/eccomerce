<?php
session_start();
include 'config.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

$mensaje = '';
if (isset($_GET['error'])) {
    $mensaje = '<div class="error">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Avanzado - E-commerce Orgánicos</title>
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="container">
<h1>Login Avanzado - E-commerce Orgánicos</h1>
<?php echo $mensaje; ?>
<form action="validar_login.php" method="post" id="loginForm">
<div class="form-group">
<label for="email">Correo Electrónico:</label>
<input type="email" id="email" name="email" value="<?php echo isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : ''; ?>" required>
</div>
<div class="form-group">
<label for="password">Contraseña:</label>
<input type="password" id="password" name="password" required>
</div>
<div class="form-group">
<label>
<input type="checkbox" name="remember" id="remember"> Recordarme
</label>
</div>
<button type="submit">Iniciar Sesión</button>
</form>
<div class="links">
<a href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
</div>
</div>
<script src="js/validaciones.js"></script>
</body>
</html>