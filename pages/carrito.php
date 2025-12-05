<?php
session_start();

// Verificar si el usuario está logueado y es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

// Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: ". $e->getMessage());
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Procesar acciones del carrito
if (isset($_POST['accion'])) {
    $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

    switch ($_POST['accion']) {
        case 'agregar':
            // Verificar que el producto existe y hay stock
            $stmt = $pdo->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = ? AND activo = 1");
            $stmt->execute([$producto_id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto && $producto['stock'] >= $cantidad) {
                // Si el producto ya está en el carrito, sumar cantidad
                if (isset($_SESSION['carrito'][$producto_id])) {
                    $nueva_cantidad = $_SESSION['carrito'][$producto_id]['cantidad'] + $cantidad;
                    if ($nueva_cantidad <= $producto['stock']) {
                        $_SESSION['carrito'][$producto_id]['cantidad'] = $nueva_cantidad;
                        $mensaje = "Cantidad actualizada en el carrito";
                    } else {
                        $error = "No hay suficiente stock disponible";
                    }
                } else {
                    // Agregar nuevo producto al carrito
                    $_SESSION['carrito'][$producto_id] = array(
                        'id' => $producto['id'],
                        'nombre' => $producto['nombre'],
                        'precio' => $producto['precio'],
                        'cantidad' => $cantidad,
                        'stock_disponible' => $producto['stock']
                    );
                    $mensaje = "Producto agregado al carrito";
                }
            } else {
                $error = "Producto no disponible o stock insuficiente";
            }
            break;

        case 'actualizar':
            if (isset($_SESSION['carrito'][$producto_id])) {
                if ($cantidad > 0 && $cantidad <= $_SESSION['carrito'][$producto_id]['stock_disponible']) {
                    $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
                    $mensaje = "Cantidad actualizada";
                } else {
                    $error = "Cantidad no válida";
                }
            }
            break;

        case 'remover':
            if (isset($_SESSION['carrito'][$producto_id])) {
                unset($_SESSION['carrito'][$producto_id]);
                $mensaje = "Producto removido del carrito";
            }
            break;

        case 'vaciar':
            $_SESSION['carrito'] = array();
            $mensaje = "Carrito vaciado";
            break;
    }
}

// Calcular total del carrito
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Ecommerce Orgánico</title>
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
        .cart-empty {
            text-align: center;
            color: #7f8c8d;
            font-size: 18px;
            margin-top: 50px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
            background-color: #ecf0f1;
        }
        .item-details {
            flex: 1;
        }
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .item-price {
            color: #27ae60;
            font-weight: bold;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        .quantity-controls input {
            width: 60px;
            padding: 5px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .quantity-controls button {
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .quantity-controls button:hover {
            background-color: #2980b9;
        }
        .remove-button {
            background-color: #e74c3c !important;
        }
        .remove-button:hover {
            background-color: #c0392b !important;
        }
        .cart-total {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: right;
        }
        .cart-total h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .checkout-button {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .checkout-button:hover {
            background-color: #229954;
        }
        .empty-cart-button {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .empty-cart-button:hover {
            background-color: #7f8c8d;
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
        <h1>Carrito de Compras</h1>
    </div>

    <div class="content">
        <div class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php elseif (isset($mensaje)): ?>
            <div class="message success"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="cart-empty">
                <p>Tu carrito está vacío.</p>
                <a href="catalogo.php">Ir al catálogo</a>
            </div>
        <?php else: ?>
            <?php foreach ($_SESSION['carrito'] as $producto_id => $item): ?>
                <div class="cart-item">
                    <img src="uploads/default.jpg" alt="Producto" class="item-image">
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['nombre']); ?></div>
                        <div class="item-price">$<?php echo number_format($item['precio'], 2); ?> cada uno</div>
                        <div class="quantity-controls">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
                                <button type="submit" name="cantidad" value="<?php echo max(1, $item['cantidad'] - 1); ?>">-</button>
                                <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1" max="<?php echo $item['stock_disponible']; ?>" readonly>
                                <button type="submit" name="cantidad" value="<?php echo min($item['stock_disponible'], $item['cantidad'] + 1); ?>">+</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="accion" value="remover">
                                <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
                                <button type="submit" class="remove-button">Remover</button>
                            </form>
                        </div>
                        <div>Subtotal: $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cart-total">
                <h3>Total: $<?php echo number_format($total, 2); ?></h3>
                <a href="checkout.php" class="checkout-button" style="display: inline-block; text-decoration: none; text-align: center;">Proceder al Pago</a>
            </div>

            <div style="text-align: center;">
                <form method="POST">
                    <input type="hidden" name="accion" value="vaciar">
                    <button type="submit" class="empty-cart-button" onclick="return confirm('¿Estás seguro de que quieres vaciar el carrito?')">Vaciar Carrito</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="catalogo.php">Continuar Comprando</a>
        </div>
    </div>
</body>
</html>