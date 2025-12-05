<?php
echo "<h1>Configuración del sistema</h1>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";
echo "<p>Servidor: " . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A') . "</p>";
echo "<p>Fecha actual: " . date('Y-m-d H:i:s') . "</p>";

// Prueba de conexión a base de datos
try {
    $conexion = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    echo "<p>✓ Conexión a base de datos: EXITOSA</p>";

    // Listar tablas
    echo "<h2>Tablas en la base de datos 'ecommerce_organico':</h2>";
    $stmt = $conexion->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";

    // Mostrar contenido de ejemplo de cada tabla
    echo "<h2>Contenido de ejemplo de las tablas:</h2>";
    foreach ($tablas as $tabla) {
        echo "<h3>Tabla: $tabla</h3>";
        try {
            $stmt = $conexion->query("SELECT * FROM $tabla LIMIT 5");
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($filas) > 0) {
                echo "<table border='1'>";
                // Cabeceras
                echo "<tr>";
                foreach (array_keys($filas[0]) as $columna) {
                    echo "<th>$columna</th>";
                }
                echo "</tr>";
                // Filas
                foreach ($filas as $fila) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>$valor</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No hay datos en esta tabla.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Error al consultar tabla $tabla: " . $e->getMessage() . "</p>";
        }
    }

} catch(PDOException $e) {
    echo "<p>✗ Error de conexión: " . $e->getMessage() . "</p>";
}
?>