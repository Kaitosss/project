<?php
require_once "./db.php";
// ===== ข้อมูลแผนก (แก้ตรงนี้ได้ตามจริง) =====
$equipment = [
    [
        "name" => "CNC Lathe",
        "qty" => 4,
        "desc" => "กลึงปาดหน้า กลึงปอก เจาะรูตรงกลาง ทำเกลียวใน-นอก",
        "tol" => "±0.02 mm",
    ],
    [
        "name" => "CNC Milling 3-Axis",
        "qty" => 5,
        "desc" => "กัดผิวเรียบ กัดร่อง เจาะรูตามแบบ 2D/3D",
        "tol" => "±0.015 mm",
    ],
    [
        "name" => "Surface Grinding",
        "qty" => 2,
        "desc" => "เจียรผิวให้เรียบและได้ขนาดตามสเปกละเอียด",
        "tol" => "±0.005 mm",
    ],
    [
        "name" => "Radial Drill Press",
        "qty" => 2,
        "desc" => "เจาะรูขนาดใหญ่ และงานที่ต้องปรับมุมเจาะ",
        "tol" => "±0.05 mm",
    ],
    [
        "name" => "Manual Lathe",
        "qty" => 1,
        "desc" => "งานซ่อมด่วนและงานต้นแบบจำนวนน้อย",
        "tol" => "±0.05 mm",
    ],
];

$process = [
    [
        "รับแบบและใบสั่งผลิต",
        "ตรวจสอบแบบงาน (Drawing) และสเปกความเที่ยงตรงที่ลูกค้าต้องการ",
    ],
    [
        "เขียนโปรแกรม CNC",
        "กำหนดเส้นทางเครื่องมือและเลือกเครื่องจักรที่เหมาะกับชิ้นงาน",
    ],
    [
        "ตั้งเครื่องและจับชิ้นงาน",
        "ติดตั้งอุปกรณ์จับยึด (Jig/Fixture) และปรับศูนย์เครื่องจักร",
    ],
    ["กลึง / กัด / เจียร", "ขึ้นรูปชิ้นงานตามลำดับกระบวนการที่วางแผนไว้"],
    [
        "ตรวจสอบคุณภาพ (QC)",
        "วัดขนาดด้วยเครื่องมือวัดละเอียด เทียบกับแบบงานทุกจุด",
    ],
    ["แพ็คและส่งมอบ", "บรรจุป้องกันความเสียหายและส่งมอบตามกำหนดเวลา"],
];

$shifts = [
    [
        "start" => 8,
        "end" => 16,
        "name" => "กะเช้า",
        "desc" => "ตั้งเครื่องจักรหลักและรับใบสั่งผลิตประจำวัน",
    ],
    [
        "start" => 16,
        "end" => 24,
        "name" => "กะบ่าย",
        "desc" => "เดินเครื่องผลิตต่อเนื่องและตรวจสอบคุณภาพระหว่างผลิต",
    ],
    [
        "start" => 0,
        "end" => 8,
        "name" => "กะดึก",
        "desc" => "ผลิตงานเร่งด่วนและบำรุงรักษาเครื่องจักรเบื้องต้น",
    ],
];

$currentHour = (int) date("G");
function is_active_shift(int $hour, array $shift): bool
{
    if ($shift["start"] < $shift["end"]) {
        return $hour >= $shift["start"] && $hour < $shift["end"];
    }
    return $hour >= $shift["start"] || $hour < $shift["end"];
}
$year = date("Y");


