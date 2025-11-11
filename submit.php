<?php
date_default_timezone_set('Europe/Istanbul');

// Coefficients (values from paper)
$coeff = [
  'tahil' => 82,
  'et' => 349,
  'sut' => 64,
  'yumurta' => 15,
  'sebze' => 18,
  'meyve' => 32,
  'nisasta' => 17,
  'cay' => 13,
  'kahve' => 56,
  'dus_say' => 5,
  'dus_dk' => 5,
  'banyo' => 9,
  'dis_firca' => 5,
  'musluk_kapat' => null,
  'camasir' => 11,
  'bulasik_el' => 10,
  'bulasik_dk' => 5,
  'bulasik_mak' => 6,
  'araba' => 8
];

// Sanitize
function val($k) {
  return isset($_POST[$k]) ? trim($_POST[$k]) : 0;
}

$name = htmlspecialchars(val('name'));

// Numeric values (default 0)
$tahil = floatval(val('tahil'));
$et = floatval(val('et'));
$sut = floatval(val('sut'));
$yumurta = floatval(val('yumurta'));
$yag_tipi = floatval(val('yag_tipi'));
$seker = floatval(val('seker'));
$sebze = floatval(val('sebze'));
$meyve = floatval(val('meyve'));
$nisasta = floatval(val('nisasta'));
$cay = floatval(val('cay'));
$kahve = floatval(val('kahve'));

$dus_say = floatval(val('dus_say'));
$dus_dk = floatval(val('dus_dk'));
$banyo = floatval(val('banyo'));
$dis_firca = floatval(val('dis_firca'));
$musluk_kapat = floatval(val('musluk_kapat'));
$camasir = floatval(val('camasir'));
$bulasik_el = floatval(val('bulasik_el'));
$bulasik_dk = floatval(val('bulasik_dk'));
$bulasik_mak = floatval(val('bulasik_mak'));
$araba = floatval(val('araba'));

// Calculate category totals
$food_total = 0;
$food_total += $tahil * $coeff['tahil'];
$food_total += $et * $coeff['et'];
$food_total += $sut * $coeff['sut'];
$food_total += $yumurta * $coeff['yumurta'];
$food_total += $yag_tipi;
$food_total += $seker;
$food_total += $sebze * $coeff['sebze'];
$food_total += $meyve * $coeff['meyve'];
$food_total += $nisasta * $coeff['nisasta'];
$food_total += $cay * $coeff['cay'];
$food_total += $kahve * $coeff['kahve'];

$household_total = 0;
$household_total += $dus_say * $coeff['dus_say'];
$household_total += $dus_dk * $coeff['dus_dk'];
$household_total += $banyo * $coeff['banyo'];
$household_total += $dis_firca * $coeff['dis_firca'];
$household_total += $musluk_kapat;
$household_total += $camasir * $coeff['camasir'];
$household_total += $bulasik_el * $coeff['bulasik_el'];
$household_total += $bulasik_dk * $coeff['bulasik_dk'];
$household_total += $bulasik_mak * $coeff['bulasik_mak'];
$household_total += $araba * $coeff['araba'];

$total_l = $food_total + $household_total;

// Calculate daily, monthly, yearly
$daily_l = $total_l;
$daily_m3 = $daily_l / 1000.0;

$monthly_l = $daily_l * 30;
$monthly_m3 = $monthly_l / 1000.0;

$yearly_l = $daily_l * 365;
$yearly_m3 = $yearly_l / 1000.0;

// Calculate percentages for pie chart
$food_percent = $total_l > 0 ? ($food_total / $total_l) * 100 : 0;
$household_percent = $total_l > 0 ? ($household_total / $total_l) * 100 : 0;

// World average comparison (approximate daily water footprint)
$world_avg = 3800; // liters per day (approximate)
$comparison_percent = ($daily_l / $world_avg) * 100;

// Save to SQLite
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create table if not exists
$db->exec("CREATE TABLE IF NOT EXISTS results (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  data TEXT,
  total_l REAL,
  total_m3 REAL,
  created_at TEXT
)");

// Store raw answers as JSON for admin
$data = [
  'tahil'=>$tahil, 'et'=>$et, 'sut'=>$sut, 'yumurta'=>$yumurta,
  'yag_tipi'=>$yag_tipi, 'seker'=>$seker,
  'sebze'=>$sebze, 'meyve'=>$meyve, 'nisasta'=>$nisasta,
  'cay'=>$cay, 'kahve'=>$kahve,
  'dus_say'=>$dus_say, 'dus_dk'=>$dus_dk, 'banyo'=>$banyo,
  'dis_firca'=>$dis_firca, 'musluk_kapat'=>$musluk_kapat,
  'camasir'=>$camasir, 'bulasik_el'=>$bulasik_el,
  'bulasik_dk'=>$bulasik_dk, 'bulasik_mak'=>$bulasik_mak,
  'araba'=>$araba
];

