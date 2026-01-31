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
        padding: 8px 15px !important;
        font-size: 0.85rem !important;
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
</style>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
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
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 40px; height: 40px;">
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
