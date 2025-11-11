<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Calculate your water footprint by answering simple questions about your daily consumption habits.">
  <title>EML - Water Footprint Calculator</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
  <main class="container">
    <header class="hero">
      <h1>EML</h1>
      <p class="subtitle">Water Footprint Calculator — Answer step by step, see your result instantly.</p>
    </header>

    <!-- Added novalidate and proper form structure -->
    <form action="submit.php" method="POST" class="form" novalidate>
      <!-- CSRF token field -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

      <!-- Food Consumption section with better semantics -->
      <section class="card" aria-labelledby="food-heading">
        <h2 id="food-heading">Food Consumption</h2>

        <div class="form-group">
          <label for="name">Your Name <span class="text-muted">(!)</span></label>
          <input 
            id="name"
            name="name" 
            type="text" 
            placeholder="Your name..."
            aria-describedby="name-hint"
          >
        </div>

        <div class="form-group">
          <label for="tahil">How many kg of grains do you consume per week? <span class="text-required" aria-label="required">(Wheat, rice, corn, etc.)</span></label>
          <input 
            id="tahil"
            name="tahil" 
            type="number" 
            step="0.1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="et">How many kg of meat do you consume per week? <span class="text-required">*</span></label>
          <input 
            id="et"
            name="et" 
            type="number" 
            step="0.1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="sut">How many kg of dairy products do you consume per week? <span class="text-required">*</span></label>
          <input 
            id="sut"
            name="sut" 
            type="number" 
            step="0.1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="yumurta">How many eggs do you consume per week? <span class="text-required">*</span></label>
          <input 
            id="yumurta"
            name="yumurta" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <!-- Fixed select dropdown consistency -->
        <div class="form-group">
          <label for="yag_tipi">What type of food do you prefer to consume? <span class="text-required">*</span></label>
          <select 
            id="yag_tipi"
            name="yag_tipi" 
            required
            aria-required="true"
          >
            <option value="">Select an option</option>
            <option value="4">High fat</option>
            <option value="3" selected>Medium fat</option>
            <option value="2">Low fat</option>
          </select>
        </div>

        <div class="form-group">
          <label for="seker">How is your sugar or sugar product consumption? <span class="text-required">*</span></label>
          <select 
            id="seker"
            name="seker" 
            required
            aria-required="true"
          >
            <option value="">Select an option</option>
            <option value="3">High</option>
            <option value="3" selected>Medium</option>
            <option value="2">Low</option>
          </select>
        </div>

        <div class="form-group">
          <label for="sebze">How many kg of vegetables do you consume per week? <span class="text-required">*</span></label>
          <input 
            id="sebze"
            name="sebze" 
            type="number" 
            step="0.1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="meyve">How many kg of fruit do you consume per week? <span class="text-required">*</span></label>
          <input 
            id="meyve"
            name="meyve" 
            type="number" 
            step="0.1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="nisasta">How many kg of starchy plants do you consume per week? <span class="text-required">*</span> (potatoes, etc.)</label>
          <input 
            id="nisasta"
            name="nisasta" 
            type="number" 
            step="0.1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="cay">How many cups of tea do you drink per day? <span class="text-required">*</span></label>
          <input 
            id="cay"
            name="cay" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="kahve">How many cups of coffee do you drink per day? <span class="text-required">*</span></label>
          <input 
            id="kahve"
            name="kahve" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>
      </section>

      <!-- Household Use section with better semantics -->
      <section class="card" aria-labelledby="household-heading">
        <h2 id="household-heading">Household Use - Indoor</h2>

        <div class="form-group">
          <label for="dus_say">How many times per week do you shower? <span class="text-required">*</span></label>
          <input 
            id="dus_say"
            name="dus_say" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="dus_dk">How many minutes does each shower last? <span class="text-required">*</span></label>
          <input 
            id="dus_dk"
            name="dus_dk" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="banyo">How many times per week do you take a bath? <span class="text-required">*</span></label>
          <input 
            id="banyo"
            name="banyo" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="dis_firca">How many times per day do you brush your teeth and wash your hands? <span class="text-required">*</span></label>
          <input 
            id="dis_firca"
            name="dis_firca" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="musluk_kapat">Do you turn off the tap when brushing teeth/washing hands? <span class="text-required">*</span></label>
          <select 
            id="musluk_kapat"
            name="musluk_kapat" 
            required
            aria-required="true"
          >
            <option value="">Select an option</option>
            <option value="0">Yes</option>
            <option value="5">No</option>
          </select>
        </div>

        <div class="form-group">
          <label for="camasir">How many times per week do you do laundry? <span class="text-required">*</span></label>
          <input 
            id="camasir"
            name="camasir" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="bulasik_el">If dishes are washed by hand at home, how many times per day? <span class="text-required">*</span></label>
          <input 
            id="bulasik_el"
            name="bulasik_el" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="bulasik_dk">How long does hand dishwashing take? <span class="text-required">*</span> (minutes)</label>
          <input 
            id="bulasik_dk"
            name="bulasik_dk" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="bulasik_mak">How many times per day do you use the dishwasher? <span class="text-required">*</span></label>
          <input 
            id="bulasik_mak"
            name="bulasik_mak" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>

        <div class="form-group">
          <label for="araba">If you have a car, how many times per week do you wash it? <span class="text-required">*</span></label>
          <input 
            id="araba"
            name="araba" 
            type="number" 
            step="1" 
            min="0" 
            value="0" 
            required
            aria-required="true"
          >
        </div>
      </section>

      <!-- Improved actions section -->
      <div class="actions">
        <button type="submit" class="btn btn-primary">Calculate and Submit</button>
      </div>
    </form>

    <footer class="footer">
      <small>Site <a href="https://www.rowdev.rf.gd" target="_blank" rel="noopener noreferrer">rowrain.dev</a>(canberk karaeski) tarafından geliştirilmiştir.</small><br>
      <small>Projeyi tamamen açık kaynak olarak yayımlıyorum. https://github.com/RowRainDev<small>
    </footer>
  </main>

  <!-- Basic client-side validation -->
  <script>
    document.querySelector('.form').addEventListener('submit', function(e) {
      const inputs = this.querySelectorAll('input[required], select[required]');
      let isValid = true;

      inputs.forEach(input => {
        if (input.type === 'number' && (input.value === '' || input.value < 0)) {
          input.classList.add('error');
          isValid = false;
        } else if (input.type === 'text' || input.tagName === 'SELECT') {
          if (input.hasAttribute('required') && input.value === '') {
            input.classList.add('error');
            isValid = false;
          }
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields correctly.');
      }
    });
  </script>
</body>
</html>
