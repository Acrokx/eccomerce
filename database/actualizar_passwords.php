<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Actualizar passwords que son md5 a bcrypt
    $stmt = $pdo->query("SELECT id, password FROM usuarios WHERE password LIKE 'e10adc3949ba59abbe56e057f20f883e'");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        $nuevo_hash = password_hash('123456', PASSWORD_DEFAULT); // Asumiendo que la contraseña original era 123456
        $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$nuevo_hash, $usuario['id']]);
    }

    echo "Passwords actualizados exitosamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>