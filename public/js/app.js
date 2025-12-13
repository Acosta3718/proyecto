/**
 * Script principal de la aplicación
 * Gestiona la interacción con AJAX para carga dinámica de productos
 */

const PLACEHOLDER_IMAGE = window.PLACEHOLDER_IMAGE || '/proyecto/public/img/placeholder.svg';

class TiendaApp {
    constructor() {
        this.paginaActual = 1;
        this.productosPorPagina = 12;
        this.filtrosActivos = {
            marca: [],
            categoria: [],
            sector: [],
            busqueda: ''
        };
        this.ordenamiento = '';
        
        this.init();
    }
    
    init() {
        this.cargarProductosDesdeURL();
        this.setupEventListeners();
        this.cargarProductos();
    }
    
    /**
     * Cargar filtros desde URL (para mantener estado en refresh)
     */
    cargarProductosDesdeURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Cargar búsqueda
        if (urlParams.has('busqueda')) {
            this.filtrosActivos.busqueda = urlParams.get('busqueda');
            document.getElementById('busqueda').value = this.filtrosActivos.busqueda;
        }
        
        // Cargar página
        if (urlParams.has('pagina')) {
            this.paginaActual = parseInt(urlParams.get('pagina'));
        }
        
        // Cargar ordenamiento
        if (urlParams.has('ordenar')) {
            this.ordenamiento = urlParams.get('ordenar');
            document.getElementById('ordenar').value = this.ordenamiento;
        }
        
