<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar .nav-link[aria-expanded="true"] .transition-icon {
        transform: rotate(180deg);
    }
    .sidebar .transition-icon {
        transition: transform 0.3s ease;
    }
    .sidebar .collapse .nav-link {
        margin: 0 15px 0 30px !important;
        padding: 5px 15px !important;
        font-size: 0.8rem !important;
        opacity: 0.8;
    }
    .sidebar .collapse .nav-link:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .sidebar .collapse .nav-link.active-sub {
        opacity: 1;
        color: #fff !important;
        font-weight: 600;
        background: transparent !important;
        box-shadow: none !important;
    }
    :root {
        --sidebar-width: 260px;
        --top-navbar-height: 70px;
    }
    .sidebar {
        display: flex !important;
        flex-direction: column !important;
        background: linear-gradient(180deg, #1a1c1e 0%, #000 100%) !important;
        height: 100vh !important;
        width: var(--sidebar-width) !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1050 !important;
        border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
        transition: all 0.3s ease !important;
    }
    .sidebar .nav.flex-column {
        flex: 1;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .sidebar .nav.flex-column::-webkit-scrollbar {
        display: none;
    }
    .sidebar .nav-link {
        padding: 8px 15px !important;
        margin: 1px 12px !important;
        font-size: 0.9rem !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        color: rgba(255, 255, 255, 0.7) !important;
        border-radius: 10px !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
    }
    .sidebar .nav-link:hover {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .sidebar .nav-link.active {
        color: #fff !important;
        font-weight: 700 !important;
        background: linear-gradient(90deg, #4361ee 0%, #3f37c9 100%) !important;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3) !important;
    }
    .main-content {
        margin-left: var(--sidebar-width) !important;
        min-height: 100vh !important;
        padding-top: var(--top-navbar-height) !important;
        transition: all 0.3s ease !important;
    }
    .top-navbar {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        left: var(--sidebar-width) !important;
        height: var(--top-navbar-height) !important;
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        z-index: 1000 !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        transition: all 0.3s ease !important;
    }

    .sidebar .px-4.py-3 {
        padding: 0.75rem 1.25rem !important;
        font-size: 0.7rem !important;
    }
    /* Logo area */
    .sidebar .p-4.mb-3 {
        padding: 1.5rem !important;
        margin-bottom: 0 !important;
    }
    /* Profile area */
    .sidebar .mt-auto.p-4 {
        padding: 1rem 1.25rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
    .sidebar .rounded-3.bg-white.bg-opacity-10 {
        padding: 0.5rem !important;
        margin-bottom: 0.75rem !important;
    }
    @media (max-width: 992px) {
        .sidebar { left: calc(var(--sidebar-width) * -1) !important; }
        .sidebar.show { left: 0 !important; }
        .main-content { margin-left: 0 !important; }
        .top-navbar { left: 0 !important; }
    }
</style>
<!-- Sidebar -->
<div class="sidebar d-flex flex-column" id="sidebar">
    <div class="p-4 mb-3">
        <h4 class="text-white fw-bold mb-0">
            <i class="bi bi-qr-code-scan text-primary me-2"></i>
            San Jose
        </h4>
    </div>
    
    <nav class="nav flex-column mt-2">
        <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>
        <a href="homeowners.php" class="nav-link <?php echo $current_page == 'homeowners.php' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill"></i> Homeowners
        </a>
        
        <a class="nav-link <?php echo ($current_page == 'activity_logs.php' || $current_page == 'visitors_logs.php') ? 'active' : ''; ?>" 
           data-bs-toggle="collapse" href="#activityCollapse" role="button" 
           aria-expanded="<?php echo ($current_page == 'activity_logs.php' || $current_page == 'visitors_logs.php') ? 'true' : 'false'; ?>">
            <i class="bi bi-clock-history"></i> 
            <span>Activity Log</span>
            <i class="bi bi-chevron-down ms-auto small transition-icon" style="font-size: 0.8rem;"></i>
        </a>
        <div class="collapse <?php echo ($current_page == 'activity_logs.php' || $current_page == 'visitors_logs.php') ? 'show' : ''; ?>" id="activityCollapse">
            <div class="flex-column nav mt-1">
                <a href="activity_logs.php" class="nav-link <?php echo $current_page == 'activity_logs.php' ? 'active-sub' : ''; ?>">
                    <i class="bi bi-dot"></i> Homeowners
                </a>
                <a href="visitors_logs.php" class="nav-link <?php echo $current_page == 'visitors_logs.php' ? 'active-sub' : ''; ?>">
                    <i class="bi bi-dot"></i> Visitors
                </a>
            </div>
        </div>
        <div class="px-4 py-3 small text-uppercase text-white-50 fw-bold" style="letter-spacing: 1px;">Management</div>
        <a href="#" class="nav-link">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports
        </a>
        <a href="#" class="nav-link">
            <i class="bi bi-shield-check"></i> Guards
        </a>
        <a href="#" class="nav-link">
            <i class="bi bi-phone-fill"></i> Scanners
        </a>
        <div class="px-4 py-3 small text-uppercase text-white-50 fw-bold" style="letter-spacing: 1px;">System</div>
        <a href="#" class="nav-link">
            <i class="bi bi-gear-fill"></i> Settings
        </a>
    </nav>
    
    <div class="mt-auto p-4">
        <div class="d-flex align-items-center mb-4 p-3 rounded-3 bg-white bg-opacity-10">
            <div class="flex-shrink-0 me-3">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                    <?php echo substr($user['name'], 0, 1); ?>
                </div>
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-bold text-white text-truncate"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="text-white-50 small text-truncate"><?php echo htmlspecialchars($user['role']); ?></div>
            </div>
        </div>
        <a href="../auth/login.php" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>
