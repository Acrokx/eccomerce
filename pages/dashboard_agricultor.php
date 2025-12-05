<?php
session_start();

// Verificar si el usuario está logueado y es agricultor
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'agricultor') {
    header("Location: login.php");
    exit;
}

$page_title = 'Panel de Agricultor';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="hero-section text-center">
                <h1 class="display-4">
                    <i class="fas fa-tractor"></i> ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!
                </h1>
                <p class="lead">Gestiona tus productos orgánicos y conecta con consumidores</p>
            </div>

            <div class="row mt-4">
                <!-- Acciones principales -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-plus-circle fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Agregar Producto</h5>
                                    <p class="card-text">Publica nuevos productos orgánicos en el catálogo</p>
                                    <a href="agregar_producto.php" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Agregar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-boxes fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Mis Productos</h5>
                                    <p class="card-text">Gestiona, edita y elimina tus productos publicados</p>
                                    <a href="mis_productos.php" class="btn btn-primary">
                                        <i class="fas fa-list"></i> Ver Productos
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-shopping-bag fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Pedidos Recibidos</h5>
                                    <p class="card-text">Revisa y gestiona los pedidos de tus productos</p>
                                    <a href="#" class="btn btn-info">
                                        <i class="fas fa-shopping-bag"></i> Ver Pedidos
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">Estadísticas</h5>
                                    <p class="card-text">Analiza las ventas y rendimiento de tus productos</p>
                                    <a href="#" class="btn btn-warning">
                                        <i class="fas fa-chart-bar"></i> Ver Estadísticas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del agricultor -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Mi Perfil</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Nombre:</strong><br>
                                <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong><br>
                                <?php echo htmlspecialchars($_SESSION['usuario_email']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Tipo de usuario:</strong><br>
                                <span class="badge bg-success">Agricultor</span>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar Perfil
                            </a>
                        </div>
                    </div>

                    <!-- Estadísticas rápidas -->
                    <div class="card mt-3">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h4 text-primary mb-1">0</div>
                                    <small class="text-muted">Productos</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-success mb-1">0</div>
                                    <small class="text-muted">Ventas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consejos para agricultores -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Consejos</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small mb-0">
                                <li><i class="fas fa-check text-success"></i> Mantén precios competitivos</li>
                                <li><i class="fas fa-check text-success"></i> Sube fotos de calidad</li>
                                <li><i class="fas fa-check text-success"></i> Actualiza stock regularmente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>