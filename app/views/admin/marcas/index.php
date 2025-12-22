<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-bookmark-star me-2"></i>Gestión de Marcas</h1>
        <p class="text-muted mb-0">Administre las marcas disponibles para sus productos</p>
    </div>
    <?php if (AuthController::tienePermiso('marcas.crear')): ?>
    <a href="<?php echo url('/admin/marcas/crear'); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nueva Marca
    </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Marcas</h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="tablaMarcas" data-datatable-manual="true">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="80">Logo</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Sitio web</th>
                        <th width="120" class="text-center">Productos</th>
                        <th width="80" class="text-center">Orden</th>
                        <th width="90" class="text-center">Estado</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($marcas as $m): ?>
                    <tr>
                        <td><?php echo $m['id']; ?></td>
                        <td>
                            <?php if (!empty($m['logo'])): ?>
                                <img src="<?php echo htmlspecialchars($m['logo']); ?>" alt="<?php echo htmlspecialchars($m['nombre']); ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($m['nombre']); ?></strong>
                            <?php if (!empty($m['descripcion'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($m['descripcion'], 0, 60)); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo htmlspecialchars($m['slug']); ?></code></td>
                        <td>
                            <?php if (!empty($m['sitio_web'])): ?>
                                <a href="<?php echo htmlspecialchars($m['sitio_web']); ?>" target="_blank"><?php echo htmlspecialchars($m['sitio_web']); ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?php echo $m['total_productos'] ?? 0; ?></span>
                        </td>
                        <td class="text-center"><?php echo $m['orden']; ?></td>
                        <td class="text-center">
                            <?php if ($m['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if (AuthController::tienePermiso('marcas.editar')): ?>
                                <a href="<?php echo url('/admin/marcas/editar/' . $m['id']); ?>" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (AuthController::tienePermiso('marcas.eliminar')): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarMarca(<?php echo $m['id']; ?>, '<?php echo htmlspecialchars($m['nombre']); ?>')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaMarcas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 25,
        order: [[6, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 8] }
        ]
    });
});

const marcasBaseUrl = '<?php echo rtrim(url('/admin/marcas'), '/'); ?>';

function eliminarMarca(id, nombre) {
    if (!confirm(`¿Eliminar la marca "${nombre}"?`)) return;

    fetch(`${marcasBaseUrl}/eliminar/${id}`, { method: 'DELETE' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Marca eliminada exitosamente', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                mostrarNotificacion(data.message || 'Error al eliminar', 'error');
            }
        })
        .catch(() => mostrarNotificacion('Error al procesar la solicitud', 'error'));
}
</script>