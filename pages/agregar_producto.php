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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria = trim($_POST['categoria']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $certificacion_organica = isset($_POST['certificacion_organica']) ? 1 : 0;
    $imagen = '';

    // Validaciones
    if (empty($nombre) || empty($descripcion) || !is_numeric($precio) || $precio <= 0 || $stock < 0) {
        $error = "Por favor, completa todos los campos correctamente.";
    } else {
        // Manejar subida de imagen
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
            // Insertar producto
            $stmt = $pdo->prepare("INSERT INTO productos (agricultor_id, nombre, descripcion, precio, stock, categoria, unidad_medida, certificacion_organica, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['usuario_id'], $nombre, $descripcion, $precio, $stock, $categoria, $unidad_medida, $certificacion_organica, $imagen])) {
                $success = "Producto agregado exitosamente.";
            } else {
                $error = "Error al agregar el producto.";
            }
        }
    }
}

$page_title = 'Agregar Producto';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Agregar Nuevo Producto</h4>
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

                    <form action="agregar_producto.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del producto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría *</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="precio" class="form-label">Precio (COP) *</label>
                                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Stock disponible *</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="unidad_medida" class="form-label">Unidad de medida *</label>
                                <input type="text" class="form-control" id="unidad_medida" name="unidad_medida"
                                       placeholder="ej: kg, litros, unidades" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="certificacion_organica" name="certificacion_organica" value="1">
                                <label class="form-check-label" for="certificacion_organica">
                                    <i class="fas fa-certificate text-success"></i> Producto con certificación orgánica
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="imagen" class="form-label">Imagen del producto</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo recomendado: 2MB</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="mis_productos.php" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>