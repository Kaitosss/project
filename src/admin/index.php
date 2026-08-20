<?php
require_once __DIR__."/../db.php";

$db = new DB();
$conn = null;
$dbError = null;
$students = [];

try {
    $conn = $db->connectDB();
    if ($conn) {
        // Auto create student table if not existing
        $conn->exec("CREATE TABLE IF NOT EXISTS student (
            id INT AUTO_INCREMENT PRIMARY KEY,
            std_code VARCHAR(50) NOT NULL,
            std_fullname VARCHAR(255) NOT NULL,
            major VARCHAR(255) NOT NULL,
            level VARCHAR(50) NOT NULL,
            image VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Process POST actions (add_student, delete_student)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add_student') {
                $code = trim($_POST['std_code'] ?? '');
                $fullname = trim($_POST['std_fullname'] ?? '');
                $major = trim($_POST['major'] ?? '');
                $level = trim($_POST['level'] ?? '');
                if (!empty($code) && !empty($fullname)) {
                    $stmt = $conn->prepare("INSERT INTO student (std_code, std_fullname, major, level) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$code, $fullname, $major, $level]);
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
                        exit;
                    }
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            } else if ($action === 'delete_student') {
                $id = intval($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
                    $stmt->execute([$id]);
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true]);
                        exit;
                    }
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            }
        }

        // Fetch students from database
        $result = $conn->query("SELECT * FROM student ORDER BY id DESC");
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $students[] = $row;
            }
        }

        // Auto-seed initial records into MySQL if table was empty
        if (empty($students)) {
            $seedData = [
                ["6630101001", "นายธนกฤต ช่างทอง", "เทคโนโลยีการผลิต", "ปวส.1"],
                ["6630101002", "นางสาวปรียาพร แม่นยำ", "เทคนิคเขียนแบบเครื่องกล", "ปวส.1"],
                ["6530101015", "นายกิตติศักดิ์ มีฝีมือ", "เทคโนโลยีการผลิต", "ปวส.2"]
            ];
            $stmt = $conn->prepare("INSERT INTO student (std_code, std_fullname, major, level) VALUES (?, ?, ?, ?)");
            foreach ($seedData as $row) {
                $stmt->execute($row);
            }
            // Re-fetch after seeding
            $result = $conn->query("SELECT * FROM student ORDER BY id DESC");
            if ($result) {
                $students = [];
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $students[] = $row;
                }
            }
        }
    }
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

