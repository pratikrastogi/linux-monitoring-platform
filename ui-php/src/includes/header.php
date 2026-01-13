<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title : 'KubeArena'; ?> | Linux Monitoring</title>

  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  
  <!-- Custom Light Theme CSS -->
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --primary-color: #667eea;
      --secondary-color: #764ba2;
      --accent-color: #00d4ff;
      --bg-light: #f4f7fc;
      --card-bg: rgba(255, 255, 255, 0.95);
      --text-primary: #2c3e50;
      --text-secondary: #7f8c8d;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg-light);
    }
    
    /* Futuristic Brand Link */
    .brand-link {
      background: var(--primary-gradient) !important;
      border-bottom: 1px solid rgba(255,255,255,.2);
      padding: 1rem;
      box-shadow: 0 2px 15px rgba(102, 126, 234, 0.3);
    }
    
    .brand-text {
      color: #fff !important;
      font-family: 'Orbitron', sans-serif;
      font-weight: 700;
      font-size: 1.3rem;
      letter-spacing: 1px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .brand-image {
      animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }
    
    /* Light Sidebar */
    .main-sidebar {
      background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%) !important;
      box-shadow: 2px 0 15px rgba(0,0,0,0.05);
    }
    
    .sidebar {
      padding-top: 10px;
    }
    
    .nav-sidebar .nav-link {
      color: var(--text-primary) !important;
      border-radius: 10px;
      margin: 5px 10px;
      transition: all 0.3s ease;
      font-weight: 500;
    }
    
    .nav-sidebar .nav-link:hover {
      background: rgba(102, 126, 234, 0.1) !important;
      transform: translateX(5px);
      color: var(--primary-color) !important;
    }
    
    .nav-sidebar .nav-link.active {
      background: var(--primary-gradient) !important;
      color: #fff !important;
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
      transform: translateX(5px);
    }
    
    .nav-sidebar .nav-link i {
      margin-right: 10px;
    }
    
    .nav-header {
      color: var(--text-secondary);
      font-weight: 600;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 15px 20px 5px;
    }
    
    /* User Panel */
    .user-panel {
      border-bottom: 1px solid rgba(102, 126, 234, 0.1);
      padding: 15px;
    }
    
    .user-panel .info a {
      color: var(--text-primary) !important;
      font-weight: 600;
    }
    
    .user-panel small {
      color: var(--text-secondary);
      font-size: 0.8rem;
    }
    
    /* Transparent Cards with Glass Effect */
    .card {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      border: 1px solid rgba(102, 126, 234, 0.1);
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .card-header {
      background: transparent;
      border-bottom: 2px solid rgba(102, 126, 234, 0.1);
      font-weight: 600;
      color: var(--text-primary);
    }
    
    /* Info Boxes with Gradient */
    .info-box {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      border: 1px solid rgba(102, 126, 234, 0.1);
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      overflow: hidden;
      position: relative;
    }
    
    .info-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary-gradient);
    }
    
    .info-box:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
    }
    
    .info-box-icon {
      border-radius: 15px 0 0 15px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .info-box-icon i {
      font-size: 2.5rem;
    }
    
    /* Content Wrapper */
    .content-wrapper {
      background: var(--bg-light);
    }
    
    /* Navbar */
    .main-header {
      background: #fff;
      border-bottom: 1px solid rgba(102, 126, 234, 0.1);
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      z-index: 1030;
      position: relative;
    }
    
    .navbar-light .navbar-nav .nav-link {
      color: var(--text-primary);
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .navbar-light .navbar-nav .nav-link:hover {
      color: var(--primary-color);
    }
    
    /* Buttons */
    .btn-primary {
      background: var(--primary-gradient);
      border: none;
      border-radius: 10px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    /* Tables */
    .table {
      background: transparent;
      border-radius: 10px;
      overflow: hidden;
    }
    
    .table thead th {
      background: var(--primary-gradient);
      color: #fff;
      border: none;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(102, 126, 234, 0.03);
    }
    
    .table tbody tr {
      transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
      background-color: rgba(102, 126, 234, 0.08);
      transform: scale(1.01);
    }
    
    /* Badges */
    .badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.8rem;
    }
    
    /* Animations */
    @keyframes slideInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .animate-slide-in {
      animation: slideInDown 0.5s ease-out;
    }
    
    .animate-fade-in {
      animation: fadeIn 0.6s ease-out;
    }
    
    /* Breadcrumb */
    .breadcrumb {
      background: transparent;
      padding: 0;
    }
    
    .breadcrumb-item a {
      color: var(--primary-color);
      text-decoration: none;
    }
    
    .breadcrumb-item.active {
      color: var(--text-secondary);
    }
    
    /* Content Header */
    .content-header h1 {
      color: var(--text-primary);
      font-weight: 700;
      font-family: 'Orbitron', sans-serif;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
      .info-box {
        margin-bottom: 15px;
      }
      
      .card {
        margin-bottom: 15px;
      }
    }
    
    /* Dropdown Menu Fix */
    .dropdown-menu {
      z-index: 1050;
      pointer-events: auto;
    }
    
    .nav-link {
      pointer-events: auto;
      cursor: pointer;
    }
    
    .dropdown-menu.show {
      display: block;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">
<div class="wrapper">
