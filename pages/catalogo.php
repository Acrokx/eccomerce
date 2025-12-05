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

// Parámetros de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : "";
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : "";
$precio_max = isset($_GET['precio_max']) ? floatval($_GET['precio_max']) : 0;
$solo_certificados = isset($_GET['certificados']) ? 1 : 0;

// Obtener categorías disponibles
$stmt_categorias = $pdo->query("SELECT DISTINCT categoria FROM productos WHERE activo = 1 AND categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_COLUMN);

// Construir consulta SQL dinámicamente
$sql = "
SELECT p.*, u.nombre as agricultor_nombre, u.telefono as agricultor_telefono
FROM productos p
JOIN usuarios u ON p.agricultor_id = u.id
WHERE p.activo = 1 AND p.stock > 0
";

$params = [];

// Agregar filtros según parámetros
if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
}

if (!empty($categoria)) {
    $sql .= " AND p.categoria = ?";
    $params[] = $categoria;
}

if ($precio_max > 0) {
    $sql .= " AND p.precio <= ?";
    $params[] = $precio_max;
}

if ($solo_certificados) {
    $sql .= " AND p.certificacion_organica = 1";
}

$sql .= " ORDER BY p.fecha_creacion DESC";

// Ejecutar consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Catálogo de Productos';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2"><i class="fas fa-store text-success"></i> Catálogo de Productos Orgánicos</h1>
                <div>
                    <a href="carrito.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-shopping-cart"></i> Carrito (<?php echo count($_SESSION['carrito'] ?? []); ?>)
                    </a>
                    <a href="dashboard_cliente.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Filtros de búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="catalogo.php" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="q" class="form-label">Buscar productos</label>
                            <input type="text" class="form-control" id="q" name="q"
                                   placeholder="Nombre o descripción..."
                                   value="<?php echo htmlspecialchars($busqueda); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"
                                            <?php echo $categoria === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="precio_max" class="form-label">Precio máximo</label>
                            <input type="number" class="form-control" id="precio_max" name="precio_max"
                                   step="0.01" min="0" placeholder="Ej: 50000"
                                   value="<?php echo $precio_max > 0 ? $precio_max : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="certificados"
                                       name="certificados" value="1" <?php echo $solo_certificados ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="certificados">
                                    Solo productos orgánicos certificados
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Productos -->
            <?php if (count($productos) > 0): ?>
                <div class="row">
                    <?php foreach ($productos as $producto): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card card-product h-100">
                                <?php if (isset($producto['imagen']) && $producto['imagen']): ?>
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
                                        <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80)) . (strlen($producto['descripcion']) > 80 ? '...' : ''); ?>
                                    </p>

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-success mb-0">
                                                $<?php echo number_format($producto['precio'], 2); ?>
                                            </span>
                                            <small class="text-warning">
                                                <i class="fas fa-boxes"></i> Stock: <?php echo $producto['stock']; ?>
                                            </small>
                                        </div>

                                        <small class="text-muted d-block mb-3">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($producto['agricultor_nombre']); ?>
                                            <?php if ($producto['certificacion_organica']): ?>
                                                <span class="badge bg-success ms-1">
                                                    <i class="fas fa-certificate"></i> Certificado
                                                </span>
                                            <?php endif; ?>
                                        </small>

                                        <form method="POST" action="carrito.php">
                                            <input type="hidden" name="accion" value="agregar">
                                            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">Cant.</span>
                                                <input type="number" class="form-control" name="cantidad"
                                                       value="1" min="1" max="<?php echo $producto['stock']; ?>">
                                            </div>
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3 class="text-muted">No hay productos disponibles</h3>
                    <p class="text-muted">Intenta cambiar los filtros de búsqueda</p>
                    <a href="catalogo.php" class="btn btn-outline-primary">
                        <i class="fas fa-refresh"></i> Limpiar filtros
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>