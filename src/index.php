<?php
require_once "./db.php";

// Initialize Database connection safely
$conn = null;
try {
    $db = new DB();
    $conn = $db->connectDB();
} catch (Exception $e) {
    // Graceful fallback if database service is starting up
    $conn = null;
}

// ===== ข้อมูลรายการเครื่องจักร แผนกช่างกลโรงงาน =====
$equipment = [
    [
        "id" => "cnc-lathe",
        "name" => "CNC Lathe Machine",
        "name_th" => "เครื่องกลึงซีเอ็นซี High-Precision",
        "category" => "cnc",
        "qty" => 4,
        "controller" => "Fanuc 0i-TF Plus",
        "desc" => "งานกลึงปาดหน้า กลึงปอก เจาะรูศูนย์ ทำเกลียวใน-นอก และงานรูปทรงซับซ้อนด้วยความแม่นยำสูง",
        "tol" => "±0.005 mm",
        "spindle" => "4,500 RPM",
        "capacity" => "Max Dia 320mm x Length 500mm",
        "icon" => "⚙️"
    ],
    [
        "id" => "cnc-milling",
        "name" => "5-Axis CNC Milling Center",
        "name_th" => "เครื่องกัดซีเอ็นซี 5 แกน",
        "category" => "cnc",
        "qty" => 3,
        "controller" => "Heidenhain TNC 640",
        "desc" => "กัดขึ้นรูปชิ้นงาน 3 มิติความละเอียดสูง กัดแม่พิมพ์ (Mold & Die) และชิ้นส่วนอากาศยาน",
        "tol" => "±0.003 mm",
        "spindle" => "12,000 RPM",
        "capacity" => "Table 600 x 500 x 450 mm",
        "icon" => "🗜️"
    ],
    [
        "id" => "surface-grinder",
        "name" => "Precision Surface Grinder",
        "name_th" => "เครื่องเจียรราบแม่นยำสูง",
        "category" => "precision",
        "qty" => 3,
        "controller" => "Digital Readout (DRO)",
        "desc" => "เจียรผิวหน้าโลหะให้เรียบเงากระจก ปรับระนาบขนานชิ้นงาน และงานเจียรแท่นแม่พิมพ์",
        "tol" => "±0.002 mm",
        "spindle" => "3,600 RPM",
        "capacity" => "Magnetic Table 300 x 600 mm",
        "icon" => "💎"
    ],
    [
        "id" => "cylindrical-grinder",
        "name" => "CNC Cylindrical Grinder",
        "name_th" => "เครื่องเจียรกลมซีเอ็นซี",
        "category" => "precision",
        "qty" => 2,
        "controller" => "Fanuc CNC",
        "desc" => "เจียรผิวนอกและผิวในของเพลา ลูกสลัก และชิ้นงานทรงกลมที่ต้องการค่าเผื่อละเอียดมาก",
        "tol" => "±0.001 mm",
        "spindle" => "2,800 RPM",
        "capacity" => "Max Dia 200mm x 750mm",
        "icon" => "⭕"
    ],
    [
        "id" => "radial-drill",
        "name" => "Heavy Radial Drill Press",
        "name_th" => "เครื่องเจาะรัศมีขนาดใหญ่",
        "category" => "conventional",
        "qty" => 3,
        "controller" => "Manual Gear Drive",
        "desc" => "เจาะรูขนาดใหญ่ คว้านรู (Boring) และต๊าปเกลียวบนชิ้นงานโครงสร้างขนาดใหญ่",
        "tol" => "±0.03 mm",
        "spindle" => "1,500 RPM",
        "capacity" => "Drill Capacity Dia 50mm",
        "icon" => "🔩"
    ],
    [
        "id" => "manual-lathe",
        "name" => "Precision Heavy Duty Lathe",
        "name_th" => "เครื่องกลึงยันศูนย์ 6 ฟุต",
        "category" => "conventional",
        "qty" => 8,
        "controller" => "DRO 2-Axis",
        "desc" => "ฝึกทักษะกลึงพื้นฐาน งานซ่อมบำรุงด่วน ขึ้นรูปชิ้นงานต้นแบบ (Prototype)",
        "tol" => "±0.02 mm",
        "spindle" => "2,000 RPM",
        "capacity" => "Swing Over Bed 400mm",
        "icon" => "🛠️"
    ],
    [
        "id" => "universal-milling",
        "name" => "Universal Milling Machine",
        "name_th" => "เครื่องกัดอเนกประสงค์",
        "category" => "conventional",
        "qty" => 6,
        "controller" => "DRO 3-Axis",
        "desc" => "กัดร่องลิ้น กัดเฟืองตรง-เฟืองเฉียง กัดราบ และเจาะรูด้วยหัวกัดแนวตั้ง-แนวนอน",
        "tol" => "±0.02 mm",
        "spindle" => "1,800 RPM",
        "capacity" => "Table 1270 x 280 mm",
        "icon" => "📐"
    ],
    [
        "id" => "cmm-measuring",
        "name" => "3D Coordinate Measuring Machine (CMM)",
        "name_th" => "เครื่องวัดพิกัด 3 มิติ (CMM)",
        "category" => "precision",
        "qty" => 1,
        "controller" => "Renishaw SP25M / PC-DMIS",
        "desc" => "ตรวจสอบขนาด มิติ รูปทรงเรขาคณิต และความคลาดเคลื่อนทางมิติ (GD&T) ด้วยสไตลัสหัวเพชร",
        "tol" => "±0.0008 mm",
        "spindle" => "Motorized Probe System",
        "capacity" => "Measuring Volume 700x1000x600 mm",
        "icon" => "🔬"
    ]
];

// ===== หลักสูตรการเรียนการสอน แผนกช่างกลโรงงาน =====
$curriculum = [
    [
        "level" => "ปวช.",
        "title" => "สาขาวิชาช่างกลโรงงาน",
        "sub" => "หลักสูตรประกาศนียบัตรวิชาชีพ (3 ปี)",
        "desc" => "มุ่งเน้นการฝึกทักษะปฏิบัติพื้นฐานเครื่องมือกล งานกลึง งานกัด งานเจียร งานวัดละเอียด และการเขียนแบบเทคนิคด้วยคอมพิวเตอร์ CAD",
        "highlight" => ["งานเครื่องกลึง & เครื่องกัดพื้นฐาน", "งานวัดละเอียด (Vernier / Micrometer)", "เขียนแบบวิศวกรรม 2D CAD", "ความปลอดภัยในโรงงานอุตสาหกรรม"]
    ],
    [
        "level" => "ปวส.",
        "title" => "สาขาวิชาเทคนิคการผลิต (กลุ่มงาน CNC)",
        "sub" => "หลักสูตรประกาศนียบัตรวิชาชีพชั้นสูง (2 ปี)",
        "desc" => "เจาะลึกเทคโนโลยีการผลิตขั้นสูง การเขียนโปรแกรม CNC (G-Code / M-Code), ระบบ CAD/CAM Mastercam/SolidWorks และการวางแผนควบคุมการผลิต",
        "highlight" => ["โปรแกรม CNC Lathe & Machining Center", "CAM Simulation & Post-Processor", "การออกแบบอุปกรณ์จับยึด Jig & Fixture", "การตรวจสอบคุณภาพด้วยเครื่อง CMM"]
    ],
    [
        "level" => "ปวส.",
        "title" => "สาขาวิชาเทคนิคการผลิต (กลุ่มงานแม่พิมพ์)",
        "sub" => "หลักสูตรประกาศนียบัตรวิชาชีพชั้นสูง (2 ปี)",
        "desc" => "เชี่ยวชาญการออกแบบและสร้างแม่พิมพ์โลหะ (Press Die) และแม่พิมพ์พลาสติก (Injection Mold) ด้วยเครื่องกลึง CNC และ EDM",
        "highlight" => ["การออกแบบแม่พิมพ์ฉีดพลาสติก & แม่พิมพ์ปั๊ม", "เครื่องกัด EDM & Wire-Cut CNC", "ชุบแข็งและวิทยาการโลหะ (Metallurgy)", "Reverse Engineering & 3D Scanning"]
    ]
];

