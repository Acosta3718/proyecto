<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Productos Disponibles</h5>
            </div>
            <div class="card-body">
                <input type="text" class="form-control mb-3" placeholder="Buscar..." 
                       onkeyup="filtrarProductos(this)">
                <div class="list-group" id="productosDisponibles">
                    <?php foreach ($productosDisponibles as $p): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($p['nombre']); ?></strong><br>
                                <small>Precio base: $<?php echo $p['precio']; ?></small>
                            </div>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="asignarProducto(<?php echo $p['id']; ?>)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Productos Asignados</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($productosAsignados as $p): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <strong><?php echo htmlspecialchars($p['nombre']); ?></strong>
                                <div class="input-group input-group-sm mt-2">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" 
                                           value="<?php echo $p['precio_lista']; ?>"
                                           onchange="actualizarPrecio(<?php echo $p['id']; ?>, this.value)">
                                </div>
                            </div>
                            <button class="btn btn-sm btn-danger ms-2" 
                                    onclick="quitarProducto(<?php echo $p['id']; ?>)">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>