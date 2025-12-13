<?php
$usuario = AuthController::usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Panel de Administración'; ?> - Mi Tienda</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }
        
        .sidebar-menu .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }
        
        .sidebar-menu .nav-link i {
            width: 24px;
            margin-right: 0.5rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        /* Top Navbar */
        .top-navbar {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .dropdown-menu {
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Content Area */
        .content-area {
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
        /* Stats Cards */
        .stat-card {
            border-left: 4px solid;
            padding: 1.5rem;
        }
        
        .stat-card.primary {
            border-left-color: #667eea;
        }
        
        .stat-card.success {
            border-left-color: #48bb78;
        }
        
        .stat-card.warning {
            border-left-color: #f6ad55;
        }
        
        .stat-card.danger {
            border-left-color: #f56565;
        }
        
        .stat-card .stat-icon {
            font-size: 2.5rem;
            opacity: 0.2;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-area {
                padding: 1rem;
            }
        }
        
        /* Mobile Toggle */
        .sidebar-toggle {
            display: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
        
        /* Table Styles */
        .table {
            background-color: white;
        }
        
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #718096;
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-shop" style="font-size: 2rem;"></i>
        <h4>Mi Tienda</h4>
        <small>Panel Admin</small>
    </div>
    
    <nav class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'dashboard' ? 'active' : ''; ?>" href="<?php echo url('/admin/dashboard'); ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <div class="px-3 text-white-50 small fw-bold">PRODUCTOS</div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'productos' ? 'active' : ''; ?>" href="<?php echo url('/admin/productos'); ?>">
                    <i class="bi bi-box-seam"></i> Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'categorias' ? 'active' : ''; ?>" href="<?php echo url('/admin/categorias'); ?>">
                    <i class="bi bi-grid"></i> Categorías
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'marcas' ? 'active' : ''; ?>" href="<?php echo url('/admin/marcas'); ?>">
                    <i class="bi bi-tag"></i> Marcas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'sectores' ? 'active' : ''; ?>" href="<?php echo url('/admin/sectores'); ?>">
                    <i class="bi bi-building"></i> Sectores
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <div class="px-3 text-white-50 small fw-bold">PRECIOS</div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'listas-precios' ? 'active' : ''; ?>" href="<?php echo url('/admin/listas-precios'); ?>">
                    <i class="bi bi-currency-dollar"></i> Listas de Precios
                </a>
            </li>
            
            <?php if (AuthController::tienePermiso('usuarios.ver')): ?>
            <li class="nav-item mt-3">
                <div class="px-3 text-white-50 small fw-bold">ADMINISTRACIÓN</div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina ?? '') == 'usuarios' ? 'active' : ''; ?>" href="<?php echo url('/admin/usuarios'); ?>">
                    <i class="bi bi-people"></i> Usuarios
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-3">
                <div class="px-3 text-white-50 small fw-bold">REPORTES</div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo url('/admin/reportes'); ?>">
                    <i class="bi bi-graph-up"></i> Reportes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo url('/admin/auditoria'); ?>">
                    <i class="bi bi-clipboard-data"></i> Auditoría
                </a>
            </li>
        </ul>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url('/admin/dashboard'); ?>">Inicio</a></li>
                    <?php if (isset($breadcrumb)): ?>
                        <?php foreach ($breadcrumb as $item): ?>
                            <?php if (isset($item['url'])): ?>
                                <li class="breadcrumb-item"><a href="<?php echo $item['url']; ?>"><?php echo $item['text']; ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?php echo $item['text']; ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
        
        <div class="user-menu">
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none text-dark dropdown-toggle d-flex align-items-center gap-2" 
                        type="button" 
                        data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                    </div>
                    <div class="text-start d-none d-md-block">
                        <div class="fw-semibold"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($usuario['rol']); ?></small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/proyecto/public/admin/perfil"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="/proyecto/public/admin/configuracion"><i class="bi bi-gear me-2"></i> Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/proyecto/public/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Content Area -->
    <div class="content-area"</div>