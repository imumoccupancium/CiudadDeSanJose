// ============================================
// QR Code Management System - Dashboard JavaScript
// ============================================

// Mock Data
const mockData = {
    stats: {
        totalQRCodes: 248,
        totalScans: 15847,
        activeLocations: 42,
        activeUsers: 156
    },

    qrCodes: [
        {
            id: 'QR-001',
            name: 'Main Entrance Info',
            code: 'ME-2024-001',
            location: 'Main Entrance',
            type: 'Information',
            scans: 1245,
            status: 'active',
            createdAt: '2024-01-15',
            url: 'https://ciudaddesanjose.com/info/entrance'
        },
        {
            id: 'QR-002',
            name: 'City Hall Directory',
            code: 'CH-2024-002',
            location: 'City Hall',
            type: 'Navigation',
            scans: 987,
            status: 'active',
            createdAt: '2024-01-18',
            url: 'https://ciudaddesanjose.com/directory'
        },
        {
            id: 'QR-003',
            name: 'Tourist Center Map',
            code: 'TC-2024-003',
            location: 'Tourist Center',
            type: 'Navigation',
            scans: 2156,
            status: 'active',
            createdAt: '2024-01-20',
            url: 'https://ciudaddesanjose.com/map'
        },
        {
            id: 'QR-004',
            name: 'Park Events Schedule',
            code: 'PA-2024-004',
            location: 'Park Area',
            type: 'Event',
            scans: 543,
            status: 'active',
            createdAt: '2024-01-22',
            url: 'https://ciudaddesanjose.com/events'
        },
        {
            id: 'QR-005',
            name: 'Emergency Services',
            code: 'ES-2024-005',
            location: 'Main Entrance',
            type: 'Service',
            scans: 234,
            status: 'inactive',
            createdAt: '2024-01-25',
            url: 'https://ciudaddesanjose.com/emergency'
        }
    ],

    activities: [
        {
            type: 'scan',
            icon: 'icon-scan',
            title: 'QR Code Scanned',
            description: 'Tourist Center Map was scanned by a visitor',
            time: '2 minutes ago'
        },
        {
            type: 'create',
            icon: 'icon-create',
            title: 'New QR Code Created',
            description: 'Admin created "Museum Guide" QR code',
            time: '15 minutes ago'
        },
        {
            type: 'scan',
            icon: 'icon-scan',
            title: 'Multiple Scans Detected',
            description: 'City Hall Directory received 45 scans in the last hour',
            time: '1 hour ago'
        },
        {
            type: 'update',
            icon: 'icon-update',
            title: 'QR Code Updated',
            description: 'Main Entrance Info URL was updated',
            time: '2 hours ago'
        },
        {
            type: 'create',
            icon: 'icon-create',
            title: 'Location Added',
            description: 'New location "Sports Complex" was added to the system',
            time: '3 hours ago'
        },
        {
            type: 'scan',
            icon: 'icon-scan',
            title: 'High Traffic Alert',
            description: 'Park Events Schedule reached 500 scans milestone',
            time: '5 hours ago'
        }
    ],

    scanData: {
        labels: ['Jan 24', 'Jan 25', 'Jan 26', 'Jan 27', 'Jan 28', 'Jan 29', 'Jan 30'],
        datasets: [
            {
                label: 'Total Scans',
                data: [420, 532, 489, 678, 745, 823, 891],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Unique Visitors',
                data: [320, 412, 378, 523, 589, 645, 712],
                borderColor: '#43e97b',
                backgroundColor: 'rgba(67, 233, 123, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },

    typeData: {
        labels: ['Information', 'Navigation', 'Event', 'Service'],
        datasets: [{
            data: [35, 28, 22, 15],
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(67, 233, 123, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(59, 130, 246, 0.8)'
            ],
            borderWidth: 0
        }]
    }
};

// ============================================
// DOM Elements
// ============================================

const elements = {
    sidebar: document.getElementById('sidebar'),
    sidebarToggle: document.getElementById('sidebarToggle'),
    sidebarClose: document.getElementById('sidebarClose'),
    themeToggle: document.getElementById('themeToggle'),
    searchInput: document.getElementById('searchInput'),
    quickActionBtn: document.getElementById('quickActionBtn'),
    createQRBtn: document.getElementById('createQRBtn'),
    navLinks: document.querySelectorAll('.nav-link')
};

// ============================================
// Initialization
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    initializeDashboard();
    setupEventListeners();
    loadDashboardData();
    initializeCharts();
    checkThemePreference();
});

// ============================================
// Dashboard Initialization
// ============================================

function initializeDashboard() {
    console.log('Dashboard initialized');
    animateStats();
}

// ============================================
// Event Listeners
// ============================================

function setupEventListeners() {
    // Sidebar toggle
    if (elements.sidebarToggle) {
        elements.sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (elements.sidebarClose) {
        elements.sidebarClose.addEventListener('click', toggleSidebar);
    }

    // Theme toggle
    if (elements.themeToggle) {
        elements.themeToggle.addEventListener('click', toggleTheme);
    }

    // Search functionality
    if (elements.searchInput) {
        elements.searchInput.addEventListener('input', handleSearch);
    }

    // Quick action button
    if (elements.quickActionBtn) {
        elements.quickActionBtn.addEventListener('click', openCreateQRModal);
    }

    // Create QR button
    if (elements.createQRBtn) {
        elements.createQRBtn.addEventListener('click', handleCreateQR);
    }

    // Navigation links
    elements.navLinks.forEach(link => {
        link.addEventListener('click', handleNavigation);
    });

    // Close sidebar on outside click (mobile)
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 991) {
            if (!elements.sidebar.contains(e.target) &&
                !elements.sidebarToggle.contains(e.target) &&
                elements.sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        }
    });
}

