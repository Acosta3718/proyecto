<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Usuario</h5>
            </div>
            <div class="card-body">
                <form id="formUsuario" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo $esEdicion ? htmlspecialchars($usuario['nombre']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                   value="<?php echo $esEdicion ? htmlspecialchars($usuario['apellido']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo $esEdicion ? htmlspecialchars($usuario['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Contraseña <?php echo $esEdicion ? '' : '*'; ?>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               <?php echo $esEdicion ? '' : 'required'; ?>>
                        <?php if ($esEdicion): ?>
                        <div class="form-text">Dejar en blanco para mantener la actual</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                               value="<?php echo $esEdicion ? htmlspecialchars($usuario['telefono']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo $esEdicion ? htmlspecialchars($usuario['direccion']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol *</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol; ?>" 
                                    <?php echo ($esEdicion && $usuario['rol'] == $rol) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($rol); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                               <?php echo (!$esEdicion || $usuario['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Usuario activo</label>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="/admin/usuarios" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>