$conn = new DB();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แผนก Machine Shop — งานกลึง กัด เจียรแม่นยำ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --steel-900:#14181c; --steel-800:#1c2227; --steel-700:#2a3138; --steel-600:#3d454d;
    --line:#4a535c; --paper:#e9e6dd; --paper-dim:#a8a49a;
    --safety-yellow:#f2b705; --weld-orange:#d9530e; --blueprint:#3a6a8f;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{background:var(--steel-900); color:var(--paper); font-family:'IBM Plex Sans Thai','IBM Plex Mono',sans-serif; line-height:1.6; overflow-x:hidden;}
  @media (prefers-reduced-motion: reduce){ *{animation-duration:0.01ms !important; transition-duration:0.01ms !important;} }
  .mono{font-family:'IBM Plex Mono',monospace;}
  a:focus-visible, button:focus-visible{outline:2px solid var(--safety-yellow); outline-offset:3px;}

  .grid-bg{
    background-image:linear-gradient(var(--steel-700) 1px, transparent 1px), linear-gradient(90deg, var(--steel-700) 1px, transparent 1px);
    background-size:40px 40px; background-position:-1px -1px;
  }
  .wrap{max-width:1180px; margin:0 auto; padding:0 28px;}

  /* ===== TOP RAIL ===== */
  header.top{position:sticky; top:0; z-index:50; background:rgba(20,24,28,0.92); backdrop-filter:blur(6px); border-bottom:1px solid var(--line);}
  .top-inner{display:flex; align-items:center; justify-content:space-between; padding:16px 28px; max-width:1180px; margin:0 auto;}
  .brand{display:flex; align-items:center; gap:12px;}
  .brand-mark{width:34px; height:34px; border:2px solid var(--safety-yellow); display:flex; align-items:center; justify-content:center; transform:rotate(45deg); flex-shrink:0;}
  .brand-mark span{transform:rotate(-45deg); font-family:'Oswald'; font-weight:700; color:var(--safety-yellow); font-size:15px;}
  .brand-text{font-family:'Oswald'; font-weight:600; letter-spacing:0.06em; font-size:15px; text-transform:uppercase;}
  .brand-text small{display:block; font-family:'IBM Plex Mono'; font-weight:400; font-size:10px; color:var(--paper-dim); letter-spacing:0.1em; text-transform:none;}
  nav.top-links{display:flex; gap:28px;}
  nav.top-links a{color:var(--paper-dim); text-decoration:none; font-size:13px; font-family:'IBM Plex Mono'; letter-spacing:0.03em; transition:color 0.2s;}
  nav.top-links a:hover{color:var(--safety-yellow);}
  @media (max-width:780px){ nav.top-links{display:none;} }

  /* ===== HERO + 3D GEAR ===== */
  .hero{position:relative; padding:100px 0 70px; border-bottom:1px solid var(--line);}
  .hero .wrap{position:relative; z-index:2; display:grid; grid-template-columns:1.3fr 0.7fr; gap:20px; align-items:center;}
  @media (max-width:900px){ .hero .wrap{grid-template-columns:1fr;} }
  .hero-eyebrow{font-family:'IBM Plex Mono'; color:var(--weld-orange); font-size:13px; letter-spacing:0.15em; text-transform:uppercase; margin-bottom:18px; display:flex; align-items:center; gap:10px;}
  .hero-eyebrow::before{content:''; width:26px; height:2px; background:var(--weld-orange); display:inline-block;}
  h1.hero-title{font-family:'Oswald'; font-weight:700; text-transform:uppercase; font-size:clamp(38px,6vw,74px); line-height:0.98;}
  h1.hero-title .accent{color:var(--safety-yellow);}
  .hero-sub{max-width:560px; margin-top:24px; font-size:17px; color:var(--paper-dim); font-weight:300;}
  .hero-ctas{margin-top:36px; display:flex; gap:16px; flex-wrap:wrap;}
  .btn{font-family:'IBM Plex Mono'; font-size:13px; letter-spacing:0.05em; padding:14px 26px; text-decoration:none; display:inline-block; border:1px solid var(--line); transition:all 0.2s;}
  .btn-primary{background:var(--safety-yellow); color:var(--steel-900); border-color:var(--safety-yellow); font-weight:600;}
  .btn-primary:hover{background:#ffd23f;}
  .btn-ghost{color:var(--paper);}
  .btn-ghost:hover{border-color:var(--paper); background:var(--steel-800);}

  /* --- 3D hex gear made of stacked CSS layers (pure CSS 3D, no images) --- */
  .gear-stage{ perspective:900px; display:flex; justify-content:center; align-items:center; height:280px; }
  .gear3d{ width:180px; height:180px; position:relative; transform-style:preserve-3d; animation: spin3d 10s linear infinite; }
  @keyframes spin3d{ from{ transform:rotateX(-18deg) rotateY(0deg); } to{ transform:rotateX(-18deg) rotateY(360deg); } }
  .gear-ring{ position:absolute; inset:0; border-radius:50%; border:10px solid var(--steel-700); box-shadow:inset 0 0 0 4px var(--steel-900); }
  .gear-ring.face-front{ transform:translateZ(20px); border-color:var(--safety-yellow); opacity:0.9;}
  .gear-ring.face-back{ transform:translateZ(-20px) rotateY(180deg); border-color:var(--weld-orange); opacity:0.55;}
  .gear-tooth{ position:absolute; width:18px; height:34px; background:var(--steel-600); left:50%; top:50%; transform-origin:center 90px; margin:-90px 0 0 -9px; }
  .gear-hub{ position:absolute; inset:66px; border-radius:50%; background:var(--steel-800); border:3px solid var(--blueprint); transform:translateZ(24px); display:flex; align-items:center; justify-content:center; font-family:'IBM Plex Mono'; font-size:11px; color:var(--paper-dim); letter-spacing:0.08em;}

  .spec-strip{ margin-top:64px; border-top:1px dashed var(--line); border-bottom:1px dashed var(--line); padding:26px 0; display:grid; grid-template-columns:repeat(4,1fr); position:relative; }
  .spec-strip::before{ content:'SPEC PLATE — REV.02'; position:absolute; top:-11px; left:0; background:var(--steel-900); padding:0 10px 0 0; font-family:'IBM Plex Mono'; font-size:10px; letter-spacing:0.12em; color:var(--paper-dim); }
  .spec-item{ padding:0 20px; border-left:1px solid var(--line); }
  .spec-item:first-child{ border-left:none; padding-left:0; }
  .spec-num{ font-family:'Oswald'; font-size:34px; font-weight:600; color:var(--paper); }
  .spec-num .unit{ font-family:'IBM Plex Mono'; font-size:15px; color:var(--safety-yellow); margin-left:2px; }
  .spec-label{ font-family:'IBM Plex Mono'; font-size:11px; color:var(--paper-dim); letter-spacing:0.06em; margin-top:6px; text-transform:uppercase; }
  @media (max-width:900px){ .spec-strip{grid-template-columns:1fr 1fr; row-gap:26px;} }

  /* ===== SECTIONS ===== */
  .sec{padding:90px 0; border-bottom:1px solid var(--line);}
  .sec-head{display:flex; align-items:flex-end; justify-content:space-between; gap:20px; margin-bottom:48px; flex-wrap:wrap;}
  .sec-tag{font-family:'IBM Plex Mono'; color:var(--blueprint); font-size:12px; letter-spacing:0.14em; text-transform:uppercase; margin-bottom:10px;}
  h2.sec-title{font-family:'Oswald'; font-weight:600; text-transform:uppercase; font-size:clamp(26px,4vw,42px);}
  .sec-note{font-family:'IBM Plex Mono'; font-size:12px; color:var(--paper-dim); max-width:260px; text-align:right;}
  @media (max-width:780px){ .sec-note{text-align:left;} }

  /* ===== EQUIPMENT — 3D TILT CARDS ===== */
  .eq-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:18px; perspective:1200px; }
  .eq-card{
    background:var(--steel-800); border:1px solid var(--steel-700); padding:26px 22px;
    transform-style:preserve-3d; transition:transform 0.15s ease, border-color 0.2s;
    will-change:transform;
  }
  .eq-card:hover{ border-color:var(--safety-yellow); }
  .eq-name{font-family:'Oswald'; font-size:19px; text-transform:uppercase; font-weight:500; transform:translateZ(24px);}
  .eq-qty{font-family:'IBM Plex Mono'; color:var(--safety-yellow); font-size:13px; margin-top:6px; transform:translateZ(18px);}
  .eq-desc{color:var(--paper-dim); font-size:13.5px; margin-top:14px; transform:translateZ(12px);}
  .eq-tol{font-family:'IBM Plex Mono'; font-size:12px; color:var(--blueprint); margin-top:16px; padding-top:14px; border-top:1px solid var(--steel-700); transform:translateZ(8px);}

  /* ===== PROCESS FLOW ===== */
  .flow-step{display:grid; grid-template-columns:90px 1fr; gap:24px; padding:28px 0; border-top:1px solid var(--steel-700);}
  .flow-step:last-child{border-bottom:1px solid var(--steel-700);}
  .flow-idx{font-family:'IBM Plex Mono'; font-size:13px; color:var(--weld-orange);}
  .flow-idx .line{display:block; width:1px; height:22px; background:var(--line); margin:8px 0 0 6px;}
  .flow-title{font-family:'Oswald'; font-size:20px; text-transform:uppercase; font-weight:500; margin-bottom:6px;}
  .flow-desc{color:var(--paper-dim); font-size:14px; max-width:520px;}

  /* ===== SAFETY ===== */
  .safety-band{background:repeating-linear-gradient(135deg, var(--safety-yellow), var(--safety-yellow) 18px, var(--steel-900) 18px, var(--steel-900) 36px); height:10px; width:100%;}
  .safety-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:var(--steel-700); margin-top:48px;}
  .safety-card{background:var(--steel-800); padding:28px 22px;}
  .safety-icon{font-family:'IBM Plex Mono'; font-size:11px; color:var(--weld-orange); letter-spacing:0.1em; margin-bottom:14px;}
  .safety-card h3{font-family:'Oswald'; font-size:16px; text-transform:uppercase; margin-bottom:8px;}
  .safety-card p{font-size:13px; color:var(--paper-dim);}
  @media (max-width:900px){ .safety-grid{grid-template-columns:1fr 1fr;} }
  @media (max-width:520px){ .safety-grid{grid-template-columns:1fr;} }

  /* ===== SHIFTS (active = highlighted by PHP) ===== */
  .shift-row{display:grid; grid-template-columns:1fr 1fr 1fr; gap:1px; background:var(--steel-700);}
  .shift-card{background:var(--steel-800); padding:34px 26px; position:relative; transition:transform 0.2s;}
  .shift-card.active{ background:var(--steel-700); }
  .shift-card.active::before{ content:'● กำลังปฏิบัติงาน'; position:absolute; top:14px; right:16px; font-family:'IBM Plex Mono'; font-size:10px; color:var(--safety-yellow); letter-spacing:0.05em; }
  .shift-time{font-family:'IBM Plex Mono'; font-size:26px; color:var(--paper); letter-spacing:0.02em;}
  .shift-time .to{color:var(--paper-dim); margin:0 4px;}
  .shift-name{font-family:'Oswald'; text-transform:uppercase; font-size:14px; color:var(--safety-yellow); margin-top:10px; letter-spacing:0.06em;}
  .shift-desc{font-size:13px; color:var(--paper-dim); margin-top:10px;}
  @media (max-width:780px){ .shift-row{grid-template-columns:1fr;} }

  footer{padding:70px 0 40px;}
  .foot-grid{display:grid; grid-template-columns:1.4fr 1fr 1fr; gap:40px;}
  .foot-title{font-family:'Oswald'; font-size:26px; text-transform:uppercase; margin-bottom:14px;}
  .foot-col h4{font-family:'IBM Plex Mono'; font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:var(--paper-dim); margin-bottom:14px;}
  .foot-col p, .foot-col a{color:var(--paper); font-size:14px; text-decoration:none; display:block; margin-bottom:8px;}
  .foot-col a:hover{color:var(--safety-yellow);}
  .foot-bottom{margin-top:60px; padding-top:20px; border-top:1px solid var(--line); font-family:'IBM Plex Mono'; font-size:11px; color:var(--paper-dim); display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;}
  @media (max-width:780px){ .foot-grid{grid-template-columns:1fr; gap:30px;} }
  ::selection{background:var(--safety-yellow); color:var(--steel-900);}
