<?php
// admin.php - Enhanced Admin System with Dashboard and Statistics
session_start();
date_default_timezone_set('Europe/Istanbul');

$password = 'gg2233';

// Handle login
if (isset($_POST['login'])) {
  if ($_POST['password'] === $password) {
    $_SESSION['admin_logged_in'] = true;
  } else {
    $error = 'Invalid password';
  }
}

// Handle logout
if (isset($_GET['logout'])) {
  unset($_SESSION['admin_logged_in']);
  header('Location: admin.php');
  exit;
}

// Check if logged in for protected actions
$isLoggedIn = isset($_SESSION['admin_logged_in']);

// Handle delete
if (isset($_GET['delete']) && $isLoggedIn) {
  $db = new PDO('sqlite:database.db');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $stmt = $db->prepare("DELETE FROM results WHERE id = :id");
  $stmt->execute([':id' => intval($_GET['delete'])]);
  header('Location: admin.php');
  exit;
}

// Handle statistics export
if (isset($_GET['export_stats']) && $isLoggedIn) {
  $db = new PDO('sqlite:database.db');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $db->query("SELECT * FROM results ORDER BY created_at DESC");
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Calculate statistics
  $total_records = count($results);
  $total_water = 0;
  $food_total = 0;
  $household_total = 0;
  
  $all_data = [];
  foreach ($results as $row) {
    $data = json_decode($row['data'], true);
    $all_data[] = $data;
    $total_water += $row['total_l'];
    
    // Calculate food and household
    $food = ($data['tahil'] * 82) + ($data['et'] * 349) + ($data['sut'] * 64) + 
            ($data['yumurta'] * 15) + $data['yag_tipi'] + $data['seker'] + 
            ($data['sebze'] * 18) + ($data['meyve'] * 32) + ($data['nisasta'] * 17) + 
            ($data['cay'] * 13) + ($data['kahve'] * 56);
    
    $household = ($data['dus_say'] * 5) + ($data['dus_dk'] * 5) + ($data['banyo'] * 9) + 
                 ($data['dis_firca'] * 5) + $data['musluk_kapat'] + ($data['camasir'] * 11) + 
                 ($data['bulasik_el'] * 10) + ($data['bulasik_dk'] * 5) + 
                 ($data['bulasik_mak'] * 6) + ($data['araba'] * 8);
    
    $food_total += $food;
    $household_total += $household;
  }
  
  $avg_water = $total_records > 0 ? $total_water / $total_records : 0;
  $avg_food = $total_records > 0 ? $food_total / $total_records : 0;
  $avg_household = $total_records > 0 ? $household_total / $total_records : 0;
  
  // Calculate field averages
  $field_averages = [];
  $fields = ['tahil', 'et', 'sut', 'yumurta', 'sebze', 'meyve', 'nisasta', 'cay', 'kahve',
             'dus_say', 'dus_dk', 'banyo', 'dis_firca', 'camasir', 'bulasik_el', 
             'bulasik_dk', 'bulasik_mak', 'araba'];
  
  foreach ($fields as $field) {
    $sum = 0;
    foreach ($all_data as $data) {
      $sum += $data[$field];
    }
    $field_averages[$field] = $total_records > 0 ? $sum / $total_records : 0;
  }
  
  $filename = 'water_footprint_statistics_' . date('Y-m-d') . '.doc';
  header("Content-Type: application/msword");
  header("Content-Disposition: attachment; filename=\"$filename\"");
  header("Pragma: no-cache");
  header("Expires: 0");
  
  ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; font-size: 10pt; margin: 20px; }
    h1 { text-align: center; font-size: 16pt; font-weight: bold; margin: 10px 0; color: #cc0000; }
    h2 { font-size: 13pt; font-weight: bold; margin: 20px 0 10px 0; background-color: #cc0000; color: white; padding: 8px; }
    h3 { font-size: 11pt; font-weight: bold; margin: 15px 0 8px 0; background-color: #d9d9d9; padding: 5px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 9pt; }
    th { background-color: #d9d9d9; font-weight: bold; text-align: center; }
    .center { text-align: center; }
    .right { text-align: right; }
    .highlight { background-color: #ffff99; font-weight: bold; }
    .summary-box { background-color: #f0f0f0; border: 2px solid #cc0000; padding: 15px; margin: 15px 0; }
    .stat-grid { display: table; width: 100%; margin: 10px 0; }
    .stat-item { display: table-cell; padding: 10px; text-align: center; border: 1px solid #ccc; }
    .stat-value { font-size: 14pt; font-weight: bold; color: #cc0000; }
    .stat-label { font-size: 9pt; color: #666; margin-top: 5px; }
    .footer { font-size: 8pt; margin-top: 30px; text-align: center; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
  </style>
</head>
<body>
  <h1>🌊 EML WATER FOOTPRINT - COMPREHENSIVE STATISTICS REPORT</h1>
  
  <div class="summary-box">
    <table style="border: none;">
      <tr>
        <td style="border: none; width: 25%;"><strong>Report Date:</strong></td>
        <td style="border: none;"><?php echo date('d.m.Y H:i:s'); ?></td>
        <td style="border: none; width: 25%;"><strong>Total Records:</strong></td>
        <td style="border: none; color: #cc0000; font-weight: bold;"><?php echo $total_records; ?></td>
      </tr>
      <tr>
        <td style="border: none;"><strong>Date Range:</strong></td>
        <td style="border: none;">
          <?php 
          if ($total_records > 0) {
            echo date('d.m.Y', strtotime($results[count($results)-1]['created_at'])) . ' - ' . 
                 date('d.m.Y', strtotime($results[0]['created_at']));
          } else {
            echo 'N/A';
          }
          ?>
        </td>
        <td style="border: none;"><strong>Generated By:</strong></td>
        <td style="border: none;">EML Admin System</td>
      </tr>
    </table>
  </div>
  
  <h2>📊 OVERALL STATISTICS</h2>
  
  <div class="stat-grid">
    <div class="stat-item">
      <div class="stat-value"><?php echo number_format($avg_water, 0); ?> L</div>
      <div class="stat-label">Average Daily<br>Water Footprint</div>
    </div>
    <div class="stat-item">
      <div class="stat-value"><?php echo number_format($avg_water * 30, 0); ?> L</div>
      <div class="stat-label">Average Monthly<br>Water Footprint</div>
    </div>
    <div class="stat-item">
      <div class="stat-value"><?php echo number_format($avg_water * 365, 0); ?> L</div>
      <div class="stat-label">Average Yearly<br>Water Footprint</div>
    </div>
    <div class="stat-item">
      <div class="stat-value"><?php echo number_format($total_water, 0); ?> L</div>
      <div class="stat-label">Total Water<br>Calculated</div>
    </div>
  </div>
  
  <h3>Category Distribution (Average)</h3>
  <table>
    <tr>
      <th style="width: 40%;">Category</th>
      <th style="width: 20%;">Average (L/day)</th>
      <th style="width: 20%;">Percentage</th>
      <th style="width: 20%;">Total (All Users)</th>
    </tr>
    <tr>
      <td>🍽️ Food & Beverages</td>
      <td class="right"><?php echo number_format($avg_food, 2); ?></td>
      <td class="center"><?php echo number_format(($avg_food / $avg_water) * 100, 1); ?>%</td>
      <td class="right"><?php echo number_format($food_total, 0); ?></td>
    </tr>
    <tr>
      <td>🏠 Household Use</td>
      <td class="right"><?php echo number_format($avg_household, 2); ?></td>
      <td class="center"><?php echo number_format(($avg_household / $avg_water) * 100, 1); ?>%</td>
      <td class="right"><?php echo number_format($household_total, 0); ?></td>
    </tr>
    <tr class="highlight">
      <td><strong>TOTAL</strong></td>
      <td class="right"><strong><?php echo number_format($avg_water, 2); ?></strong></td>
      <td class="center"><strong>100%</strong></td>
      <td class="right"><strong><?php echo number_format($total_water, 0); ?></strong></td>
    </tr>
  </table>
  
  <h2>🍽️ FOOD CONSUMPTION AVERAGES</h2>
  <table>
    <tr>
      <th style="width: 50%;">Item</th>
      <th style="width: 20%;">Average Consumption</th>
      <th style="width: 15%;">Coefficient</th>
      <th style="width: 15%;">Water Impact (L)</th>
    </tr>
    <tr>
      <td>Grains (kg/week)</td>
      <td class="center"><?php echo number_format($field_averages['tahil'], 2); ?></td>
      <td class="center">82</td>
      <td class="right"><?php echo number_format($field_averages['tahil'] * 82, 2); ?></td>
    </tr>
    <tr>
      <td>Meat (kg/week)</td>
      <td class="center"><?php echo number_format($field_averages['et'], 2); ?></td>
      <td class="center">349</td>
      <td class="right"><?php echo number_format($field_averages['et'] * 349, 2); ?></td>
    </tr>
    <tr>
      <td>Dairy products (kg/week)</td>
      <td class="center"><?php echo number_format($field_averages['sut'], 2); ?></td>
      <td class="center">64</td>
      <td class="right"><?php echo number_format($field_averages['sut'] * 64, 2); ?></td>
    </tr>
    <tr>
      <td>Eggs (count/week)</td>
      <td class="center"><?php echo number_format($field_averages['yumurta'], 2); ?></td>
      <td class="center">15</td>
      <td class="right"><?php echo number_format($field_averages['yumurta'] * 15, 2); ?></td>
    </tr>
    <tr>
      <td>Vegetables (kg/week)</td>
      <td class="center"><?php echo number_format($field_averages['sebze'], 2); ?></td>
      <td class="center">18</td>
      <td class="right"><?php echo number_format($field_averages['sebze'] * 18, 2); ?></td>
    </tr>
    <tr>
      <td>Fruits (kg/week)</td>
      <td class="center"><?php echo number_format($field_averages['meyve'], 2); ?></td>
      <td class="center">32</td>
      <td class="right"><?php echo number_format($field_averages['meyve'] * 32, 2); ?></td>
    </tr>
    <tr>
      <td>Starchy plants (kg/week)</td>
      <td class="center"><?php echo number_format($field_averages['nisasta'], 2); ?></td>
      <td class="center">17</td>
      <td class="right"><?php echo number_format($field_averages['nisasta'] * 17, 2); ?></td>
    </tr>
    <tr>
      <td>Tea (cups/day)</td>
      <td class="center"><?php echo number_format($field_averages['cay'], 2); ?></td>
      <td class="center">13</td>
      <td class="right"><?php echo number_format($field_averages['cay'] * 13, 2); ?></td>
    </tr>
    <tr>
      <td>Coffee (cups/day)</td>
      <td class="center"><?php echo number_format($field_averages['kahve'], 2); ?></td>
      <td class="center">56</td>
      <td class="right"><?php echo number_format($field_averages['kahve'] * 56, 2); ?></td>
    </tr>
  </table>
  
  <h2>🏠 HOUSEHOLD USAGE AVERAGES</h2>
  <table>
    <tr>
      <th style="width: 50%;">Activity</th>
      <th style="width: 20%;">Average Frequency</th>
      <th style="width: 15%;">Coefficient</th>
      <th style="width: 15%;">Water Impact (L)</th>
    </tr>
    <tr>
      <td>Showers (times/week)</td>
      <td class="center"><?php echo number_format($field_averages['dus_say'], 2); ?></td>
      <td class="center">5</td>
      <td class="right"><?php echo number_format($field_averages['dus_say'] * 5, 2); ?></td>
    </tr>
    <tr>
      <td>Shower duration (minutes)</td>
      <td class="center"><?php echo number_format($field_averages['dus_dk'], 2); ?></td>
      <td class="center">5</td>
      <td class="right"><?php echo number_format($field_averages['dus_dk'] * 5, 2); ?></td>
    </tr>
    <tr>
      <td>Baths (times/week)</td>
      <td class="center"><?php echo number_format($field_averages['banyo'], 2); ?></td>
      <td class="center">9</td>
      <td class="right"><?php echo number_format($field_averages['banyo'] * 9, 2); ?></td>
    </tr>
    <tr>
      <td>Teeth brushing (times/day)</td>
      <td class="center"><?php echo number_format($field_averages['dis_firca'], 2); ?></td>
      <td class="center">5</td>
      <td class="right"><?php echo number_format($field_averages['dis_firca'] * 5, 2); ?></td>
    </tr>
    <tr>
      <td>Laundry (times/week)</td>
      <td class="center"><?php echo number_format($field_averages['camasir'], 2); ?></td>
      <td class="center">11</td>
      <td class="right"><?php echo number_format($field_averages['camasir'] * 11, 2); ?></td>
    </tr>
    <tr>
      <td>Hand dishwashing (times/day)</td>
      <td class="center"><?php echo number_format($field_averages['bulasik_el'], 2); ?></td>
      <td class="center">10</td>
      <td class="right"><?php echo number_format($field_averages['bulasik_el'] * 10, 2); ?></td>
    </tr>
    <tr>
      <td>Dishwashing duration (minutes)</td>
      <td class="center"><?php echo number_format($field_averages['bulasik_dk'], 2); ?></td>
      <td class="center">5</td>
      <td class="right"><?php echo number_format($field_averages['bulasik_dk'] * 5, 2); ?></td>
    </tr>
    <tr>
      <td>Dishwasher usage (times/day)</td>
      <td class="center"><?php echo number_format($field_averages['bulasik_mak'], 2); ?></td>
      <td class="center">6</td>
      <td class="right"><?php echo number_format($field_averages['bulasik_mak'] * 6, 2); ?></td>
    </tr>
    <tr>
      <td>Car wash (times/week)</td>
      <td class="center"><?php echo number_format($field_averages['araba'], 2); ?></td>
      <td class="center">8</td>
      <td class="right"><?php echo number_format($field_averages['araba'] * 8, 2); ?></td>
    </tr>
  </table>
  
  <h2>📋 INDIVIDUAL RECORDS SUMMARY</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Daily (L)</th>
      <th>Monthly (L)</th>
      <th>Yearly (L)</th>
      <th>vs Average</th>
      <th>Submitted</th>
    </tr>
    <?php foreach ($results as $row): ?>
    <tr>
      <td class="center"><?php echo $row['id']; ?></td>
      <td><?php echo htmlspecialchars($row['name'] ?: 'Anonymous'); ?></td>
      <td class="right"><?php echo number_format($row['total_l'], 0); ?></td>
      <td class="right"><?php echo number_format($row['total_l'] * 30, 0); ?></td>
      <td class="right"><?php echo number_format($row['total_l'] * 365, 0); ?></td>
      <td class="center" style="<?php echo $row['total_l'] > $avg_water ? 'color: red;' : 'color: green;'; ?>">
        <?php echo $row['total_l'] > $avg_water ? '+' : ''; ?>
        <?php echo number_format((($row['total_l'] - $avg_water) / $avg_water) * 100, 1); ?>%
      </td>
      <td class="center"><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  
  <h2>🎯 KEY INSIGHTS & RECOMMENDATIONS</h2>
  <table>
    <tr>
      <th style="width: 30%;">Metric</th>
      <th style="width: 70%;">Analysis</th>
    </tr>
    <tr>
      <td><strong>Highest Water Impact</strong></td>
      <td><?php 
        $max_impact = max([
          $field_averages['et'] * 349,
          $field_averages['tahil'] * 82,
          $field_averages['sut'] * 64
        ]);
        if ($max_impact == $field_averages['et'] * 349) {
          echo "Meat consumption has the highest water footprint impact (" . number_format($field_averages['et'] * 349, 0) . " L). Reducing meat intake could significantly lower water usage.";
        } elseif ($max_impact == $field_averages['tahil'] * 82) {
          echo "Grain consumption is the primary water consumer (" . number_format($field_averages['tahil'] * 82, 0) . " L).";
        } else {
          echo "Dairy products consume significant water (" . number_format($field_averages['sut'] * 64, 0) . " L).";
        }
      ?></td>
    </tr>
    <tr>
      <td><strong>Household Efficiency</strong></td>
      <td><?php 
        $shower_total = ($field_averages['dus_say'] * 5) + ($field_averages['dus_dk'] * 5);
        echo "Average shower water usage: " . number_format($shower_total, 0) . " L/week. ";
        if ($shower_total > 100) {
          echo "Recommendation: Reduce shower time or frequency to save water.";
        } else {
          echo "Good water management in showering habits.";
        }
      ?></td>
    </tr>
    <tr>
      <td><strong>World Comparison</strong></td>
      <td><?php 
        $world_avg = 3800;
        $diff_percent = (($avg_water - $world_avg) / $world_avg) * 100;
        if ($avg_water < $world_avg) {
          echo "Excellent! Average footprint is " . number_format(abs($diff_percent), 1) . "% BELOW world average (3,800 L/day).";
        } else {
          echo "Average footprint is " . number_format($diff_percent, 1) . "% ABOVE world average (3,800 L/day). Room for improvement.";
        }
      ?></td>
    </tr>
  </table>
  
  <div class="footer">
    <p><strong>EML Water Footprint Calculator - Comprehensive Statistics Report</strong></p>
    <p>Data source: waterfootprint.org | Generated: <?php echo date('Y-m-d H:i:s'); ?> | Total Records Analyzed: <?php echo $total_records; ?></p>
    <p>This report provides statistical analysis of all water footprint calculations submitted through the EML system.</p>
  </div>
  
</body>
</html>
  <?php
  exit;
}

// Handle single record export (keep existing functionality)
if (isset($_GET['export']) && $isLoggedIn) {
  $db = new PDO('sqlite:database.db');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM results WHERE id = :id");
    $stmt->execute([':id' => intval($_GET['id'])]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filename = 'water_footprint_record_' . $_GET['id'] . '_' . date('Y-m-d') . '.doc';
  } else {
    $stmt = $db->query("SELECT * FROM results ORDER BY created_at DESC");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filename = 'water_footprint_all_records_' . date('Y-m-d') . '.doc';
  }
  
  header("Content-Type: application/msword");
  header("Content-Disposition: attachment; filename=\"$filename\"");
  header("Pragma: no-cache");
  header("Expires: 0");
  
  ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; font-size: 9pt; margin: 10px; }
    h2 { text-align: center; font-size: 12pt; font-weight: bold; margin: 3px 0; }
    h3 { text-align: center; font-size: 10pt; font-weight: bold; margin: 8px 0 5px 0; background-color: #d9d9d9; padding: 3px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
    th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; vertical-align: middle; font-size: 8pt; }
    th { background-color: #d9d9d9; font-weight: bold; text-align: center; }
    .center { text-align: center; }
    .result-box { background-color: #e8e8e8; border: 2px solid #000; padding: 8px; margin: 10px 0; text-align: center; }
    .result-box table { border: none; margin: 0; }
    .result-box td { border: none; padding: 2px 10px; font-size: 9pt; }
    .footer { font-size: 7pt; margin-top: 10px; text-align: center; color: #666; }
  </style>
</head>
<body>
  <h2>EML WATER FOOTPRINT CALCULATION</h2>
  
  <?php foreach ($results as $row): 
    $data = json_decode($row['data'], true);
    
    $daily_l = $row['total_l'];
    $daily_m3 = $row['total_m3'];
    $monthly_l = $daily_l * 30;
    $monthly_m3 = $monthly_l / 1000.0;
    $yearly_l = $daily_l * 365;
    $yearly_m3 = $yearly_l / 1000.0;
  ?>
  
  <h3>Record #<?php echo $row['id']; ?> - <?php echo htmlspecialchars($row['name'] ?: 'Anonymous'); ?> (<?php echo $row['created_at']; ?>)</h3>
  
  <table>
    <tr>
      <th style="width: 45%;">Food Consumption</th>
      <th style="width: 12%;">Value</th>
      <th style="width: 5%;">x</th>
      <th style="width: 10%;">Coef.</th>
      <th style="width: 13%;">Total (L)</th>
    </tr>
    <tr><td>Grain products (kg/week)</td><td class="center"><?php echo $data['tahil']; ?></td><td class="center">x</td><td class="center">82</td><td class="center"><?php echo number_format($data['tahil'] * 82, 0); ?></td></tr>
    <tr><td>Meat (kg/week)</td><td class="center"><?php echo $data['et']; ?></td><td class="center">x</td><td class="center">349</td><td class="center"><?php echo number_format($data['et'] * 349, 0); ?></td></tr>
    <tr><td>Dairy products (kg/week)</td><td class="center"><?php echo $data['sut']; ?></td><td class="center">x</td><td class="center">64</td><td class="center"><?php echo number_format($data['sut'] * 64, 0); ?></td></tr>
    <tr><td>Eggs (count/week)</td><td class="center"><?php echo $data['yumurta']; ?></td><td class="center">x</td><td class="center">15</td><td class="center"><?php echo number_format($data['yumurta'] * 15, 0); ?></td></tr>
    <tr><td>Fat type preference</td><td class="center"><?php echo $data['yag_tipi'] == 4 ? 'High' : ($data['yag_tipi'] == 3 ? 'Med' : 'Low'); ?></td><td class="center">-</td><td class="center"><?php echo $data['yag_tipi']; ?></td><td class="center"><?php echo $data['yag_tipi']; ?></td></tr>
    <tr><td>Sugar consumption</td><td class="center"><?php echo $data['seker'] == 3 ? 'High/Med' : 'Low'; ?></td><td class="center">-</td><td class="center"><?php echo $data['seker']; ?></td><td class="center"><?php echo $data['seker']; ?></td></tr>
    <tr><td>Vegetables (kg/week)</td><td class="center"><?php echo $data['sebze']; ?></td><td class="center">x</td><td class="center">18</td><td class="center"><?php echo number_format($data['sebze'] * 18, 0); ?></td></tr>
    <tr><td>Fruits (kg/week)</td><td class="center"><?php echo $data['meyve']; ?></td><td class="center">x</td><td class="center">32</td><td class="center"><?php echo number_format($data['meyve'] * 32, 0); ?></td></tr>
    <tr><td>Starchy plants (kg/week)</td><td class="center"><?php echo $data['nisasta']; ?></td><td class="center">x</td><td class="center">17</td><td class="center"><?php echo number_format($data['nisasta'] * 17, 0); ?></td></tr>
    <tr><td>Tea (cups/day)</td><td class="center"><?php echo $data['cay']; ?></td><td class="center">x</td><td class="center">13</td><td class="center"><?php echo number_format($data['cay'] * 13, 0); ?></td></tr>
    <tr><td>Coffee (cups/day)</td><td class="center"><?php echo $data['kahve']; ?></td><td class="center">x</td><td class="center">56</td><td class="center"><?php echo number_format($data['kahve'] * 56, 0); ?></td></tr>
  </table>
  
  <table>
    <tr>
      <th style="width: 45%;">Household Use</th>
      <th style="width: 12%;">Value</th>
      <th style="width: 5%;">x</th>
      <th style="width: 10%;">Coef.</th>
      <th style="width: 13%;">Total (L)</th>
    </tr>
    <tr><td>Showers (times/week)</td><td class="center"><?php echo $data['dus_say']; ?></td><td class="center">x</td><td class="center">5</td><td class="center"><?php echo number_format($data['dus_say'] * 5, 0); ?></td></tr>
    <tr><td>Shower duration (min)</td><td class="center"><?php echo $data['dus_dk']; ?></td><td class="center">x</td><td class="center">5</td><td class="center"><?php echo number_format($data['dus_dk'] * 5, 0); ?></td></tr>
    <tr><td>Baths (times/week)</td><td class="center"><?php echo $data['banyo']; ?></td><td class="center">x</td><td class="center">9</td><td class="center"><?php echo number_format($data['banyo'] * 9, 0); ?></td></tr>
    <tr><td>Teeth brushing (times/day)</td><td class="center"><?php echo $data['dis_firca']; ?></td><td class="center">x</td><td class="center">5</td><td class="center"><?php echo number_format($data['dis_firca'] * 5, 0); ?></td></tr>
    <tr><td>Turn off tap when brushing</td><td class="center"><?php echo $data['musluk_kapat'] == 0 ? 'Yes' : 'No'; ?></td><td class="center">-</td><td class="center"><?php echo $data['musluk_kapat'] == 0 ? '0' : '5'; ?></td><td class="center"><?php echo $data['musluk_kapat']; ?></td></tr>
    <tr><td>Laundry (times/week)</td><td class="center"><?php echo $data['camasir']; ?></td><td class="center">x</td><td class="center">11</td><td class="center"><?php echo number_format($data['camasir'] * 11, 0); ?></td></tr>
    <tr><td>Hand dishwashing (times/day)</td><td class="center"><?php echo $data['bulasik_el']; ?></td><td class="center">x</td><td class="center">10</td><td class="center"><?php echo number_format($data['bulasik_el'] * 10, 0); ?></td></tr>
    <tr><td>Dishwashing duration (min)</td><td class="center"><?php echo $data['bulasik_dk']; ?></td><td class="center">x</td><td class="center">5</td><td class="center"><?php echo number_format($data['bulasik_dk'] * 5, 0); ?></td></tr>
    <tr><td>Dishwasher (times/day)</td><td class="center"><?php echo $data['bulasik_mak']; ?></td><td class="center">x</td><td class="center">6</td><td class="center"><?php echo number_format($data['bulasik_mak'] * 6, 0); ?></td></tr>
    <tr><td>Car wash (times/week)</td><td class="center"><?php echo $data['araba']; ?></td><td class="center">x</td><td class="center">8</td><td class="center"><?php echo number_format($data['araba'] * 8, 0); ?></td></tr>
  </table>
  
  <div class="result-box">
    <table style="margin: 0 auto;">
      <tr>
        <td><strong>Daily:</strong></td>
        <td><?php echo number_format($daily_l, 0); ?> L</td>
        <td>(<?php echo number_format($daily_m3, 2); ?> m³)</td>
        <td style="width: 20px;"></td>
        <td><strong>Monthly:</strong></td>
        <td><?php echo number_format($monthly_l, 0); ?> L</td>
        <td>(<?php echo number_format($monthly_m3, 2); ?> m³)</td>
        <td style="width: 20px;"></td>
        <td><strong>Yearly:</strong></td>
        <td><?php echo number_format($yearly_l, 0); ?> L</td>
        <td>(<?php echo number_format($yearly_m3, 2); ?> m³)</td>
      </tr>
    </table>
  </div>
  
  <div style="page-break-after: always;"></div>
  
  <?php endforeach; ?>
  
  <div class="footer">
    Information sourced from waterfootprint.org | Generated: <?php echo date('Y-m-d H:i:s'); ?>
  </div>
  
</body>
</html>
  <?php
  exit;
}

// Handle view details
if (isset($_GET['view']) && $isLoggedIn) {
  $id = intval($_GET['view']);
  
  $db = new PDO('sqlite:database.db');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  $stmt = $db->prepare("SELECT * FROM results WHERE id = :id");
  $stmt->execute([':id' => $id]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$result) {
    header('Location: admin.php');
    exit;
  }
  
  $data = json_decode($result['data'], true);
  
  $daily_l = $result['total_l'];
  $daily_m3 = $result['total_m3'];
  $monthly_l = $daily_l * 30;
  $monthly_m3 = $monthly_l / 1000.0;
  $yearly_l = $daily_l * 365;
  $yearly_m3 = $yearly_l / 1000.0;
  
  // Calculate category totals
  $food_total = ($data['tahil'] * 82) + ($data['et'] * 349) + ($data['sut'] * 64) + 
                ($data['yumurta'] * 15) + $data['yag_tipi'] + $data['seker'] + 
                ($data['sebze'] * 18) + ($data['meyve'] * 32) + ($data['nisasta'] * 17) + 
                ($data['cay'] * 13) + ($data['kahve'] * 56);
  
  $household_total = ($data['dus_say'] * 5) + ($data['dus_dk'] * 5) + ($data['banyo'] * 9) + 
                     ($data['dis_firca'] * 5) + $data['musluk_kapat'] + ($data['camasir'] * 11) + 
                     ($data['bulasik_el'] * 10) + ($data['bulasik_dk'] * 5) + 
                     ($data['bulasik_mak'] * 6) + ($data['araba'] * 8);
  
  $food_percent = ($food_total / $daily_l) * 100;
  $household_percent = ($household_total / $daily_l) * 100;
  
  $labels = [
    'tahil' => 'Grains (kg/week)',
    'et' => 'Meat (kg/week)',
    'sut' => 'Dairy (kg/week)',
    'yumurta' => 'Eggs (count/week)',
    'yag_tipi' => 'Fat type (coefficient)',
    'seker' => 'Sugar (coefficient)',
    'sebze' => 'Vegetables (kg/week)',
    'meyve' => 'Fruits (kg/week)',
    'nisasta' => 'Starchy plants (kg/week)',
    'cay' => 'Tea (cups/day)',
    'kahve' => 'Coffee (cups/day)',
    'dus_say' => 'Showers (times/week)',
    'dus_dk' => 'Shower duration (min)',
    'banyo' => 'Baths (times/week)',
    'dis_firca' => 'Teeth brushing (times/day)',
    'musluk_kapat' => 'Tap off when brushing (0=Yes, 5=No)',
    'camasir' => 'Laundry (times/week)',
    'bulasik_el' => 'Hand dishwashing (times/day)',
    'bulasik_dk' => 'Dishwashing duration (min)',
    'bulasik_mak' => 'Dishwasher (times/day)',
    'araba' => 'Car wash (times/week)'
  ];
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EML - View Record #<?php echo $id; ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255, 68, 68, 0.2);
    }
    .detail-row:last-child {
      border-bottom: none;
    }
    .detail-label {
      color: #999;
    }
    .detail-value {
      font-weight: 600;
      color: #fff;
    }
    .period-section {
      background: rgba(255, 255, 255, 0.03);
      padding: 15px;
      margin: 15px 0;
      border-left: 3px solid #ff4444;
      border-radius: 4px;
    }
    .chart-mini {
      margin: 20px 0;
    }
    .bar-mini {
      height: 30px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
      overflow: hidden;
      margin: 10px 0;
      position: relative;
    }
    .bar-mini-fill {
      height: 100%;
      background: linear-gradient(90deg, #ff4444, #cc0000);
      transition: width 1s ease;
      display: flex;
      align-items: center;
      padding: 0 10px;
      font-size: 0.85rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <main class="container">
    <h1>📋 Record Details #<?php echo $id; ?></h1>
    
    <section class="card">
      <h2>Summary</h2>
      <div class="detail-row">
        <span class="detail-label">Name:</span>
        <span class="detail-value"><?php echo htmlspecialchars($result['name'] ?: 'Anonymous'); ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Submitted:</span>
        <span class="detail-value"><?php echo $result['created_at']; ?></span>
      </div>
      
      <div class="period-section">
        <h3>Daily Water Footprint</h3>
        <div class="detail-row">
          <span class="detail-label">Total:</span>
          <span class="detail-value"><?php echo number_format($daily_l, 0, ',', '.'); ?> liters (<?php echo number_format($daily_m3, 2, ',', '.'); ?> m³)</span>
        </div>
      </div>
      
      <div class="period-section">
        <h3>Monthly Water Footprint</h3>
        <div class="detail-row">
          <span class="detail-label">Total:</span>
          <span class="detail-value"><?php echo number_format($monthly_l, 0, ',', '.'); ?> liters (<?php echo number_format($monthly_m3, 2, ',', '.'); ?> m³)</span>
        </div>
      </div>
      
      <div class="period-section">
        <h3>Yearly Water Footprint</h3>
        <div class="detail-row">
          <span class="detail-label">Total:</span>
          <span class="detail-value"><?php echo number_format($yearly_l, 0, ',', '.'); ?> liters (<?php echo number_format($yearly_m3, 2, ',', '.'); ?> m³)</span>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Category Breakdown</h2>
      <div class="chart-mini">
        <div style="margin-bottom: 5px; color: #ddd;">🍽️ Food & Beverages: <?php echo number_format($food_total, 0); ?> L (<?php echo number_format($food_percent, 1); ?>%)</div>
        <div class="bar-mini">
          <div class="bar-mini-fill" style="width: <?php echo $food_percent; ?>%;">
            <?php echo number_format($food_percent, 1); ?>%
          </div>
        </div>
      </div>
      <div class="chart-mini">
        <div style="margin-bottom: 5px; color: #ddd;">🏠 Household Use: <?php echo number_format($household_total, 0); ?> L (<?php echo number_format($household_percent, 1); ?>%)</div>
        <div class="bar-mini">
          <div class="bar-mini-fill" style="width: <?php echo $household_percent; ?>%;">
            <?php echo number_format($household_percent, 1); ?>%
          </div>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Detailed Answers</h2>
      <?php foreach ($data as $key => $value): ?>
        <div class="detail-row">
          <span class="detail-label"><?php echo $labels[$key] ?? $key; ?>:</span>
          <span class="detail-value"><?php echo $value; ?></span>
        </div>
      <?php endforeach; ?>
    </section>

    <div class="actions">
      <a href="admin.php" class="btn btn-primary">Back to Admin Panel</a>
      <a href="admin.php?export&id=<?php echo $id; ?>" class="btn ghost">Export This Record</a>
    </div>
  </main>
  
  <script>
    window.addEventListener('load', function() {
      document.querySelectorAll('.bar-mini-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
          bar.style.width = width;
        }, 100);
      });
    });
  </script>
</body>
</html>
  <?php
  exit;
}

// Login page
if (!$isLoggedIn) {
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EML - Admin Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
  <main class="container">
    <section class="card" style="max-width: 400px; margin: 100px auto;">
      <h2>🔒 Admin Login</h2>
      <?php if (isset($error)): ?>
        <div style="color: #ff4444; margin-bottom: 15px; padding: 10px; background: rgba(255,68,68,0.1); border-radius: 4px;"><?php echo $error; ?></div>
      <?php endif; ?>
      <form method="POST" class="form">
        <div class="form-group">
          <label>Password</label>
          <input name="password" type="password" required autofocus>
        </div>
        <div class="actions">
          <button type="submit" name="login" class="btn btn-primary">Login</button>
          <a class="btn ghost" href="index.php">Back to Calculator</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
  <?php
  exit;
}

// Admin panel - Dashboard with statistics
$db = new PDO('sqlite:database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT * FROM results ORDER BY created_at DESC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_records = count($results);
$total_water = 0;
$food_total_all = 0;
$household_total_all = 0;

foreach ($results as $row) {
  $data = json_decode($row['data'], true);
  $total_water += $row['total_l'];
  
  $food = ($data['tahil'] * 82) + ($data['et'] * 349) + ($data['sut'] * 64) + 
          ($data['yumurta'] * 15) + $data['yag_tipi'] + $data['seker'] + 
          ($data['sebze'] * 18) + ($data['meyve'] * 32) + ($data['nisasta'] * 17) + 
          ($data['cay'] * 13) + ($data['kahve'] * 56);
  
  $household = ($data['dus_say'] * 5) + ($data['dus_dk'] * 5) + ($data['banyo'] * 9) + 
               ($data['dis_firca'] * 5) + $data['musluk_kapat'] + ($data['camasir'] * 11) + 
               ($data['bulasik_el'] * 10) + ($data['bulasik_dk'] * 5) + 
               ($data['bulasik_mak'] * 6) + ($data['araba'] * 8);
  
  $food_total_all += $food;
  $household_total_all += $household;
}

$avg_water = $total_records > 0 ? $total_water / $total_records : 0;
$avg_food = $total_records > 0 ? $food_total_all / $total_records : 0;
$avg_household = $total_records > 0 ? $household_total_all / $total_records : 0;

$world_avg = 3800;
$comparison_percent = $avg_water > 0 ? (($avg_water / $world_avg) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EML - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .admin-header h1 {
      color: #ff4444;
      font-size: 2rem;
      margin: 0;
    }
    
    .header-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    
    /* Dashboard Stats */
    .dashboard-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card-admin {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .stat-card-admin:hover {
      transform: translateY(-5px);
      border-color: #ff4444;
      box-shadow: 0 10px 30px rgba(255, 68, 68, 0.3);
    }
    
    .stat-icon {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #ff4444;
      margin: 10px 0;
    }
    
    .stat-desc {
      font-size: 0.9rem;
      color: #aaa;
    }
    
    /* Charts Section */
    .charts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .chart-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 12px;
      padding: 20px;
    }
    
    .chart-title {
      font-size: 1.2rem;
      color: #ff4444;
      margin-bottom: 20px;
      font-weight: 600;
    }
    
    .chart-bar {
      margin: 15px 0;
    }
    
    .chart-bar-label {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
      font-size: 0.9rem;
    }
    
    .chart-bar-track {
      height: 25px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      overflow: hidden;
    }
    
    .chart-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #ff4444, #cc0000);
      border-radius: 12px;
      transition: width 1.5s ease;
      width: 0;
      display: flex;
      align-items: center;
      padding-left: 10px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    
    /* Table Styles */
    .data-table {
      width: 100%;
      overflow-x: auto;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 68, 68, 0.3);
      border-radius: 12px;
      padding: 20px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid rgba(255, 68, 68, 0.2);
    }
    
    th {
      background: rgba(255, 68, 68, 0.1);
      color: #ff4444;
      font-weight: 600;
      position: sticky;
      top: 0;
    }
    
    tr:hover {
      background: rgba(255, 255, 255, 0.05);
    }
    
    .actions-cell {
      white-space: nowrap;
    }
    
    .btn-small {
      padding: 6px 12px;
      font-size: 0.85rem;
      margin-right: 5px;
      display: inline-block;
      margin-bottom: 5px;
    }
    
    .no-data {
      text-align: center;
      padding: 60px 20px;
      color: #aaa;
      font-size: 1.1rem;
    }
    
    .comparison-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    
    .badge-good {
      background: rgba(0, 255, 0, 0.2);
      color: #0f0;
    }
    
    .badge-warning {
      background: rgba(255, 165, 0, 0.2);
      color: #ffa500;
    }
    
    .badge-bad {
      background: rgba(255, 0, 0, 0.2);
      color: #f00;
    }
    
    @media (max-width: 768px) {
      .dashboard-stats {
        grid-template-columns: 1fr;
      }
      
      .charts-grid {
        grid-template-columns: 1fr;
      }
      
      .admin-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .header-actions {
        width: 100%;
      }
      
      .header-actions .btn {
        flex: 1;
      }
      
      table {
        font-size: 0.85rem;
      }
      
      th, td {
        padding: 8px;
      }
    }
  </style>
</head>
<body>
  <main class="container">
    <div class="admin-header">
      <h1>📊 Admin Dashboard</h1>
      <div class="header-actions">
        <a href="?export_stats" class="btn btn-primary">📈 Export Statistics</a>
        <a href="?export" class="btn ghost">📄 Export All Records</a>
        <a href="?logout" class="btn ghost">🚪 Logout</a>
      </div>
    </div>

    <!-- Dashboard Statistics -->
    <div class="dashboard-stats">
      <div class="stat-card-admin">
        <div class="stat-icon">📝</div>
        <div class="stat-number"><?php echo $total_records; ?></div>
        <div class="stat-desc">Total Submissions</div>
      </div>
      
      <div class="stat-card-admin">
        <div class="stat-icon">💧</div>
        <div class="stat-number"><?php echo number_format($avg_water, 0); ?></div>
        <div class="stat-desc">Avg. Daily (Liters)</div>
      </div>
      
      <div class="stat-card-admin">
        <div class="stat-icon">📅</div>
        <div class="stat-number"><?php echo number_format($avg_water * 30, 0); ?></div>
        <div class="stat-desc">Avg. Monthly (Liters)</div>
      </div>
      
      <div class="stat-card-admin">
        <div class="stat-icon">🌍</div>
        <div class="stat-number"><?php echo number_format($comparison_percent, 0); ?>%</div>
        <div class="stat-desc">vs World Average</div>
      </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
      <!-- Category Distribution -->
      <div class="chart-card">
        <div class="chart-title">Average Category Distribution</div>
        <div class="chart-bar">
          <div class="chart-bar-label">
            <span>🍽️ Food & Beverages</span>
            <span><?php echo number_format($avg_food, 0); ?> L</span>
          </div>
          <div class="chart-bar-track">
            <div class="chart-bar-fill" data-width="<?php echo $avg_water > 0 ? ($avg_food / $avg_water) * 100 : 0; ?>">
              <?php echo $avg_water > 0 ? number_format(($avg_food / $avg_water) * 100, 1) : 0; ?>%
            </div>
          </div>
        </div>
        <div class="chart-bar">
          <div class="chart-bar-label">
            <span>🏠 Household Use</span>
            <span><?php echo number_format($avg_household, 0); ?> L</span>
          </div>
          <div class="chart-bar-track">
            <div class="chart-bar-fill" data-width="<?php echo $avg_water > 0 ? ($avg_household / $avg_water) * 100 : 0; ?>">
              <?php echo $avg_water > 0 ? number_format(($avg_household / $avg_water) * 100, 1) : 0; ?>%
            </div>
          </div>
        </div>
      </div>
      
      <!-- World Comparison -->
      <div class="chart-card">
        <div class="chart-title">Comparison with World Average</div>
        <div class="chart-bar">
          <div class="chart-bar-label">
            <span>Your Users</span>
            <span><?php echo number_format($avg_water, 0); ?> L</span>
          </div>
          <div class="chart-bar-track">
            <div class="chart-bar-fill" data-width="<?php echo min(100, ($avg_water / $world_avg) * 100); ?>" style="background: linear-gradient(90deg, #ff4444, #cc0000);">
              <?php echo number_format(($avg_water / $world_avg) * 100, 0); ?>%
            </div>
          </div>
        </div>
        <div class="chart-bar">
          <div class="chart-bar-label">
            <span>World Average</span>
            <span><?php echo number_format($world_avg, 0); ?> L</span>
          </div>
          <div class="chart-bar-track">
            <div class="chart-bar-fill" data-width="100" style="background: linear-gradient(90deg, #666, #333);">
              100%
            </div>
          </div>
        </div>
        <div style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 4px; font-size: 0.9rem; text-align: center;">
          <?php if ($avg_water < $world_avg * 0.9): ?>
            <span class="comparison-badge badge-good">✓ Below Average</span>
          <?php elseif ($avg_water > $world_avg * 1.1): ?>
            <span class="comparison-badge badge-bad">⚠ Above Average</span>
          <?php else: ?>
            <span class="comparison-badge badge-warning">≈ Near Average</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Records Table -->
    <div class="card">
      <h2 style="color: #ff4444; margin-bottom: 20px;">📋 All Submissions</h2>
      
      <?php if ($total_records === 0): ?>
        <div class="no-data">
          <div style="font-size: 3rem; margin-bottom: 10px;">📭</div>
          <div>No submissions yet. Share the calculator to start collecting data!</div>
        </div>
      <?php else: ?>
        <div class="data-table">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Daily (L)</th>
                <th>Daily (m³)</th>
                <th>vs Avg</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($results as $row): 
                $diff_percent = $avg_water > 0 ? (($row['total_l'] - $avg_water) / $avg_water) * 100 : 0;
              ?>
              <tr>
                <td><strong>#<?php echo $row['id']; ?></strong></td>
                <td><?php echo htmlspecialchars($row['name'] ?: '👤 Anonymous'); ?></td>
                <td><?php echo number_format($row['total_l'], 0, ',', '.'); ?> L</td>
                <td><?php echo number_format($row['total_m3'], 2, ',', '.'); ?> m³</td>
                <td>
                  <?php if ($diff_percent < -10): ?>
                    <span class="comparison-badge badge-good">
                      <?php echo number_format($diff_percent, 1); ?>%
                    </span>
                  <?php elseif ($diff_percent > 10): ?>
                    <span class="comparison-badge badge-bad">
                      +<?php echo number_format($diff_percent, 1); ?>%
                    </span>
                  <?php else: ?>
                    <span class="comparison-badge badge-warning">
                      <?php echo $diff_percent > 0 ? '+' : ''; ?><?php echo number_format($diff_percent, 1); ?>%
                    </span>
                  <?php endif; ?>
                </td>
                <td><?php echo date('d.m.Y H:i', strtotime($row['created_at'])); ?></td>
                <td class="actions-cell">
                  <a href="?view=<?php echo $row['id']; ?>" class="btn btn-small btn-primary">👁️ View</a>
                  <a href="?export&id=<?php echo $row['id']; ?>" class="btn btn-small ghost">📄 Export</a>
                  <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-small ghost" onclick="return confirm('Are you sure you want to delete this record?')">🗑️ Delete</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <div style="margin-top: 20px; text-align: center; color: #888; font-size: 0.9rem;">
          Showing all <?php echo $total_records; ?> record<?php echo $total_records != 1 ? 's' : ''; ?>
        </div>
      <?php endif; ?>
    </div>

    <div style="margin-top: 30px; text-align: center;">
      <a class="btn ghost" href="index.php">🏠 Back to Calculator</a>
    </div>
  </main>
  
  <script>
    // Animate charts on load
    window.addEventListener('load', function() {
      setTimeout(() => {
        document.querySelectorAll('.chart-bar-fill').forEach(bar => {
          const targetWidth = bar.dataset.width;
          bar.style.width = targetWidth + '%';
        });
      }, 300);
      
      // Animate stat cards
      document.querySelectorAll('.stat-card-admin').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
          card.style.transition = 'all 0.5s ease';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, index * 100);
      });
    });
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
  </script>
</body>
</html>