// ===== ขั้นตอนกระบวนการขึ้นรูปโลหะแม่นยำ =====
$process = [
    [
        "step" => "01",
        "title" => "รับแบบ & วิเคราะห์แบบงาน (Drawing Analysis)",
        "desc" => "ตรวจสอบแบบวิศวกรรม (2D/3D Drawing), สเปกวัสดุ (S45C, SKD11, Aluminium 7075, Stainless 304) และพิกัดความเผื่อ (Tolerance GD&T)"
    ],
    [
        "step" => "02",
        "title" => "ออกแบบ CAD & วางแผนกระบวนการ (CAM Programming)",
        "desc" => "สร้างโมเดล 3D ด้วย SolidWorks จำลองเส้นทางมีดตัด (Toolpath) ด้วย Mastercam ออกแบบ Jig & Fixture และ Generate G-Code"
    ],
    [
        "step" => "03",
        "title" => "เตรียมวัตถุดิบ & ตั้งเครื่องจักร (Machine Setup)",
        "desc" => "ตัดเตรียมท่อนโลหะ เลือกรัศมีมีดตัด (Cutting Tools) เซ็ตศูนย์ชิ้นงาน (Work Coordinate System G54-G59) และวัดความยาวมีด (Tool Length Offset)"
    ],
    [
        "step" => "04",
        "title" => "แปรรูปขึ้นรูปชิ้นงาน (High-Precision Machining)",
        "desc" => "เดินเครื่องกลึง CNC / กัด CNC 3D ตามโปรแกรม ควบคุมความเร็วรอบ Spindle ความเร็วฟีด Feed Rate และฉีดน้ำยาคูลแลนท์ระบายความร้อน"
    ],
    [
        "step" => "05",
        "title" => "ตรวจสอบคุณภาพ & มิติ (CMM Quality Inspection)",
        "desc" => "ตรวจวัดขนาดชิ้นงานจริงเทียบแบบ Drawing ด้วยเครื่อง CMM, ไมโครมิเตอร์, ไฮเกจ และวัดค่าความเรียบผิว (Surface Roughness Ra)"
    ],
    [
        "step" => "06",
        "title" => "ลบคม ชุบผิว & ส่งมอบ (Finishing & Delivery)",
        "desc" => "ลบคมชิ้นงาน (Deburring) ชุบพ่นป้องกันสนิม บรรจุหีบห่อกันกระแทกอย่างดี และจัดส่งมอบชิ้นงานตรงตามเวลา"
    ]
];

// ===== ระบบตารางกะการทำงาน =====
$shifts = [
    [
        "start" => 8,
        "end" => 16,
        "name" => "กะเช้า (Morning Shift)",
        "desc" => "เดินเครื่องจักรหลักเต็มกำลัง, รับใบสั่งผลิตประจำวัน, งานสอนปฏิบัติการนักศึกษา",
        "capacity" => "100% Full Capacity"
    ],
    [
        "start" => 16,
        "end" => 24,
        "name" => "กะบ่าย (Afternoon Shift)",
        "desc" => "ผลิตงานต่อเนื่อง CNC, ตรวจสอบคุณภาพระหว่างผลิต (In-Process QC), เคลียร์คิวงานเร่งด่วน",
        "capacity" => "85% Operating"
    ],
    [
        "start" => 0,
        "end" => 8,
        "name" => "กะดึก (Night Shift)",
        "desc" => "เดินเครื่องระบบอัตโนมัติ Lights-out Machining, งานบำรุงรักษาเครื่องจักรประจำวัน (TPM)",
        "capacity" => "50% Automated Run"
    ]
];

$currentHour = (int) date("G");
function is_active_shift(int $hour, array $shift): bool {
    if ($shift["start"] < $shift["end"]) {
        return $hour >= $shift["start"] && $hour < $shift["end"];
    }
    return $hour >= $shift["start"] || $hour < $shift["end"];
}
$year = date("Y");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แผนกช่างกลโรงงาน — Department of Mechanical & Factory Machining Technology</title>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,500;0,600;0,700;1,500&family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Three.js 3D Engine & OrbitControls -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

