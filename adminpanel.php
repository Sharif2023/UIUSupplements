<?php
session_start();

// Admin authentication check - redirect to login if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

// Fetch admin info
$admin_sql = "SELECT * FROM admins WHERE admin_id = ?";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param('i', $_SESSION['admin_id']);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();
$admin_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - UIU Supplements</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Modal Styles -->
    <link rel="stylesheet" href="assets/css/modal-styles.css">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary-color: #ec4899;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-bg: #0f172a;
            --dark-secondary: #1e293b;
            --dark-tertiary: #334155;
            --light-bg: #f8fafc;
            --light-secondary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            --border-color: #e2e8f0;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-secondary) 100%);
            padding: 30px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--dark-tertiary);
            border-radius: 3px;
        }

        .sidebar-brand {
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-brand h1 {
            font-family: "Montserrat", sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            text-transform: uppercase;
        }

        .title-word {
            animation: color-animation 4s linear infinite;
        }

        .title-word-1 {
            --color-1: #DF8453;
            --color-2: #3D8DAE;
            --color-3: #E4A9A8;
        }

        .title-word-2 {
            --color-1: #DBAD4A;
            --color-2: #ACCFCB;
            --color-3: #17494D;
        }

        .title-word-3 {
            --color-1: #ACCFCB;
            --color-2: #E4A9A8;
            --color-3: #ACCFCB;
        }

        .title-word-4 {
            --color-1: #3D8DAE;
            --color-2: #DF8453;
            --color-3: #E4A9A8;
        }

        @keyframes color-animation {
            0% { color: var(--color-1) }
            32% { color: var(--color-1) }
            33% { color: var(--color-2) }
            65% { color: var(--color-2) }
            66% { color: var(--color-3) }
            99% { color: var(--color-3) }
            100% { color: var(--color-1) }
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            color: var(--text-light);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 30px 10px;
        }

        .menu-item {
            padding: 14px 30px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            font-size: 15px;
            position: relative;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-color));
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .menu-item.active::before {
            transform: scaleY(1);
        }

        .menu-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            padding: 14px 30px;
            margin: 20px 30px 0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            text-decoration: none;
            display: block;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: white;
            height: var(--header-height);
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 40px 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            width: 300px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .notification-icon {
            position: relative;
            font-size: 22px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .notification-icon:hover {
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 60px;
            right: 80px;
            width: 360px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .notification-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .notification-header h4 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .mark-all-read {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .mark-all-read:hover {
            text-decoration: underline;
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .notification-item:hover {
            background: var(--light-bg);
        }

        .notification-item.unread {
            background: rgba(99, 102, 241, 0.05);
        }

        .notification-icon-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .notification-icon-small.user {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }

        .notification-icon-small.session {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .notification-icon-small.claim {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .notification-content span {
            font-size: 12px;
            color: var(--text-light);
        }

        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-light);
        }

        .notification-empty i {
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .admin-info {
            text-align: left;
        }

        .admin-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
        }

        .admin-role {
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Content Area */
        .content {
            padding: 40px;
        }

        .page-section {
            display: none;
        }

        .page-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            flex-shrink: 0;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Data Table */
        .table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .table-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .btn-secondary {
            background: var(--light-secondary);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        /* Remove underlines from all buttons and links inside tables */
        .btn, .btn:hover, .btn:focus, .btn:active,
        a.btn, a.btn:hover, a.btn:focus, a.btn:active {
            text-decoration: none !important;
        }

        /* Make icons inside buttons not intercept clicks */
        .btn i, button i {
            pointer-events: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--light-bg);
        }

        th {
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: var(--light-bg);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .mentor-photo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-available {
            background: #d1fae5;
            color: #065f46;
        }

        .status-not-available {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 35px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            margin-bottom: 25px;
        }

        .modal-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            padding: 18px 24px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 3000;
            animation: slideIn 0.3s ease;
            min-width: 300px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success {
            border-left: 4px solid var(--success-color);
        }

        .toast-error {
            border-left: 4px solid var(--danger-color);
        }

        .toast-icon {
            font-size: 20px;
        }

        .toast-success .toast-icon {
            color: var(--success-color);
        }

        .toast-error .toast-icon {
            color: var(--danger-color);
        }

        .toast-message {
            flex: 1;
            font-weight: 500;
            color: var(--text-primary);
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 20px;
        }

        .pagination button {
            padding: 8px 14px;
            border: 1px solid var(--border-color);
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination button.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .chart-container h4 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h4 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
        }

        .loading i {
            font-size: 32px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .search-box input {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 20px;
            }

            .content {
                padding: 20px;
            }

            .header-left h2 {
                font-size: 20px;
            }

            .search-box {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h1>
                <span class="title-word title-word-1">U</span>
                <span class="title-word title-word-2">I</span>
                <span class="title-word title-word-3">U</span>
                <span class="title-word title-word-4">Supplement</span>
            </h1>
        </div>

        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <div class="menu-item active" data-page="dashboard">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item" data-page="analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </div>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Management</div>
                <div class="menu-item" data-page="users">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </div>
                <div class="menu-item" data-page="mentors">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Mentors</span>
                </div>
                <div class="menu-item" data-page="rooms">
                    <i class="fas fa-building"></i>
                    <span>Rooms</span>
                </div>
                <div class="menu-item" data-page="products">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Sell & Exchange</span>
                </div>
                <div class="menu-item" data-page="lostandfound">
                    <i class="fas fa-search"></i>
                    <span>Lost & Found</span>
                </div>
                <div class="menu-item" data-page="jobs">
                    <i class="fas fa-briefcase"></i>
                    <span>Jobs</span>
                </div>
                <div class="menu-item" data-page="shuttle">
                    <i class="fas fa-bus"></i>
                    <span>Shuttle Service</span>
                </div>
                <div class="menu-item" data-page="sessions">
                    <i class="fas fa-calendar-check"></i>
                    <span>Mentorship Sessions</span>
                </div>
                <div class="menu-item" data-page="activity">
                    <i class="fas fa-history"></i>
                    <span>Activity Log</span>
                </div>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h2 id="pageTitle">Dashboard</h2>
            </div>
            <div class="header-right">
                <div class="notification-icon" id="notificationIcon" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge">0</span>
                </div>
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                    </div>
                </div>
                <div class="admin-profile">
                    <div class="admin-avatar"><?php echo strtoupper(substr($admin['admin_name'], 0, 1)); ?></div>
                    <div class="admin-info">
                        <div class="admin-name"><?php echo htmlspecialchars($admin['admin_name']); ?></div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Dashboard Page -->
            <div class="page-section active" id="dashboard">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalUsers">0</div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalMentors">0</div>
                            <div class="stat-label">Total Mentors</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalRooms">0</div>
                            <div class="stat-label">Total Rooms</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="pendingSessions">0</div>
                            <div class="stat-label">Pending Sessions</div>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-container">
                        <h4>User Growth (Last 30 Days)</h4>
                        <canvas id="growthChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Feature Usage</h4>
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Users Page -->
            <div class="page-section" id="users">
                <div class="table-container">
                    <div class="table-header">
                        <h3>All Users</h3>
                        <div class="table-actions">
                            <input type="text" placeholder="Search users..." id="userSearch" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);">
                        </div>
                    </div>
                    <div id="usersTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading users...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentors Page -->
            <div class="page-section" id="mentors">
                <div class="table-container">
                    <div class="table-header">
                        <h3>All Mentors</h3>
                        <div class="table-actions">
                            <input type="text" placeholder="Search mentors..." id="mentorSearch" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);">
                            <a href="addnewmentor.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Mentor
                            </a>
                        </div>
                    </div>
                    <div id="mentorsTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading mentors...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rooms Page -->
            <div class="page-section" id="rooms">
                <div class="table-container">
                    <div class="table-header">
                        <h3>All Rooms</h3>
                        <div class="table-actions">
                            <input type="text" placeholder="Search rooms..." id="roomSearch" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);">
                            <select id="roomStatusFilter" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);">
                                <option value="">All Status</option>
                                <option value="available">Available</option>
                                <option value="not-available">Not Available</option>
                            </select>
                            <a href="addnewroom.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Room
                            </a>
                        </div>
                    </div>
                    <div id="roomsTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading rooms...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Page -->
            <div class="page-section" id="products">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Sell & Exchange Products</h3>
                        <div class="table-actions">
                            <a href="add-product.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </a>
                        </div>
                    </div>
                    <div id="productsTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading products...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lost & Found Page -->
            <div class="page-section" id="lostandfound">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Lost & Found Items</h3>
                    </div>
                    <div id="lostFoundTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading items...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jobs Page -->
            <div class="page-section" id="jobs">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Job Listings</h3>
                        <div class="table-actions">
                            <button class="btn btn-primary" onclick="window.location.href='postjob.php'">
                                <i class="fas fa-plus"></i> Post Job
                            </button>
                        </div>
                    </div>
                    <div id="jobsTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading jobs...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shuttle Service Page -->
            <div class="page-section" id="shuttle">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Shuttle Drivers</h3>
                        <div class="table-actions">
                            <button class="btn btn-primary" onclick="openAddDriverModal()">
                                <i class="fas fa-plus"></i> Add Driver
                            </button>
                        </div>
                    </div>
                    <div id="driversTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading drivers...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sessions Page -->
            <div class="page-section" id="sessions">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Mentorship Session Requests</h3>
                    </div>
                    <div id="sessionsTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading session requests...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Page -->
            <div class="page-section" id="analytics">
                <h3 style="margin-bottom: 25px;">Platform Analytics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalProducts">0</div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalLostItems">0</div>
                            <div class="stat-label">Lost Items</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-bus-alt"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalDrivers">0</div>
                            <div class="stat-label">Shuttle Drivers</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="rentedRooms">0</div>
                            <div class="stat-label">Rented Rooms</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Log Page -->
            <div class="page-section" id="activity">
                <!-- Activity Stats -->
                <div class="stats-grid" style="margin-bottom: 25px;">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="totalActivities">0</div>
                            <div class="stat-label">Total Actions</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="actionsToday">0</div>
                            <div class="stat-label">Actions Today</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="createActions">0</div>
                            <div class="stat-label">Create Actions</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" id="updateActions">0</div>
                            <div class="stat-label">Update Actions</div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Admin Activity Log</h3>
                        <div class="table-actions" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <select id="activityActionFilter" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);">
                                <option value="">All Actions</option>
                            </select>
                            <select id="activityTargetFilter" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);">
                                <option value="">All Targets</option>
                            </select>
                            <input type="date" id="activityDateFrom" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);" placeholder="From Date">
                            <input type="date" id="activityDateTo" style="padding: 10px; border-radius: 8px; border: 2px solid var(--border-color);" placeholder="To Date">
                            <button class="btn btn-secondary" onclick="resetActivityFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div id="activityTableContainer">
                        <div class="loading">
                            <i class="fas fa-spinner"></i>
                            <p>Loading activity logs...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="editUserId">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="editUserName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="editUserEmail" required>
                </div>
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" id="editUserMobile" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal" id="addDriverModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Driver</h3>
            </div>
            <form id="addDriverForm">
                <div class="form-group">
                    <label>Driver ID</label>
                    <input type="text" id="newDriverId" required>
                </div>
                <div class="form-group">
                    <label>Driver Name</label>
                    <input type="text" id="newDriverName" required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" id="newDriverContact" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="newDriverPassword" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add Driver</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addDriverModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/modal-utils.js"></script>
    <script src="adminpanel/admin-scripts.js"></script>
</body>
</html>