</style>
</head>
<body>

<header class="top">
  <div class="top-inner">
    <div class="brand">
      <div class="brand-mark"><span>M</span></div>
      <div class="brand-text">Machine Shop<small>Precision Manufacturing Dept.</small></div>
    </div>
    <nav class="top-links">
      <a href="#equipment">เครื่องจักร</a>
      <a href="#process">ขั้นตอนงาน</a>
      <a href="#safety">ความปลอดภัย</a>
      <a href="#shifts">กะทำงาน</a>
      <a href="#contact">ติดต่อ</a>
    </nav>
  </div>
</header>

<section class="hero grid-bg">
  <div class="wrap">
    <div>
      <div class="hero-eyebrow">แผนกผลิต · งานโลหะแม่นยำ</div>
      <h1 class="hero-title">Machine<br>Shop <span class="accent">Dept.</span></h1>
      <p class="hero-sub">แผนกกลึง กัด เจียร และเจาะชิ้นงานโลหะตามแบบ ด้วยเครื่องจักร CNC และเครื่องแบบธรรมดา ควบคุมความเที่ยงตรงทุกขั้นตอนตั้งแต่รับแบบจนถึงส่งมอบ</p>
      <div class="hero-ctas">
        <a href="#equipment" class="btn btn-primary">ดูเครื่องจักรทั้งหมด</a>
        <a href="#contact" class="btn btn-ghost">ส่งงานเข้าคิวการผลิต</a>
      </div>
    </div>

    <div class="gear-stage">
      <div class="gear3d">
        <div class="gear-ring face-front"></div>
        <div class="gear-ring face-back"></div>
        <div class="gear-hub">CNC</div>
        <?php for ($i = 0; $i < 10; $i++):
            $angle = $i * 36; ?>
          <div class="gear-tooth" style="transform: translateZ(0) rotate(<?= $angle ?>deg);"></div>
        <?php
        endfor; ?>
      </div>
    </div>

    <div class="spec-strip" style="grid-column:1 / -1;">
      <div class="spec-item"><div class="spec-num">±0.01<span class="unit">mm</span></div><div class="spec-label">ความเที่ยงตรงสูงสุด</div></div>
      <div class="spec-item"><div class="spec-num"><?= count(
          $equipment,
      ) ?><span class="unit">รุ่น</span></div><div class="spec-label">เครื่องจักรในไลน์ผลิต</div></div>
      <div class="spec-item"><div class="spec-num"><?= count(
          $shifts,
      ) ?><span class="unit">กะ</span></div><div class="spec-label">ทำงานต่อเนื่อง 24 ชม.</div></div>
      <div class="spec-item"><div class="spec-num">0<span class="unit">อุบัติเหตุ</span></div><div class="spec-label">สะสม 180 วัน</div></div>
    </div>
  </div>
