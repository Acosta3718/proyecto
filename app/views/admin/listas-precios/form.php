<?php
$esEdicion = isset($lista);
$formAction = $esEdicion
    ? url('admin/listas-precios/actualizar/' . $lista['id'])
    : url('admin/listas-precios/guardar');
?>

<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-cash-coin me-2"></i><?php echo $esEdicion ? 'Editar' : 'Nueva'; ?> Lista de Precios</h1>
        <p class="text-muted mb-0">Configure listas para aplicar precios o descuentos específicos</p>
    </div>
    <a href="<?php echo url('admin/listas-precios'); ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver al listado
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Datos de la Lista</h5>
            </div>
            <div class="card-body">
                <form id="formLista">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $esEdicion ? htmlspecialchars($lista['nombre']) : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $esEdicion ? htmlspecialchars($lista['descripcion']) : ''; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <input list="tiposSugeridos" class="form-control" id="tipo" name="tipo" value="<?php echo $esEdicion ? htmlspecialchars($lista['tipo']) : ''; ?>" placeholder="mayorista, minorista, promocional">
                            <datalist id="tiposSugeridos">
                                <option value="mayorista">
                                <option value="minorista">
                                <option value="promocional">
                                <option value="especial">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="descuento_porcentaje" class="form-label">Descuento (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="descuento_porcentaje" name="descuento_porcentaje" step="0.01" min="0" max="100" value="<?php echo $esEdicion ? $lista['descuento_porcentaje'] : 0; ?>">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Si la lista aplica descuento general</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $esEdicion ? $lista['fecha_inicio'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $esEdicion ? $lista['fecha_fin'] : ''; ?>">
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" <?php echo (!$esEdicion || $lista['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Lista activa</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save me-2"></i><?php echo $esEdicion ? 'Actualizar' : 'Guardar'; ?> Lista
                        </button>
                        <a href="<?php echo url('admin/listas-precios'); ?>" class="btn btn-outline-secondary">
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
                <hr>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Lista creada el <?php echo date('d/m/Y', strtotime($lista['fecha_creacion'])); ?>
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
                    <li>Asigne un nombre que identifique el público objetivo.</li>
                    <li>Use el descuento general solo si aplica a todos los productos.</li>
                    <li>Puede limitar la vigencia con fechas de inicio y fin.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formLista').addEventListener('submit', function(e) {
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
            mostrarNotificacion('Lista de precios guardada exitosamente', 'success');
            setTimeout(() => window.location.href = '<?php echo url("admin/listas-precios"); ?>', 1200);
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