<style>
  :root {
    --bg-dark: #070a0f;
    --bg-surface: #0f1623;
    --bg-card: #151e2e;
    --bg-card-hover: #1c293f;
    
    --steel-border: #28374f;
    --steel-light: #94a3b8;
    --paper-text: #e2e8f0;
    
    --industrial-yellow: #f59e0b;
    --industrial-amber: #d97706;
    --safety-orange: #ff5722;
    --cyber-cyan: #06b6d4;
    --cyber-blue: #38bdf8;
    --blueprint-line: rgba(6, 182, 212, 0.25);
    
    --glow-cyan: 0 0 20px rgba(6, 182, 212, 0.4);
    --glow-yellow: 0 0 20px rgba(245, 158, 11, 0.4);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  
  body {
    background-color: var(--bg-dark);
    color: var(--paper-text);
    font-family: 'IBM Plex Sans Thai', sans-serif;
    line-height: 1.6;
    overflow-x: hidden;
  }

  .mono { font-family: 'IBM Plex Mono', monospace; }
  .heading-font { font-family: 'Chakra Petch', sans-serif; }

  /* Grid Blueprint Background */
  .blueprint-bg {
    background-color: var(--bg-dark);
    background-image: 
      linear-gradient(rgba(6, 182, 212, 0.07) 1px, transparent 1px),
      linear-gradient(90deg, rgba(6, 182, 212, 0.07) 1px, transparent 1px),
      linear-gradient(rgba(245, 158, 11, 0.03) 2px, transparent 2px),
      linear-gradient(90deg, rgba(245, 158, 11, 0.03) 2px, transparent 2px);
    background-size: 30px 30px, 30px 30px, 150px 150px, 150px 150px;
    background-position: -1px -1px;
  }

  .wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
  }

  /* ===== HEADER & NAV ===== */
  header.top-nav {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(7, 10, 15, 0.88);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--steel-border);
  }
  .top-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    max-width: 1280px;
    margin: 0 auto;
  }
  .brand {
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
  }
  .brand-badge {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, var(--industrial-yellow), var(--industrial-amber));
    clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--glow-yellow);
    animation: gearSpin 20s linear infinite;
  }
  @keyframes gearSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  
  .brand-badge span {
    font-family: 'Oswald', sans-serif;
    font-weight: 700;
    color: #000;
    font-size: 20px;
  }
  .brand-title {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #fff;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }
  .brand-title small {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 400;
    font-size: 10.5px;
    color: var(--cyber-cyan);
    letter-spacing: 0.1em;
  }

  nav.nav-links {
    display: flex;
    gap: 22px;
    align-items: center;
  }
  nav.nav-links a {
    color: var(--steel-light);
    text-decoration: none;
    font-size: 13.5px;
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 500;
    letter-spacing: 0.04em;
    transition: all 0.25s ease;
    padding: 6px 12px;
    border-radius: 4px;
  }
  nav.nav-links a:hover {
    color: var(--industrial-yellow);
    background: rgba(245, 158, 11, 0.1);
  }
  .nav-badge-status {
    background: rgba(6, 182, 212, 0.12);
    border: 1px solid var(--cyber-cyan);
    color: var(--cyber-cyan);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    padding: 6px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .pulse-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 10px #10b981;
    animation: pulse 1.8s infinite;
  }
  @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.4; transform: scale(0.8); } }

  @media (max-width: 960px) { nav.nav-links { display: none; } }

  /* ===== HERO & THREE.JS CANVAS ===== */
  .hero-section {
    position: relative;
    padding: 60px 0 80px;
    border-bottom: 1px solid var(--steel-border);
    overflow: hidden;
  }
  .hero-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 36px;
    align-items: center;
  }
  @media (max-width: 990px) {
    .hero-grid { grid-template-columns: 1fr; text-align: center; }
  }

  .hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid var(--industrial-yellow);
    color: var(--industrial-yellow);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.12em;
    padding: 6px 14px;
    border-radius: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
  }

  h1.hero-h1 {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: clamp(36px, 4.8vw, 62px);
    line-height: 1.08;
    text-transform: uppercase;
    color: #ffffff;
  }
  h1.hero-h1 .highlight {
    color: var(--industrial-yellow);
    text-shadow: var(--glow-yellow);
  }
  h1.hero-h1 .sub-cyan {
    color: var(--cyber-cyan);
  }

  p.hero-desc {
    font-size: 16.5px;
    color: var(--steel-light);
    margin-top: 20px;
    max-width: 580px;
    font-weight: 300;
  }
  @media (max-width: 990px) { p.hero-desc { margin: 20px auto 0; } }

  .hero-actions {
    margin-top: 32px;
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
  }
  @media (max-width: 990px) { .hero-actions { justify-content: center; } }

  .btn {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.06em;
    padding: 14px 28px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.25s ease;
    cursor: pointer;
    border: none;
  }
  .btn-primary {
    background: linear-gradient(135deg, var(--industrial-yellow), var(--industrial-amber));
    color: #000;
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.35);
  }
  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 26px rgba(245, 158, 11, 0.55);
  }
  .btn-cyber {
    background: rgba(6, 182, 212, 0.12);
    border: 1px solid var(--cyber-cyan);
    color: var(--cyber-cyan);
  }
  .btn-cyber:hover {
    background: var(--cyber-cyan);
    color: #000;
    box-shadow: var(--glow-cyan);
  }

  /* 3D Hero Canvas Container */
  .hero-3d-wrapper {
    position: relative;
    width: 100%;
    height: 480px;
    background: radial-gradient(circle at center, rgba(6, 182, 212, 0.12) 0%, rgba(15, 22, 35, 0.8) 70%);
    border: 1px solid var(--steel-border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.6);
  }
  #heroCanvas3D {
    width: 100%;
    height: 100%;
    display: block;
  }

  .canvas-controls-overlay {
    position: absolute;
    bottom: 16px;
    left: 16px;
    right: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(15, 22, 35, 0.85);
    backdrop-filter: blur(8px);
    padding: 10px 16px;
    border: 1px solid var(--steel-border);
    border-radius: 6px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
  }
  .control-btn-group {
    display: flex;
    gap: 8px;
  }
  .c-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid var(--steel-border);
    color: var(--paper-text);
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    transition: all 0.2s;
  }
  .c-btn:hover, .c-btn.active {
    background: var(--industrial-yellow);
    color: #000;
    border-color: var(--industrial-yellow);
  }

  /* Spec Plate Strip */
  .spec-plate-grid {
    margin-top: 50px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    padding: 24px;
    border-radius: 6px;
    position: relative;
  }
  .spec-plate-grid::before {
    content: 'TECHNICAL SPECIFICATION & STATUS';
    position: absolute;
    top: -11px;
    left: 20px;
    background: var(--bg-dark);
    padding: 0 10px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--cyber-cyan);
    letter-spacing: 0.12em;
  }
  .spec-box {
    border-right: 1px solid var(--steel-border);
    padding: 0 16px;
  }
  .spec-box:last-child { border-right: none; }
  .spec-val {
    font-family: 'Chakra Petch', sans-serif;
    font-size: 32px;
    font-weight: 700;
    color: #fff;
  }
  .spec-val span {
    font-size: 16px;
    color: var(--industrial-yellow);
    margin-left: 4px;
  }
  .spec-lbl {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--steel-light);
    margin-top: 4px;
    text-transform: uppercase;
  }
  @media (max-width: 800px) {
    .spec-plate-grid { grid-template-columns: 1fr 1fr; row-gap: 20px; }
    .spec-box { border-right: none; }
  }

  /* ===== SECTION COMMON ===== */
  section.sec {
    padding: 90px 0;
    border-bottom: 1px solid var(--steel-border);
  }
  .sec-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 44px;
    flex-wrap: wrap;
    gap: 16px;
  }
  .sec-tag {
    font-family: 'IBM Plex Mono', monospace;
    color: var(--cyber-cyan);
    font-size: 12px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .sec-tag::before {
    content: '';
    width: 16px;
    height: 2px;
    background: var(--cyber-cyan);
  }
  h2.sec-title {
    font-family: 'Chakra Petch', sans-serif;
    font-size: clamp(28px, 3.5vw, 42px);
    font-weight: 700;
    text-transform: uppercase;
    color: #fff;
  }

  /* ===== 3D INTERACTIVE PART VIEWER SECTION ===== */
  .part-viewer-container {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    border-radius: 8px;
    overflow: hidden;
  }
  @media (max-width: 960px) {
    .part-viewer-container { grid-template-columns: 1fr; }
  }
  
  .part-canvas-area {
    position: relative;
    height: 480px;
    background: radial-gradient(circle at center, #131c2d 0%, #090e17 80%);
  }
  #partViewerCanvas3D {
    width: 100%;
    height: 100%;
    display: block;
  }

  .part-sidebar {
    padding: 24px;
    background: var(--bg-card);
    border-left: 1px solid var(--steel-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .part-selector-title {
    font-family: 'Chakra Petch', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: var(--industrial-yellow);
    margin-bottom: 14px;
    text-transform: uppercase;
  }
  .part-btn-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .part-select-btn {
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    color: var(--paper-text);
    padding: 12px 14px;
    border-radius: 4px;
    text-align: left;
    cursor: pointer;
    font-family: 'Chakra Petch', sans-serif;
    font-size: 14px;
    transition: all 0.2s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .part-select-btn:hover, .part-select-btn.active {
    background: rgba(6, 182, 212, 0.15);
    border-color: var(--cyber-cyan);
    color: var(--cyber-cyan);
  }
  .part-select-btn.active::after {
    content: '▶';
    font-size: 10px;
    color: var(--industrial-yellow);
  }

  .part-info-box {
    margin-top: 20px;
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    padding: 14px;
    border-radius: 4px;
  }
  .part-info-name {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 600;
    font-size: 15px;
    color: #fff;
  }
  .part-info-desc {
    font-size: 12.5px;
    color: var(--steel-light);
    margin-top: 6px;
  }

  /* ===== EQUIPMENT FLEET GRID (3D TILT CARDS) ===== */
  .filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 28px;
    flex-wrap: wrap;
  }
  .tab-btn {
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    color: var(--steel-light);
    font-family: 'Chakra Petch', sans-serif;
    font-size: 13.5px;
    padding: 8px 18px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
  }
  .tab-btn:hover, .tab-btn.active {
    background: var(--industrial-yellow);
    color: #000;
    border-color: var(--industrial-yellow);
    font-weight: 600;
  }

  .eq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 22px;
    perspective: 1000px;
  }
  .eq-card {
    background: var(--bg-card);
    border: 1px solid var(--steel-border);
    border-radius: 6px;
    padding: 26px 22px;
    transform-style: preserve-3d;
    transition: transform 0.15s ease-out, border-color 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: hidden;
  }
  .eq-card:hover {
    border-color: var(--industrial-yellow);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), var(--glow-yellow);
  }
  .eq-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 4px; height: 100%;
    background: var(--industrial-yellow);
    opacity: 0;
    transition: opacity 0.3s;
  }
  .eq-card:hover::before { opacity: 1; }

  .eq-icon {
    font-size: 32px;
    margin-bottom: 12px;
    transform: translateZ(20px);
  }
  .eq-name-th {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #fff;
    transform: translateZ(25px);
  }
  .eq-name-en {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--cyber-cyan);
    transform: translateZ(20px);
    margin-bottom: 10px;
  }
  .eq-qty-badge {
    display: inline-block;
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid var(--industrial-yellow);
    color: var(--industrial-yellow);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 3px;
    margin-bottom: 14px;
    transform: translateZ(15px);
  }
  .eq-desc {
    font-size: 13px;
    color: var(--steel-light);
    margin-bottom: 16px;
    transform: translateZ(10px);
  }
  .eq-specs {
    border-top: 1px dashed var(--steel-border);
    padding-top: 12px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    transform: translateZ(8px);
  }
  .spec-item-sm {
    color: var(--steel-light);
  }
  .spec-item-sm strong {
    color: #fff;
    display: block;
  }

  /* ===== CURRICULUM SECTION ===== */
  .curr-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: 24px;
  }
  .curr-card {
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    border-radius: 8px;
    padding: 32px 26px;
    position: relative;
    transition: transform 0.3s, border-color 0.3s;
  }
  .curr-card:hover {
    transform: translateY(-4px);
    border-color: var(--cyber-cyan);
  }
  .curr-badge {
    display: inline-block;
    background: var(--cyber-cyan);
    color: #000;
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 13px;
    padding: 4px 12px;
    border-radius: 3px;
    margin-bottom: 14px;
  }
  .curr-title {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 21px;
    color: #fff;
    margin-bottom: 4px;
  }
  .curr-sub {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    color: var(--industrial-yellow);
    margin-bottom: 14px;
  }
  .curr-desc {
    font-size: 13.5px;
    color: var(--steel-light);
    margin-bottom: 20px;
  }
  .curr-skills-title {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--cyber-cyan);
    text-transform: uppercase;
    margin-bottom: 10px;
  }
  .curr-skills-list {
    list-style: none;
  }
  .curr-skills-list li {
    font-size: 13px;
    color: var(--paper-text);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .curr-skills-list li::before {
    content: '⚡';
    font-size: 11px;
    color: var(--industrial-yellow);
  }

  /* ===== PROCESS TIMELINE ===== */
  .process-timeline {
    position: relative;
    max-width: 900px;
    margin: 0 auto;
  }
  .process-timeline::before {
    content: '';
    position: absolute;
    left: 38px;
    top: 20px;
    bottom: 20px;
    width: 2px;
    background: linear-gradient(to bottom, var(--industrial-yellow), var(--cyber-cyan));
  }
  @media (max-width: 640px) { .process-timeline::before { left: 24px; } }

  .process-item {
    display: flex;
    gap: 28px;
    margin-bottom: 32px;
    position: relative;
  }
  .process-num {
    width: 76px;
    height: 76px;
    background: var(--bg-card);
    border: 2px solid var(--industrial-yellow);
    color: var(--industrial-yellow);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Oswald', sans-serif;
    font-size: 24px;
    font-weight: 700;
    flex-shrink: 0;
    z-index: 2;
    box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);
  }
  @media (max-width: 640px) {
    .process-num { width: 50px; height: 50px; font-size: 18px; }
  }
  .process-content {
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    padding: 22px 26px;
    border-radius: 6px;
    flex: 1;
  }
  .process-title {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #fff;
    margin-bottom: 6px;
  }
  .process-desc {
    font-size: 13.5px;
    color: var(--steel-light);
  }

  /* ===== SAFETY SECTION ===== */
  .hazard-band {
    height: 12px;
    background: repeating-linear-gradient(
      -45deg,
      var(--industrial-yellow),
      var(--industrial-yellow) 20px,
      #000 20px,
      #000 40px
    );
  }
  .safety-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-top: 36px;
  }
  .safety-card {
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    border-top: 3px solid var(--safety-orange);
    padding: 26px 22px;
    border-radius: 6px;
  }
  .safety-tag {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--safety-orange);
    margin-bottom: 8px;
  }
  .safety-card h3 {
    font-family: 'Chakra Petch', sans-serif;
    font-size: 18px;
    color: #fff;
    margin-bottom: 8px;
  }
  .safety-card p {
    font-size: 13px;
    color: var(--steel-light);
  }

  /* ===== SHIFT STATUS ===== */
  .shift-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  @media (max-width: 860px) { .shift-grid { grid-template-columns: 1fr; } }

  .shift-card {
    background: var(--bg-surface);
    border: 1px solid var(--steel-border);
    padding: 30px 24px;
    border-radius: 8px;
    position: relative;
    transition: all 0.3s;
  }
  .shift-card.active {
    border-color: var(--industrial-yellow);
    background: linear-gradient(180deg, rgba(245, 158, 11, 0.08) 0%, var(--bg-surface) 100%);
    box-shadow: var(--glow-yellow);
  }
  .shift-status-pill {
    position: absolute;
    top: 18px;
    right: 18px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.08);
    color: var(--steel-light);
  }
  .shift-card.active .shift-status-pill {
    background: var(--industrial-yellow);
    color: #000;
    font-weight: 700;
  }

  .shift-time {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 28px;
    font-weight: 700;
    color: #fff;
  }
  .shift-name {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 17px;
    color: var(--cyber-cyan);
    margin: 8px 0;
  }
  .shift-desc {
    font-size: 13px;
    color: var(--steel-light);
    margin-bottom: 14px;
  }
  .shift-cap {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--industrial-yellow);
    border-top: 1px dashed var(--steel-border);
    padding-top: 10px;
  }

  /* ===== FOOTER ===== */
  footer {
    background: #04060a;
    border-top: 1px solid var(--steel-border);
    padding: 70px 0 40px;
  }
  .foot-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 48px;
  }
  @media (max-width: 800px) { .foot-grid { grid-template-columns: 1fr; gap: 32px; } }

  .foot-brand-title {
    font-family: 'Chakra Petch', sans-serif;
    font-weight: 700;
    font-size: 22px;
    color: #fff;
    margin-bottom: 12px;
  }
  .foot-desc {
    font-size: 13.5px;
    color: var(--steel-light);
    max-width: 400px;
  }
  .foot-col h4 {
    font-family: 'Chakra Petch', sans-serif;
    font-size: 15px;
    color: var(--industrial-yellow);
    text-transform: uppercase;
    margin-bottom: 16px;
  }
  .foot-col a, .foot-col p {
    color: var(--steel-light);
    text-decoration: none;
    font-size: 13.5px;
    display: block;
    margin-bottom: 10px;
    transition: color 0.2s;
  }
  .foot-col a:hover { color: var(--cyber-cyan); }

  .foot-bottom {
    margin-top: 60px;
    padding-top: 24px;
    border-top: 1px solid var(--steel-border);
    display: flex;
    justify-content: space-between;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--steel-light);
    flex-wrap: wrap;
    gap: 12px;
  }

  /* ===== MODAL ===== */
  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
  }
  .modal-overlay.open {
    opacity: 1;
    pointer-events: auto;
  }
  .modal-box {
    background: var(--bg-surface);
    border: 1px solid var(--industrial-yellow);
    border-radius: 8px;
    width: 100%;
    max-width: 540px;
    padding: 32px;
    position: relative;
    box-shadow: var(--glow-yellow);
  }
  .modal-close {
    position: absolute;
    top: 16px; right: 16px;
    background: none; border: none;
    color: var(--steel-light); font-size: 24px;
    cursor: pointer;
  }
  .form-group { margin-bottom: 16px; }
  .form-group label {
    display: block;
    font-family: 'Chakra Petch', sans-serif;
    font-size: 13px;
    color: var(--industrial-yellow);
    margin-bottom: 6px;
  }
  .form-control {
    width: 100%;
    background: var(--bg-card);
    border: 1px solid var(--steel-border);
    color: #fff;
    padding: 10px 14px;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
  }
  .form-control:focus {
    outline: none;
    border-color: var(--cyber-cyan);
  }
