<?php
    require_once 'conn.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $noEmpleado = '';
    if (isset($_COOKIE['noEmpleado']) && $_COOKIE['noEmpleado'] !== '') {
        $noEmpleado = $_COOKIE['noEmpleado'];
    } elseif (isset($_COOKIE['noEmpleadoL']) && $_COOKIE['noEmpleadoL'] !== '') {
        $noEmpleado = $_COOKIE['noEmpleadoL'];
    } elseif (isset($_SESSION['noEmpleado']) && $_SESSION['noEmpleado'] !== '') {
        $noEmpleado = $_SESSION['noEmpleado'];
    }

    if ($noEmpleado === '') {
        echo '<script>window.location.assign("../loginMaster")</script>';
    }
?>
<style>        
    .text-bg-orange {
        --bs-bg-opacity: 1;
        background-color: #ff7300ff !important;
        color: #ffffffff !important;
    }
    .btn-logistica{
        --bs-bg-opacity: 1;
        background-color: #bf00ffff !important;
        color: #ffffffff !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="inicio">
    <div class="sidebar-brand-icon rotate-n-1">
        <img class="sidebar-card-illustration mb-2" href="" src="img/MESS_07_CuboMess_2.png" width="40" alt="Logo">
    </div>
</a>
<!-- Heading -->
<div class="sidebar-heading">
    <span class="badge text-xl-white">Opciones</span>
</div>
<!-- Divider -->
<hr class="sidebar-divider my-2 alert-light">
<!-- Nav Item - Pages Collapse Menu -->

<li class="nav-item">
    <a class="nav-link" href="registro_actividades_SGC">
        <i class="fas fa-fw fa-pen text-gray-400"></i>
        <span>Registro SGC</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="detalles_actividades_SGC">
        <i class="fas fa-fw fa-table text-gray-400"></i>
        <span>Detalle Actividades</span>
    </a>
</li>


<li class = "nav-item">
    <a class = "nav-link" href = "#" data-toggle = "modal" data-target = "#logoutModalN">
        <i class = "fas fa-sign-out-alt text-gray-100"></i>
        Salir
    </a>
</li>

<hr class="sidebar-divider my-1 alert-light">

<div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button> 
</div>
</ul>