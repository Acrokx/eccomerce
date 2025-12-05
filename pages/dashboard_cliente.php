<?php
session_start();

// Verificar si el usuario está logueado y es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

$page_title = 'Panel de Cliente';
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="hero-section text-center">
                <h1 class="display-4">
                    <i class="fas fa-user-circle"></i> ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!
                </h1>
                <p class="lead">Tu panel de control para productos orgánicos frescos</p>
            </div>

            <div class="row mt-4">
                <!-- Acciones principales -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-store fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Explorar Catálogo</h5>
                                    <p class="card-text">Descubre productos orgánicos frescos de agricultores locales</p>
                                    <a href="catalogo.php" class="btn btn-success">
                                        <i class="fas fa-arrow-right"></i> Ver Productos
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Mi Carrito</h5>
                                    <p class="card-text">Revisa y gestiona los productos en tu carrito</p>
                                    <a href="carrito.php" class="btn btn-primary">
                                        <i class="fas fa-shopping-cart"></i> Ver Carrito
                                        <span class="badge bg-light text-dark ms-1">
                                            <?php echo count($_SESSION['carrito'] ?? []); ?>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-history fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Mis Pedidos</h5>
                                    <p class="card-text">Revisa el historial de tus compras</p>
                                    <a href="#" class="btn btn-info">
                                        <i class="fas fa-list"></i> Ver Pedidos
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card card-product text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                                    <h5 class="card-title">Favoritos</h5>
                                    <p class="card-text">Productos que has marcado como favoritos</p>
                                    <a href="#" class="btn btn-danger">
                                        <i class="fas fa-heart"></i> Ver Favoritos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del usuario -->
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
                                <span class="badge bg-success">Cliente</span>
                            </div>
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar Perfil
                            </a>
                        </div>
                    </div>

                    <!-- Estadísticas rápidas -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h4 text-info mb-1">0</div>
                                    <small class="text-muted">Pedidos</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 text-success mb-1">$0</div>
                                    <small class="text-muted">Total Gastado</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos destacados o recomendaciones -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="mb-4"><i class="fas fa-star text-warning"></i> Productos Destacados</h3>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Próximamente: Recomendaciones personalizadas basadas en tus preferencias
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>