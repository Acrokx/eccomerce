<?php
session_start();
include 'config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $mensaje = '<div class="error">Ingresa un correo electrónico válido.</div>';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $mensaje = '<div class="success">Se ha enviado un enlace de recuperación a tu correo electrónico.</div>';
        } else {
            $mensaje = '<div class="error">Correo electrónico no encontrado.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña - E-commerce Orgánicos</title>
<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="container">
<h1>Recuperar Contraseña</h1>
<?php echo $mensaje; ?>
<form action="recuperar_password.php" method="post">
<div class="form-group">
<label for="email">Correo Electrónico:</label>
<input type="email" id="email" name="email" required>
</div>
<button type="submit">Enviar Enlace de Recuperación</button>
</form>
<div class="links">
<a href="index.php">Volver al Login</a>
</div>
</div>
</body>
</html>