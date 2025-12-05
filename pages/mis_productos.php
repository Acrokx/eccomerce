<?php
session_start();

// Verificar si el usuario está logueado y es agricultor
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'agricultor') {
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

// Procesar eliminación
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    // Verificar que el producto pertenece al agricultor
    $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ? AND agricultor_id = ?");
    $stmt->execute([$product_id, $_SESSION['usuario_id']]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        $stmt->execute([$product_id]);
        $success = "Producto eliminado exitosamente.";
    } else {
        $error = "Producto no encontrado.";
    }
}

// Obtener productos del agricultor
$stmt = $pdo->prepare("SELECT * FROM productos WHERE agricultor_id = ? AND activo = 1 ORDER BY fecha_creacion DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Mis Productos';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><i class="fas fa-boxes text-success"></i> Mis Productos</h1>
                <a href="agregar_producto.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Agregar Producto
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php elseif (isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (count($productos) > 0): ?>
                <div class="row">
                    <?php foreach ($productos as $producto): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card card-product h-100">
                                <?php if ($producto['imagen']): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>"
                                         class="card-img-top" alt="Imagen del producto"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                                         style="height: 200px; color: #6c757d;">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100)) . (strlen($producto['descripcion']) > 100 ? '...' : ''); ?>
                                    </p>

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h6 text-success mb-0">
                                                $<?php echo number_format($producto['precio'], 2); ?>
                                            </span>
                                            <small class="text-warning">
                                                <i class="fas fa-boxes"></i> Stock: <?php echo $producto['stock']; ?>
                                            </small>
                                        </div>

                                        <?php if ($producto['certificacion_organica']): ?>
                                            <span class="badge bg-success mb-2">
                                                <i class="fas fa-certificate"></i> Certificado Orgánico
                                            </span>
                                        <?php endif; ?>

                                        <div class="btn-group w-100" role="group">
                                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="mis_productos.php?delete=<?php echo $producto['id']; ?>"
                                               onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')"
                                               class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">No tienes productos registrados</h3>
                    <p class="text-muted">¡Comienza agregando tu primer producto orgánico!</p>
                    <a href="agregar_producto.php" class="btn btn-success btn-lg">
                        <i class="fas fa-plus"></i> Agregar Primer Producto
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>