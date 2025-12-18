<?php
$esEdicion = isset($marca);
$formAction = $esEdicion ? "/admin/marcas/actualizar/{$marca['id']}" : "/admin/marcas/guardar";
?>

<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-bookmark-star me-2"></i><?php echo $esEdicion ? 'Editar' : 'Nueva'; ?> Marca</h1>
        <p class="text-muted mb-0">Complete el formulario para <?php echo $esEdicion ? 'actualizar' : 'crear'; ?> la marca</p>
    </div>
    <a href="/admin/marcas" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información de la Marca</h5>
            </div>
            <div class="card-body">
                <form id="formMarca" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $esEdicion ? htmlspecialchars($marca['nombre']) : ''; ?>" required>
                        <div class="form-text">Nombre comercial que se mostrará en la tienda</div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $esEdicion ? htmlspecialchars($marca['descripcion']) : ''; ?></textarea>
                        <div class="form-text">Resumen breve de la marca (opcional)</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sitio_web" class="form-label">Sitio web</label>
                            <input type="url" class="form-control" id="sitio_web" name="sitio_web" value="<?php echo $esEdicion ? htmlspecialchars($marca['sitio_web']) : ''; ?>" placeholder="https://www.ejemplo.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="<?php echo $esEdicion ? $marca['orden'] : 0; ?>" min="0">
                            <div class="form-text">Orden de visualización (menor aparece primero)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previewImage(this, 'logoPreview')">
                        <div class="form-text">Imagen del logo (opcional, máx 5MB)</div>
                        <?php if ($esEdicion && $marca['logo']): ?>
                        <div class="mt-2">
                            <img id="logoPreview" src="<?php echo htmlspecialchars($marca['logo']); ?>" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php else: ?>
                        <img id="logoPreview" src="#" alt="Preview" class="img-thumbnail mt-2" style="max-width: 200px; display: none;">
                        <?php endif; ?>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" <?php echo (!$esEdicion || $marca['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Marca activa</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save me-2"></i><?php echo $esEdicion ? 'Actualizar' : 'Guardar'; ?> Marca
                        </button>
                        <a href="/admin/marcas" class="btn btn-outline-secondary">
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
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total de productos:</span>
                    <strong class="h4 mb-0 text-primary"><?php echo $stats['total_productos']; ?></strong>
                </div>
                <hr>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Marca creada el <?php echo date('d/m/Y', strtotime($marca['fecha_creacion'])); ?>
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
                    <li>Use nombres reconocibles para los clientes.</li>
                    <li>El slug se genera automáticamente en el backend.</li>
                    <li>Agregue el sitio web oficial si existe.</li>
                    <li>Puede subir el logo en formato JPG o PNG.</li>
                    <li>El orden define la posición en listados.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Submit del formulario
const formMarca = document.getElementById('formMarca');
formMarca.addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('btnGuardar');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

    const formData = new FormData(this);

    fetch('<?php echo $formAction; ?>', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion('Marca guardada exitosamente', 'success');
                setTimeout(() => window.location.href = '/admin/marcas', 1200);
            } else {
                mostrarNotificacion(data.message || 'Error al guardar', 'error');
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        })
        .catch(error => {
            console.error(error);
            mostrarNotificacion('Error al procesar la solicitud', 'error');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        });
});
</script>