</style>
</head>
<body class="blueprint-bg">

<!-- ===== TOP NAVIGATION ===== -->
<header class="top-nav">
  <div class="top-inner">
    <a href="#" class="brand">
      <div class="brand-badge">
        <span>M</span>
      </div>
      <div class="brand-title">
        แผนกช่างกลโรงงาน
        <small>FACTORY MACHINING TECHNOLOGY DEPT.</small>
      </div>
    </a>

    <nav class="nav-links">
      <a href="#hero">หน้าแรก</a>
      <a href="#3d-viewer">ชิ้นส่วน 3D</a>
      <a href="#equipment">รายการเครื่องจักร</a>
      <a href="#curriculum">หลักสูตร</a>
      <a href="#process">ขั้นตอนผลิต</a>
      <a href="#safety">ความปลอดภัย</a>
      <a href="#shifts">กะทำงาน</a>
      <a href="#contact">ติดต่อ</a>
    </nav>

    <div class="nav-badge-status">
      <div class="pulse-dot"></div>
      <span>สถานะโรงงาน: เปิดปฏิบัติการปกติ</span>
    </div>
  </div>
</header>

<!-- ===== HERO SECTION WITH 3D GEAR ANIMATION ===== -->
<section class="hero-section" id="hero">
  <div class="wrap">
    <div class="hero-grid">
      <div>
        <div class="hero-tag">⚙️ PRECISION MANUFACTURING & CNC TECHNOLOGY</div>
        <h1 class="hero-h1">
          แผนกช่างกล<span class="highlight">โรงงาน</span><br>
          <span class="sub-cyan">แม่นยำระดับไมครอน</span>
        </h1>
        <p class="hero-desc">
          มุ่งสู่ความเป็นเลิศด้านเทคโนโลยีการผลิต งานกลึง กัด ไส เจียร และระบบอัตโนมัติ CNC ด้วยเครื่องจักรมาตรฐานอุตสาหกรรม การฝึกทักษะปฏิบัติจริง และการควบคุมคุณภาพระดับสากล
        </p>

        <div class="hero-actions">
          <a href="#equipment" class="btn btn-primary">
            <span>⚙️ สำรวจเครื่องจักรในโรงงาน</span>
          </a>
          <button onclick="openModal()" class="btn btn-cyber">
            <span>📋 ส่งแบบประเมินคิวงานผลิต</span>
          </button>
        </div>
      </div>

      <!-- THREE.JS 3D CANVAS FOR INTERACTIVE GEAR TRANSMISSION -->
      <div class="hero-3d-wrapper">
        <canvas id="heroCanvas3D"></canvas>
        
        <div class="canvas-controls-overlay">
          <div>
            <span style="color:var(--cyber-cyan);">3D GEAR ASSEMBLY</span>
            <span style="color:var(--steel-light); margin-left:8px;">[INTERACTIVE CANVAS]</span>
          </div>
          <div class="control-btn-group">
            <button class="c-btn" id="btnExplode">Explode View</button>
            <button class="c-btn" id="btnWireframe">Wireframe</button>
            <button class="c-btn active" id="btnSpin">Auto Rotate</button>
          </div>
        </div>
      </div>
    </div>

    <!-- SPEC PLATE METRICS -->
    <div class="spec-plate-grid">
      <div class="spec-box">
        <div class="spec-val">±0.001<span>mm</span></div>
        <div class="spec-lbl">ความเที่ยงตรงสูงสุด (Tolerance)</div>
      </div>
      <div class="spec-box">
        <div class="spec-val"><?= count($equipment) ?><span>รุ่น</span></div>
        <div class="spec-lbl">เครื่องจักร CNC & Conventional</div>
      </div>
      <div class="spec-box">
        <div class="spec-val">100<span>%</span></div>
        <div class="spec-lbl">อัตราการได้งานทำของนักศึกษา</div>
      </div>
      <div class="spec-box">
        <div class="spec-val">180<span>วัน</span></div>
        <div class="spec-lbl">อุบัติเหตุสะสมเท่ากับศูนย์ (Zero Accident)</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== 3D INTERACTIVE PART VIEWER SECTION ===== -->
