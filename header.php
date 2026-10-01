<?php
// header.php — à inclure en haut de chaque page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'Emploi du Temps</title>

    <!-- Bootstrap 5.3 + Icônes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #1e40af;
        }
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        [data-bs-theme="dark"] body {
            background: #0f172a;
        }

        /* ===== Navbar ===== */
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .navbar-brand i {
            color: #60a5fa;
            margin-right: 6px;
        }
        .navbar .nav-link {
            font-size: 0.92rem;
            padding: 0.5rem 0.85rem !important;
            border-radius: 8px;
            transition: background 0.15s ease;
        }
        .navbar .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        .navbar .nav-link.active {
            background: rgba(96, 165, 250, 0.2);
            color: #93c5fd !important;
            font-weight: 600;
        }

        /* ===== Cards génériques ===== */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        [data-bs-theme="dark"] .card {
            border-color: #1e293b;
        }

        /* ===== Statistiques ===== */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 5px solid var(--brand);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        [data-bs-theme="dark"] .stat-card {
            background: #1e293b;
            color: #e2e8f0;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }
        .stat-card .stat-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1e293b;
        }
        [data-bs-theme="dark"] .stat-card .stat-value {
            color: #f1f5f9;
        }
        .stat-card .stat-label {
            font-size: 0.78rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .stat-icon {
            font-size: 1.6rem;
            opacity: 0.85;
        }

        /* ===== Feature cards ===== */
        .feature-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        [data-bs-theme="dark"] .feature-card {
            background: #1e293b;
            color: #e2e8f0;
            border-color: #334155;
        }
        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0,0,0,0.08);
            border-color: var(--brand);
            color: inherit;
        }
        .feature-card .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 14px;
        }
        .feature-card h5 {
            font-weight: 600;
            margin-bottom: 6px;
        }
        .feature-card p {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0;
        }
        [data-bs-theme="dark"] .feature-card p {
            color: #94a3b8;
        }
        .icon-blue   { background: #dbeafe; color: #1d4ed8; }
        .icon-green  { background: #dcfce7; color: #15803d; }
        .icon-purple { background: #ede9fe; color: #6d28d9; }
        .icon-orange { background: #ffedd5; color: #c2410c; }
        .icon-pink   { background: #fce7f3; color: #be185d; }
        .icon-teal   { background: #ccfbf1; color: #0f766e; }

        /* ===== Hero ===== */
        .dashboard-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            color: #fff;
            padding: 40px 30px;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
        }
        .dashboard-hero h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .dashboard-hero p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        /* ===== Section titles ===== */
        .section-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 8px 0 16px;
        }
        [data-bs-theme="dark"] .section-title {
            color: #94a3b8;
        }

        /* ===== Grille d'occupation ===== */
        .grille-occupation {
            border-collapse: collapse;
            width: 100%;
            font-size: 0.75rem;
        }
        .grille-occupation th,
        .grille-occupation td {
            border: 1px solid #dee2e6;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        .grille-occupation thead th {
            background: #343a40;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .grille-occupation tbody th {
            background: #f1f3f5;
            font-weight: 600;
            position: sticky;
            left: 0;
            z-index: 1;
        }
        .grille-occupation .libre {
            background: #d4edda;
            color: #155724;
        }
        .grille-occupation .occupe {
            color: #fff;
            font-size: 0.7rem;
            line-height: 1.15;
        }
        .grille-occupation .occupe small {
            display: block;
            opacity: 0.85;
            font-size: 0.65rem;
        }
        .legende-prof {
            display: inline-flex;
            align-items: center;
            margin-right: 12px;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }
        .legende-prof .pastille {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            margin-right: 6px;
            border: 1px solid rgba(0,0,0,0.15);
        }
        .filtre-jour .btn {
            margin-right: 4px;
            margin-bottom: 4px;
        }
        .filtre-jour .btn.active {
            font-weight: bold;
        }
        .grille-wrapper {
            max-height: 600px;
            overflow: auto;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-calendar2-week"></i> School Timetable
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
                           href="dashboard.php"><i class="bi bi-house-door"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'view_timetable.php' ? 'active' : '' ?>"
                           href="view_timetable.php"><i class="bi bi-table"></i> Emploi du Temps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'visualize_hours.php' ? 'active' : '' ?>"
                           href="visualize_hours.php"><i class="bi bi-bar-chart"></i> Heures</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'view_students_modules.php' ? 'active' : '' ?>"
                           href="view_students_modules.php"><i class="bi bi-people"></i> Étudiants/Modules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'add_session.php' ? 'active' : '' ?>"
                           href="add_session.php"><i class="bi bi-plus-circle"></i> Ajouter Séance</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'manage_all.php' ? 'active' : '' ?>"
                           href="manage_all.php"><i class="bi bi-gear"></i> Gestion</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button class="btn btn-sm btn-outline-light" id="toggleTheme" title="Changer de thème">
                            <i class="bi bi-moon-stars" id="themeIcon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">