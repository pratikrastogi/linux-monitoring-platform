<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title : 'KubeArena'; ?> | Linux Monitoring</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  
  <!-- Custom CSS -->
  <style>
    :root {
      --primary-color: #2c5364;
      --secondary-color: #203a43;
      --accent-color: #0f2027;
    }
    
    .brand-link {
      background: linear-gradient(135deg, var(--accent-color), var(--secondary-color), var(--primary-color)) !important;
      border-bottom: 1px solid rgba(255,255,255,.1);
    }
    
    .brand-text {
      color: #fff !important;
      font-weight: 600;
    }
    
    .sidebar-dark-primary {
      background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
    }
    
    .nav-sidebar .nav-link.active {
      background: rgba(255,255,255,.1) !important;
      color: #fff !important;
      border-left: 3px solid #007bff;
    }
    
    .nav-sidebar .nav-link:hover {
      background: rgba(255,255,255,.05);
    }
    
    .info-box {
      box-shadow: 0 0 15px rgba(0,0,0,.1);
      transition: transform .3s ease;
    }
    
    .info-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 25px rgba(0,0,0,.15);
    }
    
    .small-box {
      border-radius: 10px;
      box-shadow: 0 2px 15px rgba(0,0,0,.1);
      transition: all .3s ease;
    }
    
    .small-box:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 25px rgba(0,0,0,.2);
    }
    
    .card {
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,.05);
    }
    
    .content-wrapper {
      background: #f4f6f9;
    }
    
    /* Dark Mode Support */
    .dark-mode .content-wrapper {
      background: #1a1a2e;
    }
    
    .dark-mode .card {
      background: #16213e;
      color: #e4e4e4;
    }
    
    .dark-mode .small-box {
      background: #0f3460 !important;
      color: #fff;
    }
    
    .dark-mode .info-box {
      background: #16213e;
      color: #e4e4e4;
    }
    
    /* Status badges */
    .status-ok {
      background: #28a745;
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .status-down {
      background: #dc3545;
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .status-warning {
      background: #ffc107;
      color: #000;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
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
    
    .animate-slide-in {
      animation: slideInDown 0.5s ease-out;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
      .small-box h3 {
        font-size: 1.5rem !important;
      }
      
      .info-box-text {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed <?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] == '1' ? 'dark-mode' : ''; ?>">
<div class="wrapper">
