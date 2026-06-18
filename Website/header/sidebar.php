<?php
/**
 * Sidebar Navigation Component
 * Barra lateral con soporte responsive y offcanvas en móviles
 */

$current_page = basename($_SERVER['PHP_SELF'], '.php');

function is_active($page) {
    global $current_page;
    return $current_page === $page ? 'active' : '';
}

// Menú centralizado — solo items visibles para el usuario
$menu_items = [
    /* Comentado temporalmente — descomentar si se reactiva
    [
        'href'  => 'dashboard.php',
        'icon'  => 'fas fa-home',
        'label' => 'Inicio',
        'page'  => 'dashboard',
    ],
    [
        'href'  => 'postulaciones.php',
        'icon'  => 'fas fa-clipboard-list',
        'label' => 'Postulaciones',
        'page'  => 'postulaciones',
    ],
    */
    [
        'href'  => '../user/documentos.php',
        'icon'  => 'fas fa-file-upload',
        'label' => 'Documentos',
        'page'  => 'documentos',
    ],
    [
        'href'  => '../user/datosPersonales.php',
        'icon'  => 'fas fa-user',
        'label' => 'Mi Perfil',
        'page'  => 'Mi Perfil',
    ],
    [
        'href'  => '../ajustes/ajustes.php',
        'icon'  => 'fas fa-cog',
        'label' => 'Ajustes',
        'page'  => 'ajustes',
    ],
];
?>

<button class="btn btn-primary d-lg-none position-fixed bottom-0 end-0 m-3"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#sidebarOffcanvas"
        aria-controls="sidebarOffcanvas"
        style="z-index:999;">
    <i class="fas fa-bars"></i>
</button>

<nav class="sidebar-desktop">
    <div class="sidebar-content">

        <div class="sidebar-header">
            <div style="display:flex; align-items:center; gap:0.7rem; margin-bottom:0.8rem;">
                <div style="width:45px; height:45px; background:rgba(255,255,255,0.2); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div>
                    <h6 style="font-size:0.9rem; margin:0; font-weight:700; letter-spacing:0.5px;">NewWays</h6>
                    <p style="font-size:0.7rem; margin:0; opacity:0.85;">Sistema de Selección</p>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <?php foreach ($menu_items as $item): ?>
            <li class="sidebar-item">
                <a href="<?php echo $item['href']; ?>"
                   class="sidebar-link <?php echo is_active($item['page']); ?>">
                    <i class="<?php echo $item['icon']; ?> me-2"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <hr class="sidebar-divider">

        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="/ds9p2/Website/views/user/logout.php" class="sidebar-link logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>

    </div>
</nav>

<div class="offcanvas offcanvas-start sidebar-offcanvas"
     tabindex="-1"
     id="sidebarOffcanvas"
     aria-labelledby="sidebarOffcanvasLabel">

    <div class="offcanvas-header"
         style="background:linear-gradient(180deg,#0c4ed4 0%,#0a3fb0 100%);">
        <div style="display:flex; align-items:center; gap:0.7rem;">
            <div style="width:40px; height:40px; background:rgba(255,255,255,0.2); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; color:white;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div style="color:white;">
                <h6 style="font-size:0.85rem; margin:0; font-weight:700; letter-spacing:0.5px;">NewWays</h6>
                <p style="font-size:0.65rem; margin:0; opacity:0.85;">Sistema de Selección</p>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
        <ul class="offcanvas-menu">
            <?php foreach ($menu_items as $item): ?>
            <li class="offcanvas-item">
                <a href="<?php echo $item['href']; ?>"
                   class="offcanvas-link <?php echo is_active($item['page']); ?>"
                   data-bs-dismiss="offcanvas">
                    <i class="<?php echo $item['icon']; ?> me-2"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
            <?php endforeach; ?>

            <li class="offcanvas-divider"></li>

            <li class="offcanvas-item">
                <a href="/ds9p2/Website/views/user/logout.php"
                   class="offcanvas-link logout"
                   data-bs-dismiss="offcanvas">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </div>

</div>