<section class="sec" id="3d-viewer">
  <div class="wrap">
    <div class="sec-header">
      <div>
        <div class="sec-tag">3D Interactive Inspection</div>
        <h2 class="sec-title">คลังชิ้นส่วน และชิ้นงานกลึง-กัด 3D</h2>
      </div>
      <div class="mono" style="font-size:12px; color:var(--steel-light);">
        *คลิกหมุน ลากซูม และปรับโหมดการแสดงผลชิ้นงาน 3D ได้ในเรียลไทม์
      </div>
    </div>

    <div class="part-viewer-container">
      <div class="part-canvas-area">
        <canvas id="partViewerCanvas3D"></canvas>
      </div>

      <div class="part-sidebar">
        <div>
          <div class="part-selector-title">เลือกชิ้นงาน 3D เพื่อตรวจสอบ</div>
          <div class="part-btn-list">
            <button class="part-select-btn active" onclick="load3DPart('gear')">
              <span>⚙️ Spur Gear Assembly</span>
            </button>
            <button class="part-select-btn" onclick="load3DPart('endmill')">
              <span>🗜️ 4-Flute CNC End Mill</span>
            </button>
            <button class="part-select-btn" onclick="load3DPart('chuck')">
              <span>🌀 Lathe 3-Jaw Chuck</span>
            </button>
            <button class="part-select-btn" onclick="load3DPart('piston')">
              <span>🔩 Engine Piston & Rod</span>
            </button>
          </div>

          <div class="part-info-box" id="partInfoBox">
            <div class="part-info-name" id="partName">Spur Gear Assembly (ชุดเฟืองขบตรง)</div>
            <div class="part-info-desc" id="partDesc">เฟืองตรงมาตรฐานอุตสาหกรรม ชุบแข็งผิวฟันเฟือง รับแรงบิดสูง ขึ้นรูปด้วยเครื่องกัดเฟืองอเนกประสงค์</div>
          </div>
        </div>

        <div style="margin-top:20px;">
          <div class="mono" style="font-size:11px; color:var(--steel-light); margin-bottom:8px;">SHADER MODE</div>
          <div style="display:flex; gap:6px;">
            <button class="c-btn active" id="btnShaderMetal" onclick="setShaderMode('metal')">Metallic</button>
            <button class="c-btn" id="btnShaderWire" onclick="setShaderMode('wire')">Blueprint</button>
            <button class="c-btn" id="btnShaderHeat" onclick="setShaderMode('heat')">Heatmap</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== EQUIPMENT FLEET SECTION ===== -->
<section class="sec" id="equipment">
  <div class="wrap">
    <div class="sec-header">
      <div>
        <div class="sec-tag">Machinery Fleet</div>
        <h2 class="sec-title">เครื่องจักรประจำโรงงานปฏิบัติการ</h2>
      </div>
      <div class="mono" style="font-size:12px; color:var(--steel-light);">
        เครื่องจักรคุณภาพสูงสำหรับการเรียนการสอนและการแปรรูปชิ้นงานจริง
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-tabs">
      <button class="tab-btn active" onclick="filterEq('all', this)">ทั้งหมด (<?= count($equipment) ?>)</button>
      <button class="tab-btn" onclick="filterEq('cnc', this)">เครื่องจักร CNC</button>
      <button class="tab-btn" onclick="filterEq('conventional', this)">เครื่องมือกลแบบธรรมดา</button>
      <button class="tab-btn" onclick="filterEq('precision', this)">เครื่องวัดละเอียด & เจียร</button>
    </div>

    <!-- EQUIPMENT GRID WITH 3D TILT CARDS -->
    <div class="eq-grid" id="eqGrid">
      <?php foreach ($equipment as $item): ?>
      <div class="eq-card" data-category="<?= htmlspecialchars($item["category"]) ?>">
        <div class="eq-icon"><?= $item["icon"] ?></div>
        <div class="eq-name-th"><?= htmlspecialchars($item["name_th"]) ?></div>
        <div class="eq-name-en"><?= htmlspecialchars($item["name"]) ?></div>
        <div class="eq-qty-badge">ประจำการ <?= (int)$item["qty"] ?> เครื่อง</div>
        <p class="eq-desc"><?= htmlspecialchars($item["desc"]) ?></p>
        
        <div class="eq-specs">
          <div class="spec-item-sm">
            ความเที่ยงตรง:
            <strong><?= htmlspecialchars($item["tol"]) ?></strong>
          </div>
          <div class="spec-item-sm">
            ระบบควบคุม:
            <strong><?= htmlspecialchars($item["controller"]) ?></strong>
          </div>
          <div class="spec-item-sm" style="grid-column: 1 / -1; margin-top:4px;">
            ขีดความสามารถ:
            <strong><?= htmlspecialchars($item["capacity"]) ?></strong>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== CURRICULUM SECTION ===== -->
<section class="sec" id="curriculum">
  <div class="wrap">
    <div class="sec-header">
      <div>
        <div class="sec-tag">Academic Courses</div>
        <h2 class="sec-title">หลักสูตรการเรียนการสอน</h2>
      </div>
      <div class="mono" style="font-size:12px; color:var(--steel-light);">
        การศึกษาและสร้างกำลังคนเฉพาะทางสายช่างกลโรงงาน
      </div>
    </div>

    <div class="curr-grid">
      <?php foreach ($curriculum as $c): ?>
      <div class="curr-card">
        <div class="curr-badge"><?= htmlspecialchars($c["level"]) ?></div>
        <div class="curr-title"><?= htmlspecialchars($c["title"]) ?></div>
        <div class="curr-sub"><?= htmlspecialchars($c["sub"]) ?></div>
        <div class="curr-desc"><?= htmlspecialchars($c["desc"]) ?></div>
        
        <div class="curr-skills-title">สมรรถนะสำคัญที่จะได้รับ</div>
        <ul class="curr-skills-list">
          <?php foreach ($c["highlight"] as $h): ?>
          <li><?= htmlspecialchars($h) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== PRODUCTION PROCESS SECTION ===== -->
