<?php
$esEdicion = isset($sector);
$formAction = $esEdicion
    ? url("/admin/sectores/actualizar/{$sector['id']}")
    : url('/admin/sectores/guardar');
$sectoresListadoUrl = url('/admin/sectores');
?>

<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-diagram-3 me-2"></i><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Sector</h1>
        <p class="text-muted mb-0">Defina los sectores para organizar los productos</p>
    </div>
    <a href="<?php echo $sectoresListadoUrl; ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Sector</h5>
            </div>
            <div class="card-body">
                <form id="formSector">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $esEdicion ? htmlspecialchars($sector['nombre']) : ''; ?>" required>
                        <div class="form-text">Nombre que verán los usuarios en el catálogo</div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $esEdicion ? htmlspecialchars($sector['descripcion']) : ''; ?></textarea>
                        <div class="form-text">Detalle opcional sobre el sector</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo $esEdicion ? $sector['orden'] : 0; ?>" min="0">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div>
                                <label class="form-label d-block">Estado</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" <?php echo (!$esEdicion || $sector['activo']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="activo">Sector activo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save me-2"></i><?php echo $esEdicion ? 'Actualizar' : 'Guardar'; ?> Sector
                        </button>
                        <a href="<?php echo $sectoresListadoUrl; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if ($esEdicion): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total de productos:</span>
                    <strong class="h4 mb-0 text-primary"><?php echo $stats['total_productos']; ?></strong>
                </div>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Sector creado el <?php echo date('d/m/Y', strtotime($sector['fecha_creacion'])); ?>
                </small>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Ayuda</h5>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Organice los productos por rubros o industrias.</li>
                    <li>El slug se genera automáticamente en el backend.</li>
                    <li>Use el orden para priorizar sectores.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formSector').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnGuardar');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

    fetch('<?php echo $formAction; ?>', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Sector guardado exitosamente', 'success');
            setTimeout(() => window.location.href = '<?php echo $sectoresListadoUrl; ?>', 1200);
        } else {
            mostrarNotificacion(data.message || 'Error al guardar', 'error');
            btn.disabled = false;
            btn.innerHTML = original;
        }
    })
    .catch(() => {
        mostrarNotificacion('Error al procesar la solicitud', 'error');
        btn.disabled = false;
        btn.innerHTML = original;
    });
});
</script>