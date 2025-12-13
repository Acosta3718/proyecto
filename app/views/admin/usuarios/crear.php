<?php
$esEdicion = isset($item);
$formAction = $esEdicion ? "/admin/MODULO/actualizar/{$item['id']}" : "/admin/MODULO/guardar";
?>

<div class="page-header mb-4">
    <h1><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> ITEM</h1>
</div>

<div class="card">
    <div class="card-body">
        <form id="formItem" enctype="multipart/form-data">
            <!-- Campos del formulario -->
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre *</label>
                <input type="text" 
                       class="form-control" 
                       id="nombre" 
                       name="nombre" 
                       value="<?php echo $esEdicion ? htmlspecialchars($item['nombre']) : ''; ?>" 
                       required>
            </div>
            
            <!-- Más campos... -->
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="bi bi-save me-2"></i>Guardar
                </button>
                <a href="/admin/MODULO" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('formItem').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnGuardar = document.getElementById('btnGuardar');
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
            mostrarNotificacion('Guardado exitosamente', 'success');
            setTimeout(() => window.location.href = '/admin/MODULO', 1500);
        } else {
            mostrarNotificacion(data.message || 'Error al guardar', 'error');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save me-2"></i>Guardar';
        }
    });
});
</script>