<?php
$esEdicion = isset($producto);
$formAction = $esEdicion ? "/admin/productos/actualizar/{$producto['id']}" : "/admin/productos/guardar";
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-box-seam me-2"></i><?php echo $esEdicion ? 'Editar' : 'Nuevo'; ?> Producto</h1>
            <p class="text-muted mb-0">Complete todos los campos para <?php echo $esEdicion ? 'actualizar' : 'crear'; ?> el producto</p>
        </div>
        <a href="<?php echo url('admin/productos'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al listado
        </a>
    </div>
</div>

<form id="formProducto" enctype="multipart/form-data">
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            
            <!-- Información Básica -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información Básica</h5>
                </div>
                <div class="card-body">
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="nombre" 
                               name="nombre" 
                               value="<?php echo $esEdicion ? htmlspecialchars($producto['nombre']) : ''; ?>" 
                               required>
                        <div class="form-text">Nombre descriptivo que aparecerá en la tienda</div>
                    </div>
                    
                    <!-- Descripción Corta -->
                    <div class="mb-3">
                        <label for="descripcion_corta" class="form-label">Descripción Corta</label>
                        <input type="text" 
                               class="form-control" 
                               id="descripcion_corta" 
                               name="descripcion_corta" 
                               maxlength="500"
                               value="<?php echo $esEdicion ? htmlspecialchars($producto['descripcion_corta']) : ''; ?>">
                        <div class="form-text">Resumen breve del producto (máx. 500 caracteres)</div>
                    </div>
                    
                    <!-- Descripción Completa -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción Completa</label>
                        <textarea class="form-control" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="5"><?php echo $esEdicion ? htmlspecialchars($producto['descripcion']) : ''; ?></textarea>
                        <div class="form-text">Descripción detallada con características y especificaciones</div>
                    </div>
                </div>
            </div>
            
            <!-- Clasificación -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-tags me-2"></i>Clasificación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Marca -->
                        <div class="col-md-4 mb-3">
                            <label for="marca_id" class="form-label">Marca <span class="text-danger">*</span></label>
                            <select class="form-select" id="marca_id" name="marca_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?php echo $marca['id']; ?>" 
                                            <?php echo ($esEdicion && $producto['marca_id'] == $marca['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($marca['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Categoría -->
                        <div class="col-md-4 mb-3">
                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($esEdicion && $producto['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Sector -->
                        <div class="col-md-4 mb-3">
                            <label for="sector_id" class="form-label">Sector</label>
                            <select class="form-select" id="sector_id" name="sector_id">
                                <option value="">Seleccione...</option>
                                <?php foreach ($sectores as $sector): ?>
                                    <option value="<?php echo $sector['id']; ?>" 
                                            <?php echo ($esEdicion && $producto['sector_id'] == $sector['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sector['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Precios e Inventario -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Precios e Inventario</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Precio de Venta -->
                        <div class="col-md-3 mb-3">
                            <label for="precio" class="form-label">Precio de Venta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Gs</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio" 
                                       name="precio" 
                                       step="0.01" 
                                       min="0"
                                       value="<?php echo $esEdicion ? $producto['precio'] : '0.00'; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <!-- Precio de Costo -->
                        <div class="col-md-3 mb-3">
                            <label for="precio_costo" class="form-label">Precio de Costo</label>
                            <div class="input-group">
                                <span class="input-group-text">Gs</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio_costo" 
                                       name="precio_costo" 
                                       step="0.01" 
                                       min="0"
                                       value="<?php echo $esEdicion ? $producto['precio_costo'] : '0.00'; ?>">
                            </div>
                        </div>
                        
                        <!-- Stock -->
                        <div class="col-md-3 mb-3">
                            <label for="stock" class="form-label">Stock Disponible</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="stock" 
                                   name="stock" 
                                   min="0"
                                   value="<?php echo $esEdicion ? $producto['stock'] : 0; ?>">
                        </div>
                        
                        <!-- Stock Mínimo -->
                        <div class="col-md-3 mb-3">
                            <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="stock_minimo" 
                                   name="stock_minimo" 
                                   min="0"
                                   value="<?php echo $esEdicion ? $producto['stock_minimo'] : 0; ?>">
                            <div class="form-text small">Alerta cuando llegue a este nivel</div>
                        </div>
                    </div>
                    
                    <!-- Margen de Ganancia (calculado) -->
                    <div class="alert alert-info mb-0">
                        <strong>Margen de Ganancia:</strong> <span id="margenGanancia">0%</span>
                    </div>
                </div>
            </div>
            
            <!-- Referencias y Códigos -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-upc-scan me-2"></i>Referencias y Códigos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Referencia -->
                        <div class="col-md-6 mb-3">
                            <label for="referencia" class="form-label">Referencia/SKU <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="referencia" 
                                   name="referencia" 
                                   value="<?php echo $esEdicion ? htmlspecialchars($producto['referencia']) : ''; ?>" 
                                   required>
                            <div class="form-text">Código único de identificación del producto</div>
                        </div>
                        
                        <!-- Código de Barras -->
                        <div class="col-md-6 mb-3">
                            <label for="codigo_barras" class="form-label">Código de Barras</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="codigo_barras" 
                                   name="codigo_barras" 
                                   value="<?php echo $esEdicion ? htmlspecialchars($producto['codigo_barras']) : ''; ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Características Físicas -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-rulers me-2"></i>Características Físicas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Peso -->
                        <div class="col-md-4 mb-3">
                            <label for="peso" class="form-label">Peso (kg)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="peso" 
                                   name="peso" 
                                   step="0.01" 
                                   min="0"
                                   value="<?php echo $esEdicion ? $producto['peso'] : ''; ?>">
                        </div>
                        
                        <!-- Dimensiones -->
                        <div class="col-md-4 mb-3">
                            <label for="dimensiones" class="form-label">Dimensiones (cm)</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="dimensiones" 
                                   name="dimensiones" 
                                   placeholder="20 x 30 x 40"
                                   value="<?php echo $esEdicion ? htmlspecialchars($producto['dimensiones']) : ''; ?>">
                        </div>
                        
                        <!-- Garantía -->
                        <div class="col-md-4 mb-3">
                            <label for="garantia" class="form-label">Garantía</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="garantia" 
                                   name="garantia" 
                                   placeholder="12 meses"
                                   value="<?php echo $esEdicion ? htmlspecialchars($producto['garantia']) : ''; ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Imágenes -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-images me-2"></i>Imágenes del Producto</h5>
                </div>
                <div class="card-body">
                    <!-- Imagen Principal -->
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen Principal</label>
                        <input type="file" 
                               class="form-control" 
                               id="imagen" 
                               name="imagen" 
                               accept="image/*"
                               onchange="previewImage(this, 'imagenPreview')">
                        <div class="form-text">Imagen principal del producto (máx 5MB)</div>
                        
                        <div class="mt-3">
                            <?php if ($esEdicion && $producto['imagen']): ?>
                            <img id="imagenPreview" 
                                 src="<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 alt="Preview" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px;">
                            <?php else: ?>
                            <img id="imagenPreview" 
                                 src="#" 
                                 alt="Preview" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px; display: none;">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Galería de Imágenes -->
                    <div class="mb-3">
                        <label for="imagenes_adicionales" class="form-label">Galería de Imágenes</label>
                        <input type="file" 
                               class="form-control" 
                               id="imagenes_adicionales" 
                               name="imagenes_adicionales[]" 
                               accept="image/*"
                               multiple
                               onchange="previewMultipleImages(this)">
                        <div class="form-text">Puede seleccionar múltiples imágenes (máx 5MB cada una)</div>
                        
                        <!-- Preview de galería -->
                        <div id="galeriaPreview" class="row g-2 mt-2">
                            <?php if ($esEdicion && isset($imagenes)): ?>
                                <?php foreach ($imagenes as $img): ?>
                                <div class="col-md-3 position-relative">
                                    <img src="<?php echo htmlspecialchars($img['imagen']); ?>" 
                                         class="img-thumbnail w-100" 
                                         style="height: 150px; object-fit: cover;">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                            onclick="eliminarImagenGaleria(<?php echo $img['id']; ?>)">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Columna Lateral -->
        <div class="col-lg-4">
            
            <!-- Estado y Opciones -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-toggles me-2"></i>Estado y Opciones</h5>
                </div>
                <div class="card-body">
                    <!-- Estado Activo -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="activo" 
                               name="activo" 
                               <?php echo (!$esEdicion || $producto['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">
                            <strong>Producto Activo</strong><br>
                            <small class="text-muted">Visible en la tienda</small>
                        </label>
                    </div>
                    
                    <hr>
                    
                    <!-- Destacado -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="destacado" 
                               name="destacado" 
                               <?php echo ($esEdicion && $producto['destacado']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="destacado">
                            <strong>Producto Destacado</strong><br>
                            <small class="text-muted">Aparece en sección destacados</small>
                        </label>
                    </div>
                    
                    <hr>
                    
                    <!-- Nuevo -->
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="nuevo" 
                               name="nuevo" 
                               <?php echo ($esEdicion && $producto['nuevo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="nuevo">
                            <strong>Producto Nuevo</strong><br>
                            <small class="text-muted">Muestra etiqueta "NUEVO"</small>
                        </label>
                    </div>
                </div>
            </div>
            
            <?php if ($esEdicion): ?>
            <!-- Estadísticas -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Creado:</small><br>
                        <strong><?php echo date('d/m/Y H:i', strtotime($producto['fecha_creacion'])); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Última actualización:</small><br>
                        <strong><?php echo date('d/m/Y H:i', strtotime($producto['fecha_actualizacion'])); ?></strong>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4 class="text-primary mb-0"><?php echo $producto['stock']; ?></h4>
                        <small class="text-muted">Unidades en stock</small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Ayuda -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Ayuda</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Consejos:</h6>
                    <ul class="small mb-0">
                        <li>Los campos marcados con * son obligatorios</li>
                        <li>Use nombres descriptivos y claros</li>
                        <li>La referencia debe ser única</li>
                        <li>Agregue imágenes de alta calidad</li>
                        <li>Configure el stock mínimo para alertas</li>
                        <li>Active "Destacado" para promocionar</li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Botones de Acción -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?php echo url('admin/productos'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-2"></i>Cancelar
                </a>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="bi bi-save me-2"></i>
                        <?php echo $esEdicion ? 'Actualizar' : 'Guardar'; ?> Producto
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Calcular margen de ganancia
function calcularMargen() {
    const precio = parseFloat($('#precio').val()) || 0;
    const costo = parseFloat($('#precio_costo').val()) || 0;
    
    if (costo > 0) {
        const margen = ((precio - costo) / costo * 100).toFixed(2);
        $('#margenGanancia').text(margen + '%');
        
        if (margen < 0) {
            $('#margenGanancia').addClass('text-danger').removeClass('text-success');
        } else {
            $('#margenGanancia').addClass('text-success').removeClass('text-danger');
        }
    } else {
        $('#margenGanancia').text('0%');
    }
}

$('#precio, #precio_costo').on('input', calcularMargen);

// Preview de múltiples imágenes
function previewMultipleImages(input) {
    const preview = document.getElementById('galeriaPreview');
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3';
                col.innerHTML = `
                    <img src="${e.target.result}" 
                         class="img-thumbnail w-100" 
                         style="height: 150px; object-fit: cover;">
                `;
                preview.appendChild(col);
            };
            
            reader.readAsDataURL(file);
        });
    }
}

// Eliminar imagen de galería
function eliminarImagenGaleria(id) {
    if (!confirm('¿Eliminar esta imagen?')) return;
    
    fetch(`/admin/productos/eliminar-imagen/${id}`, {
        method: 'DELETE'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Imagen eliminada', 'success');
            location.reload();
        }
    });
}

// Submit del formulario
document.getElementById('formProducto').addEventListener('submit', function(e) {
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
            mostrarNotificacion('Producto guardado exitosamente', 'success');
            setTimeout(() => {
                window.location.href = '/admin/productos';
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

// Calcular margen inicial
$(document).ready(function() {
    calcularMargen();
});
</script>