<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-grid me-2"></i>Gestión de Categorías</h1>
        <p class="text-muted mb-0">Administre las categorías de productos</p>
    </div>
    <?php if (AuthController::tienePermiso('categorias.crear')): ?>
    <a href="/admin/categorias/crear" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nueva Categoría
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
        <h5 class="mb-0">Listado de Categorías</h5>
        <div>
            <button class="btn btn-sm btn-outline-secondary" onclick="recargarTabla()">
                <i class="bi bi-arrow-clockwise"></i> Actualizar
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="tablaCategorias">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="80">Imagen</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th width="100">Icono</th>
                        <th width="100" class="text-center">Productos</th>
                        <th width="80" class="text-center">Orden</th>
                        <th width="80" class="text-center">Estado</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td>
                            <?php if ($cat['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($cat['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($cat['nombre']); ?>" 
                                     class="img-thumbnail" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>
                            <?php if ($cat['descripcion']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($cat['descripcion'], 0, 60)); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                        <td class="text-center">
                            <?php if ($cat['icono']): ?>
                                <i class="<?php echo htmlspecialchars($cat['icono']); ?>" style="font-size: 1.5rem;"></i>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?php echo $cat['total_productos'] ?? 0; ?></span>
                        </td>
                        <td class="text-center">
                            <input type="number" 
                                   class="form-control form-control-sm text-center" 
                                   value="<?php echo $cat['orden']; ?>" 
                                   style="width: 60px;"
                                   onchange="cambiarOrden(<?php echo $cat['id']; ?>, this.value)">
                        </td>
                        <td class="text-center">
                            <?php if ($cat['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <?php if (AuthController::tienePermiso('categorias.editar')): ?>
                                <a href="/admin/categorias/editar/<?php echo $cat['id']; ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (AuthController::tienePermiso('categorias.eliminar')): ?>
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        onclick="eliminarCategoria(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['nombre']); ?>')"
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
// Inicializar DataTable
$(document).ready(function() {
    $('#tablaCategorias').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[6, 'asc']], // Ordenar por columna "Orden"
        columnDefs: [
            { orderable: false, targets: [1, 8] } // Imagen y Acciones no ordenables
        ]
    });
});

// Recargar tabla
function recargarTabla() {
    location.reload();
}

// Cambiar orden
function cambiarOrden(id, nuevoOrden) {
    fetch('/admin/categorias/cambiar-orden', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&orden=${nuevoOrden}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Orden actualizado', 'success');
        } else {
            mostrarNotificacion('Error al actualizar orden', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al actualizar orden', 'error');
    });
}

// Eliminar categoría
function eliminarCategoria(id, nombre) {
    if (!confirm(`¿Está seguro de eliminar la categoría "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch(`/admin/categorias/eliminar/${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Categoría eliminada exitosamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarNotificacion(data.message || 'Error al eliminar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar categoría', 'error');
    });
}
</script>