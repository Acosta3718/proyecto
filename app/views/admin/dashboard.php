<?php
$titulo = 'Dashboard';
$pagina = 'dashboard';
?>

<div class="page-header">
    <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <p class="text-muted">Bienvenido al panel de administración</p>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Total Productos</h6>
                        <h2 class="mb-0"><?php echo $totalProductos; ?></h2>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> +5% este mes</small>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Usuarios</h6>
                        <h2 class="mb-0"><?php echo $totalUsuarios; ?></h2>
                        <small class="text-success"><i class="bi bi-arrow-up"></i> +2 nuevos</small>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Stock Bajo</h6>
                        <h2 class="mb-0"><?php echo $productosAgotados; ?></h2>
                        <small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Requiere atención</small>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Destacados</h6>
                        <h2 class="mb-0"><?php echo $productosDestacados; ?></h2>
                        <small class="text-muted"><i class="bi bi-star-fill"></i> En promoción</small>
                    </div>
                    <div class="stat-icon text-danger">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Productos por Categoría</h5>
            </div>
            <div class="card-body">
                <canvas id="chartCategorias" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Distribución por Marca</h5>
            </div>
            <div class="card-body">
                <canvas id="chartMarcas" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="row g-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Últimos Productos Agregados</h5>
                <a href="/proyecto/public/admin/productos" class="btn btn-sm btn-primary">
                    Ver Todos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ultimosProductos)): ?>
                                <?php foreach ($ultimosProductos as $producto): ?>
                                <tr>
                                    <td>
                                        <?php $placeholder = asset('img/placeholder.svg'); ?>
                                        <?php $imagenProducto = !empty($producto['imagen']) ? $producto['imagen'] : $placeholder; ?>
                                        <img src="<?php echo htmlspecialchars($imagenProducto); ?>"
                                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                             class="rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             onerror="this.src='<?php echo $placeholder; ?>'">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong><br>
                                        <small class="text-muted">Ref: <?php echo htmlspecialchars($producto['referencia']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                                    <td><strong>Gs <?php echo number_format($producto['precio'], 0, ',', '.'); ?></strong></td>
                                    <td>
                                        <?php if ($producto['stock'] > 10): ?>
                                            <span class="badge bg-success"><?php echo $producto['stock']; ?></span>
                                        <?php elseif ($producto['stock'] > 0): ?>
                                            <span class="badge bg-warning"><?php echo $producto['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Agotado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($producto['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/proyecto/public/admin/productos/editar/<?php echo $producto['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
                                        <p class="text-muted mt-2">No hay productos registrados</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Chart de Categorías
const ctxCategorias = document.getElementById('chartCategorias');
if (ctxCategorias) {
    new Chart(ctxCategorias, {
        type: 'bar',
        data: {
            labels: ['Smartphones', 'Laptops', 'Tablets', 'Televisores', 'Audio', 'Gaming'],
            datasets: [{
                label: 'Productos por Categoría',
                data: [12, 19, 8, 5, 15, 7],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(72, 187, 120, 0.8)',
                    'rgba(246, 173, 85, 0.8)',
                    'rgba(245, 101, 101, 0.8)',
                    'rgba(159, 122, 234, 0.8)'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Chart de Marcas
const ctxMarcas = document.getElementById('chartMarcas');
if (ctxMarcas) {
    new Chart(ctxMarcas, {
        type: 'doughnut',
        data: {
            labels: ['Samsung', 'Apple', 'LG', 'Sony', 'Otros'],
            datasets: [{
                data: [30, 25, 20, 15, 10],
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(72, 187, 120, 0.8)',
                    'rgba(246, 173, 85, 0.8)',
                    'rgba(245, 101, 101, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
</script>