<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla valoraciones
    $pdo->exec("CREATE TABLE IF NOT EXISTS valoraciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        producto_id INT NOT NULL,
        agricultor_id INT NOT NULL,
        pedido_id INT NOT NULL,
        calificacion_producto INT NOT NULL CHECK (calificacion_producto BETWEEN 1 AND 5),
        calificacion_agricultor INT NOT NULL CHECK (calificacion_agricultor BETWEEN 1 AND 5),
        comentario_producto TEXT,
        comentario_agricultor TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
        FOREIGN KEY (agricultor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
    )");

    // Agregar columnas de calificación a productos
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS calificacion_promedio DECIMAL(3,2) DEFAULT 0");
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS total_valoraciones INT DEFAULT 0");

    // Agregar columnas de calificación a usuarios
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS calificacion_promedio DECIMAL(3,2) DEFAULT 0");
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS total_valoraciones INT DEFAULT 0");

    echo "Tabla 'valoraciones' creada y columnas agregadas exitosamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>