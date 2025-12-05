<?php
session_start();

// Verificar si el usuario está logueado y es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

// Verificar que el carrito no esté vacío
if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

// Incluir clase Notificador
require_once '../includes/Notificador.php';

// Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: ". $e->getMessage());
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Procesar el pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_pedido'])) {
    $direccion_entrega = trim($_POST['direccion_entrega']);
    $metodo_pago = $_POST['metodo_pago'];
    $notas = trim($_POST['notas']);

    if (empty($direccion_entrega)) {
        $error = "La dirección de entrega es requerida";
    } else {
        try {
            // Iniciar transacción para garantizar consistencia
            $pdo->beginTransaction();

            // Crear el pedido principal
            $stmt = $pdo->prepare("
            INSERT INTO pedidos (cliente_id, total, direccion_entrega, metodo_pago, notas, estado, fecha_pedido)
            VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())
            ");
            $stmt->execute([$_SESSION['usuario_id'], $total, $direccion_entrega, $metodo_pago, $notas]);
            $pedido_id = $pdo->lastInsertId();

            // Agregar detalles del pedido y actualizar stock
            foreach ($_SESSION['carrito'] as $item) {
                // Insertar detalle del pedido
                $stmt = $pdo->prepare("
                INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $pedido_id,
                    $item['id'],
                    $item['cantidad'],
                    $item['precio'],
                    $item['precio'] * $item['cantidad']
                ]);

                // Actualizar stock del producto
                $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['cantidad'], $item['id']]);
            }

            // Confirmar transacción
            $pdo->commit();

            // Enviar notificaciones usando la clase Notificador
            $notificador = new Notificador($pdo);
            $notificador->enviarConfirmacionPedido($pedido_id);

            // Aquí se pueden agregar más notificaciones (a agricultores, etc.)

            // Vaciar carrito y redirigir
            $_SESSION['carrito'] = array();
            $success = "¡Pedido realizado exitosamente! Número de pedido: ". $pedido_id;

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $pdo->rollback();
            $error = "Error al procesar el pedido: ". $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ecommerce Orgánico</title>
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
        .order-summary {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .order-summary h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #bdc3c7;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            color: #27ae60;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .checkout-button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
        }
        .checkout-button:hover {
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
        <h1>Finalizar Compra</h1>
    </div>

    <div class="content">
        <div class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
            <div class="back-link">
                <a href="dashboard_cliente.php">Volver al Dashboard</a>
            </div>
        <?php else: ?>
            <div class="order-summary">
                <h3>Resumen del Pedido</h3>
                <?php foreach ($_SESSION['carrito'] as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['nombre']); ?> (x<?php echo $item['cantidad']; ?>)</span>
                        <span>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="order-item total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>

            <form action="checkout.php" method="POST">
                <div class="form-group">
                    <label for="direccion_entrega">Dirección de Entrega:</label>
                    <textarea id="direccion_entrega" name="direccion_entrega" required placeholder="Ingresa tu dirección completa"></textarea>
                </div>

                <div class="form-group">
                    <label for="metodo_pago">Método de Pago:</label>
                    <select id="metodo_pago" name="metodo_pago" required>
                        <option value="">Selecciona un método</option>
                        <option value="efectivo">Efectivo contra entrega</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="tarjeta">Tarjeta de crédito/débito</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notas">Notas adicionales (opcional):</label>
                    <textarea id="notas" name="notas" placeholder="Instrucciones especiales de entrega, etc."></textarea>
                </div>

                <button type="submit" name="confirmar_pedido" class="checkout-button">Confirmar Pedido</button>
            </form>

            <div class="back-link">
                <a href="carrito.php">Volver al Carrito</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>