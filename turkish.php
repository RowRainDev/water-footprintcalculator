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
  <title>EML - Su Ayak izi hesaplayıcı</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
  <main class="container">
    <header class="hero">
      <h1>EML</h1>
      <p class="subtitle">Su ayak izi hesaplayıcı — Adım adım cevablarınızı giriniz.</p>
    </header>

    <!-- Added novalidate and proper form structure -->
    <form action="submit.php" method="POST" class="form" novalidate>
      <!-- CSRF token field -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

      <!-- Food Consumption section with better semantics -->
      <section class="card" aria-labelledby="food-heading">
        <h2 id="food-heading">Gıda Tüketimi</h2>

        <div class="form-group">
          <label for="name">Adınız ve Soyadınız <span class="text-muted">(!)</span></label>
          <input 
            id="name"
            name="name" 
            type="text" 
            placeholder="İsminiz..."
            aria-describedby="name-hint"
          >
        </div>

        <div class="form-group">
          <label for="tahil">Haftada kaç kilo tahıl tüketiyorsunuz? <span class="text-required" aria-label="required">(Buğday, pirinç, mısır, diğer.)</span></label>
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
          <label for="et">Haftada kaç kilo et tüketiyorsunuz? <span class="text-required">*</span></label>
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
          <label for="sut">Haftada kaç kilo süt ürünü tüketiyorsunuz? <span class="text-required">*</span></label>
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
          <label for="yumurta">Haftada kaç tane yumurta tüketiyorsunuz? <span class="text-required">*</span></label>
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
          <label for="yag_tipi">Ne tür yiyecekleri tüketmeyi tercih edersiniz? <span class="text-required">*</span></label>
          <select 
            id="yag_tipi"
            name="yag_tipi" 
            required
            aria-required="true"
          >
            <option value="">seçim yapınız.</option>
            <option value="4">Yüksek yağlı</option>
            <option value="3" selected>Orta yağlı</option>
            <option value="2">Düşük yağlı</option>
          </select>
        </div>

        <div class="form-group">
          <label for="seker">Şeker veya şekerli ürün tüketiminiz nasıl? <span class="text-required">*</span></label>
          <select 
            id="seker"
            name="seker" 
            required
            aria-required="true"
          >
            <option value="">Seçim yapınız</option>
            <option value="3">Yüksek</option>
            <option value="3" selected>Orta</option>
            <option value="2">Düşük</option>
          </select>
        </div>

        <div class="form-group">
          <label for="sebze">Haftada kaç kilo sebze tüketiyorsunuz? <span class="text-required">*</span></label>
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
          <label for="meyve">Haftada kaç kilo meyve tüketiyorsunuz? <span class="text-required">*</span></label>
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
          <label for="nisasta">Haftada kaç kilo nişastalı bitki tüketiyorsunuz? <span class="text-required">*</span> (Patates, vs.)</label>
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
          <label for="cay">Günde kaç fincan çay içiyorsunuz? <span class="text-required">*</span></label>
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
          <label for="kahve">Günde kaç fincan kahve içiyorsunuz? <span class="text-required">*</span></label>
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
        <h2 id="household-heading">Evde Kullanım - İç Mekan</h2>

        <div class="form-group">
          <label for="dus_say">Haftada kaç kez duş alıyorsunuz? <span class="text-required">*</span></label>
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
          <label for="dus_dk">Her duş kaç dakika sürer? <span class="text-required">*</span></label>
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
          <label for="banyo">Haftada kaç kez banyo yaparsınız? <span class="text-required">*</span></label>
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
          <label for="dis_firca">Günde kaç kez dişlerinizi fırçalıyor ve ellerinizi yıkıyorsunuz? <span class="text-required">*</span></label>
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
          <label for="musluk_kapat">Dişlerinizi fırçalarken/ellerinizi yıkarken musluğu kapatıyor musunuz? <span class="text-required">*</span></label>
          <select 
            id="musluk_kapat"
            name="musluk_kapat" 
            required
            aria-required="true"
          >
            <option value="">Seçim yapınız..</option>
            <option value="0">Evet</option>
            <option value="5">Hayır</option>
          </select>
        </div>

        <div class="form-group">
          <label for="camasir">Haftada kaç kez çamaşır yıkarsınız? <span class="text-required">*</span></label>
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
          <label for="bulasik_el">Evde bulaşıklar elle yıkanıyorsa, günde kaç kez yıkanıyor? <span class="text-required">*</span></label>
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
          <label for="bulasik_dk">El ile bulaşık yıkamak ne kadar sürer? <span class="text-required">*</span> (Dakkika)</label>
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
          <label for="bulasik_mak">Günde kaç kez bulaşık makinesini kullanıyorsunuz? <span class="text-required">*</span></label>
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
          <label for="araba">Arabanız varsa, haftada kaç kez yıkarsınız? <span class="text-required">*</span></label>
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
