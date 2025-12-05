<?php
session_start();

// Verificar si el usuario está logueado y es agricultor
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'agricultor') {
    header("Location: login.php");
    exit;
}

// Verificar que se proporcionó un ID de producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: mis_productos.php");
    exit;
}

$product_id = $_GET['id'];

// Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecommerce_organico", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: ". $e->getMessage());
}

// Obtener el producto
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND agricultor_id = ? AND activo = 1");
$stmt->execute([$product_id, $_SESSION['usuario_id']]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: mis_productos.php");
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria = trim($_POST['categoria']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $certificacion_organica = isset($_POST['certificacion_organica']) ? 1 : 0;
    $imagen = $producto['imagen']; // Mantener imagen actual por defecto

    // Validaciones
    if (empty($nombre) || empty($descripcion) || !is_numeric($precio) || $precio <= 0 || $stock < 0) {
        $error = "Por favor, completa todos los campos correctamente.";
    } else {
        // Manejar subida de nueva imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $target_dir = "../uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Verificar si es imagen real
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if ($check !== false && in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                    $imagen = $target_file;
                } else {
                    $error = "Error al subir la imagen.";
                }
            } else {
                $error = "El archivo no es una imagen válida.";
            }
        }

        if (!isset($error)) {
            // Actualizar producto
            $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria = ?, unidad_medida = ?, certificacion_organica = ?, imagen = ? WHERE id = ? AND agricultor_id = ?");
            if ($stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria, $unidad_medida, $certificacion_organica, $imagen, $product_id, $_SESSION['usuario_id']])) {
                $success = "Producto actualizado exitosamente.";
                // Recargar datos del producto
                $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
                $stmt->execute([$product_id]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error al actualizar el producto.";
            }
        }
    }
}

$page_title = 'Editar Producto';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Editar Producto</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    <?php elseif (isset($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form action="editar_producto.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del producto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                       value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría *</label>
                                <input type="text" class="form-control" id="categoria" name="categoria"
                                       value="<?php echo htmlspecialchars($producto['categoria']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="precio" class="form-label">Precio (COP) *</label>
                                <input type="number" class="form-control" id="precio" name="precio"
                                       step="0.01" min="0" value="<?php echo $producto['precio']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Stock disponible *</label>
                                <input type="number" class="form-control" id="stock" name="stock"
                                       min="0" value="<?php echo $producto['stock']; ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="unidad_medida" class="form-label">Unidad de medida *</label>
                                <input type="text" class="form-control" id="unidad_medida" name="unidad_medida"
                                       placeholder="ej: kg, litros, unidades"
                                       value="<?php echo htmlspecialchars($producto['unidad_medida']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="certificacion_organica"
                                       name="certificacion_organica" value="1"
                                       <?php echo $producto['certificacion_organica'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="certificacion_organica">
                                    <i class="fas fa-certificate text-success"></i> Producto con certificación orgánica
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="imagen" class="form-label">Imagen del producto</label>
                            <?php if ($producto['imagen']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>"
                                         alt="Imagen actual" class="img-thumbnail" style="max-width: 200px;">
                                    <small class="text-muted d-block">Imagen actual</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                            <small class="text-muted">Deja vacío para mantener la imagen actual. Formatos: JPG, PNG, GIF</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="mis_productos.php" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>