        // Cargar productos por página
        if (urlParams.has('porPagina')) {
            this.productosPorPagina = parseInt(urlParams.get('porPagina'));
            document.getElementById('porPagina').value = this.productosPorPagina;
        }
    }
    
    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Búsqueda
        document.getElementById('formBusqueda')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.filtrosActivos.busqueda = document.getElementById('busqueda').value;
            this.paginaActual = 1;
            this.cargarProductos();
            this.actualizarURL();
        });
        
        // Filtros de checkbox
        document.querySelectorAll('.filtro-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.handleFiltroChange(e.target);
            });
        });
        
        // Limpiar filtros
        document.getElementById('limpiarFiltros')?.addEventListener('click', () => {
            this.limpiarFiltros();
        });
        
        // Ordenamiento
        document.getElementById('ordenar')?.addEventListener('change', (e) => {
            this.ordenamiento = e.target.value;
            this.cargarProductos();
            this.actualizarURL();
        });
        
        // Productos por página
        document.getElementById('porPagina')?.addEventListener('change', (e) => {
            this.productosPorPagina = parseInt(e.target.value);
            this.paginaActual = 1;
            this.cargarProductos();
            this.actualizarURL();
        });
        
        // Paginación (delegación de eventos)
        document.getElementById('paginacion')?.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.target.tagName === 'A' && e.target.dataset.pagina) {
                this.paginaActual = parseInt(e.target.dataset.pagina);
                this.cargarProductos();
                this.actualizarURL();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
    
    /**
     * Manejar cambio en filtros
     */
    handleFiltroChange(checkbox) {
        const tipo = checkbox.dataset.filtro;
        const valor = checkbox.value;
        
        if (checkbox.checked) {
            if (!this.filtrosActivos[tipo].includes(valor)) {
                this.filtrosActivos[tipo].push(valor);
            }
        } else {
            const index = this.filtrosActivos[tipo].indexOf(valor);
            if (index > -1) {
                this.filtrosActivos[tipo].splice(index, 1);
            }
        }
        
        this.paginaActual = 1;
        this.cargarProductos();
        this.mostrarFiltrosActivos();
        this.actualizarURL();
    }
    
    /**
     * Cargar productos vía AJAX
     */
    async cargarProductos() {
        const gridProductos = document.getElementById('gridProductos');
        
        // Mostrar loading
        gridProductos.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando productos...</p>
            </div>
        `;
        
        try {
            // Preparar datos para enviar
            const formData = new FormData();
            formData.append('pagina', this.paginaActual);
            formData.append('porPagina', this.productosPorPagina);
            formData.append('busqueda', this.filtrosActivos.busqueda);
            formData.append('ordenar', this.ordenamiento);
            
            // Agregar filtros
            this.filtrosActivos.marca.forEach(marca => {
                formData.append('marca[]', marca);
            });
            this.filtrosActivos.categoria.forEach(categoria => {
                formData.append('categoria[]', categoria);
            });
            this.filtrosActivos.sector.forEach(sector => {
                formData.append('sector[]', sector);
            });
            
            // Hacer petición AJAX
            const response = await fetch('/productos/obtenerProductosAjax', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Error al cargar productos');
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.renderizarProductos(result.data.productos);
                this.renderizarPaginacion(result.data.total);
                document.getElementById('totalProductos').textContent = result.data.total;
            } else {
                throw new Error(result.message || 'Error desconocido');
            }
            
        } catch (error) {
            console.error('Error:', error);
            gridProductos.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        Error al cargar los productos. Por favor, intenta nuevamente.
                    </div>
                </div>
            `;
        }
    }
    
    /**
     * Renderizar productos en el grid
     */
    renderizarProductos(productos) {
        const grid = document.getElementById('gridProductos');
        
        if (productos.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>No se encontraron productos</h5>
                        <p>Intenta ajustar los filtros de búsqueda</p>
                    </div>
                </div>
            `;
            return;
        }
        
        grid.innerHTML = productos.map(producto => `
            <div class="col-lg-3 col-md-4 col-sm-6 fade-in">
                <div class="card producto-card h-100">
                    <img src="${producto.imagen || PLACEHOLDER_IMAGE}"
                         class="card-img-top"
                         alt="${this.escapeHtml(producto.nombre)}"
                         onerror="this.src='${PLACEHOLDER_IMAGE}'">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary mb-2 align-self-start">${this.escapeHtml(producto.marca)}</span>
                        <h6 class="card-title">${this.escapeHtml(producto.nombre)}</h6>
                        <p class="card-text text-muted small mb-1">Ref: ${this.escapeHtml(producto.referencia)}</p>
                        <p class="card-text text-muted small mb-2">
                            <i class="bi bi-grid"></i> ${this.escapeHtml(producto.categoria)}
                        </p>
                        ${producto.stock > 0 
                            ? `<span class="badge bg-success small mb-2">Stock: ${producto.stock}</span>`
                            : `<span class="badge bg-danger small mb-2">Agotado</span>`
                        }
                        <div class="mt-auto">
                            <p class="h5 text-primary mb-2">$${this.formatearPrecio(producto.precio)}</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm" 
                                        onclick="app.agregarAlCarrito(${producto.id})"
                                        ${producto.stock === 0 ? 'disabled' : ''}>
                                    <i class="bi bi-cart-plus"></i> Agregar
                                </button>
                                <a href="/productos/detalle/${producto.id}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye"></i> Ver detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Renderizar paginación
     */
    renderizarPaginacion(totalProductos) {
        const totalPaginas = Math.ceil(totalProductos / this.productosPorPagina);
        const paginacion = document.getElementById('paginacion');
        
        if (totalPaginas <= 1) {
            paginacion.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Botón anterior
        html += `
            <li class="page-item ${this.paginaActual === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-pagina="${this.paginaActual - 1}">
                    <i class="bi bi-chevron-left"></i> Anterior
                </a>
            </li>
        `;
        
        // Páginas
        for (let i = 1; i <= totalPaginas; i++) {
            // Mostrar primera página, última página, y páginas cercanas a la actual
            if (i === 1 || i === totalPaginas || (i >= this.paginaActual - 2 && i <= this.paginaActual + 2)) {
                html += `
                    <li class="page-item ${i === this.paginaActual ? 'active' : ''}">
                        <a class="page-link" href="#" data-pagina="${i}">${i}</a>
                    </li>
                `;
            } else if (i === this.paginaActual - 3 || i === this.paginaActual + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        // Botón siguiente
        html += `
            <li class="page-item ${this.paginaActual === totalPaginas ? 'disabled' : ''}">
                <a class="page-link" href="#" data-pagina="${this.paginaActual + 1}">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginacion.innerHTML = html;
    }
    
    /**
     * Mostrar filtros activos como badges
     */
    mostrarFiltrosActivos() {
        const contenedor = document.getElementById('filtrosActivos');
        let html = '';
        
        if (this.filtrosActivos.busqueda) {
            html += `
                <span class="badge bg-info badge-filtro" onclick="app.eliminarFiltro('busqueda', '')">
                    <i class="bi bi-search"></i> ${this.escapeHtml(this.filtrosActivos.busqueda)}
                    <i class="bi bi-x"></i>
                </span>
            `;
        }
        
        ['marca', 'categoria', 'sector'].forEach(tipo => {
            this.filtrosActivos[tipo].forEach(valor => {
                html += `
                    <span class="badge bg-primary badge-filtro" onclick="app.eliminarFiltro('${tipo}', '${this.escapeHtml(valor)}')">
                        ${this.escapeHtml(valor)} <i class="bi bi-x"></i>
                    </span>
                `;
            });
        });
        
        contenedor.innerHTML = html;
    }
    
    /**
     * Eliminar un filtro específico
     */
    eliminarFiltro(tipo, valor) {
        if (tipo === 'busqueda') {
            this.filtrosActivos.busqueda = '';
            document.getElementById('busqueda').value = '';
        } else {
            const index = this.filtrosActivos[tipo].indexOf(valor);
            if (index > -1) {
                this.filtrosActivos[tipo].splice(index, 1);
            }
            document.querySelectorAll(`input[data-filtro="${tipo}"][value="${valor}"]`).forEach(cb => {
                cb.checked = false;
            });
        }
        
        this.paginaActual = 1;
        this.cargarProductos();
        this.mostrarFiltrosActivos();
        this.actualizarURL();
    }
    
    /**
     * Limpiar todos los filtros
     */
    limpiarFiltros() {
        this.filtrosActivos = {
            marca: [],
            categoria: [],
            sector: [],
            busqueda: ''
        };
        this.ordenamiento = '';
        this.paginaActual = 1;
        
        document.querySelectorAll('.filtro-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('busqueda').value = '';
        document.getElementById('ordenar').value = '';
        
        this.cargarProductos();
        this.mostrarFiltrosActivos();
        this.actualizarURL();
    }
    
    /**
     * Actualizar URL con parámetros actuales
     */
    actualizarURL() {
        const params = new URLSearchParams();
        
        if (this.filtrosActivos.busqueda) {
            params.append('busqueda', this.filtrosActivos.busqueda);
        }
        if (this.ordenamiento) {
            params.append('ordenar', this.ordenamiento);
        }
        if (this.paginaActual > 1) {
            params.append('pagina', this.paginaActual);
        }
        if (this.productosPorPagina !== 12) {
            params.append('porPagina', this.productosPorPagina);
        }
        
        const newURL = params.toString() ? `?${params.toString()}` : window.location.pathname;
        window.history.pushState({}, '', newURL);
    }
    
    /**
     * Agregar producto al carrito
     */
    agregarAlCarrito(productoId) {
        // Aquí implementarías la lógica del carrito
        console.log('Agregar al carrito producto:', productoId);
        
        // Ejemplo de notificación
        this.mostrarNotificacion('Producto agregado al carrito', 'success');
    }
    
    /**
     * Mostrar notificación toast
     */
    mostrarNotificacion(mensaje, tipo = 'info') {
        // Implementación simple con alert (puedes mejorar con toast libraries)
        const iconos = {
            success: '✓',
            error: '✗',
            info: 'ℹ',
            warning: '⚠'
        };
        
        console.log(`${iconos[tipo]} ${mensaje}`);
        // Aquí podrías integrar Bootstrap Toast o alguna librería de notificaciones
    }
    
    /**
     * Formatear precio
     */
    formatearPrecio(precio) {
        return parseFloat(precio).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    /**
     * Escapar HTML para prevenir XSS
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Inicializar la aplicación cuando el DOM esté listo
let app;
document.addEventListener('DOMContentLoaded', () => {
    app = new TiendaApp();
});