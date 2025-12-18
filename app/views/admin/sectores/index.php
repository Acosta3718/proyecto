<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-diagram-3 me-2"></i>Gestión de Sectores</h1>
        <p class="text-muted mb-0">Organice los productos por sector o industria</p>
    </div>
    <?php if (AuthController::tienePermiso('sectores.crear')): ?>
    <a href="/admin/sectores/crear" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Sector
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
        <h5 class="mb-0">Listado de Sectores</h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="tablaSectores" data-datatable-manual="true">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th width="120" class="text-center">Productos</th>
                        <th width="80" class="text-center">Orden</th>
                        <th width="90" class="text-center">Estado</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sectores as $s): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($s['nombre']); ?></strong>
                            <?php if (!empty($s['descripcion'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($s['descripcion'], 0, 70)); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo htmlspecialchars($s['slug']); ?></code></td>
                        <td class="text-center"><span class="badge bg-info"><?php echo $s['total_productos'] ?? 0; ?></span></td>
                        <td class="text-center"><?php echo $s['orden']; ?></td>
                        <td class="text-center">
                            <?php if ($s['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if (AuthController::tienePermiso('sectores.editar')): ?>
                                <a href="/admin/sectores/editar/<?php echo $s['id']; ?>" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (AuthController::tienePermiso('sectores.eliminar')): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarSector(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars($s['nombre']); ?>')" title="Eliminar">
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
    $('#tablaSectores').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 25,
        order: [[4, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
});

function eliminarSector(id, nombre) {
    if (!confirm(`¿Eliminar el sector "${nombre}"?`)) return;

    fetch(`/admin/sectores/eliminar/${id}`, { method: 'DELETE' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Sector eliminado exitosamente', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                mostrarNotificacion(data.message || 'Error al eliminar', 'error');
            }
        })
        .catch(() => mostrarNotificacion('Error al procesar la solicitud', 'error'));
}
</script>