<section class="sec" id="process">
  <div class="wrap">
    <div class="sec-header">
      <div>
        <div class="sec-tag">Production Workflow</div>
        <h2 class="sec-title">ขั้นตอนกระบวนการผลิตโลหะแม่นยำ</h2>
      </div>
      <div class="mono" style="font-size:12px; color:var(--steel-light);">
        มาตรฐานการทำงานตั้งแต่รับแบบ Drawing จนถึงตรวจสอบ QC และส่งมอบ
      </div>
    </div>

    <div class="process-timeline">
      <?php foreach ($process as $p): ?>
      <div class="process-item">
        <div class="process-num"><?= htmlspecialchars($p["step"]) ?></div>
        <div class="process-content">
          <div class="process-title"><?= htmlspecialchars($p["title"]) ?></div>
          <div class="process-desc"><?= htmlspecialchars($p["desc"]) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== SAFETY & PPE PROTOCOL ===== -->
<div class="hazard-band" role="presentation"></div>
<section class="sec" id="safety" style="background:var(--bg-dark);">
  <div class="wrap">
    <div class="sec-header">
      <div>
        <div class="sec-tag" style="color:var(--safety-orange);">Safety First & 5S Protocol</div>
        <h2 class="sec-title">มาตรฐานความปลอดภัยในโรงงาน</h2>
      </div>
      <div class="mono" style="font-size:12px; color:var(--steel-light);">
        บังคับใช้อุปกรณ์ป้องกันอันตรายส่วนบุคคล (PPE) ทุกคนก่อนเข้าพื้นที่ปฏิบัติงาน
      </div>
    </div>

    <div class="safety-grid">
      <div class="safety-card">
        <div class="safety-tag">PPE RULE #01</div>
        <h3>🥽 แว่นตานิรภัย (Safety Glasses)</h3>
        <p>สวมใส่ตลอดเวลาในรัศมีเครื่องจักรหมุน ป้องกันเศษโลหะและคูลแลนท์กระเด็นเข้าตา</p>
      </div>
      <div class="safety-card">
        <div class="safety-tag">PPE RULE #02</div>
        <h3>🥾 รองเท้าเซฟตี้ (Steel-Toe Shoes)</h3>
        <p>หัวเหล็กรับแรงกระแทก 200 จูล ป้องกันของหนักตกใส่ และพื้นกันลื่นจากคราบน้ำมัน</p>
      </div>
      <div class="safety-card">
        <div class="safety-tag">PPE RULE #03</div>
        <h3>🧤 ข้อควรระวังการใช้ถุงมือ</h3>
        <p>ห้ามสวมถุงมือขณะควบคุมเครื่องกลึงหรือเครื่องกัดหมุน ห้ามปล่อยผมยาวหรือใส่เครื่องประดับ</p>
      </div>
      <div class="safety-card">
        <div class="safety-tag">PPE RULE #04</div>
        <h3>🎧 อุปกรณ์ลดเสียง (Ear Protection)</h3>
        <p>สวมใส่ในโซนที่มีเสียงดังเกิน 85 เดซิเบล เช่น โซนเจียรและโซนเป่าลมทำความสะอาด</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== SHIFT SCHEDULE SECTION ===== -->
