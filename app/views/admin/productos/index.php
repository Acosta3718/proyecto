<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-box-seam me-2"></i>Gestión de Productos</h1>
        <p class="text-muted mb-0">Administre el catálogo completo de productos</p>
    </div>
    <div class="d-flex gap-2">
        <?php if (AuthController::tienePermiso('productos.crear')): ?>
        <a href="<?php echo url('/admin/productos/crear'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Producto
        </a>
        <?php endif; ?>
        <button class="btn btn-outline-secondary" onclick="exportarExcel()">
            <i class="bi bi-file-earmark-excel me-2"></i>Exportar
        </button>
    </div>
</div>


<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filtros Rápidos -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" id="filtroCat" onchange="filtrarTabla()">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Marca</label>
                <select class="form-select" id="filtroMarca" onchange="filtrarTabla()">
                    <option value="">Todas</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?php echo $marca['id']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-select" id="filtroEstado" onchange="filtrarTabla()">
                    <option value="">Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Stock</label>
                <select class="form-select" id="filtroStock" onchange="filtrarTabla()">
                    <option value="">Todos</option>
                    <option value="disponible">Disponible</option>
                    <option value="bajo">Stock Bajo</option>
                    <option value="agotado">Agotados</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                    <i class="bi bi-x-circle me-2"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Productos</h5>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">Total: <span id="totalProductos">0</span></span>
            <button class="btn btn-sm btn-outline-secondary" onclick="recargarTabla()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tablaProductos" style="width: 100%;">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="80">Imagen</th>
                        <th>Producto</th>
                        <th width="120">Marca</th>
                        <th width="120">Categoría</th>
                        <th width="100">Referencia</th>
                        <th width="100" class="text-end">Precio</th>
                        <th width="80" class="text-center">Stock</th>
                        <th width="80" class="text-center">Estado</th>
                        <th width="80" class="text-center">Destacado</th>
                        <th width="150" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se carga vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para vista rápida -->
<div class="modal fade" id="modalVistaRapida" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vista Rápida del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoVistaRapida">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>
<script>
const placeholderImage = '<?php echo asset('img/placeholder.svg'); ?>';
const formatGs = (value) => new Intl.NumberFormat('es-PY', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
}).format(Number(value) || 0);
let tablaProductos;

$(document).ready(function() {
    // Inicializar DataTable
    tablaProductos = $('#tablaProductos').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?php echo url('/admin/productos/obtenerProductosJson'); ?>',
            dataSrc: function(json) {
                $('#totalProductos').text(json.data.length);
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { 
                data: 'imagen',
                render: function(data, type, row) {
                    const src = data || placeholderImage;
                    return `<img src="${src}"
                                 class="img-thumbnail"
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.src='${placeholderImage}'">`;
                },
                orderable: false
            },
            { 
                data: 'nombre',
                render: function(data, type, row) {
                    let html = `<strong>${data}</strong>`;
                    if (row.descripcion_corta) {
                        html += `<br><small class="text-muted">${row.descripcion_corta.substring(0, 50)}...</small>`;
                    }
                    return html;
                }
            },
            { 
                data: 'marca_nombre',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'categoria_nombre',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'referencia',
                render: function(data) {
                    return `<code class="small">${data}</code>`;
                }
            },
            { 
                data: 'precio',
                className: 'text-end',
                render: function(data) {
                    return `<strong>Gs ${formatGs(data)}</strong>`;
                }
            },
            { 
                data: 'stock',
                className: 'text-center',
                render: function(data, type, row) {
                    let badgeClass = 'bg-success';
                    if (data == 0) {
                        badgeClass = 'bg-danger';
                    } else if (data <= row.stock_minimo) {
                        badgeClass = 'bg-warning';
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { 
                data: 'activo',
                className: 'text-center',
                render: function(data) {
                    return data == 1 
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            { 
                data: 'destacado',
                className: 'text-center',
                render: function(data) {
                    return data == 1 
                        ? '<i class="bi bi-star-fill text-warning" title="Destacado"></i>'
                        : '<i class="bi bi-star text-muted" title="No destacado"></i>';
                }
            },
            { 
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    let html = '<div class="btn-group btn-group-sm">';
                    
                    html += `<button class="btn btn-outline-info" 
                                     onclick="verProducto(${row.id})" 
                                     title="Vista rápida">
                                <i class="bi bi-eye"></i>
                             </button>`;
                    
                    <?php if (AuthController::tienePermiso('productos.editar')): ?>
                    html += `<a href="<?php echo url('/admin/productos/editar/'); ?>${row.id}"
                                class="btn btn-outline-primary" 
                                title="Editar">
                                <i class="bi bi-pencil"></i>
                             </a>`;
                    <?php endif; ?>
                    
                    <?php if (AuthController::tienePermiso('productos.eliminar')): ?>
                    html += `<button class="btn btn-outline-danger" 
                                     onclick="eliminarProducto(${row.id}, '${row.nombre}')" 
                                     title="Eliminar">
                                <i class="bi bi-trash"></i>
                             </button>`;
                    <?php endif; ?>
                    
                    html += '</div>';
                    return html;
                }
            }
        ],
        language: typeof dataTableLangEs !== 'undefined' ? dataTableLangEs : {},
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });
});

// Recargar tabla
function recargarTabla() {
    tablaProductos.ajax.reload();
    mostrarNotificacion('Tabla actualizada', 'info');
}

// Filtrar tabla
function filtrarTabla() {
    const categoria = $('#filtroCat').val();
    const marca = $('#filtroMarca').val();
    const estado = $('#filtroEstado').val();
    const stock = $('#filtroStock').val();
    
    // Aplicar filtros
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            const row = tablaProductos.row(dataIndex).data();
            
            if (categoria && row.categoria_id != categoria) return false;
            if (marca && row.marca_id != marca) return false;
            if (estado !== '' && row.activo != estado) return false;
            
            if (stock === 'disponible' && row.stock <= 0) return false;
            if (stock === 'bajo' && row.stock > row.stock_minimo) return false;
            if (stock === 'agotado' && row.stock > 0) return false;
            
            return true;
        }
    );
    
    tablaProductos.draw();
    $.fn.dataTable.ext.search.pop();
}