</section>

<section class="sec" id="equipment">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <div class="sec-tag">Equipment List</div>
        <h2 class="sec-title">เครื่องจักรในแผนก</h2>
      </div>
      <div class="sec-note">เลื่อนเมาส์บนการ์ดเพื่อดูมุมมอง 3D</div>
    </div>

    <div class="eq-grid" id="eqGrid">
      <?php foreach ($equipment as $m): ?>
      <div class="eq-card">
        <div class="eq-name"><?= htmlspecialchars($m["name"]) ?></div>
        <div class="eq-qty"><?= (int) $m["qty"] ?> เครื่อง</div>
        <div class="eq-desc"><?= htmlspecialchars($m["desc"]) ?></div>
        <div class="eq-tol"><?= htmlspecialchars($m["tol"]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec" id="process">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <div class="sec-tag">Production Flow</div>
        <h2 class="sec-title">ขั้นตอนการทำงาน</h2>
      </div>
      <div class="sec-note">ลำดับงานตั้งแต่รับใบสั่งผลิตจนถึงส่งมอบชิ้นงาน</div>
    </div>

    <div class="flow">
      <?php foreach ($process as $i => $p):
          $idx = str_pad($i + 1, 2, "0", STR_PAD_LEFT); ?>
      <div class="flow-step">
        <div class="flow-idx"><?=
        $idx;
        if($i < count($process) - 1): ?><span class="line"></span><?php endif;
        ?></div>
        <div>
          <div class="flow-title"><?= htmlspecialchars($p[0]) ?></div>
          <div class="flow-desc"><?= htmlspecialchars($p[1]) ?></div>
        </div>
      </div>
      <?php
      endforeach; ?>
    </div>
  </div>
</section>

<div class="safety-band" role="presentation"></div>
<section class="sec" id="safety" style="padding-top:64px;">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <div class="sec-tag">Safety Protocol</div>
        <h2 class="sec-title">ความปลอดภัยในพื้นที่ปฏิบัติงาน</h2>
      </div>
      <div class="sec-note">ข้อกำหนดพื้นฐานที่ต้องปฏิบัติก่อนเข้าพื้นที่เครื่องจักร</div>
    </div>

    <div class="safety-grid">
      <div class="safety-card"><div class="safety-icon">PPE — 01</div><h3>แว่นตานิรภัย</h3><p>สวมตลอดเวลาที่อยู่ในรัศมีเครื่องจักรทำงาน ป้องกันเศษโลหะกระเด็น</p></div>
      <div class="safety-card"><div class="safety-icon">PPE — 02</div><h3>รองเท้าเซฟตี้</h3><p>หัวเหล็กป้องกันของหนักตกใส่ พื้นกันลื่นสำหรับพื้นที่มีน้ำมันตัด</p></div>
      <div class="safety-card"><div class="safety-icon">PPE — 03</div><h3>ถุงมือกันบาด</h3><p>ใช้เฉพาะขั้นตอนจับชิ้นงาน ถอดออกก่อนควบคุมเครื่องจักรหมุน</p></div>
      <div class="safety-card"><div class="safety-icon">PPE — 04</div><h3>ที่ครอบหูลดเสียง</h3><p>บังคับใช้ในโซนเครื่องกัดและเจียรที่มีระดับเสียงเกินมาตรฐาน</p></div>
    </div>
  </div>
</section>



<section class="sec" id="shifts">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <div class="sec-tag">Working Shifts</div>
        <h2 class="sec-title">กะการทำงาน</h2>
      </div>
      <div class="sec-note">เวลา server ปัจจุบัน: <?= date(
          "H:i",
      ) ?> น. — ไฮไลต์กะที่ทำงานอยู่อัตโนมัติ</div>
    </div>

    <div class="shift-row">
      <?php foreach ($shifts as $s):
          $active = is_active_shift($currentHour, $s); ?>
      <div class="shift-card<?= $active ? " active" : "" ?>">
        <div class="shift-time"><?= str_pad(
            $s["start"],
            2,
            "0",
            STR_PAD_LEFT,
        ) ?><span class="to">:00–</span><?= str_pad(
    $s["end"] === 24 ? 0 : $s["end"],
    2,
    "0",
    STR_PAD_LEFT,
) ?><span class="to"></span>00</div>
        <div class="shift-name"><?= htmlspecialchars($s["name"]) ?></div>
        <div class="shift-desc"><?= htmlspecialchars($s["desc"]) ?></div>
      </div>
      <?php
      endforeach; ?>
    </div>
  </div>
</section>

<footer id="contact">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <div class="foot-title">พร้อมรับงานเข้าคิวผลิต</div>
        <p style="color:var(--paper-dim); font-size:14px; max-width:380px;">ส่งแบบงานพร้อมสเปกความเที่ยงตรงมาที่แผนก เจ้าหน้าที่จะประเมินระยะเวลาและคิวเครื่องจักรให้ภายใน 1 วันทำการ</p>
      </div>
      <div class="foot-col">
        <h4>ติดต่อแผนก</h4>
        <a href="mailto:machineshop@company.internal">machineshop@company.internal</a>
        <a href="tel:+660000000">ต่อ 2210 — หัวหน้าแผนก</a>
      </div>
      <div class="foot-col">
        <h4>ลิงก์ด่วน</h4>
        <a href="#equipment">รายการเครื่องจักร</a>
        <a href="#process">ขั้นตอนการทำงาน</a>
        <a href="#safety">ข้อกำหนดความปลอดภัย</a>
      </div>
    </div>
    <div class="foot-bottom">
      <span>MACHINE SHOP DEPT. — INTERNAL DOCUMENT</span>
      <span>© <?= $year ?> SPEC PLATE REV.02</span>
    </div>
  </div>
</footer>

<script>
// 3D tilt effect on equipment cards
document.querySelectorAll('.eq-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    const x = e.clientX - r.left, y = e.clientY - r.top;
    const rx = ((y / r.height) - 0.5) * -12;
    const ry = ((x / r.width) - 0.5) * 12;
    card.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) scale(1.02)`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = 'rotateX(0) rotateY(0) scale(1)';
  });
});
</script>
</body>
</html>
