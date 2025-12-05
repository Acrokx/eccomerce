<?php
session_start();

// Verificar si el usuario está logueado y es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

$pedido_id = isset($_GET['pedido_id']) ? intval($_GET['pedido_id']) : 0;

if (!$pedido_id) {
    die("ID de pedido no válido");
}

// Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: ". $e->getMessage());
}

// Obtener detalles del pedido
$stmt = $pdo->prepare("
SELECT pd.*, p.nombre as producto_nombre, p.agricultor_id, u.nombre as agricultor_nombre
FROM pedido_detalles pd
JOIN productos p ON pd.producto_id = p.id
JOIN usuarios u ON p.agricultor_id = u.id
JOIN pedidos pe ON pd.pedido_id = pe.id
WHERE pd.pedido_id = ? AND pe.cliente_id = ? AND pe.estado = 'entregado'
");
$stmt->execute([$pedido_id, $_SESSION['usuario_id']]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($detalles)) {
    die("Pedido no encontrado o no se puede valorar aún");
}

// Procesar valoraciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        foreach ($detalles as $detalle) {
            $producto_id = $detalle['producto_id'];
            $agricultor_id = $detalle['agricultor_id'];

            $calificacion_producto = intval($_POST['calificacion_producto_' . $producto_id]);
            $calificacion_agricultor = intval($_POST['calificacion_agricultor_' . $agricultor_id]);
            $comentario_producto = trim($_POST['comentario_producto_' . $producto_id]);
            $comentario_agricultor = trim($_POST['comentario_agricultor_' . $agricultor_id]);

            // Verificar que no existe valoración previa
            $stmt = $pdo->prepare("SELECT id FROM valoraciones WHERE cliente_id = ? AND producto_id = ? AND pedido_id = ?");
            $stmt->execute([$_SESSION['usuario_id'], $producto_id, $pedido_id]);

            if ($stmt->rowCount() == 0) {
                // Insertar nueva valoración
                $stmt = $pdo->prepare("
                INSERT INTO valoraciones (cliente_id, producto_id, agricultor_id, pedido_id, calificacion_producto, calificacion_agricultor, comentario_producto, comentario_agricultor)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['usuario_id'],
                    $producto_id,
                    $agricultor_id,
                    $pedido_id,
                    $calificacion_producto,
                    $calificacion_agricultor,
                    $comentario_producto,
                    $comentario_agricultor
                ]);

                // Actualizar promedio del producto
                $stmt = $pdo->prepare("
                UPDATE productos
                SET calificacion_promedio = (SELECT AVG(calificacion_producto) FROM valoraciones WHERE producto_id = ?),
                    total_valoraciones = (SELECT COUNT(*) FROM valoraciones WHERE producto_id = ?)
                WHERE id = ?
                ");
                $stmt->execute([$producto_id, $producto_id, $producto_id]);

                // Actualizar promedio del agricultor
                $stmt = $pdo->prepare("
                UPDATE usuarios
                SET calificacion_promedio = (SELECT AVG(calificacion_agricultor) FROM valoraciones WHERE agricultor_id = ?),
                    total_valoraciones = (SELECT COUNT(*) FROM valoraciones WHERE agricultor_id = ?)
                WHERE id = ?
                ");
                $stmt->execute([$agricultor_id, $agricultor_id, $agricultor_id]);
            }
        }

        $pdo->commit();
        $success = "Valoraciones guardadas exitosamente!";

    } catch (Exception $e) {
        $pdo->rollback();
        $error = "Error al guardar valoraciones: ". $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorar Pedido - Ecommerce Orgánico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background-color: #3498db;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logout {
            text-align: right;
            margin-bottom: 20px;
        }
        .logout a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }
        .logout a:hover {
            text-decoration: underline;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .product-rating {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .product-rating h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .stars {
            display: flex;
            gap: 5px;
            margin: 10px 0;
        }
        .star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
        }
        .star.selected {
            color: #f39c12;
        }
        .comment {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
            box-sizing: border-box;
        }
        .submit-button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }
        .submit-button:hover {
            background-color: #229954;
        }
        .back-link {
            margin-top: 20px;
            text-align: center;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Valorar Pedido #<?php echo $pedido_id; ?></h1>
    </div>

    <div class="content">
        <div class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php else: ?>
            <form action="valorar_pedido.php?pedido_id=<?php echo $pedido_id; ?>" method="POST">
                <?php
                $agricultores_valorados = [];
                foreach ($detalles as $detalle):
                ?>
                    <div class="product-rating">
                        <h3><?php echo htmlspecialchars($detalle['producto_nombre']); ?> (x<?php echo $detalle['cantidad']; ?>)</h3>

                        <label>Calificación del producto:</label>
                        <div class="stars" data-name="calificacion_producto_<?php echo $detalle['producto_id']; ?>">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" data-value="<?php echo $i; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="calificacion_producto_<?php echo $detalle['producto_id']; ?>" value="0">

                        <label>Comentario del producto:</label>
                        <textarea name="comentario_producto_<?php echo $detalle['producto_id']; ?>" class="comment" placeholder="Deja tu comentario sobre el producto..."></textarea>

                        <?php if (!in_array($detalle['agricultor_id'], $agricultores_valorados)): ?>
                            <hr>
                            <h4>Valorar Agricultor: <?php echo htmlspecialchars($detalle['agricultor_nombre']); ?></h4>

                            <label>Calificación del agricultor:</label>
                            <div class="stars" data-name="calificacion_agricultor_<?php echo $detalle['agricultor_id']; ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?php echo $i; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="calificacion_agricultor_<?php echo $detalle['agricultor_id']; ?>" value="0">

                            <label>Comentario del agricultor:</label>
                            <textarea name="comentario_agricultor_<?php echo $detalle['agricultor_id']; ?>" class="comment" placeholder="Deja tu comentario sobre el agricultor..."></textarea>

                            <?php $agricultores_valorados[] = $detalle['agricultor_id']; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="submit-button">Enviar Valoraciones</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="dashboard_cliente.php">Volver al Dashboard</a>
        </div>
    </div>

    <script>
        // JavaScript para las estrellas interactivas
        document.querySelectorAll('.stars').forEach(function(starsContainer) {
            const stars = starsContainer.querySelectorAll('.star');
            const hiddenInput = document.querySelector('input[name="' + starsContainer.dataset.name + '"]');

            stars.forEach(function(star, index) {
                star.addEventListener('click', function() {
                    const value = index + 1;
                    hiddenInput.value = value;

                    // Actualizar visualización
                    stars.forEach(function(s, i) {
                        if (i < value) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>