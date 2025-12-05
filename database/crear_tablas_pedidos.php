<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla pedidos
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        direccion_entrega TEXT NOT NULL,
        metodo_pago VARCHAR(50) NOT NULL,
        notas TEXT,
        estado VARCHAR(50) DEFAULT 'pendiente',
        fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

    // Crear tabla pedido_detalles
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedido_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
        FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
    )");

    echo "Tablas 'pedidos' y 'pedido_detalles' creadas exitosamente.";
} catch (PDOException $e) {
    echo "Error al crear las tablas: " . $e->getMessage();
}
?>