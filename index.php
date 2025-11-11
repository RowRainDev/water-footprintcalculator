<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Language / Dil Seçin</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Force black background */
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d0a0a 100%) !important;
            min-height: 100vh;
        }

        /* Language page specific styles */
        .language-page {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .language-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 10px;
        }

        .language-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #ff4444;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .language-subtitle {
            font-size: 0.95rem;
            color: #ddd;
            font-weight: 300;
            line-height: 1.5;
        }

        .language-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 12px;
            padding: 20px 15px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
        }

        .language-options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .language-option {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 68, 68, 0.3);
            border-radius: 12px;
            padding: 25px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        .language-option:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: #ff4444;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
        }

        .language-option.selected {
            border-color: #ff4444;
            background: rgba(255, 68, 68, 0.15);
            box-shadow: 0 5px 20px rgba(255, 68, 68, 0.4);
        }

        .language-flag {
            font-size: 3.5rem;
            width: 70px;
            text-align: center;
            flex-shrink: 0;
            line-height: 1;
        }

        .language-info {
            flex: 1;
            text-align: left;
        }

        .language-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
        }

        .language-native {
            font-size: 1rem;
            color: #aaa;
            font-weight: 300;
        }

        .language-check {
            font-size: 1.8rem;
            color: #ff4444;
            opacity: 0;
            transition: opacity 0.3s ease;
            flex-shrink: 0;
            filter: drop-shadow(0 0 5px rgba(255, 68, 68, 0.5));
        }

        .language-option.selected .language-check {
            opacity: 1;
        }

        .language-actions {
            text-align: center;
            margin-top: 30px;
        }

        .language-btn {
            display: inline-block;
            padding: 14px 30px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: 'Poppins', sans-serif;
            width: 100%;
            max-width: 100%;
            background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.4);
        }

        .language-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .language-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 68, 68, 0.6);
        }

        .language-btn:not(:disabled):active {
            transform: translateY(0);
        }

        /* Tablet için (768px ve üzeri) */
        @media (min-width: 768px) {
            .language-header {
                padding: 30px 20px;
            }

            .language-header h1 {
                font-size: 2.5rem;
            }

            .language-subtitle {
                font-size: 1.1rem;
            }

            .language-card {
                padding: 30px 25px;
            }

            .language-options {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .language-option {
                flex-direction: column;
                text-align: center;
                padding: 35px 25px;
            }

            .language-flag {
                font-size: 5rem;
                width: auto;
            }

            .language-info {
                text-align: center;
            }

            .language-name {
                font-size: 1.4rem;
            }

            .language-native {
                font-size: 1.1rem;
            }

            .language-check {
                position: absolute;
                top: 20px;
                right: 20px;
                font-size: 2rem;
            }

            .language-btn {
                width: auto;
                min-width: 250px;
                padding: 16px 40px;
                font-size: 1.1rem;
            }
        }

        /* Desktop için (1024px ve üzeri) */
        @media (min-width: 1024px) {
            .language-header h1 {
                font-size: 3rem;
            }

            .language-flag {
                font-size: 6rem;
            }

            .language-option {
                padding: 40px 30px;
            }
        }

        /* Küçük ekranlar için (480px ve altı) */
        @media (max-width: 480px) {
            .language-header {
                margin-bottom: 20px;
                padding: 15px 5px;
            }

            .language-header h1 {
                font-size: 1.75rem;
            }

            .language-subtitle {
                font-size: 0.9rem;
            }

            .language-card {
                padding: 15px 12px;
                margin-bottom: 20px;
            }

            .language-option {
                padding: 18px 15px;
                gap: 15px;
            }

            .language-flag {
                font-size: 2.8rem;
                width: 55px;
            }

            .language-name {
                font-size: 1.15rem;
            }

            .language-native {
                font-size: 0.9rem;
            }

            .language-check {
                font-size: 1.5rem;
            }

            .language-btn {
                padding: 12px 25px;
                font-size: 0.95rem;
            }
        }

        /* Touch cihazlar için */
        @media (hover: none) and (pointer: coarse) {
            .language-option {
                padding: 22px 18px;
            }

            .language-btn {
                padding: 16px 30px;
            }
        }

        /* Landscape mod için */
        @media (max-height: 600px) and (orientation: landscape) {
            .language-header {
                margin-bottom: 20px;
                padding: 10px;
            }

            .language-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <main class="container language-page">
        <!-- Header -->
        <div class="language-header">
            <h1>🌍 Choose Your Language</h1>
            <p class="language-subtitle">Select your preferred language to continue<br>Devam etmek için dilinizi seçin</p>
        </div>

        <!-- Language Card -->
        <div class="language-card">
            <div class="language-options">
                <div class="language-option" onclick="selectLanguage('turkish')" id="turkish-option">
                    <div class="language-flag">🇹🇷</div>
                    <div class="language-info">
                        <div class="language-name">Turkish</div>
                        <div class="language-native">Türkçe</div>
                    </div>
                    <div class="language-check">✓</div>
                </div>
                
                <div class="language-option" onclick="selectLanguage('english')" id="english-option">
                    <div class="language-flag">🇬🇧</div>
                    <div class="language-info">
                        <div class="language-name">English</div>
                        <div class="language-native">English</div>
                    </div>
                    <div class="language-check">✓</div>
                </div>
            </div>
            
            <div class="language-actions">
                <button class="language-btn" onclick="continueToPage()" id="continue-btn" disabled>
                    Continue / Devam Et →
                </button>
            </div>
        </div>

        <!-- Footer -->
		<center>
        <div class="footer">
      	<small style="color:white;">Site <a href="https://www.rowdev.rf.gd" target="_blank" rel="noopener noreferrer">rowrain.dev</a>(canberk karaeski) tarafından geliştirilmiştir.</small><br>
      	<small style="color:white;">Projeyi tamamen açık kaynak olarak yayımlıyorum. https://github.com/RowRainDev<small>
        </div>
		</center>
    </main>

    <script>
        let selectedLanguage = null;

        function selectLanguage(language) {
            selectedLanguage = language;
            
            // Tüm seçeneklerden selected class'ını kaldır
            document.querySelectorAll('.language-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Tıklanan seçeneğe selected class'ı ekle
            document.getElementById(language + '-option').classList.add('selected');
            
            // Continue butonunu aktif et
            document.getElementById('continue-btn').disabled = false;
        }

        function continueToPage() {
            if (selectedLanguage === 'turkish') {
                window.location.href = 'turkish.php';
            } else if (selectedLanguage === 'english') {
                window.location.href = 'english.php';
            }
        }

        // Klavye desteği (Enter tuşu ile devam)
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && selectedLanguage !== null) {
                continueToPage();
            }
        });

        // Fade-in animation on load
        window.addEventListener('load', function() {
            document.querySelector('.language-page').style.opacity = '1';
        });
    </script>
</body>
</html>