$stmt = $db->prepare("INSERT INTO results (name, data, total_l, total_m3, created_at) VALUES (:name, :data, :tl, :tm3, :ca)");
$stmt->execute([
  ':name'=>$name,
  ':data'=>json_encode($data),
  ':tl'=>$daily_l,
  ':tm3'=>$daily_m3,
  ':ca'=>date('Y-m-d H:i:s')
]);

// ID of inserted record
$lastId = $db->lastInsertId();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EML - Your Water Footprint Result</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    /* Additional styles for result page */
    .result-page {
      animation: fadeIn 0.6s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .result-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .result-header h1 {
      font-size: 2rem;
      color: #ff4444;
      margin-bottom: 10px;
    }

    .result-header .user-name {
      font-size: 1.2rem;
      color: #ddd;
      font-weight: 300;
    }

    /* Main gauge */
    .gauge-container {
      text-align: center;
      margin: 40px 0;
    }

    .circular-gauge {
      position: relative;
      width: 200px;
      height: 200px;
      margin: 0 auto;
    }

    .circular-gauge svg {
      transform: rotate(-90deg);
    }

    .gauge-bg {
      fill: none;
      stroke: rgba(255, 68, 68, 0.1);
      stroke-width: 20;
    }

    .gauge-fill {
      fill: none;
      stroke: #ff4444;
      stroke-width: 20;
      stroke-linecap: round;
      transition: stroke-dashoffset 2s ease-in-out;
      filter: drop-shadow(0 0 10px rgba(255, 68, 68, 0.5));
    }

    .gauge-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }

    .gauge-value {
      font-size: 2rem;
      font-weight: 700;
      color: #ff4444;
      line-height: 1;
    }

    .gauge-label {
      font-size: 0.9rem;
      color: #aaa;
      margin-top: 5px;
    }

    /* Stats cards */
    .stats-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      margin: 30px 0;
    }

    .stat-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      border-color: #ff4444;
    }

    .stat-label {
      font-size: 0.9rem;
      color: #aaa;
      margin-bottom: 10px;
    }

    .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
      color: #ff4444;
      margin-bottom: 5px;
    }

    .stat-subvalue {
      font-size: 0.95rem;
      color: #ddd;
    }

    /* Bar chart */
    .chart-section {
      margin: 40px 0;
    }

    .chart-title {
      font-size: 1.3rem;
      color: #ff4444;
      margin-bottom: 20px;
      text-align: center;
    }

    .bar-chart {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 12px;
      padding: 25px;
    }

    .bar-item {
      margin-bottom: 25px;
    }

    .bar-label {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .bar-name {
      color: #fff;
      font-weight: 500;
    }

    .bar-value {
      color: #ff4444;
      font-weight: 600;
    }

    .bar-track {
      height: 12px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 6px;
      overflow: hidden;
    }

    .bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #ff4444 0%, #cc0000 100%);
      border-radius: 6px;
      transition: width 1.5s ease-in-out;
      width: 0;
      box-shadow: 0 0 10px rgba(255, 68, 68, 0.5);
    }

    /* Comparison section */
    .comparison-section {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 12px;
      padding: 25px;
      margin: 30px 0;
      text-align: center;
    }

    .comparison-title {
      font-size: 1.2rem;
      color: #ff4444;
      margin-bottom: 20px;
    }

    .comparison-bars {
      display: flex;
      align-items: flex-end;
      justify-content: center;
      gap: 30px;
      margin: 30px 0;
      height: 150px;
    }

    .comparison-bar {
      flex: 1;
      max-width: 100px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .comparison-bar-fill {
      width: 100%;
      background: linear-gradient(180deg, #ff4444 0%, #cc0000 100%);
      border-radius: 8px 8px 0 0;
      transition: height 1.5s ease-in-out;
      height: 0;
      min-height: 20px;
      box-shadow: 0 -5px 15px rgba(255, 68, 68, 0.4);
    }

    .comparison-bar-label {
      margin-top: 10px;
      font-size: 0.9rem;
      color: #ddd;
      text-align: center;
    }

    .comparison-bar-value {
      font-weight: 700;
      color: #ff4444;
      display: block;
      margin-top: 5px;
    }

    .comparison-text {
      font-size: 1rem;
      color: #ddd;
      line-height: 1.6;
    }

    .comparison-highlight {
      color: #ff4444;
      font-weight: 600;
    }

    /* Pie chart */
    .pie-chart-section {
      margin: 40px 0;
    }

    .pie-chart-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 40px;
      flex-wrap: wrap;
    }

    .pie-chart {
      position: relative;
      width: 200px;
      height: 200px;
    }

    .pie-legend {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .legend-color {
      width: 20px;
      height: 20px;
      border-radius: 4px;
    }

    .legend-label {
      font-size: 0.95rem;
      color: #ddd;
    }

    .legend-value {
      color: #ff4444;
      font-weight: 600;
      margin-left: auto;
    }

    /* Info box */
    .info-box {
      background: rgba(255, 68, 68, 0.1);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 8px;
      padding: 20px;
      margin: 30px 0;
    }

    .info-box-title {
      font-size: 1.1rem;
      color: #ff4444;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .info-box-text {
      color: #ddd;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    /* Responsive */
    @media (min-width: 768px) {
      .stats-grid {
        grid-template-columns: repeat(3, 1fr);
      }

      .circular-gauge {
        width: 250px;
        height: 250px;
      }

      .gauge-value {
        font-size: 2.5rem;
      }

      .result-header h1 {
        font-size: 2.5rem;
      }
    }

    @media (max-width: 480px) {
      .circular-gauge {
        width: 180px;
        height: 180px;
      }

      .gauge-value {
        font-size: 1.5rem;
      }

      .stat-value {
        font-size: 1.5rem;
      }

      .comparison-bars {
        gap: 15px;
        height: 120px;
      }
    }
  </style>
</head>
<body>
  <main class="container result-page">
    <!-- Header -->
    <div class="result-header">
      <h1>🌊 Your Water Footprint</h1>
      <?php if($name): ?>
        <p class="user-name">Hello, <?php echo $name; ?>!</p>
      <?php endif; ?>
    </div>

    <!-- Main Gauge -->
    <div class="gauge-container">
      <div class="circular-gauge">
        <svg width="100%" height="100%" viewBox="0 0 200 200">
          <circle class="gauge-bg" cx="100" cy="100" r="80"/>
          <circle class="gauge-fill" cx="100" cy="100" r="80"
                  stroke-dasharray="<?php echo 2 * pi() * 80; ?>"
                  stroke-dashoffset="<?php echo 2 * pi() * 80; ?>"
                  data-value="<?php echo min(100, ($daily_l/5000)*100); ?>"/>
        </svg>
        <div class="gauge-text">
          <div class="gauge-value" data-target="<?php echo round($daily_l); ?>">0</div>
          <div class="gauge-label">liters/day</div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Daily</div>
        <div class="stat-value"><?php echo number_format($daily_l, 0, ',', '.'); ?></div>
        <div class="stat-subvalue"><?php echo number_format($daily_m3, 2, ',', '.'); ?> m³</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Monthly</div>
        <div class="stat-value"><?php echo number_format($monthly_l, 0, ',', '.'); ?></div>
        <div class="stat-subvalue"><?php echo number_format($monthly_m3, 2, ',', '.'); ?> m³</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Yearly</div>
        <div class="stat-value"><?php echo number_format($yearly_l, 0, ',', '.'); ?></div>
        <div class="stat-subvalue"><?php echo number_format($yearly_m3, 2, ',', '.'); ?> m³</div>
      </div>
    </div>

    <!-- Category Breakdown -->
    <div class="chart-section">
      <h2 class="chart-title">Breakdown by Category</h2>
      <div class="bar-chart">
        <div class="bar-item">
          <div class="bar-label">
            <span class="bar-name">🍽️ Food & Beverages</span>
            <span class="bar-value"><?php echo number_format($food_total, 0, ',', '.'); ?> L (<?php echo number_format($food_percent, 1); ?>%)</span>
          </div>
          <div class="bar-track">
            <div class="bar-fill" data-width="<?php echo $food_percent; ?>"></div>
          </div>
        </div>
        <div class="bar-item">
          <div class="bar-label">
            <span class="bar-name">🏠 Household Use</span>
            <span class="bar-value"><?php echo number_format($household_total, 0, ',', '.'); ?> L (<?php echo number_format($household_percent, 1); ?>%)</span>
          </div>
          <div class="bar-track">
            <div class="bar-fill" data-width="<?php echo $household_percent; ?>"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pie Chart -->
    <div class="pie-chart-section">
      <h2 class="chart-title">Distribution</h2>
      <div class="bar-chart">
        <div class="pie-chart-container">
          <div class="pie-chart">
            <svg width="100%" height="100%" viewBox="0 0 200 200">
              <circle cx="100" cy="100" r="80" fill="none" stroke="#ff4444" stroke-width="40"
                      stroke-dasharray="<?php echo ($food_percent/100) * 502.65; ?> 502.65"
                      transform="rotate(-90 100 100)"
                      style="transition: stroke-dasharray 1.5s ease-in-out;"/>
              <circle cx="100" cy="100" r="80" fill="none" stroke="#cc0000" stroke-width="40"
                      stroke-dasharray="<?php echo ($household_percent/100) * 502.65; ?> 502.65"
                      stroke-dashoffset="<?php echo -($food_percent/100) * 502.65; ?>"
                      transform="rotate(-90 100 100)"
                      style="transition: stroke-dasharray 1.5s ease-in-out, stroke-dashoffset 1.5s ease-in-out;"/>
            </svg>
          </div>
          <div class="pie-legend">
            <div class="legend-item">
              <div class="legend-color" style="background: #ff4444;"></div>
              <span class="legend-label">Food & Beverages</span>
              <span class="legend-value"><?php echo number_format($food_percent, 1); ?>%</span>
            </div>
            <div class="legend-item">
              <div class="legend-color" style="background: #cc0000;"></div>
              <span class="legend-label">Household Use</span>
              <span class="legend-value"><?php echo number_format($household_percent, 1); ?>%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparison with World Average -->
    <div class="comparison-section">
      <h3 class="comparison-title">Compare with World Average</h3>
      <div class="comparison-bars">
        <div class="comparison-bar">
          <div class="comparison-bar-fill" data-height="<?php echo min(100, ($daily_l/$world_avg)*100); ?>" style="background: linear-gradient(180deg, #ff4444 0%, #cc0000 100%);"></div>
          <div class="comparison-bar-label">
            You
            <span class="comparison-bar-value"><?php echo number_format($daily_l, 0, ',', '.'); ?> L</span>
          </div>
        </div>
        <div class="comparison-bar">
          <div class="comparison-bar-fill" data-height="100" style="background: linear-gradient(180deg, #666 0%, #333 100%);"></div>
          <div class="comparison-bar-label">
            World Avg
            <span class="comparison-bar-value"><?php echo number_format($world_avg, 0, ',', '.'); ?> L</span>
          </div>
        </div>
      </div>
      <p class="comparison-text">
        <?php if($comparison_percent < 90): ?>
          Great job! Your water footprint is <span class="comparison-highlight"><?php echo number_format(100 - $comparison_percent, 1); ?>% lower</span> than the world average.
        <?php elseif($comparison_percent > 110): ?>
          Your water footprint is <span class="comparison-highlight"><?php echo number_format($comparison_percent - 100, 1); ?>% higher</span> than the world average. Consider ways to reduce consumption.
        <?php else: ?>
          Your water footprint is close to the world average.
        <?php endif; ?>
      </p>
    </div>

    <!-- Tips -->
    <div class="info-box">
      <div class="info-box-title">💡 Tips to Reduce Your Water Footprint</div>
      <div class="info-box-text">
        • Reduce meat consumption - beef requires 15,000+ liters per kg<br>
        • Take shorter showers and turn off taps when not needed<br>
        • Fix leaky faucets - a drip can waste 15+ liters per day<br>
        • Choose seasonal and local produce<br>
        • Run dishwashers and washing machines only when full
      </div>
    </div>

    <!-- Record Info -->
    <div style="text-align: center; margin: 30px 0; color: #888; font-size: 0.85rem;">
      Record ID: #<?php echo $lastId; ?> • <?php echo date('d.m.Y H:i'); ?>
    </div>

    <!-- Actions -->
    <div class="actions">
      <a class="btn btn-primary" href="index.php">Calculate Again</a>
    </div>
  </main>

  <script>
    // Animate circular gauge
    window.addEventListener('load', function() {
      const gaugeFill = document.querySelector('.gauge-fill');
      const gaugeValue = document.querySelector('.gauge-value');
      const targetValue = parseInt(gaugeValue.dataset.target);
      const circumference = 2 * Math.PI * 80;
      const fillPercent = parseFloat(gaugeFill.dataset.value);
      
      setTimeout(() => {
        gaugeFill.style.strokeDashoffset = circumference - (circumference * fillPercent / 100);
      }, 100);

      // Animate number
      let current = 0;
      const increment = targetValue / 50;
      const timer = setInterval(() => {
        current += increment;
        if (current >= targetValue) {
          current = targetValue;
          clearInterval(timer);
        }
        gaugeValue.textContent = Math.round(current).toLocaleString('tr-TR');
      }, 30);

      // Animate bar charts
      setTimeout(() => {
        document.querySelectorAll('.bar-fill').forEach(bar => {
          bar.style.width = bar.dataset.width + '%';
        });
      }, 500);

      // Animate comparison bars
      setTimeout(() => {
        document.querySelectorAll('.comparison-bar-fill').forEach(bar => {
          bar.style.height = bar.dataset.height + '%';
        });
      }, 800);
    });
  </script>
</body>
</html>