// Limpiar filtros
function limpiarFiltros() {
    $('#filtroCat, #filtroMarca, #filtroEstado, #filtroStock').val('');
    tablaProductos.search('').draw();
}

// Ver producto (vista rápida)
function verProducto(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalVistaRapida'));
    modal.show();
    
    fetch(`<?php echo url('/admin/productos/ver/'); ?>${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const p = data.producto;
                document.getElementById('contenidoVistaRapida').innerHTML = `
                    <div class="row">
                        <div class="col-md-4">
                            <img src="${p.imagen || placeholderImage}"
                                 class="img-fluid rounded"
                                 alt="${p.nombre}"
                                 onerror="this.src='${placeholderImage}'">
                        </div>
                        <div class="col-md-8">
                            <h4>${p.nombre}</h4>
                            <p class="text-muted">${p.descripcion || 'Sin descripción'}</p>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Referencia:</strong> ${p.referencia}<br>
                                    <strong>Marca:</strong> ${p.marca_nombre || '-'}<br>
                                    <strong>Categoría:</strong> ${p.categoria_nombre || '-'}<br>
                                    <strong>Sector:</strong> ${p.sector_nombre || '-'}
                                </div>
                                <div class="col-6">
                                    <strong>Precio:</strong> Gs ${formatGs(p.precio)}<br>
                                    <strong>Stock:</strong> ${p.stock} unidades<br>
                                    <strong>Estado:</strong> ${p.activo ? 'Activo' : 'Inactivo'}<br>
                                    <strong>Destacado:</strong> ${p.destacado ? 'Sí' : 'No'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                } else {
                document.getElementById('contenidoVistaRapida').innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message || 'No se pudo cargar la información del producto.'}
                    </div>
                `;
            }
            })
        .catch(() => {
            document.getElementById('contenidoVistaRapida').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-wifi-off me-2"></i>
                    Ocurrió un error al cargar la vista rápida. Intente nuevamente.
                </div>
            `;
        });
}

// Eliminar producto
function eliminarProducto(id, nombre) {
    if (!confirm(`¿Está seguro de eliminar el producto "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch(`<?php echo url('/admin/productos/eliminar/'); ?>${id}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Producto eliminado exitosamente', 'success');
            tablaProductos.ajax.reload();
        } else {
            mostrarNotificacion(data.message || 'Error al eliminar', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar producto', 'error');
    });
}

// Exportar a Excel
function exportarExcel() {
    window.location.href = '<?php echo url('/admin/productos/exportar'); ?>';
}
</script>