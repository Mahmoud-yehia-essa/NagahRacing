<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>نجاح ريسنج - قريباً</title>
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: #0d0107;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }

        /* Full screen premium background blend */
        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(135deg, rgba(20, 2, 10, 0.95) 0%, rgba(76, 8, 37, 0.92) 50%, rgba(150, 17, 72, 0.85) 100%), url('{{ asset("backend/assets/images/login-images/bg-login-img.png") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -2;
        }

        /* Animated ambient glowing orbs */
        .orb {
            position: fixed;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }

        .orb-1 {
            top: -100px;
            right: -100px;
            background: #961148;
            animation: float 10s ease-in-out infinite alternate;
        }

        .orb-2 {
            bottom: -150px;
            left: -100px;
            background: #d4af37;
            animation: float 12s ease-in-out infinite alternate-reverse 2s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 40px) scale(1.15); }
        }

        /* Wrapper Container */
        .wrapper {
            max-width: 900px;
            width: 100%;
            padding: 50px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 1;
        }

        /* Closed Beta / Coming Soon Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(150, 17, 72, 0.2);
            border: 1px solid rgba(150, 17, 72, 0.4);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            color: #ff3e8b;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(150, 17, 72, 0.1);
            animation: pulse-glow 2s infinite alternate;
        }

        @keyframes pulse-glow {
            0% { box-shadow: 0 0 10px rgba(150, 17, 72, 0.2); }
            100% { box-shadow: 0 0 20px rgba(150, 17, 72, 0.5); }
        }

        /* Logo pulsating gold/burgundy frame */
        .logo-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin-bottom: 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: #ffffff;
            padding: 10px;
            box-shadow: 0 10px 30px rgba(150, 17, 72, 0.35);
            animation: constant-wobble 3s ease-in-out infinite alternate;
        }

        @keyframes constant-wobble {
            0% {
                transform: scale(1) rotate(0deg);
            }
            100% {
                transform: scale(1.06) rotate(3deg);
            }
        }

        /* Continuous glowing animation running around it */
        .logo-container::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: linear-gradient(0deg, #961148, #d4af37, #961148);
            z-index: -1;
            animation: rotate-glow 4s linear infinite;
        }

        .logo-container::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: linear-gradient(0deg, #961148, #d4af37, #961148);
            z-index: -1;
            filter: blur(12px);
            animation: rotate-glow 4s linear infinite;
            opacity: 0.85;
        }

        @keyframes rotate-glow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Main Typography */
        h1 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff 30%, #fcd6e4 70%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .teaser-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #d4af37;
            margin-bottom: 25px;
        }

        .teaser-description-box {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 35px 30px;
            max-width: 800px;
            width: 100%;
            margin-bottom: 45px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .teaser-description {
            font-size: 1.2rem;
            line-height: 1.9;
            color: #f1f5f9;
            font-weight: 500;
            text-align: justify;
            text-align-last: center;
        }

        /* Stores / Download Area */
        .download-box {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 35px 50px;
            width: 100%;
            max-width: 650px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .download-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 25px;
            letter-spacing: 0.5px;
        }

        .stores {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .stores a {
            display: inline-flex;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .stores img {
            height: 52px;
            width: auto;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .stores a:hover {
            transform: translateY(-4px) scale(1.05);
        }

        .stores a:hover img {
            box-shadow: 0 8px 25px rgba(150, 17, 72, 0.45);
            border-color: #961148;
            filter: brightness(1.1);
        }

        /* Footer */
        footer {
            margin-top: 60px;
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 600;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 20px;
            width: 100%;
            max-width: 800px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.3rem;
            }
            
            .teaser-title {
                font-size: 1.4rem;
            }

            .teaser-description {
                font-size: 1.05rem;
                line-height: 1.8;
            }

            .download-box {
                padding: 30px 20px;
            }

            .stores img {
                height: 46px;
            }
        }
    </style>
</head>
<body>

    <!-- Background Graphics -->
    <div class="bg-overlay"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="wrapper">
        
        <!-- Private Beta status -->
        <div class="status-badge">
            <i class="fa-solid fa-rocket"></i>
            <span>جاري التجهيز للإطلاق الرسمي قريباً</span>
        </div>

        <!-- Pulser Logo -->
        <div class="logo-container">
            <img src="{{ asset('backend/assets/images/login-images/logo_maragin.png') }}" alt="شعار نجاح ريسنج" class="logo" />
        </div>

        <!-- Branding Heading -->
        <h1>نجاح ريسنج</h1>
        <h2 class="teaser-title">عصر جديد لرصد وتتبع تدريب الهجن</h2>
        
        <!-- Teaser Description Box -->
        <div class="teaser-description-box">
            <p class="teaser-description">
                تطبيقك المتكامل لرصد وتتبع التدريب بدقة عالية. يتيح لك التطبيق تحديد الموقع بشكل مباشر للتتبع الدقيق للمطايا عبر الخريطة، مع رصد وتحديد السرعة، الوقت ومستوى الأداء أولاً بأول. بالإضافة إلى توفير سجل متكامل وموثق لكل ما تم من تدريب للعودة إليه في أي وقت - حتى لو فاتك وقت التدريب الفعلي - والعديد من التحليلات الهامة لرفع كفاءة ومستوى المطايا وإعدادها للمنافسات.
            </p>
        </div>

        <!-- Download & Stores box -->
        <div class="download-box">
            <div class="download-title">التطبيق سيكون متوفراً قريباً على المتاجر الرسمية</div>
            <div class="stores">
                
                <!-- App Store -->
                <a href="#">
                    <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store" />
                </a>

                <!-- Google Play -->
                <a href="#">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" />
                </a>

            </div>
        </div>

        <!-- Footer -->
        <footer>
            © {{ date('Y') }} جميع الحقوق محفوظة لتطبيق نجاح ريسنج
        </footer>

    </div>

</body>
</html>