// ============================================
// Sidebar Functions
// ============================================

function toggleSidebar() {
    elements.sidebar.classList.toggle('active');
}

// ============================================
// Theme Functions
// ============================================

function toggleTheme() {
    const body = document.body;
    const icon = elements.themeToggle.querySelector('i');

    body.classList.toggle('dark-mode');

    if (body.classList.contains('dark-mode')) {
        icon.classList.remove('bi-moon-stars-fill');
        icon.classList.add('bi-sun-fill');
        localStorage.setItem('theme', 'dark');
    } else {
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-stars-fill');
        localStorage.setItem('theme', 'light');
    }
}

function checkThemePreference() {
    const savedTheme = localStorage.getItem('theme');
    const icon = elements.themeToggle.querySelector('i');

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        icon.classList.remove('bi-moon-stars-fill');
        icon.classList.add('bi-sun-fill');
    }
}

// ============================================
// Navigation Functions
// ============================================

function handleNavigation(e) {
    e.preventDefault();

    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });

    // Add active class to clicked item
    this.parentElement.classList.add('active');

    // Get page name
    const page = this.getAttribute('data-page');

    // Update breadcrumb and page title
    updatePageTitle(page);

    // Close sidebar on mobile
    if (window.innerWidth <= 991) {
        toggleSidebar();
    }

    // Show notification (mock)
    showNotification(`Navigated to ${page} page`, 'info');
}

function updatePageTitle(page) {
    const pageTitle = document.querySelector('.page-title');
    const breadcrumbActive = document.querySelector('.breadcrumb-item.active');

    const pageTitles = {
        'dashboard': 'Dashboard',
        'qr-codes': 'QR Codes',
        'analytics': 'Analytics',
        'locations': 'Locations',
        'users': 'Users',
        'settings': 'Settings'
    };

    if (pageTitle) {
        pageTitle.textContent = pageTitles[page] || 'Dashboard';
    }

    if (breadcrumbActive) {
        breadcrumbActive.textContent = pageTitles[page] || 'Dashboard';
    }
}

// ============================================
// Search Functions
// ============================================

function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    console.log('Searching for:', searchTerm);

    // Mock search functionality
    if (searchTerm.length > 2) {
        const results = mockData.qrCodes.filter(qr =>
            qr.name.toLowerCase().includes(searchTerm) ||
            qr.location.toLowerCase().includes(searchTerm) ||
            qr.code.toLowerCase().includes(searchTerm)
        );

        console.log('Search results:', results);
        // In a real application, you would update the UI with search results
    }
}

// ============================================
// Load Dashboard Data
// ============================================

function loadDashboardData() {
    loadStats();
    loadRecentQRCodes();
    loadTopQRCodes();
    loadActivityFeed();
}

function loadStats() {
    // Animate stat values
    animateValue('totalQRCodes', 0, mockData.stats.totalQRCodes, 1500);
    animateValue('totalScans', 0, mockData.stats.totalScans, 1500);
    animateValue('activeLocations', 0, mockData.stats.activeLocations, 1500);
    animateValue('activeUsers', 0, mockData.stats.activeUsers, 1500);
}

function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    if (!element) return;

    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            element.textContent = end.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, 16);
}

function animateStats() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
}

// ============================================
// Load Recent QR Codes
// ============================================

