<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-cash-coin me-2"></i>Listas de Precios</h1>
        <p class="text-muted mb-0">Cree y administre listas para diferentes clientes o promociones</p>
    </div>
    <?php if (AuthController::tienePermiso('listas_precios.crear')): ?>
    <a href="<?php echo url('admin/listas-precios/crear'); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nueva Lista
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
        <h5 class="mb-0">Listado de Listas</h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="tablaListas" data-datatable-manual="true">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th width="120" class="text-center">Descuento</th>
                        <th width="140" class="text-center">Vigencia</th>
                        <th width="120" class="text-center">Productos</th>
                        <th width="90" class="text-center">Estado</th>
                        <th width="200" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listas as $l): ?>
                    <tr>
                        <td><?php echo $l['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($l['nombre']); ?></strong>
                            <?php if (!empty($l['descripcion'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($l['descripcion'], 0, 70)); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($l['tipo']); ?></td>
                        <td class="text-center">
                            <?php if ($l['descuento_porcentaje'] > 0): ?>
                                <span class="badge bg-info"><?php echo number_format($l['descuento_porcentaje'], 2); ?>%</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($l['fecha_inicio'] || $l['fecha_fin']): ?>
                                <small><?php echo $l['fecha_inicio'] ? date('d/m/Y', strtotime($l['fecha_inicio'])) : '—'; ?> - <?php echo $l['fecha_fin'] ? date('d/m/Y', strtotime($l['fecha_fin'])) : '—'; ?></small>
                            <?php else: ?>
                                <span class="text-muted">Sin límite</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="badge bg-info"><?php echo $l['total_productos'] ?? 0; ?></span></td>
                        <td class="text-center">
                            <?php if ($l['activo']): ?>
                                <span class="badge bg-success">Activa</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if (AuthController::tienePermiso('listas_precios.editar')): ?>
                                <a href="<?php echo url('admin/listas-precios/editar/' . $l['id']); ?>" class="btn btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?php echo url('admin/listas-precios/asignar-productos/' . $l['id']); ?>" class="btn btn-outline-success" title="Asignar productos">
                                    <i class="bi bi-box-seam"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (AuthController::tienePermiso('listas_precios.eliminar')): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarLista(<?php echo $l['id']; ?>, '<?php echo htmlspecialchars($l['nombre']); ?>')" title="Eliminar">
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
    $('#tablaListas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
});

function eliminarLista(id, nombre) {
    if (!confirm(`¿Eliminar la lista "${nombre}"?`)) return;

     fetch(`<?php echo url('admin/listas-precios/eliminar/'); ?>${id}`, { method: 'DELETE' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Lista eliminada exitosamente', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                mostrarNotificacion(data.message || 'Error al eliminar', 'error');
            }
        })
        .catch(() => mostrarNotificacion('Error al procesar la solicitud', 'error'));
}
</script>