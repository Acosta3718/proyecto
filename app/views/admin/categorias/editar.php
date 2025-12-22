<?php
$esEdicion = isset($categoria);
$formAction = $esEdicion
    ? url("/admin/categorias/actualizar/{$categoria['id']}")
    : url('/admin/categorias/guardar');
$categoriasListadoUrl = url('/admin/categorias');
?>

<div class="page-header mb-4">
    <h1><i class="bi bi-grid me-2"></i><?php echo $esEdicion ? 'Editar' : 'Nueva'; ?> Categoría</h1>
    <p class="text-muted mb-0">Complete el formulario para <?php echo $esEdicion ? 'actualizar' : 'crear'; ?> la categoría</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información de la Categoría</h5>
            </div>
            <div class="card-body">
                <form id="formCategoria" enctype="multipart/form-data">
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               value="<?php echo $esEdicion ? htmlspecialchars($categoria['nombre']) : ''; ?>" 
                               required>
                        <div class="form-text">Nombre de la categoría que se mostrará en la tienda</div>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="3"><?php echo $esEdicion ? htmlspecialchars($categoria['descripcion']) : ''; ?></textarea>
                        <div class="form-text">Descripción breve de la categoría (opcional)</div>
                    </div>
                    
                    <!-- Icono Bootstrap Icons -->
                    <div class="mb-3">
                        <label for="icono" class="form-label">Icono Bootstrap</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i id="iconoPreview" class="<?php echo $esEdicion && $categoria['icono'] ? htmlspecialchars($categoria['icono']) : 'bi-image'; ?>"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="icono" 
                                   name="icono" 
                                   value="<?php echo $esEdicion ? htmlspecialchars($categoria['icono']) : ''; ?>" 
                                   placeholder="bi-laptop">
                        </div>
                        <div class="form-text">
                            Clase del icono de Bootstrap Icons (ej: bi-laptop, bi-phone). 
                            <a href="https://icons.getbootstrap.com/" target="_blank">Ver iconos</a>
                        </div>
                    </div>
                    
                    <!-- Imagen -->
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen</label>
                        <input type="file" 
                               class="form-control" 
                               id="imagen" 
                               name="imagen" 
                               accept="image/*"
                               onchange="previewImage(this, 'imagenPreview')">
                        <div class="form-text">Imagen representativa de la categoría (opcional, máx 5MB)</div>
                        
                        <?php if ($esEdicion && $categoria['imagen']): ?>
                        <div class="mt-2">
                            <img id="imagenPreview" 
                                 src="<?php echo htmlspecialchars($categoria['imagen']); ?>" 
                                 alt="Preview" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px;">
                        </div>
                        <?php else: ?>
                        <img id="imagenPreview" 
                             src="#" 
                             alt="Preview" 
                             class="img-thumbnail mt-2" 
                             style="max-width: 200px; display: none;">
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <!-- Orden -->
                        <div class="col-md-6 mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="orden" 
                                   name="orden" 
                                   value="<?php echo $esEdicion ? $categoria['orden'] : 0; ?>" 
                                   min="0">
                            <div class="form-text">Orden de visualización (menor número aparece primero)</div>
                        </div>
                        
                        <!-- Estado -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="activo" 
                                       name="activo" 
                                       <?php echo (!$esEdicion || $categoria['activo']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">
                                    Categoría activa
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save me-2"></i>
                            <?php echo $esEdicion ? 'Actualizar' : 'Guardar'; ?> Categoría
                        </button>
                        <a href="<?php echo $categoriasListadoUrl; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar con información adicional -->
    <div class="col-lg-4">
        <?php if ($esEdicion): ?>
        <!-- Estadísticas -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total de Productos:</span>
                    <strong class="h4 mb-0 text-primary"><?php echo $stats['total_productos']; ?></strong>
                </div>
                <hr>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Categoría creada el <?php echo date('d/m/Y', strtotime($categoria['fecha_creacion'])); ?>
                </small>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Ayuda -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Ayuda</h5>
            </div>
            <div class="card-body">
                <h6>Consejos:</h6>
                <ul class="small">
                    <li>Use nombres claros y descriptivos</li>
                    <li>El slug se genera automáticamente del nombre</li>
                    <li>Los iconos mejoran la navegación visual</li>
                    <li>Use imágenes optimizadas (jpg/png)</li>
                    <li>El orden determina la posición en el menú</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Preview de icono
document.getElementById('icono')?.addEventListener('input', function() {
    const iconPreview = document.getElementById('iconoPreview');
    iconPreview.className = this.value || 'bi-image';
});

// Submit del formulario
document.getElementById('formCategoria').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnGuardar = document.getElementById('btnGuardar');
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    const formData = new FormData(this);
    
    fetch('<?php echo $formAction; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Categoría guardada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = '<?php echo $categoriasListadoUrl; ?>';
            }, 1500);
        } else {
            mostrarNotificacion(data.message || 'Error al guardar', 'error');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginal;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al procesar la solicitud', 'error');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = textoOriginal;
    });
});
</script>