// Default Fallback Equipment Data
$machines = [
    [
        "id" => "MC-01",
        "name" => "CNC Lathe DMG MORI",
        "category" => "CNC Machine",
        "location" => "Zone A-01",
        "status" => "operational",
        "status_label" => "พร้อมใช้งาน",
        "operator" => "สมชาย ใจดี",
        "last_maint" => "2026-07-28",
        "utilization" => 88
    ],
    [
        "id" => "MC-02",
        "name" => "CNC Milling 3-Axis Haas",
        "category" => "CNC Machine",
        "location" => "Zone A-03",
        "status" => "operational",
        "status_label" => "พร้อมใช้งาน",
        "operator" => "วิชัย รักการกลึง",
        "last_maint" => "2026-08-01",
        "utilization" => 94
    ],
    [
        "id" => "MC-03",
        "name" => "Surface Grinder Okamoto",
        "category" => "Grinding Machine",
        "location" => "Zone B-02",
        "status" => "maintenance",
        "status_label" => "ซ่อมบำรุง",
        "operator" => "ช่างสุรพล",
        "last_maint" => "2026-08-05",
        "utilization" => 0
    ],
    [
        "id" => "MC-04",
        "name" => "Radial Drill Press Precision",
        "category" => "Drilling Machine",
        "location" => "Zone B-05",
        "status" => "operational",
        "status_label" => "พร้อมใช้งาน",
        "operator" => "ประวิทย์ มั่นคง",
        "last_maint" => "2026-07-15",
        "utilization" => 65
    ],
    [
        "id" => "MC-05",
        "name" => "Manual Lathe Engine",
        "category" => "Manual Machine",
        "location" => "Zone C-01",
        "status" => "idle",
        "status_label" => "สแตนด์บาย",
        "operator" => "ทีมงานกะบ่าย",
        "last_maint" => "2026-06-30",
        "utilization" => 25
    ]
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — KPT Mechanic Machine Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --bg-sidebar: #ffffff;
            --topbar-bg: rgba(255, 255, 255, 0.9);
            
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;

            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-subtle: #94a3b8;

            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;

            --status-green: #10b981;
            --status-green-bg: #ecfdf5;
            --status-green-text: #047857;

            --status-red: #ef4444;
            --status-red-bg: #fef2f2;
            --status-red-text: #b91c1c;

            --status-yellow: #f59e0b;
            --status-yellow-bg: #fffbeb;
            --status-yellow-text: #b45309;

            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -2px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            font-family: 'IBM Plex Sans Thai', 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .mono { font-family: 'IBM Plex Mono', monospace; }

        .app-container {
            display: flex;
            flex: 1;
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        aside.sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 100;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header {
            padding: 22px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        }

        .brand-text {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text-main);
            line-height: 1.2;
        }
        .brand-text small {
            display: block;
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
            margin-top: 2px;
        }

        .sidebar-menu {
            padding: 20px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }

        .menu-category {
            font-size: 11px;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 14px 12px 6px 12px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13.5px;
            border-radius: 8px;
            transition: all 0.15s ease;
            cursor: pointer;
            font-weight: 500;
        }

        .nav-item:hover {
            background: var(--bg-main);
            color: var(--text-main);
        }

        .nav-item.active {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.75;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px 18px;
            border-top: 1px solid var(--border-color);
            background: var(--bg-main);
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            border: 1px solid var(--primary-border);
        }

        /* ===== MAIN CONTENT AREA ===== */
        main.main-content {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* TOP BAR */
        header.topbar {
            height: 64px;
            background: var(--topbar-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-box {
            position: relative;
            width: 320px;
        }
        .search-box input {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 14px 8px 36px;
            color: var(--text-main);
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s ease;
        }
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .search-box svg {
            position: absolute;
            left: 11px; top: 50%;
            transform: translateY(-50%);
            width: 16px; height: 16px;
            stroke: var(--text-subtle);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            color: var(--text-main);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }
        .btn:hover {
            border-color: var(--border-hover);
            background: var(--bg-main);
        }
        .btn-primary {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            font-weight: 600;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            color: #ffffff;
        }
        .btn-danger {
            color: var(--status-red-text);
            border-color: #fca5a5;
            background: #fff5f5;
        }
        .btn-danger:hover {
            background: #fee2e2;
            border-color: var(--status-red);
        }

        /* PAGE BODY */
        .content-body {
            padding: 28px;
            flex: 1;
            max-width: 1400px;
            width: 100%;
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .page-title {
            font-family: 'Inter', 'IBM Plex Sans Thai', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-main);
        }
        .page-subtitle {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-title {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .stat-value {
            font-family: 'Inter', sans-serif;
            font-size: 30px;
            font-weight: 700;
            margin: 8px 0 4px 0;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }
        .stat-desc {
            font-size: 12.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stat-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 6px;
            background: var(--status-green-bg);
            color: var(--status-green-text);
            font-weight: 600;
        }

        /* PANELS & TABLES */
        .panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .panel-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }
        .panel-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 4px;
            background: var(--bg-main);
            padding: 4px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .tab-btn {
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 12px;
            padding: 6px 12px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.15s ease;
            font-weight: 500;
        }
        .tab-btn.active {
            background: #ffffff;
            color: var(--primary);
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        /* TABLE DESIGN */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }
        table.data-table th {
            background: #f8fafc;
            padding: 12px 18px;
            font-size: 11.5px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }
        table.data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }
        table.data-table tr:last-child td {
            border-bottom: none;
        }
        table.data-table tr:hover td {
            background: #f8fafc;
        }

        /* Status Badges */
        .badge {
            font-size: 11.5px;
            padding: 4px 10px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }
        .badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            display: inline-block;
        }
        .badge-green { background: var(--status-green-bg); color: var(--status-green-text); }
        .badge-green .badge-dot { background: var(--status-green); }
        .badge-red { background: var(--status-red-bg); color: var(--status-red-text); }
        .badge-red .badge-dot { background: var(--status-red); }
        .badge-yellow { background: var(--status-yellow-bg); color: var(--status-yellow-text); }
        .badge-yellow .badge-dot { background: var(--status-yellow); }

        /* MODAL DIALOG */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 200;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #ffffff;
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 500px;
            border-radius: 14px;
            box-shadow: var(--shadow-lg);
            animation: modalIn 0.2s ease-out;
        }
        @keyframes modalIn {
            from { transform: scale(0.96); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-main);
        }
        .modal-close {
            background: none;
            border: none;
            color: var(--text-subtle);
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }
        .modal-close:hover { color: var(--text-main); }
        .modal-body {
            padding: 22px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 6px;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 9px 12px;
            color: var(--text-main);
            font-family: 'IBM Plex Sans Thai', sans-serif;
            font-size: 13.5px;
            outline: none;
            transition: all 0.15s ease;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .modal-footer {
            padding: 16px 22px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* TOAST NOTIFICATION */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 300;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .toast {
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastIn 0.25s ease-out;
        }
        @keyframes toastIn {
            from { transform: translateY(10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            aside.sidebar { transform: translateX(-100%); }
            aside.sidebar.open { transform: translateX(0); }
            main.main-content { margin-left: 0; }
            .mobile-toggle { display: block !important; }
        }
        .mobile-toggle { display: none; background: none; border: none; color: var(--text-main); font-size: 20px; cursor: pointer; }
    </style>
</head>
<body>

<div class="app-container">
    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand-mark">KPT</div>
            <div class="brand-text">
                MECHANIC
                <small>ADMIN CENTER</small>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-category">เมนูหลัก</div>
            <a class="nav-item active" onclick="switchTab('overview')">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                ภาพรวมระบบ (Overview)
            </a>
            <a class="nav-item" onclick="switchTab('machines')">
                <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                จัดการเครื่องจักร (Machines)
            </a>
            <a class="nav-item" onclick="switchTab('students')">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                นักศึกษา / ช่าง (Students/Techs)
            </a>

            <div class="menu-category">การดำเนินงาน</div>
            <a class="nav-item" onclick="showToast('ฟีเจอร์ Work Orders อยู่ในระหว่างพัฒนา')">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                ใบสั่งผลิต (Work Orders)
            </a>
            <a class="nav-item" onclick="showToast('ฟีเจอร์ Maintenance Log พร้อมใช้งานในโหมด Preview')">
                <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                บันทึกการซ่อมบำรุง
            </a>

            <div class="menu-category">การเชื่อมโยง</div>
            <a href="../index.php" class="nav-item" style="color: var(--primary);">
                <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                กลับสู่หน้าหลัก (Main Site)
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-avatar">A</div>
                <div>
                    <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">Administrator</div>
                    <div style="font-size: 11px; color: var(--text-muted);">ผู้ดูแลระบบ</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="adminSearch" placeholder="ค้นหาเครื่องจักร, นักศึกษา, รหัสงาน..." onkeyup="filterTable()">
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-primary" onclick="openModal('addMachineModal')">+ เพิ่มเครื่องจักร</button>
                <button class="btn" onclick="openModal('addStudentModal')">+ เพิ่มนักศึกษา</button>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <div class="content-body">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Admin Dashboard</h1>
                    <div class="page-subtitle">ระบบบริหารจัดการเครื่องจักร ใบสั่งผลิต และข้อมูลนักศึกษา แผนก Machine Shop</div>
                </div>
                <div class="mono" style="font-size: 12px; color: var(--text-muted); background: #ffffff; padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    อัปเดตล่าสุด: <?php echo date("H:i / d M Y"); ?>
                </div>
            </div>

            <!-- STATS GRID -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">เครื่องจักรทั้งหมด</div>
                    <div class="stat-value"><?php echo count($machines); ?> <span style="font-size: 15px; font-weight: 500; color: var(--text-muted);">เครื่อง</span></div>
                    <div class="stat-desc"><span class="stat-badge">80% Ready</span> พร้อมใช้งานในโรงงาน</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">ใบสั่งผลิตที่กำลังทำ</div>
                    <div class="stat-value">12 <span style="font-size: 15px; font-weight: 500; color: var(--text-muted);">รายการ</span></div>
                    <div class="stat-desc">กำลังดำเนินการผลิตประจำวัน</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">แจ้งเตือนซ่อมบำรุง</div>
                    <div class="stat-value" style="color: var(--status-red-text);">1 <span style="font-size: 15px; font-weight: 500; color: var(--text-muted);">รายการ</span></div>
                    <div class="stat-desc" style="color: var(--status-red-text);">Surface Grinder กำลังซ่อมบำรุง</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">นักศึกษา / ช่าง</div>
                    <div class="stat-value" id="statStudentCount"><?php echo count($students); ?> <span style="font-size: 15px; font-weight: 500; color: var(--text-muted);">คน</span></div>
                    <div class="stat-desc">ข้อมูลจากฐานข้อมูล MySQL</div>
                </div>
            </div>

            <!-- SECTION 1: MACHINES MANAGEMENT -->
            <div class="panel" id="section-machines">
                <div class="panel-header">
                    <div class="panel-title">
                        <span style="color: var(--primary);">⚙</span> ตารางจัดการเครื่องจักร (Equipment Status)
                    </div>
                    <div class="filter-tabs">
                        <button class="tab-btn active" onclick="filterStatus('all', this)">ทั้งหมด</button>
                        <button class="tab-btn" onclick="filterStatus('operational', this)">พร้อมใช้งาน</button>
                        <button class="tab-btn" onclick="filterStatus('maintenance', this)">ซ่อมบำรุง</button>
                        <button class="tab-btn" onclick="filterStatus('idle', this)">สแตนด์บาย</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="machineTable">
                        <thead>
                            <tr>
                                <th>CODE / ID</th>
                                <th>ชื่อเครื่องจักร</th>
                                <th>หมวดหมู่</th>
                                <th>โซนตำแหน่ง</th>
                                <th>ผู้ดูแลหลัก</th>
                                <th>อัตราการใช้งาน</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($machines as $mc): ?>
                            <tr data-status="<?php echo $mc['status']; ?>">
                                <td class="mono" style="color: var(--primary); font-weight: 600;"><?php echo $mc['id']; ?></td>
                                <td style="font-weight: 500;"><?php echo $mc['name']; ?></td>
                                <td style="font-size: 12.5px; color: var(--text-muted);"><?php echo $mc['category']; ?></td>
                                <td><?php echo $mc['location']; ?></td>
                                <td><?php echo $mc['operator']; ?></td>
                                <td class="mono">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex:1; height: 6px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; min-width: 60px;">
                                            <div style="width: <?php echo $mc['utilization']; ?>%; height: 100%; background: var(--primary); border-radius: 9999px;"></div>
                                        </div>
                                        <span style="font-size: 12px; color: var(--text-muted);"><?php echo $mc['utilization']; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($mc['status'] === 'operational'): ?>
                                        <span class="badge badge-green"><span class="badge-dot"></span><?php echo $mc['status_label']; ?></span>
                                    <?php elseif ($mc['status'] === 'maintenance'): ?>
                                        <span class="badge badge-red"><span class="badge-dot"></span><?php echo $mc['status_label']; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-yellow"><span class="badge-dot"></span><?php echo $mc['status_label']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn" style="padding: 4px 10px; font-size: 11.5px;" onclick="showToast('แก้ไขข้อมูล <?php echo $mc['id']; ?>')">แก้ไข</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 2: STUDENTS / TECHS MANAGEMENT -->
            <div class="panel" id="section-students">
                <div class="panel-header">
                    <div class="panel-title">
                        <span style="color: var(--primary);">👤</span> ข้อมูลนักศึกษา / ช่างเทคนิค (Students & Techs)
                    </div>
                    <?php if ($dbError): ?>
                        <span class="mono" style="font-size: 11px; color: var(--status-red-text);">DB CONNECT: FALLBACK MODE</span>
                    <?php else: ?>
                        <span class="badge badge-green"><span class="badge-dot"></span>CONNECTED TO MYSQL</span>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="studentTable">
                        <thead>
                            <tr>
                                <th># ID</th>
                                <th>รหัสนักศึกษา</th>
                                <th>ชื่อ - นามสกุล</th>
                                <th>สาขาวิชา</th>
                                <th>ระดับชั้น</th>
                                <th>วันที่บันทึก</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $std): ?>
                            <tr>
                                <td class="mono" style="color: var(--text-subtle);"><?php echo $std['id']; ?></td>
                                <td class="mono" style="color: var(--primary); font-weight: 600;"><?php echo htmlspecialchars($std['std_code'] ?? '-'); ?></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($std['std_fullname'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($std['major'] ?? '-'); ?></td>
                                <td class="mono"><?php echo htmlspecialchars($std['level'] ?? '-'); ?></td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($std['created_at'] ?? '2026-08-06'); ?></td>
                                <td style="display: flex; gap: 6px;">
                                    <button class="btn" style="padding: 4px 10px; font-size: 11.5px;" onclick="showToast('ข้อมูล: <?php echo htmlspecialchars($std['std_fullname'] ?? ''); ?> (รหัส: <?php echo htmlspecialchars($std['std_code'] ?? ''); ?>)')">ดูข้อมูล</button>
                                    <button class="btn btn-danger" style="padding: 4px 10px; font-size: 11.5px;" onclick="handleDeleteStudent(<?php echo $std['id']; ?>, '<?php echo htmlspecialchars(addslashes($std['std_fullname'] ?? '')); ?>')">ลบ</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- MODAL 1: ADD MACHINE -->
<div class="modal-overlay" id="addMachineModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">+ เพิ่มรายการเครื่องจักรใหม่</div>
            <button class="modal-close" onclick="closeModal('addMachineModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="machineForm" onsubmit="handleAddMachine(event)">
                <div class="form-group">
                    <label>รหัสเครื่องจักร (Machine ID)</label>
                    <input type="text" class="form-control" placeholder="เช่น MC-06" required>
                </div>
                <div class="form-group">
                    <label>ชื่อเครื่องจักร (Machine Name)</label>
                    <input type="text" class="form-control" placeholder="เช่น CNC Milling 5-Axis" required>
                </div>
                <div class="form-group">
                    <label>โซน / ตำแหน่ง (Location)</label>
                    <input type="text" class="form-control" placeholder="เช่น Zone A-04" required>
                </div>
                <div class="form-group">
                    <label>ผู้รับผิดชอบหลัก</label>
                    <input type="text" class="form-control" placeholder="ระบุชื่อผู้ดูแล" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('addMachineModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: ADD STUDENT -->
<div class="modal-overlay" id="addStudentModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">+ เพิ่มข้อมูลนักศึกษาใหม่</div>
            <button class="modal-close" onclick="closeModal('addStudentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="studentForm" onsubmit="handleAddStudent(event)">
                <div class="form-group">
                    <label>รหัสนักศึกษา</label>
                    <input type="text" name="std_code" id="std_code" class="form-control" placeholder="เช่น 6630101099" required>
                </div>
                <div class="form-group">
                    <label>ชื่อ - นามสกุล</label>
                    <input type="text" name="std_fullname" id="std_fullname" class="form-control" placeholder="เช่น นายสมชาย กล้าหาญ" required>
                </div>
                <div class="form-group">
                    <label>สาขาวิชา</label>
                    <input type="text" name="major" id="major" class="form-control" placeholder="เช่น เทคโนโลยีการผลิต" required>
                </div>
                <div class="form-group">
                    <label>ระดับชั้น</label>
                    <input type="text" name="level" id="level" class="form-control" placeholder="เช่น ปวส.1" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('addStudentModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }

    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    function showToast(msg) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = '<span>⚡</span> <span>' + msg + '</span>';
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.25s';
            setTimeout(() => toast.remove(), 250);
        }, 3000);
    }

    function filterStatus(status, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#machineTable tbody tr');
        rows.forEach(row => {
            if (status === 'all' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterTable() {
        const query = document.getElementById('adminSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.data-table tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    function handleAddMachine(e) {
        e.preventDefault();
        closeModal('addMachineModal');
        showToast('เพิ่มเครื่องจักรใหม่เรียบร้อยแล้ว (Demo)');
    }

    async function handleAddStudent(e) {
        e.preventDefault();
        const form = e.target;
        const stdCode = form.querySelector('[name="std_code"]').value;
        const stdFullname = form.querySelector('[name="std_fullname"]').value;
        const major = form.querySelector('[name="major"]').value;
        const level = form.querySelector('[name="level"]').value;

        const params = new URLSearchParams();
        params.append('action', 'add_student');
        params.append('std_code', stdCode);
        params.append('std_fullname', stdFullname);
        params.append('major', major);
        params.append('level', level);

        try {
            const res = await fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            });
            const data = await res.json();
            if (data.success) {
                showToast('บันทึกข้อมูลนักศึกษาลงฐานข้อมูลสำเร็จ');
                closeModal('addStudentModal');
                form.reset();
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        } catch (err) {
            showToast('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
        }
    }

    async function handleDeleteStudent(id, name) {
        if (!confirm(`คุณต้องการลบข้อมูลนักศึกษา "${name}" ใช่หรือไม่?`)) return;

        const params = new URLSearchParams();
        params.append('action', 'delete_student');
        params.append('id', id);

        try {
            const res = await fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            });
            const data = await res.json();
            if (data.success) {
                showToast('ลบข้อมูลนักศึกษาเรียบร้อยแล้ว');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast('เกิดข้อผิดพลาดในการลบข้อมูล');
            }
        } catch (err) {
            showToast('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
        }
    }

    function switchTab(tab) {
        if(tab === 'overview') {
            document.getElementById('section-machines').style.display = 'block';
            document.getElementById('section-students').style.display = 'block';
        } else if(tab === 'machines') {
            document.getElementById('section-machines').style.display = 'block';
            document.getElementById('section-students').style.display = 'none';
        } else if(tab === 'students') {
            document.getElementById('section-machines').style.display = 'none';
            document.getElementById('section-students').style.display = 'block';
        }
        showToast('สลับไปยังมุมมอง ' + tab);
    }
</script>

</body>
</html>
