<?php $usuarios = $usuarios ?? []; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-people me-2"></i>Gestión de Usuarios</h1>
        <p class="text-muted mb-0">Administre los usuarios y sus permisos</p>
    </div>
    <?php if (AuthController::tienePermiso('usuarios.crear')): ?>
    <a href="<?php echo url('/admin/usuarios/crear'); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
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
        <h5 class="mb-0">Listado de Usuarios</h5>
        <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="tablaUsuarios" data-datatable-manual="true">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th width="120">Rol</th>
                        <th width="150">Último acceso</th>
                        <th width="90" class="text-center">Estado</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></strong>
                            <br><small class="text-muted">Registrado: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo ucfirst($usuario['rol']); ?></span></td>
                        <td>
                            <?php if (!empty($usuario['ultimo_acceso'])): ?>
                                <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin registros</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($usuario['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if (AuthController::tienePermiso('usuarios.editar')): ?>
                                <a href="<?php echo url('/admin/usuarios/editar/' . $usuario['id']); ?>"
                                   class="btn btn-outline-primary"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (AuthController::tienePermiso('usuarios.eliminar')): ?>
                                <button type="button"
                                        class="btn btn-outline-danger"
                                        onclick="eliminarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')"
                                        title="Eliminar">
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
    $('#tablaUsuarios').DataTable({
        language: typeof dataTableLangEs !== 'undefined' ? dataTableLangEs : {},
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });
});

function eliminarUsuario(id, nombre) {
    if (!confirm(`¿Eliminar el usuario "${nombre}"?`)) return;

    fetch(`<?php echo url('/admin/usuarios/eliminar/'); ?>${id}`, { method: 'DELETE' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Usuario eliminado exitosamente', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                 mostrarNotificacion(data.message || 'Error al eliminar', 'error');
            }
        })
        .catch(() => mostrarNotificacion('Error al procesar la solicitud', 'error'));
}
</script>