<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-ICONO me-2"></i>Gestión de MODULO</h1>
        <p class="text-muted mb-0">Administre los MODULO del sistema</p>
    </div>
    <?php if (AuthController::tienePermiso('MODULO.crear')): ?>
    <a href="/admin/MODULO/crear" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo ITEM
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <table class="table datatable table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <!-- Agregar columnas según necesidad -->
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td class="text-center">
                        <?php if ($item['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="/admin/MODULO/editar/<?php echo $item['id']; ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button onclick="eliminar(<?php echo $item['id']; ?>)" 
                                class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        language: typeof dataTableLangEs !== 'undefined' ? dataTableLangEs : {}
    });
});

function eliminar(id) {
    if (!confirm('¿Está seguro de eliminar este elemento?')) return;
    
    fetch(`/admin/MODULO/eliminar/${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Eliminado exitosamente', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarNotificacion(data.message, 'error');
            }
        });
}
</script>