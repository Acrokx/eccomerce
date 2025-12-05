<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Agregar columna fecha_creacion si no existe
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    // Agregar columna activo si no existe
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS activo TINYINT(1) DEFAULT 1");

    // Agregar columna categoria si no existe
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS categoria VARCHAR(100)");

    // Agregar columna unidad_medida si no existe
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS unidad_medida VARCHAR(50)");

    // Agregar columna certificacion_organica si no existe
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS certificacion_organica TINYINT(1) DEFAULT 0");

    // Agregar columna imagen si no existe
    $pdo->exec("ALTER TABLE productos ADD COLUMN IF NOT EXISTS imagen VARCHAR(255)");

    echo "Tabla 'productos' actualizada exitosamente.";
} catch (PDOException $e) {
    echo "Error al actualizar la tabla: " . $e->getMessage();
}
?>