function loadRecentQRCodes() {
    const tableBody = document.querySelector('#recentQRTable tbody');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    mockData.qrCodes.forEach(qr => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="qr-code-cell">
                    <div class="qr-thumbnail">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    <div class="qr-info">
                        <h6>${qr.name}</h6>
                        <p>${qr.code}</p>
                    </div>
                </div>
            </td>
            <td>${qr.location}</td>
            <td><span class="badge badge-info">${qr.type}</span></td>
            <td><strong>${qr.scans.toLocaleString()}</strong></td>
            <td>
                <span class="badge badge-${qr.status === 'active' ? 'success' : 'warning'}">
                    ${qr.status}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-icon btn-outline-primary" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// ============================================
// Load Top QR Codes
// ============================================

function loadTopQRCodes() {
    const topQRList = document.getElementById('topQRList');
    if (!topQRList) return;

    // Sort by scans and get top 5
    const topQRs = [...mockData.qrCodes]
        .sort((a, b) => b.scans - a.scans)
        .slice(0, 5);

    topQRList.innerHTML = '';

    topQRs.forEach((qr, index) => {
        const item = document.createElement('div');
        item.className = 'top-qr-item';
        item.innerHTML = `
            <div class="qr-rank">${index + 1}</div>
            <div class="qr-details">
                <h6>${qr.name}</h6>
                <p>${qr.location}</p>
            </div>
            <div class="qr-stats">
                <span class="scan-count">${qr.scans.toLocaleString()}</span>
                <span class="scan-label">scans</span>
            </div>
        `;
        topQRList.appendChild(item);
    });
}

// ============================================
// Load Activity Feed
// ============================================

function loadActivityFeed() {
    const activityFeed = document.getElementById('activityFeed');
    if (!activityFeed) return;

    activityFeed.innerHTML = '';

    mockData.activities.forEach(activity => {
        const item = document.createElement('div');
        item.className = 'activity-item';
        item.innerHTML = `
            <div class="activity-icon ${activity.icon}">
                <i class="bi bi-${getActivityIcon(activity.type)}"></i>
            </div>
            <div class="activity-content">
                <h6>${activity.title}</h6>
                <p>${activity.description}</p>
            </div>
            <div class="activity-time">${activity.time}</div>
        `;
        activityFeed.appendChild(item);
    });
}

function getActivityIcon(type) {
    const icons = {
        'scan': 'eye-fill',
        'create': 'plus-circle-fill',
        'update': 'pencil-fill',
        'delete': 'trash-fill'
    };
    return icons[type] || 'circle-fill';
}

// ============================================
// Initialize Charts
// ============================================

let scanChart, typeChart;

function initializeCharts() {
    initializeScanChart();
    initializeTypeChart();
}

function initializeScanChart() {
    const ctx = document.getElementById('scanChart');
    if (!ctx) return;

    scanChart = new Chart(ctx, {
        type: 'line',
        data: mockData.scanData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            family: 'Inter',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        family: 'Inter',
                        size: 13
                    },
                    bodyFont: {
                        family: 'Inter',
                        size: 12
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

function initializeTypeChart() {
    const ctx = document.getElementById('typeChart');
    if (!ctx) return;

    typeChart = new Chart(ctx, {
        type: 'doughnut',
        data: mockData.typeData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            family: 'Inter',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: {
                        family: 'Inter',
                        size: 13
                    },
                    bodyFont: {
                        family: 'Inter',
                        size: 12
                    },
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return `${label}: ${value}%`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
}

// ============================================
// Modal Functions
// ============================================

function openCreateQRModal() {
    const modal = new bootstrap.Modal(document.getElementById('createQRModal'));
    modal.show();
}

function handleCreateQR() {
    const form = document.getElementById('createQRForm');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = {
        name: document.getElementById('qrName').value,
        location: document.getElementById('qrLocation').value,
        type: document.getElementById('qrType').value,
        url: document.getElementById('qrURL').value
    };

    console.log('Creating QR Code:', formData);

    // Mock creation
    showNotification('QR Code created successfully!', 'success');

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('createQRModal'));
    modal.hide();

    // Reset form
    form.reset();

    // Update stats (mock)
    setTimeout(() => {
        mockData.stats.totalQRCodes++;
        animateValue('totalQRCodes', mockData.stats.totalQRCodes - 1, mockData.stats.totalQRCodes, 500);
    }, 500);
}

// ============================================
// Notification System
// ============================================

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed`;
    notification.style.cssText = `
        top: 90px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease;
    `;

    const icons = {
        'success': 'check-circle-fill',
        'info': 'info-circle-fill',
        'warning': 'exclamation-triangle-fill',
        'danger': 'x-circle-fill'
    };

    notification.innerHTML = `
        <i class="bi bi-${icons[type]} me-2"></i>
        ${message}
    `;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// Utility Functions
// ============================================

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatNumber(num) {
    return num.toLocaleString();
}

// ============================================
// Export for debugging
// ============================================

window.dashboardDebug = {
    mockData,
    elements,
    charts: { scanChart, typeChart }
};

console.log('Dashboard loaded successfully! Access debug info via window.dashboardDebug');
