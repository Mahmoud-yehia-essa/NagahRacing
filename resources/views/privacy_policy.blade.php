<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية | Privacy Policy - Nagah Racing</title>
    
    <!-- Meta tags for SEO & App Store -->
    <meta name="description" content="سياسة الخصوصية وحماية بيانات المستخدمين لتطبيق نجاح ريسنج (Nagah Racing) لسباقات وتضمير الهجن والفانتسي.">
    <meta name="keywords" content="نجاح ريسنج, سياسة الخصوصية, سباقات الهجن, Nagah Racing, Privacy Policy">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg-main: #1A040B;
            --bg-card: #2B0814;
            --bg-card-hover: #3B1220;
            --border-card: #5A2033;
            --gold-primary: #E9C349;
            --gold-light: #FFDF78;
            --gold-dark: #C69E2E;
            --text-primary: #FFFFFF;
            --text-secondary: rgba(255, 255, 255, 0.85);
            --text-muted: rgba(255, 255, 255, 0.6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', 'Outfit', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.7;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(233, 195, 73, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(90, 32, 51, 0.3) 0%, transparent 50%);
            background-attachment: fixed;
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 24px 20px;
            flex: 1;
        }

        /* Header Bar */
        .top-navbar {
            background: rgba(43, 8, 20, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(233, 195, 73, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 14px 20px;
        }

        .nav-content {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--gold-primary);
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            color: var(--bg-card);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 12px rgba(233, 195, 73, 0.3);
        }

        .lang-switch {
            display: flex;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(233, 195, 73, 0.3);
            border-radius: 30px;
            padding: 3px;
            gap: 4px;
        }

        .lang-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .lang-btn.active {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            color: var(--bg-main);
            box-shadow: 0 2px 8px rgba(233, 195, 73, 0.3);
        }

        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(135deg, #4A1828 0%, #2B0814 100%);
            border: 1.5px solid rgba(233, 195, 73, 0.4);
            border-radius: 24px;
            padding: 32px 24px;
            text-align: center;
            margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(233, 195, 73, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(233, 195, 73, 0.15);
            border: 1px solid rgba(233, 195, 73, 0.5);
            color: var(--gold-light);
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .hero-title {
            font-size: 1.85rem;
            font-weight: 900;
            color: var(--gold-primary);
            margin-bottom: 8px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }

        .hero-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .last-updated {
            margin-top: 14px;
            font-size: 0.8rem;
            color: var(--text-muted);
            display: inline-block;
        }

        /* Sections Cards */
        .section-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            margin-bottom: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .section-card:hover {
            border-color: rgba(233, 195, 73, 0.5);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .section-header {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            gap: 14px;
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: rgba(233, 195, 73, 0.12);
            border: 1px solid rgba(233, 195, 73, 0.3);
            border-radius: 12px;
            color: var(--gold-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .section-card.active .section-icon {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            color: var(--bg-main);
            border-color: var(--gold-primary);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .section-card.active .section-title {
            color: var(--gold-primary);
        }

        .chevron-icon {
            color: var(--gold-primary);
            font-size: 1.1rem;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .section-card.active .chevron-icon {
            transform: rotate(180deg);
        }

        .section-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding 0.3s ease;
            padding: 0 20px;
            color: var(--text-secondary);
            font-size: 0.95rem;
            border-top: 1px solid transparent;
        }

        .section-card.active .section-body {
            max-height: 1000px;
            padding: 16px 20px 22px 20px;
            border-top-color: var(--border-card);
        }

        .section-body ul {
            list-style: none;
            padding: 0;
            margin-top: 8px;
        }

        .section-body li {
            position: relative;
            padding-right: 20px;
            margin-bottom: 10px;
        }

        [dir="ltr"] .section-body li {
            padding-right: 0;
            padding-left: 20px;
        }

        .section-body li::before {
            content: '✦';
            position: absolute;
            right: 0;
            top: 0;
            color: var(--gold-primary);
            font-size: 0.8rem;
        }

        [dir="ltr"] .section-body li::before {
            right: auto;
            left: 0;
        }

        /* Support Box */
        .support-box {
            background: linear-gradient(135deg, #4A1828 0%, #260A14 100%);
            border: 1px solid rgba(233, 195, 73, 0.3);
            border-radius: 20px;
            padding: 28px 24px;
            text-align: center;
            margin-top: 32px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .support-icon {
            font-size: 2.2rem;
            color: var(--gold-primary);
            margin-bottom: 12px;
            display: inline-block;
        }

        .support-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 6px;
        }

        .support-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
            max-width: 500px;
            margin: 0 auto 18px auto;
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark));
            color: #1A040B;
            padding: 12px 28px;
            border-radius: 30px;
            font-weight: 800;
            font-size: 0.95rem;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(233, 195, 73, 0.35);
            transition: all 0.3s ease;
        }

        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(233, 195, 73, 0.5);
        }

        /* Footer */
        footer {
            border-top: 1px solid rgba(90, 32, 51, 0.5);
            background: rgba(26, 4, 11, 0.9);
            padding: 20px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .footer-brand {
            color: var(--gold-primary);
            font-weight: 700;
        }

        /* Hide elements according to language */
        .content-en {
            display: none;
        }

        [lang="en"] .content-ar {
            display: none;
        }

        [lang="en"] .content-en {
            display: block;
        }

        @media (max-width: 600px) {
            .hero-title {
                font-size: 1.45rem;
            }
            .section-title {
                font-size: 1rem;
            }
            .container {
                padding: 16px 14px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="top-navbar">
        <div class="nav-content">
            <a href="{{ url('/') }}" class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <span>Nagah Racing</span>
            </a>

            <!-- Language Switcher -->
            <div class="lang-switch">
                <button class="lang-btn active" id="btn-ar" onclick="setLanguage('ar')">العربية</button>
                <button class="lang-btn" id="btn-en" onclick="setLanguage('en')">English</button>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">

        <!-- ================= ARABIC CONTENT ================= -->
        <div class="content-ar">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>وثيقة رسمية ومعتمدة</span>
                </div>
                <h1 class="hero-title">سياسة الخصوصية وحماية البيانات</h1>
                <p class="hero-subtitle">
                    نلتزم في تطبيق نجاح ريسنج بحماية خصوصيتك وضمان أمان بياناتك الشخصية وبيانات عزبتك وهجنك بأعلى المعايير الأمنية.
                </p>
                <span class="last-updated">آخر تحديث: أكتوبر 2026</span>
            </div>

            <!-- Accordion Sections -->
            <div class="section-card active">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h2 class="section-title">1. مقدمة والتزام الخصوصية</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    نحن في تطبيق <strong>نجاح ريسنج (Nagah Racing)</strong> نولي أهمية قصوى لخصوصية مستخدمينا من ملاك الهجن، والمضمرين، والمستخدمين الفرعيين، وعشاق مسابقات الفانتسي. تهدف هذه الوثيقة إلى توضيح الشفافية الكاملة لكيفية جمع بياناتك، واستخدامها، وتخزينها، وحمايتها عند استخدام التطبيق والخدمات المرتبطة به.
                </div>
            </div>

            <div class="section-card active">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-database"></i></div>
                        <h2 class="section-title">2. البيانات التي نقوم بجمعها</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    <ul>
                        <li><strong>بيانات الحساب الشخصي:</strong> الاسم الكامل، رقم الهاتف، البريد الإلكتروني، ونوع الحساب (مالك، مضمر/عامل، مستفيد فرعي).</li>
                        <li><strong>بيانات الهجن والسباقات:</strong> أسماء المطايا، سجلات الأشواط، الفئات، والإحصائيات الخاصة بالأداء.</li>
                        <li><strong>بيانات جلسات التدريب:</strong> سجلات التمارين والتضمير، المسافات المقطوعة، السرعات اللحظية والمتوسطة، والزمن المنقضي.</li>
                        <li><strong>بيانات تسجيل الدخول الاجتماعي:</strong> المعرف الرقمي الموثق المشفر عبر (Apple ID أو Google) لتأمين الدخول السريع دون الاطلاع على كلمات المرور الخاصة بك.</li>
                    </ul>
                </div>
            </div>

            <div class="section-card active">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-location-crosshairs"></i></div>
                        <h2 class="section-title">3. بيانات الموقع الجغرافي (GPS) والتتبع الميداني</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    يستخدم التطبيق خدمات تحديد الموقع الجغرافي (GPS) بدقة عالية <strong>أثناء تشغيل جلسات التدريب الميداني الحية فقط</strong>، وذلك بغرض:
                    <ul>
                        <li>تتبع مسار ركض الهجن وسيارات التدريب المرافقة بدقة على الخريطة التفاعلية.</li>
                        <li>حساب السرعة اللحظية، متوسط السرعة، ومسافة الشوط بدقة.</li>
                        <li><strong>تنبيه هام:</strong> لا يتم تتبع موقعك الجغرافي نهائياً خارج أوقات جلسات التدريب النشطة التي تبدأها بنفسك.</li>
                    </ul>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-headset"></i></div>
                        <h2 class="section-title">4. الاتصال الصوتي واستخدام الميكروفون</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    يوفر التطبيق ميزة التواصل الصوتي اللاسلكي المباشر أثناء التدريب (عبر شبكة Agora المشفرة). يتم طلب إذن استخدام <strong>الميكروفون (Microphone)</strong> حصراً لإجراء المكالمة وتوجيه التعليمات الصوتية الحية بين المالك والعامل الميداني، ولا يتم تسجيل أو تخزين المكالمات الصوتية على خوادمنا نهائياً.
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-lock"></i></div>
                        <h2 class="section-title">5. أمن وحماية البيانات</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    نطبق أعلى المعايير الأمنية وبروتوكولات التشفير العالمية (SSL/TLS 256-bit) لحماية كافة الاتصالات ونقل البيانات بين التطبيق والخوادم. نلتزم بعدم بيع، تأجير، أو مشاركة أي بيانات تخص المستخدمين أو العزب مع أي طرف ثالث تجاري.
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-user-xmark"></i></div>
                        <h2 class="section-title">6. حقوق المستخدم وحذف الحساب نهائياً</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    يحق لك في أي وقت تعديل بياناتك أو طلب حذف حسابك بالكامل. يوفر التطبيق خياراً مباشراً وموثقاً لـ <strong>(حذف الحساب نهائياً)</strong> من خلال القائمة الجانبية داخل التطبيق، أو عبر التواصل مع الدعم الفني، وسيتم حذف كافة بيانات الحساب والجلسات نهائياً من خوادمنا بما يتوافق مع سياسات متجر Apple App Store.
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <h2 class="section-title">7. التحديثات والتعديلات</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    قد نقوم بتحديث هذه السياسة دورياً لمواكبة التحديثات التقنية والقانونية. سيتم إشعار المستخدمين بأي تعديلات جوهرية عبر إشعار رسمي داخل التطبيق.
                </div>
            </div>

            <!-- Support Box -->
            <div class="support-box">
                <i class="fa-solid fa-headset support-icon"></i>
                <h3 class="support-title">هل لديك أي استفسار حول سياسة الخصوصية؟</h3>
                <p class="support-desc">فريق الدعم الفني لتطبيق نجاح ريسنج جاهز للإجابة على جميع تساؤلاتك ومساعدتك على مدار الساعة.</p>
                <a href="https://wa.me/96551673464" target="_blank" class="whatsapp-btn">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i>
                    <span>تواصل مع الدعم عبر واتساب</span>
                </a>
            </div>
        </div>

        <!-- ================= ENGLISH CONTENT ================= -->
        <div class="content-en">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Official Privacy Policy</span>
                </div>
                <h1 class="hero-title">Privacy Policy & Data Protection</h1>
                <p class="hero-subtitle">
                    At Nagah Racing, we are committed to safeguarding your privacy and protecting your personal and training telemetry data with the highest security standards.
                </p>
                <span class="last-updated">Last Updated: October 2026</span>
            </div>

            <!-- Accordion Sections -->
            <div class="section-card active">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h2 class="section-title">1. Introduction & Commitment</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    <strong>Nagah Racing</strong> provides an advanced telemetry, management, and fantasy platform for camel racing owners, trainers, and enthusiasts. This Privacy Policy details how we collect, use, store, and protect your information when utilizing our mobile application and related services.
                </div>
            </div>

            <div class="section-card active">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-database"></i></div>
                        <h2 class="section-title">2. Information We Collect</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    <ul>
                        <li><strong>Account Information:</strong> Full name, phone number, email address, and role type (Owner, Trainer/Worker, Sub-User).</li>
                        <li><strong>Camel & Racing Data:</strong> Camel names, categories, round histories, and racing performance statistics.</li>
                        <li><strong>Training Telemetry:</strong> Distance traveled, real-time speed, average speed, and session duration.</li>
                        <li><strong>Social Authentication:</strong> Secure encrypted identifier tokens via (Apple ID or Google Sign-In) for seamless authentication.</li>
                    </ul>
                </div>
            </div>

            <div class="section-card active">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-location-crosshairs"></i></div>
                        <h2 class="section-title">3. Precise Location (GPS) Telemetry</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    The app accesses precise GPS location data <strong>solely during active live training sessions</strong> to:
                    <ul>
                        <li>Plot and track live running paths and training tracks on interactive maps.</li>
                        <li>Calculate instantaneous velocity, average speeds, and distance accurately.</li>
                        <li><strong>Notice:</strong> We do NOT track or store your location outside active training sessions.</li>
                    </ul>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-headset"></i></div>
                        <h2 class="section-title">4. Real-time Audio & Microphone Permissions</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    Nagah Racing provides two-way walkie-talkie audio streaming via Agora RTC during training. <strong>Microphone permission</strong> is requested exclusively to facilitate live voice transmission between camel owners and field trainers. Voice calls are encrypted in transit and are NEVER recorded or stored on our servers.
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-lock"></i></div>
                        <h2 class="section-title">5. Data Security & Encryption</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    We employ industry-leading SSL/TLS encryption protocols to secure all network communications. Your data is stored securely, and we never sell, rent, or distribute personal or telemetry data to any third parties.
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-user-xmark"></i></div>
                        <h2 class="section-title">6. User Rights & Account Deletion</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    You maintain the right to view, update, or permanently delete your account and all associated telemetry records at any time. A dedicated <strong>"Delete Account"</strong> option is available directly inside the app settings, fully compliant with Apple App Store policies.
                </div>
            </div>

            <div class="section-card">
                <div class="section-header" onclick="toggleSection(this)">
                    <div class="section-header-left">
                        <div class="section-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <h2 class="section-title">7. Policy Amendments</h2>
                    </div>
                    <i class="fa-solid fa-chevron-down chevron-icon"></i>
                </div>
                <div class="section-body">
                    We may update this policy periodically to reflect platform enhancements and regulatory requirements. Users will be notified of substantial revisions via in-app alerts.
                </div>
            </div>

            <!-- Support Box -->
            <div class="support-box">
                <i class="fa-solid fa-headset support-icon"></i>
                <h3 class="support-title">Have Questions About Your Privacy?</h3>
                <p class="support-desc">Our support team is always available to help answer your inquiries and ensure a secure experience.</p>
                <a href="https://wa.me/96551673464" target="_blank" class="whatsapp-btn">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i>
                    <span>Contact Support on WhatsApp</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 <span class="footer-brand">Nagah Racing</span>. All rights reserved. | جميع الحقوق محفوظة لتطبيق نجاح ريسنج</p>
    </footer>

    <!-- Interactive Script -->
    <script>
        function toggleSection(element) {
            const card = element.closest('.section-card');
            card.classList.toggle('active');
        }

        function setLanguage(lang) {
            const html = document.documentElement;
            const btnAr = document.getElementById('btn-ar');
            const btnEn = document.getElementById('btn-en');

            if (lang === 'en') {
                html.setAttribute('lang', 'en');
                html.setAttribute('dir', 'ltr');
                btnEn.classList.add('active');
                btnAr.classList.remove('active');
            } else {
                html.setAttribute('lang', 'ar');
                html.setAttribute('dir', 'rtl');
                btnAr.classList.add('active');
                btnEn.classList.remove('active');
            }
        }
    </script>
</body>
</html>