<section class="sec" id="shifts">
  <div class="wrap">
    <div class="sec-header">
      <div>
        <div class="sec-tag">Operation Shifts</div>
        <h2 class="sec-title">ตารางการทำงานและการเดินเครื่อง</h2>
      </div>
      <div class="mono" style="font-size:12px; color:var(--cyber-cyan);">
        เวลาเซิร์ฟเวอร์ปัจจุบัน: <?= date("H:i") ?> น. — ระบบไฮไลต์กะปัจจุบันอัตโนมัติ
      </div>
    </div>

    <div class="shift-grid">
      <?php foreach ($shifts as $s): 
        $active = is_active_shift($currentHour, $s);
      ?>
      <div class="shift-card<?= $active ? ' active' : '' ?>">
        <div class="shift-status-pill"><?= $active ? '🟢 กำลังปฏิบัติงานอยู่' : 'STANDBY' ?></div>
        <div class="shift-time">
          <?= str_pad($s["start"], 2, "0", STR_PAD_LEFT) ?>:00–<?= str_pad($s["end"] === 24 ? 0 : $s["end"], 2, "0", STR_PAD_LEFT) ?>:00
        </div>
        <div class="shift-name"><?= htmlspecialchars($s["name"]) ?></div>
        <div class="shift-desc"><?= htmlspecialchars($s["desc"]) ?></div>
        <div class="shift-cap">สถานะคิวเครื่อง: <?= htmlspecialchars($s["capacity"]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== FOOTER SECTION ===== -->
<footer id="contact">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <div class="foot-brand-title">แผนกช่างกลโรงงาน (Factory Machining Dept.)</div>
        <p class="foot-desc">
          พร้อมรับงานแปรรูปโลหะแม่นยำ งานวิจัย และงานบริการวิชาการ ส่งแบบ 2D/3D มาเพื่อประเมินระยะเวลาและคิวเครื่องจักรได้ตลอดเวลา
        </p>
      </div>

      <div class="foot-col">
        <h4>ช่องทางติดต่อ</h4>
        <p>📍 อาคารปฏิบัติการช่างกลโรงงาน แผนกวิชาช่างกล</p>
        <a href="tel:021234567">📞 โทรศัพท์: 02-123-4567 ต่อ 2210</a>
        <a href="mailto:machining@dept.ac.th">✉️ อีเมล: machining@dept.ac.th</a>
      </div>

      <div class="foot-col">
        <h4>เมนูด่วน</h4>
        <a href="#equipment">รายการเครื่องจักรทั้งหมด</a>
        <a href="#3d-viewer">เครื่องมือวัด & ชิ้นส่วน 3D</a>
        <a href="#curriculum">หลักสูตรการศึกษา</a>
        <a href="#safety">กฎระเบียบความปลอดภัย</a>
      </div>
    </div>

    <div class="foot-bottom">
      <span>© <?= $year ?> DEPARTMENT OF FACTORY MACHINING TECHNOLOGY. ALL RIGHTS RESERVED.</span>
      <span>HIGH-PRECISION MANUFACTURING DIVISION</span>
    </div>
  </div>
</footer>

<!-- WORK ORDER MODAL -->
<div class="modal-overlay" id="orderModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">×</button>
    <div class="heading-font" style="font-size:22px; color:var(--industrial-yellow); margin-bottom:6px;">
      📋 ส่งแบบประเมินคิวงานผลิต (Job Inquiry)
    </div>
    <p class="mono" style="font-size:12px; color:var(--steel-light); margin-bottom:20px;">
      กรอกข้อมูลชิ้นงานเพื่อให้เจ้าหน้าที่วิเคราะห์คิวเครื่องจักร
    </p>

    <form onsubmit="event.preventDefault(); alert('ส่งข้อมูลประเมินคิวงานเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับภายใน 24 ชม.'); closeModal();">
      <div class="form-group">
        <label>ชื่อผู้ติดต่อ / หน่วยงาน</label>
        <input type="text" class="form-control" required placeholder="เช่น นายสมชาย ช่างกล (บริษัท ABC จำกัด)">
      </div>
      <div class="form-group">
        <label>ประเภทงานที่ต้องการ</label>
        <select class="form-control">
          <option>งานกลึง CNC (CNC Lathe)</option>
          <option>งานกัด CNC 3D/5-Axis (CNC Milling)</option>
          <option>งานเจียรราบ / เจียรกลมละเอียด (Grinding)</option>
          <option>งานสร้างแม่พิมพ์ (Die & Mold)</option>
          <option>งานวัดพิกัด 3 มิติ (CMM Inspection)</option>
        </select>
      </div>
      <div class="form-group">
        <label>จำนวนชิ้นงาน (Pcs)</label>
        <input type="number" class="form-control" required placeholder="เช่น 10">
      </div>
      <div class="form-group">
        <label>เบอร์โทรศัพท์ติดต่อ</label>
        <input type="tel" class="form-control" required placeholder="081-234-5678">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:10px;">
        ส่งข้อมูลประเมินคิวงาน
      </button>
    </form>
  </div>
</div>

<!-- ===== THREE.JS 3D JAVASCRIPT ANIMATION SCRIPTS ===== -->
<script>
/* ==========================================================================
   1. HERO 3D SCENE: INTERACTIVE GEAR TRANSMISSION & SPINDLE ASSEMBLY
   ========================================================================== */
(function initHero3D() {
  const container = document.getElementById('heroCanvas3D').parentElement;
  const canvas = document.getElementById('heroCanvas3D');
  
  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x070a0f, 0.035);

  const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
  camera.position.set(0, 5, 14);

  const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
  renderer.setSize(container.clientWidth, container.clientHeight);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.shadowMap.enabled = true;

  // Controls
  const controls = new THREE.OrbitControls(camera, renderer.domElement);
  controls.enableDamping = true;
  controls.dampingFactor = 0.05;
  controls.maxDistance = 25;
  controls.minDistance = 6;

  // Lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
  scene.add(ambientLight);

  const dirLight1 = new THREE.DirectionalLight(0xf59e0b, 1.2);
  dirLight1.position.set(10, 15, 10);
  scene.add(dirLight1);

  const dirLight2 = new THREE.DirectionalLight(0x06b6d4, 1.5);
  dirLight2.position.set(-10, -10, -10);
  scene.add(dirLight2);

  const pointLight = new THREE.PointLight(0xffffff, 1, 20);
  pointLight.position.set(0, 0, 5);
  scene.add(pointLight);

  // Group for whole assembly
  const mainGroup = new THREE.Group();
  scene.add(mainGroup);

  // Helper function to create gear geometry
  function createGearGeometry(radius, thickness, teethCount, holeRadius) {
    const shape = new THREE.Shape();
    const toothAngle = (Math.PI * 2) / teethCount;

    for (let i = 0; i < teethCount; i++) {
      const angle = i * toothAngle;
      const rOuter = radius + 0.35;
      const rInner = radius;

      const a1 = angle;
      const a2 = angle + toothAngle * 0.25;
      const a3 = angle + toothAngle * 0.55;
      const a4 = angle + toothAngle * 0.8;

      if (i === 0) shape.moveTo(Math.cos(a1) * rInner, Math.sin(a1) * rInner);
      else shape.lineTo(Math.cos(a1) * rInner, Math.sin(a1) * rInner);

      shape.lineTo(Math.cos(a2) * rOuter, Math.sin(a2) * rOuter);
      shape.lineTo(Math.cos(a3) * rOuter, Math.sin(a3) * rOuter);
      shape.lineTo(Math.cos(a4) * rInner, Math.sin(a4) * rInner);
    }

    const holePath = new THREE.Path();
    holePath.absarc(0, 0, holeRadius, 0, Math.PI * 2, true);
    shape.holes.push(holePath);

    const extrudeSettings = {
      steps: 1,
      depth: thickness,
      bevelEnabled: true,
      bevelThickness: 0.08,
      bevelSize: 0.08,
      bevelSegments: 3
    };

    return new THREE.ExtrudeGeometry(shape, extrudeSettings);
  }

  // Materials
  const metalMaterial = new THREE.MeshStandardMaterial({
    color: 0x94a3b8,
    metalness: 0.85,
    roughness: 0.25,
  });

  const goldMaterial = new THREE.MeshStandardMaterial({
    color: 0xf59e0b,
    metalness: 0.9,
    roughness: 0.2,
  });

  const cyanMaterial = new THREE.MeshStandardMaterial({
    color: 0x06b6d4,
    metalness: 0.8,
    roughness: 0.3,
  });

  // Gear 1 (Main Big Gear)
  const g1Geo = createGearGeometry(3.2, 0.6, 24, 1.2);
  const gear1 = new THREE.Mesh(g1Geo, goldMaterial);
  gear1.position.set(0, 0, 0);
  mainGroup.add(gear1);

  // Gear 2 (Pinion Small Gear)
  const g2Geo = createGearGeometry(1.6, 0.6, 12, 0.6);
  const gear2 = new THREE.Mesh(g2Geo, cyanMaterial);
  gear2.position.set(4.9, 0, 0);
  mainGroup.add(gear2);

  // Gear 3 (Upper Interlocking Gear)
  const g3Geo = createGearGeometry(2.0, 0.6, 15, 0.8);
  const gear3 = new THREE.Mesh(g3Geo, metalMaterial);
  gear3.position.set(-2.8, 3.5, 0);
  mainGroup.add(gear3);

  // CNC Spindle Shaft Center
  const spindleGeo = new THREE.CylinderGeometry(0.7, 0.7, 4, 32);
  const spindle = new THREE.Mesh(spindleGeo, metalMaterial);
  spindle.rotation.x = Math.PI / 2;
  spindle.position.z = -1;
  mainGroup.add(spindle);

  // Machining Spark Particles System
  const sparkCount = 120;
  const sparkGeo = new THREE.BufferGeometry();
  const sparkPositions = new Float32Array(sparkCount * 3);
  const sparkVelocities = [];

  for (let i = 0; i < sparkCount; i++) {
    sparkPositions[i * 3] = (Math.random() - 0.5) * 2;
    sparkPositions[i * 3 + 1] = (Math.random() - 0.5) * 2;
    sparkPositions[i * 3 + 2] = (Math.random() - 0.5) * 2;

    sparkVelocities.push({
      x: (Math.random() - 0.5) * 0.15,
      y: (Math.random() - 0.5) * 0.15,
      z: (Math.random() - 0.5) * 0.15
    });
  }

  sparkGeo.setAttribute('position', new THREE.BufferAttribute(sparkPositions, 3));
  const sparkMat = new THREE.PointsMaterial({
    color: 0xffaa00,
    size: 0.15,
    transparent: true,
    opacity: 0.9,
    blending: THREE.AdditiveBlending
  });
  const sparkParticles = new THREE.Points(sparkGeo, sparkMat);
  mainGroup.add(sparkParticles);

  // Blueprint Grid background plane
  const gridHelper = new THREE.GridHelper(30, 30, 0x06b6d4, 0x1f293d);
  gridHelper.rotation.x = Math.PI / 2;
  gridHelper.position.z = -3;
  scene.add(gridHelper);

  // State variables for controls
  let autoRotate = true;
  let isWireframe = false;
  let isExplode = false;

  // Animation Loop
  let clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    const delta = clock.getDelta();

    if (autoRotate) {
      gear1.rotation.z += 0.4 * delta;
      gear2.rotation.z -= 0.8 * delta;
      gear3.rotation.z -= 0.64 * delta;
      spindle.rotation.y += 2.0 * delta;
      mainGroup.rotation.y += 0.15 * delta;
    }

    // Animate sparks
    const positions = sparkParticles.geometry.attributes.position.array;
    for (let i = 0; i < sparkCount; i++) {
      positions[i * 3] += sparkVelocities[i].x;
      positions[i * 3 + 1] += sparkVelocities[i].y;
      positions[i * 3 + 2] += sparkVelocities[i].z;

      if (Math.abs(positions[i * 3]) > 5) positions[i * 3] = 0;
      if (Math.abs(positions[i * 3 + 1]) > 5) positions[i * 3 + 1] = 0;
      if (Math.abs(positions[i * 3 + 2]) > 5) positions[i * 3 + 2] = 0;
    }
    sparkParticles.geometry.attributes.position.needsUpdate = true;

    // Explode effect interpolation
    const targetZ2 = isExplode ? 2.5 : 0;
    const targetZ3 = isExplode ? -2.5 : 0;
    gear2.position.z += (targetZ2 - gear2.position.z) * 0.1;
    gear3.position.z += (targetZ3 - gear3.position.z) * 0.1;

    controls.update();
    renderer.render(scene, camera);
  }
  animate();

  // Control Buttons Event Listeners
  document.getElementById('btnSpin').addEventListener('click', function() {
    autoRotate = !autoRotate;
    this.classList.toggle('active', autoRotate);
  });

  document.getElementById('btnWireframe').addEventListener('click', function() {
    isWireframe = !isWireframe;
    this.classList.toggle('active', isWireframe);
    gear1.material.wireframe = isWireframe;
    gear2.material.wireframe = isWireframe;
    gear3.material.wireframe = isWireframe;
    spindle.material.wireframe = isWireframe;
  });

  document.getElementById('btnExplode').addEventListener('click', function() {
    isExplode = !isExplode;
    this.classList.toggle('active', isExplode);
  });

  // Responsive Resize
  window.addEventListener('resize', () => {
    camera.aspect = container.clientWidth / container.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
  });
})();


