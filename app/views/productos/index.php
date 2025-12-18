<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        .filtros-sidebar {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        
        .producto-card {
            transition: transform 0.2s;
            height: 100%;
        }
        
        .producto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .filtro-section {
            margin-bottom: 1.5rem;
        }
        
        .badge-filtro {
            cursor: pointer;
            margin: 3px;
        }
        
        /* En pantallas grandes, sticky */
        @media (min-width: 769px) {
            .filtros-sidebar {
                position: sticky;
                top: 20px;
            }
        }

        /* Overlay para móviles */
        .filtros-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        .filtros-overlay.show {
            display: block;
        }

        /* Estilos para móviles */
        @media (max-width: 768px) {
            .col-filtros-mobile {
                position: fixed;
                top: 0;
                left: -100%;
                width: 80%;
                max-width: 320px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease-in-out;
                background: white;
                overflow-y: auto;
            }

            .col-filtros-mobile.show {
                left: 0;
            }

            .filtros-sidebar {
                position: static;
                height: 100%;
                border-radius: 0;
            }
        }

        @media (min-width: 769px) {
            .filtros-overlay {
                display: none !important;
            }

            .btn-cerrar-filtros {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<!-- Navbar con Buscador -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="bi bi-shop"></i> Mi Tienda
        </a>
        
        <button class="navbar-toggler d-md-none" type="button" id="navbarToggler">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="d-flex flex-grow-1 align-items-center gap-3">
            <form class="d-flex flex-grow-1 justify-content-center" id="formBusqueda" style="max-width: 650px;">
                <div class="input-group">
                    <input type="text" class="form-control" id="busqueda"
                           placeholder="Buscar por descripción, marca o referencia..."
                           name="busqueda" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ; ?>">
                    <button class="btn btn-light" type="submit">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="bi bi-cart3"></i> Carrito (0)</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Overlay para filtros en móviles -->
<div class="filtros-overlay" id="filtrosOverlay"></div>

<div class="container-fluid mt-4">
    <div class="row">
        
        <!-- Sidebar de Filtros -->
         <div class="col-lg-3 col-md-4 d-none d-md-block">
            <div class="filtros-sidebar" id="filtrosSidebarDesktop">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="limpiarFiltros">
                        Limpiar
                    </button>
                </div>
                
                <!-- Filtros Activos -->
                <div id="filtrosActivosDesktop" class="mb-3"></div>

                <!-- Filtro por Marca -->
                <div class="filtro-section">
                    <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtroMarcaDesktop">
                        <span><i class="bi bi-tag"></i> Marca</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse show mt-2" id="filtroMarcaDesktop">
                        <?php if (!empty($marcas)): ?>
                            <?php foreach ($marcas as $i => $marca): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-checkbox"
                                        type="checkbox"
                                        value="<?= htmlspecialchars($marca['nombre']) ?>"
                                        id="marcaDesktop<?= $i ?>"
                                        data-filtro="marca">
                                    <label class="form-check-label" for="marcaDesktop<?= $i ?>">
                                        <?= htmlspecialchars($marca['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay marcas registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtro por Categoría -->
                <div class="filtro-section">
                    <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtroCategoriaDesktop">
                        <span><i class="bi bi-grid"></i> Categoría</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse show mt-2" id="filtroCategoriaDesktop">
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $i => $categoria): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-checkbox"
                                        type="checkbox"
                                        value="<?= htmlspecialchars($categoria['nombre']) ?>"
                                        id="catDesktop<?= $i ?>"
                                        data-filtro="categoria">
                                    <label class="form-check-label" for="catDesktop<?= $i ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay categorías registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtro por Sector -->
                <div class="filtro-section">
                    <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtroSectorDesktop">
                        <span><i class="bi bi-building"></i> Sector</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse show mt-2" id="filtroSectorDesktop">
                        <?php if (!empty($sectores)): ?>
                            <?php foreach ($sectores as $i => $sector): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-checkbox"
                                        type="checkbox"
                                        value="<?= htmlspecialchars($sector['nombre']) ?>"
                                        id="sectorDesktop<?= $i ?>"
                                        data-filtro="sector">
                                    <label class="form-check-label" for="sectorDesktop<?= $i ?>">
                                        <?= htmlspecialchars($sector['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay sectores registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sidebar de Filtros para Móviles -->
        <div class="col-filtros-mobile d-md-none">
            <div class="filtros-sidebar" id="filtrosSidebar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="limpiarFiltrosMobile">
                            Limpiar
                        </button>
                        <button class="btn btn-sm btn-close btn-cerrar-filtros" id="cerrarFiltros" aria-label="Cerrar"></button>
                    </div>
                </div>

                <!-- Filtros Activos -->
                <div id="filtrosActivos" class="mb-3"></div>
                
                <!-- Filtro por Marca -->
                <div class="filtro-section">
                    <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtroMarca">
                        <span><i class="bi bi-tag"></i> Marca</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse show mt-2" id="filtroMarca">
                        <?php if (!empty($marcas)): ?>
                            <?php foreach ($marcas as $i => $marca): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-checkbox"
                                        type="checkbox"
                                        value="<?= htmlspecialchars($marca['nombre']) ?>"
                                        id="marca<?= $i ?>" 
                                        data-filtro="marca">
                                    <label class="form-check-label" for="marca<?= $i ?>">
                                        <?= htmlspecialchars($marca['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay marcas registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Filtro por Categoría -->
                <div class="filtro-section">
                    <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtroCategoria">
                        <span><i class="bi bi-grid"></i> Categoría</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse show mt-2" id="filtroCategoria">
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $i => $categoria): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-checkbox"
                                        type="checkbox"
                                        value="<?= htmlspecialchars($categoria['nombre']) ?>" 
                                        id="cat<?= $i ?>"
                                        data-filtro="categoria">
                                    <label class="form-check-label" for="cat<?= $i ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay categorías registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Filtro por Sector -->
                <div class="filtro-section">
                    <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtroSector">
                        <span><i class="bi bi-building"></i> Sector</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="collapse show mt-2" id="filtroSector">
                        <?php if (!empty($sectores)): ?>
                            <?php foreach ($sectores as $i => $sector): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-checkbox"
                                        type="checkbox"
                                        value="<?= htmlspecialchars($sector['nombre']) ?>" 
                                        id="sector<?= $i ?>" 
                                        data-filtro="sector">
                                    <label class="form-check-label" for="sector<?= $i ?>">
                                        <?= htmlspecialchars($sector['nombre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No hay sectores registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Área de Productos -->
        <div class="col-lg-9 col-md-8 col-12">
            
            <!-- Barra de resultados y ordenamiento -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div>
                    <h5 class="mb-0">Productos encontrados: <span id="totalProductos" class="text-primary">48</span></h5>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="ordenar" style="width: auto;">
                        <option value="">Ordenar por</option>
                        <option value="precio_asc">Precio: Menor a Mayor</option>
                        <option value="precio_desc">Precio: Mayor a Menor</option>
                        <option value="nombre_asc">Nombre: A-Z</option>
                        <option value="nombre_desc">Nombre: Z-A</option>
                    </select>
                    <select class="form-select form-select-sm" id="porPagina" style="width: auto;">
                        <option value="12">12 por página</option>
                        <option value="24">24 por página</option>
                        <option value="48">48 por página</option>
                    </select>
                </div>
            </div>
            
            <!-- Grid de Productos -->
            <div class="row g-3" id="gridProductos">
                <!-- Los productos se cargarán aquí dinámicamente -->
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            
            <!-- Paginación -->
            <nav aria-label="Navegación de páginas" class="mt-4">
                <ul class="pagination justify-content-center" id="paginacion">
                    <!-- La paginación se generará dinámicamente -->
                </ul>
            </nav>
            
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Variables globales
let paginaActual = 1;
let productosPorPagina = 12;
let filtrosActivos = {
    marca: [],
    categoria: [],
    sector: [],
    busqueda: ''
};
<?php
// --- Bloque PHP antes del <script> ---
// Asegurar imagen y precio por defecto
$placeholder = asset('img/placeholder.svg');
foreach ($productos as &$p) {
    if (empty($p['imagen'])) $p['imagen'] = $placeholder;
    if (!isset($p['precio'])) $p['precio'] = 0;
}
unset($p);
?>

const productosEjemplo = <?php echo json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

const formatGs = (value) => new Intl.NumberFormat('es-PY', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
}).format(Number(value) || 0);

// Función para obtener productos filtrados
function obtenerProductosFiltrados() {
    return productosEjemplo.filter(producto => {
        // Convertimos valores potencialmente nulos a strings vacíos
        const nombre = (producto.nombre || '').toString().toLowerCase();
        const marca = (producto.marca || '').toString().toLowerCase();
        const referencia = (producto.referencia || '').toString().toLowerCase();
        const categoria = (producto.categoria || '').toString();
        const sector = (producto.sector || '').toString();

        // Filtro de búsqueda
        if (filtrosActivos.busqueda) {
            const busqueda = filtrosActivos.busqueda.toLowerCase();
            const coincide =
                nombre.includes(busqueda) ||
                marca.includes(busqueda) ||
                referencia.includes(busqueda);
            if (!coincide) return false;
        }

       if (filtrosActivos.marca.length > 0) {
            const coincideMarca = filtrosActivos.marca.some(
                f => f.toLowerCase() === marca
            );
            if (!coincideMarca) return false;
        }

        // Filtro por categoría
        if (filtrosActivos.categoria.length > 0 && !filtrosActivos.categoria.includes(producto.categoria)) {
            return false;
        }

        // Filtro por sector
        if (filtrosActivos.sector.length > 0 && !filtrosActivos.sector.includes(producto.sector)) {
            return false;
        }

        return true;
    });
}

// Función para renderizar productos
function renderizarProductos() {
    const productosFiltrados = obtenerProductosFiltrados();
    const inicio = (paginaActual - 1) * productosPorPagina;
    const fin = inicio + productosPorPagina;
    const productosActuales = productosFiltrados.slice(inicio, fin);
    
    const grid = document.getElementById('gridProductos');
    
    if (productosActuales.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No se encontraron productos</p></div>';
        return;
    }
    
    grid.innerHTML = productosActuales.map(producto => `
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card producto-card h-100">
                <img src="${producto.imagen}" class="card-img-top" alt="${producto.nombre}">
                <div class="card-body d-flex flex-column">
                    <span class="badge bg-secondary mb-2 align-self-start">${producto.marca}</span>
                    <h6 class="card-title">${producto.nombre}</h6>
                    <p class="card-text text-muted small">Ref: ${producto.referencia}</p>
                    <div class="mt-auto">
                        <p class="h5 text-primary mb-2">Gs ${formatGs(producto.precio)}</p>
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-cart-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    document.getElementById('totalProductos').textContent = productosFiltrados.length;
    renderizarPaginacion(productosFiltrados.length);
}

// Función para renderizar paginación
function renderizarPaginacion(totalProductos) {
    const totalPaginas = Math.ceil(totalProductos / productosPorPagina);
    const paginacion = document.getElementById('paginacion');
    
    if (totalPaginas <= 1) {
        paginacion.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Botón anterior
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-pagina="${paginaActual - 1}">Anterior</a>
    </li>`;
    
    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
                <a class="page-link" href="#" data-pagina="${i}">${i}</a>
            </li>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Botón siguiente
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
        <a class="page-link" href="#" data-pagina="${paginaActual + 1}">Siguiente</a>
    </li>`;
    
    paginacion.innerHTML = html;
}

// Función para mostrar filtros activos
function mostrarFiltrosActivos() {
    const contenedorDesktop = document.getElementById('filtrosActivosDesktop');
    const contenedorMobile = document.getElementById('filtrosActivos');
    let html = '';
    
    if (filtrosActivos.busqueda) {
        html += `<span class="badge bg-info badge-filtro">
            <i class="bi bi-search"></i> ${filtrosActivos.busqueda}
            <i class="bi bi-x" onclick="eliminarFiltro('busqueda', '')"></i>
        </span>`;
    }
    
    ['marca', 'categoria', 'sector'].forEach(tipo => {
        filtrosActivos[tipo].forEach(valor => {
            html += `<span class="badge bg-primary badge-filtro">
                ${valor} <i class="bi bi-x" onclick="eliminarFiltro('${tipo}', '${valor}')"></i>
            </span>`;
        });
    });
    
    contenedor.innerHTML = html;

    if (contenedorDesktop) contenedorDesktop.innerHTML = html;
    if (contenedorMobile) contenedorMobile.innerHTML = html;
}

// Función para eliminar filtro
function eliminarFiltro(tipo, valor) {
    if (tipo === 'busqueda') {
        filtrosActivos.busqueda = '';
        document.getElementById('busqueda').value = '';
    } else {
        const index = filtrosActivos[tipo].indexOf(valor);
        if (index > -1) {
            filtrosActivos[tipo].splice(index, 1);
        }
        document.querySelectorAll(`input[data-filtro="${tipo}"][value="${valor}"]`).forEach(cb => cb.checked = false);
    }
    paginaActual = 1;
    renderizarProductos();
    mostrarFiltrosActivos();
}

// Event Listeners
const formBusqueda = document.getElementById('formBusqueda');
if (formBusqueda) {
    formBusqueda.addEventListener('submit', function(e) {
        e.preventDefault();
        filtrosActivos.busqueda = document.getElementById('busqueda').value;
        paginaActual = 1;
        renderizarProductos();
        mostrarFiltrosActivos();
    });
}

document.querySelectorAll('.filtro-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const tipo = this.dataset.filtro;
        const valor = this.value;
        
        if (this.checked) {
            if (!filtrosActivos[tipo].includes(valor)) {
                filtrosActivos[tipo].push(valor);
            }
        } else {
            const index = filtrosActivos[tipo].indexOf(valor);
            if (index > -1) {
                filtrosActivos[tipo].splice(index, 1);
            }
        }
        
        paginaActual = 1;
        renderizarProductos();
        mostrarFiltrosActivos();
    });
});

const btnLimpiarDesktop = document.getElementById('limpiarFiltros');
if (btnLimpiarDesktop) {
    btnLimpiarDesktop.addEventListener('click', function() {
        filtrosActivos = { marca: [], categoria: [], sector: [], busqueda: '' };
        document.querySelectorAll('.filtro-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('busqueda').value = '';
        paginaActual = 1;
        renderizarProductos();
        mostrarFiltrosActivos();
    });
}

const btnLimpiarMobile = document.getElementById('limpiarFiltrosMobile');
if (btnLimpiarMobile) {
    btnLimpiarMobile.addEventListener('click', function() {
        filtrosActivos = { marca: [], categoria: [], sector: [], busqueda: '' };
        document.querySelectorAll('.filtro-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('busqueda').value = '';
        paginaActual = 1;
        renderizarProductos();
        mostrarFiltrosActivos();
    });
}

const porPaginaSelect = document.getElementById('porPagina');
if (porPaginaSelect) {
    porPaginaSelect.addEventListener('change', function() {
        productosPorPagina = parseInt(this.value);
        paginaActual = 1;
        renderizarProductos();
        });
}

const paginacion = document.getElementById('paginacion');
if (paginacion) {
    paginacion.addEventListener('click', function(e) {
        e.preventDefault();
        if (e.target.tagName === 'A' && e.target.dataset.pagina) {
            paginaActual = parseInt(e.target.dataset.pagina);
            renderizarProductos();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
}

// Panel de filtros en móviles
const navbarToggler = document.getElementById('navbarToggler');
const filtrosOverlay = document.getElementById('filtrosOverlay');
const filtrosMobile = document.querySelector('.col-filtros-mobile');
const cerrarFiltros = document.getElementById('cerrarFiltros');

if (navbarToggler && filtrosMobile && filtrosOverlay) {
    navbarToggler.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        filtrosMobile.classList.add('show');
        filtrosOverlay.classList.add('show');
    });
}

if (cerrarFiltros && filtrosMobile && filtrosOverlay) {
    cerrarFiltros.addEventListener('click', function() {
        filtrosMobile.classList.remove('show');
        filtrosOverlay.classList.remove('show');
    });
}

if (filtrosOverlay && filtrosMobile) {
    filtrosOverlay.addEventListener('click', function() {
        filtrosMobile.classList.remove('show');
        filtrosOverlay.classList.remove('show');
    });
}

// Inicializar
renderizarProductos();
mostrarFiltrosActivos();
</script>

</body>
</html>