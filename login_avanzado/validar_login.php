<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password_plain = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password_plain)) {
        header('Location: index.php?error=Email y contraseña son requeridos');
        exit();
    } else {
        $stmt = $pdo->prepare("SELECT id, nombre, email, password, tipo_usuario FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password_plain, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

            if ($remember) {
                setcookie('remember_email', $email, time() + 3600 * 24 * 30, '/');
            }

            if ($usuario['tipo_usuario'] == 'agricultor') {
                header("Location: ../dashboard_agricultor.php");
            } else {
                header("Location: ../dashboard_cliente.php");
            }
            exit;
        } else {
            header('Location: index.php?error=Email o contraseña incorrectos');
            exit();
        }
    }
} else {
    header('Location: index.php');
    exit();
}
?>