/* ==========================================================================
   2. 3D INTERACTIVE PART VIEWER ENGINE
   ========================================================================== */
let partScene, partCamera, partRenderer, partControls, currentMeshGroup;
let currentPartType = 'gear';
let currentShaderMode = 'metal';

(function initPartViewer() {
  const container = document.getElementById('partViewerCanvas3D').parentElement;
  const canvas = document.getElementById('partViewerCanvas3D');

  partScene = new THREE.Scene();
  partScene.fog = new THREE.FogExp2(0x090e17, 0.03);

  partCamera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
  partCamera.position.set(0, 3, 8);

  partRenderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
  partRenderer.setSize(container.clientWidth, container.clientHeight);
  partRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  partControls = new THREE.OrbitControls(partCamera, partRenderer.domElement);
  partControls.enableDamping = true;
  partControls.dampingFactor = 0.05;

  // Lights
  const ambLight = new THREE.AmbientLight(0xffffff, 0.7);
  partScene.add(ambLight);

  const light1 = new THREE.DirectionalLight(0x06b6d4, 1.2);
  light1.position.set(5, 10, 7);
  partScene.add(light1);

  const light2 = new THREE.DirectionalLight(0xf59e0b, 1.0);
  light2.position.set(-5, -5, -5);
  partScene.add(light2);

  currentMeshGroup = new THREE.Group();
  partScene.add(currentMeshGroup);

  // Load default model
  buildModel('gear');

  function renderPart() {
    requestAnimationFrame(renderPart);
    if (currentMeshGroup) {
      currentMeshGroup.rotation.y += 0.005;
    }
    partControls.update();
    partRenderer.render(partScene, partCamera);
  }
  renderPart();

  window.addEventListener('resize', () => {
    partCamera.aspect = container.clientWidth / container.clientHeight;
    partCamera.updateProjectionMatrix();
    partRenderer.setSize(container.clientWidth, container.clientHeight);
  });
})();

function getPartMaterial() {
  if (currentShaderMode === 'wire') {
    return new THREE.MeshBasicMaterial({ color: 0x06b6d4, wireframe: true });
  } else if (currentShaderMode === 'heat') {
    return new THREE.MeshNormalMaterial();
  } else {
    return new THREE.MeshStandardMaterial({
      color: 0xcbd5e1,
      metalness: 0.9,
      roughness: 0.2,
    });
  }
}

function buildModel(type) {
  while (currentMeshGroup.children.length > 0) {
    currentMeshGroup.remove(currentMeshGroup.children[0]);
  }

  const mat = getPartMaterial();

  if (type === 'gear') {
    // Gear model
    const geo = new THREE.CylinderGeometry(2, 2, 0.8, 24);
    const mesh = new THREE.Mesh(geo, mat);
    currentMeshGroup.add(mesh);

    // Inner shaft
    const shaftGeo = new THREE.CylinderGeometry(0.6, 0.6, 2, 16);
    const shaft = new THREE.Mesh(shaftGeo, mat);
    currentMeshGroup.add(shaft);
  } else if (type === 'endmill') {
    // CNC End Mill Cutter
    const bodyGeo = new THREE.CylinderGeometry(0.6, 0.6, 4, 32);
    const body = new THREE.Mesh(bodyGeo, mat);
    currentMeshGroup.add(body);

    const tipGeo = new THREE.ConeGeometry(0.6, 1.2, 4);
    const tip = new THREE.Mesh(tipGeo, mat);
    tip.position.y = -2.4;
    tip.rotation.x = Math.PI;
    currentMeshGroup.add(tip);
  } else if (type === 'chuck') {
    // Lathe Chuck Body
    const chuckGeo = new THREE.CylinderGeometry(2.4, 2.4, 1.2, 32);
    const chuck = new THREE.Mesh(chuckGeo, mat);
    currentMeshGroup.add(chuck);

    // 3 Jaws
    for (let i = 0; i < 3; i++) {
      const jawGeo = new THREE.BoxGeometry(0.6, 0.8, 1.4);
      const jaw = new THREE.Mesh(jawGeo, mat);
      const angle = (i * Math.PI * 2) / 3;
      jaw.position.set(Math.cos(angle) * 1.4, 0.8, Math.sin(angle) * 1.4);
      jaw.rotation.y = -angle;
      currentMeshGroup.add(jaw);
    }
  } else if (type === 'piston') {
    // Engine Piston Head
    const headGeo = new THREE.CylinderGeometry(1.6, 1.6, 2.2, 32);
    const head = new THREE.Mesh(headGeo, mat);
    head.position.y = 1;
    currentMeshGroup.add(head);

    // Connecting Rod
    const rodGeo = new THREE.CylinderGeometry(0.4, 0.4, 3.5, 16);
    const rod = new THREE.Mesh(rodGeo, mat);
    rod.position.y = -1.5;
    currentMeshGroup.add(rod);
  }
}

function load3DPart(type) {
  currentPartType = type;
  document.querySelectorAll('.part-select-btn').forEach(btn => btn.classList.remove('active'));
  event.currentTarget.classList.add('active');

  const names = {
    gear: 'Spur Gear Assembly (ชุดเฟืองขบตรง)',
    endmill: '4-Flute CNC End Mill (ดอกกัดเอ็นมิล 4 ฟัน)',
    chuck: 'Lathe 3-Jaw Chuck (หัวจับเครื่องกลึง 3 จับ)',
    piston: 'Engine Piston & Rod (ลูกสูบและก้านสูบ)'
  };
  const descs = {
    gear: 'เฟืองตรงมาตรฐานอุตสาหกรรม ชุบแข็งผิวฟันเฟือง รับแรงบิดสูง ขึ้นรูปด้วยเครื่องกัดเฟืองอเนกประสงค์',
    endmill: 'ดอกกัดคาร์ไบด์ 4 ฟัน เคลือบสาร TiAlN สำหรับกัดโลหะแข็งพิเศษ ความเร็วรอบ Spindle สูงสุด 12,000 RPM',
    chuck: 'หัวจับเครื่องกลึง 3 จับอิสระ ปรับตั้งศูนย์ด้วยความเที่ยงตรง สำหรับจับยึดชิ้นงานทรงกลมและเพลา',
    piston: 'ชุดลูกสูบและก้านสูบเครื่องยนต์อะลูมิเนียมอัลลอยด์ แปรรูปด้วยเครื่องกลึง CNC และ CMM Inspection'
  };

  document.getElementById('partName').innerText = names[type];
  document.getElementById('partDesc').innerText = descs[type];

  buildModel(type);
}

function setShaderMode(mode) {
  currentShaderMode = mode;
  document.getElementById('btnShaderMetal').classList.toggle('active', mode === 'metal');
  document.getElementById('btnShaderWire').classList.toggle('active', mode === 'wire');
  document.getElementById('btnShaderHeat').classList.toggle('active', mode === 'heat');
  buildModel(currentPartType);
}

/* ==========================================================================
   3. CARD 3D TILT EFFECT & FILTERS
   ========================================================================== */
document.querySelectorAll('.eq-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const rx = ((y / rect.height) - 0.5) * -16;
    const ry = ((x / rect.width) - 0.5) * 16;
    card.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) translateZ(10px)`;
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
  });
});

function filterEq(cat, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  document.querySelectorAll('.eq-card').forEach(card => {
    if (cat === 'all' || card.dataset.category === cat) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

/* ==========================================================================
   4. MODAL CONTROLS
   ========================================================================== */
function openModal() {
  document.getElementById('orderModal').classList.add('open');
}
function closeModal() {
  document.getElementById('orderModal').classList.remove('open');
}
</script>
</body>
</html>
