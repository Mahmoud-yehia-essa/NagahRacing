<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نجاح ريسنج | تطبيق رصد وتتبع تدريب الهجن لحظة بلحظة</title>
    <meta name="description" content="تطبيق نجاح ريسنج — منصتك المتكاملة لرصد وتتبع تدريب الهجن بدقة عالية عبر الخريطة، مع خرائط بدون إنترنت للوضع الصحراوي، مكالمات صوتية مشفرة، ولوحة تحكم شاملة للملاك والعمال.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="نجاح ريسنج | لرصد وتتبع تدريب الهجن">
    <meta property="og:description" content="تتبع مباشر للسرعة والمسافة والوقت، خرائط بدون إنترنت للوضع الصحراوي، ومكالمات صوتية مشفرة مع العمال.">
    <meta property="og:image" content="{{ asset('landing/banner.jpeg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#1A040B">

    <link rel="icon" href="{{ asset('backend/assets/images/logo-icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-deep: #140308;
            --bg-dark: #1E050D;
            --bg-surface: #2B0914;
            --bg-card: #340C19;
            --bg-card-hover: #3F1020;
            --brand-crimson: #971048;
            --brand-crimson-light: #B8175A;
            --gold: #E9C349;
            --gold-bright: #FAD96B;
            --gold-deep: #C49B26;
            --text-main: #FFFFFF;
            --text-soft: #E5D4DA;
            --text-muted: #B698A3;
            --border-gold: rgba(233, 195, 73, 0.24);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --shadow-gold: 0 12px 35px rgba(233, 195, 73, 0.16);
            --shadow-deep: 0 24px 60px rgba(0, 0, 0, 0.55);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-main);
            line-height: 1.7;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            display: block;
        }

        .container {
            width: 100%;
            max-width: 1220px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Ambient Background Glows */
        .page-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(circle at 85% 12%, rgba(151, 16, 72, 0.28) 0%, transparent 45%),
                radial-gradient(circle at 12% 38%, rgba(233, 195, 73, 0.11) 0%, transparent 42%),
                radial-gradient(circle at 75% 78%, rgba(151, 16, 72, 0.22) 0%, transparent 48%),
                linear-gradient(180deg, #140308 0%, #1C050C 50%, #120207 100%);
        }

        /* ================= NAVBAR ================= */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            background: rgba(20, 3, 8, 0.78);
            border-bottom: 1px solid rgba(233, 195, 73, 0.16);
            transition: background 0.25s ease;
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 76px;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 20px;
            color: var(--gold);
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid var(--gold);
            object-fit: cover;
            box-shadow: 0 0 18px rgba(233, 195, 73, 0.3);
            background: var(--brand-crimson);
        }

        .brand-sub {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            line-height: 1.1;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 26px;
            list-style: none;
        }

        .nav-links a {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--text-soft);
            transition: color 0.2s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--gold);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 999px;
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, border-color 0.2s ease;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-bright) 0%, var(--gold) 55%, var(--gold-deep) 100%);
            color: #1D060D;
            box-shadow: 0 8px 24px rgba(233, 195, 73, 0.28);
        }

        .btn-gold:hover {
            box-shadow: 0 12px 30px rgba(233, 195, 73, 0.42);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-soft);
            border-color: rgba(255, 255, 255, 0.14);
        }

        .btn-ghost:hover {
            background: rgba(233, 195, 73, 0.12);
            border-color: var(--gold);
            color: var(--gold);
        }

        .menu-toggle {
            display: none;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-gold);
            color: var(--gold);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            font-size: 24px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .mobile-drawer {
            display: none;
            padding: 16px 20px 22px;
            border-top: 1px solid var(--border-subtle);
            background: rgba(20, 3, 8, 0.96);
        }

        .mobile-drawer.open {
            display: block;
        }

        .mobile-drawer > a {
            display: block;
            padding: 11px 0;
            font-weight: 600;
            color: var(--text-soft);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .mobile-drawer .mobile-cta {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mobile-drawer .mobile-cta .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            text-align: center;
        }

        .mobile-drawer .mobile-cta .btn-gold {
            color: #1D060D !important;
            font-weight: 800;
        }

        .mobile-drawer .mobile-cta .btn-ghost {
            color: #FFFFFF !important;
            font-weight: 700;
        }

        /* ================= STORE BUTTONS ================= */
        .store-buttons {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
        }

        .store-badge-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            border: 1.5px solid rgba(233, 195, 73, 0.35);
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.42);
            transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
            background: #0c0c24;
        }

        .store-badge-link:hover {
            transform: translateY(-3px) scale(1.02);
            border-color: var(--gold);
            box-shadow: 0 14px 34px rgba(233, 195, 73, 0.28);
        }

        .store-badge-link img {
            height: 56px;
            width: auto;
            display: block;
        }

        /* ================= HERO ================= */
        .hero {
            padding: 64px 0 72px;
            position: relative;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 48px;
            align-items: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 999px;
            background: rgba(233, 195, 73, 0.12);
            border: 1px solid rgba(233, 195, 73, 0.35);
            color: var(--gold-bright);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .hero-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22C55E;
            box-shadow: 0 0 10px #22C55E;
        }

        .hero h1 {
            font-size: clamp(32px, 4.4vw, 52px);
            font-weight: 900;
            line-height: 1.24;
            margin-bottom: 18px;
        }

        .hero h1 .gold-text {
            background: linear-gradient(135deg, #FFF0B3 0%, var(--gold) 55%, #D3A329 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            font-size: 17px;
            color: var(--text-soft);
            margin-bottom: 28px;
            max-width: 560px;
        }

        .hero-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 26px;
        }

        .highlight-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle);
            font-size: 13px;
            font-weight: 600;
            color: var(--text-soft);
        }

        .highlight-chip i {
            color: var(--gold);
            font-size: 17px;
        }

        .hero-visual {
            position: relative;
        }

        .hero-banner-card {
            position: relative;
            border-radius: 26px;
            padding: 12px;
            background: linear-gradient(145deg, rgba(233, 195, 73, 0.25), rgba(151, 16, 72, 0.28), rgba(20, 3, 8, 0.9));
            box-shadow: var(--shadow-deep), var(--shadow-gold);
        }

        .hero-banner-inner {
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            background: #1A040B;
        }

        .hero-banner-inner img {
            width: 100%;
            height: auto;
            display: block;
        }

        .hero-video-fab {
            position: absolute;
            bottom: 16px;
            left: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 999px;
            background: rgba(20, 3, 8, 0.86);
            backdrop-filter: blur(10px);
            border: 1px solid var(--gold);
            color: var(--gold-bright);
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .hero-video-fab:hover {
            transform: scale(1.04);
            background: var(--brand-crimson);
            color: #fff;
        }

        .hero-video-fab i {
            font-size: 20px;
        }

        .hero-stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .hero-stat {
            background: rgba(43, 9, 20, 0.78);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 12px 14px;
            text-align: center;
        }

        .hero-stat strong {
            display: block;
            color: var(--gold);
            font-size: 18px;
            font-weight: 800;
        }

        .hero-stat span {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* ================= SECTION HEADERS ================= */
        .section {
            padding: 76px 0;
            position: relative;
        }

        .section-head {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 44px;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 999px;
            background: rgba(151, 16, 72, 0.28);
            border: 1px solid rgba(233, 195, 73, 0.28);
            color: var(--gold);
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .section-head h2 {
            font-size: clamp(26px, 3.2vw, 38px);
            font-weight: 900;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .section-head p {
            color: var(--text-muted);
            font-size: 15.5px;
        }

        /* ================= VIDEO THEATER ================= */
        .video-section {
            background: linear-gradient(180deg, transparent 0%, rgba(52, 12, 25, 0.48) 50%, transparent 100%);
        }

        .video-frame-outer {
            max-width: 940px;
            margin: 0 auto;
            padding: 12px;
            border-radius: 26px;
            background: linear-gradient(135deg, rgba(233, 195, 73, 0.35), rgba(151, 16, 72, 0.35), rgba(255, 255, 255, 0.06));
            box-shadow: var(--shadow-deep), 0 0 50px rgba(151, 16, 72, 0.25);
        }

        .video-wrapper {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: #090104;
            aspect-ratio: 16 / 9;
        }

        .video-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .video-play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background: radial-gradient(circle, rgba(20, 3, 8, 0.35) 0%, rgba(20, 3, 8, 0.72) 100%);
            cursor: pointer;
            transition: opacity 0.25s ease;
        }

        .video-play-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .play-circle {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold-bright), var(--gold-deep));
            color: #1A040B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            box-shadow: 0 0 0 12px rgba(233, 195, 73, 0.22), 0 14px 35px rgba(0, 0, 0, 0.5);
            transition: transform 0.25s ease;
        }

        .video-play-overlay:hover .play-circle {
            transform: scale(1.08);
        }

        .play-label {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            background: rgba(20, 3, 8, 0.72);
            padding: 6px 18px;
            border-radius: 999px;
            border: 1px solid rgba(233, 195, 73, 0.35);
        }

        /* ================= FEATURES GRID ================= */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature-card {
            background: linear-gradient(160deg, rgba(52, 12, 25, 0.85) 0%, rgba(26, 4, 11, 0.92) 100%);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 26px 22px;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--border-gold);
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.38);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(233, 195, 73, 0.18), rgba(151, 16, 72, 0.28));
            border: 1px solid rgba(233, 195, 73, 0.3);
            color: var(--gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 16px;
        }

        .feature-card h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #fff;
        }

        .feature-card p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.75;
        }

        /* ================= INTERACTIVE SCREENS SHOWCASE ================= */
        .showcase-stage {
            display: grid;
            grid-template-columns: 0.92fr 1.08fr;
            gap: 36px;
            align-items: center;
            background: linear-gradient(155deg, rgba(45, 10, 22, 0.88), rgba(18, 3, 8, 0.96));
            border: 1px solid var(--border-gold);
            border-radius: 28px;
            padding: 32px;
            box-shadow: var(--shadow-deep);
        }

        .showcase-phone-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .showcase-image-box {
            width: 100%;
            max-width: 340px;
            border-radius: 24px;
            overflow: hidden;
            border: 2px solid rgba(233, 195, 73, 0.45);
            box-shadow: 0 22px 55px rgba(0, 0, 0, 0.65), 0 0 36px rgba(233, 195, 73, 0.12);
            cursor: zoom-in;
            position: relative;
            background: #1A040B;
        }

        .showcase-image-box img {
            width: 100%;
            height: auto;
            display: block;
            transition: opacity 0.22s ease, transform 0.3s ease;
        }

        .showcase-image-box:hover img {
            transform: scale(1.02);
        }

        .zoom-hint {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(20, 3, 8, 0.82);
            color: var(--gold);
            border: 1px solid rgba(233, 195, 73, 0.35);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 11.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .showcase-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .showcase-step-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: flex-start;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(233, 195, 73, 0.14);
            border: 1px solid rgba(233, 195, 73, 0.35);
            color: var(--gold);
            font-size: 13px;
            font-weight: 700;
        }

        .showcase-info h3 {
            font-size: clamp(24px, 2.8vw, 32px);
            font-weight: 900;
            color: #fff;
            line-height: 1.3;
        }

        .showcase-info .lead {
            color: var(--text-soft);
            font-size: 15.5px;
            line-height: 1.85;
        }

        .showcase-bullets {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 4px 0 8px;
        }

        .showcase-bullets li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14.5px;
            color: var(--text-soft);
        }

        .showcase-bullets i {
            color: var(--gold);
            font-size: 20px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .showcase-nav-btns {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
        }

        .circle-nav-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-gold);
            color: var(--gold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .circle-nav-btn:hover {
            background: var(--gold);
            color: #1A040B;
        }

        /* 7-Screen Thumbnails Strip */
        .screens-strip {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 12px;
            margin-top: 24px;
        }

        .screen-thumb {
            background: rgba(38, 7, 17, 0.85);
            border: 2px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.22s ease;
        }

        .screen-thumb:hover {
            border-color: rgba(233, 195, 73, 0.5);
            transform: translateY(-3px);
        }

        .screen-thumb.active {
            border-color: var(--gold);
            background: linear-gradient(160deg, rgba(151, 16, 72, 0.35), rgba(233, 195, 73, 0.14));
            box-shadow: 0 10px 24px rgba(233, 195, 73, 0.18);
        }

        .screen-thumb img {
            width: 100%;
            aspect-ratio: 9 / 16;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .screen-thumb span {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-soft);
            line-height: 1.35;
        }

        .screen-thumb.active span {
            color: var(--gold-bright);
        }

        /* ================= HOW IT WORKS ================= */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .step-card {
            background: rgba(38, 7, 17, 0.78);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 28px 24px;
            position: relative;
        }

        .step-number {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--gold-bright), var(--gold-deep));
            color: #1A040B;
            font-size: 20px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow: 0 8px 20px rgba(233, 195, 73, 0.25);
        }

        .step-card h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .step-card p {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* ================= DOWNLOAD CTA ================= */
        .cta-box {
            background: linear-gradient(135deg, rgba(151, 16, 72, 0.42) 0%, rgba(43, 9, 20, 0.94) 55%, rgba(233, 195, 73, 0.16) 100%);
            border: 1px solid rgba(233, 195, 73, 0.4);
            border-radius: 28px;
            padding: 54px 36px;
            text-align: center;
            box-shadow: var(--shadow-deep);
            position: relative;
            overflow: hidden;
        }

        .cta-box img.cta-logo {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            border: 2.5px solid var(--gold);
            margin: 0 auto 18px;
            box-shadow: 0 0 30px rgba(233, 195, 73, 0.35);
            object-fit: cover;
        }

        .cta-box h2 {
            font-size: clamp(26px, 3.4vw, 40px);
            font-weight: 900;
            margin-bottom: 12px;
        }

        .cta-box p {
            color: var(--text-soft);
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .cta-box .store-buttons {
            justify-content: center;
        }

        /* ================= FOOTER ================= */
        .footer {
            border-top: 1px solid rgba(233, 195, 73, 0.15);
            padding: 34px 0;
            margin-top: 40px;
            background: rgba(12, 2, 5, 0.72);
        }

        .footer-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 13.5px;
            color: var(--text-muted);
        }

        .footer-links a:hover {
            color: var(--gold);
        }

        .copyright {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* ================= LIGHTBOX MODAL ================= */
        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(10, 2, 5, 0.92);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .lightbox.open {
            display: flex;
        }

        .lightbox-inner {
            position: relative;
            max-width: 440px;
            width: 100%;
            text-align: center;
        }

        .lightbox-inner img {
            width: 100%;
            max-height: 82vh;
            object-fit: contain;
            border-radius: 22px;
            border: 2px solid var(--gold);
            margin: 0 auto;
        }

        .lightbox-close {
            position: absolute;
            top: -16px;
            left: -16px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--gold);
            color: #1A040B;
            border: none;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 992px) {
            .hero-grid,
            .showcase-stage {
                grid-template-columns: 1fr;
            }

            .features-grid,
            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .screens-strip {
                grid-template-columns: repeat(4, 1fr);
            }

            .nav-links,
            .nav-actions {
                display: none;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .hero {
                padding: 36px 0 54px;
            }

            .showcase-stage {
                padding: 22px;
            }
        }

        @media (max-width: 600px) {
            .features-grid,
            .steps-grid {
                grid-template-columns: 1fr;
            }

            .screens-strip {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                padding-bottom: 8px;
                gap: 10px;
            }

            .screen-thumb {
                flex: 0 0 112px;
                scroll-snap-align: start;
            }

            .store-badge-link img {
                height: 50px;
            }

            .hero-stats-bar {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
            }

            .section {
                padding: 54px 0;
            }

            .cta-box {
                padding: 36px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="page-bg"></div>

    <!-- ================= NAVBAR ================= -->
    <header class="navbar">
        <div class="container navbar-inner">
            <a href="#top" class="brand">
                <img src="{{ asset('backend/assets/images/logo-icon.png') }}" alt="نجاح ريسنج" class="brand-logo">
                <div>
                    نجاح ريسنج
                    <span class="brand-sub">لرصد وتتبع تدريب الهجن</span>
                </div>
            </a>

            <ul class="nav-links">
                <li><a href="#video">الفيديو التعريفي</a></li>
                <li><a href="#features">المميزات</a></li>
                <li><a href="#screens">شاشات التطبيق</a></li>
                <li><a href="#how-it-works">كيف يعمل</a></li>
                <li><a href="#download">تحميل التطبيق</a></li>
            </ul>

            <div class="nav-actions">
                <a href="#download" class="btn btn-gold">
                    <i class="bx bx-download"></i> حمّل التطبيق
                </a>
            </div>

            <button type="button" class="menu-toggle" id="mobileMenuBtn" aria-label="القائمة">
                <i class="bx bx-menu"></i>
            </button>
        </div>

        <nav class="mobile-drawer" id="mobileDrawer">
            <a href="#video" class="drawer-link">الفيديو التعريفي</a>
            <a href="#features" class="drawer-link">المميزات</a>
            <a href="#screens" class="drawer-link">شاشات التطبيق</a>
            <a href="#how-it-works" class="drawer-link">كيف يعمل</a>
            <a href="#download" class="drawer-link">تحميل التطبيق</a>
            <div class="mobile-cta">
                <a href="#download" class="btn btn-gold drawer-link" style="flex:1;">
                    <i class="bx bx-download"></i> حمّل التطبيق الآن
                </a>
            </div>
        </nav>
    </header>

    <!-- ================= HERO ================= -->
    <section class="hero" id="top">
        <div class="container hero-grid">
            <div>
                <h1>
                    تابع <span class="gold-text">مطاياك وتدريباتك</span>
                    لحظة بلحظة… حتى في قلب الصحراء
                </h1>

                <p class="hero-desc">
                    الحل الذكي والمتكامل لملاك ومدربي الهجن: تتبع مباشر على الخريطة للسرعة والمسافة والوقت،
                    خرائط بدون إنترنت للوضع الصحراوي، إدارة كاملة للعمال والمستخدمين الفرعيين، ومكالمات صوتية مشفرة بضغطة زر.
                </p>

                <div class="store-buttons">
                    <a href="https://apps.apple.com/us/app/%D9%86%D8%AC%D8%A7%D8%AD-%D8%B1%D9%8A%D8%B3%D9%86%D8%AC/id6818084350"
                       target="_blank" rel="noopener noreferrer" class="store-badge-link" aria-label="Download on the App Store">
                        <img src="{{ asset('landing/app-store-badge.png') }}" alt="Download on the App Store">
                    </a>

                    <a href="https://play.google.com/store/apps/details?id=com.najahracing.app"
                       target="_blank" rel="noopener noreferrer" class="store-badge-link" aria-label="Get it on Google Play">
                        <img src="{{ asset('landing/google-play-badge.png') }}" alt="Get it on Google Play">
                    </a>
                </div>

                <div class="hero-highlights">
                    <span class="highlight-chip"><i class="bx bx-radar"></i> تتبع GPS مباشر</span>
                    <span class="highlight-chip"><i class="bx bx-wifi-off"></i> خرائط بدون إنترنت</span>
                    <span class="highlight-chip"><i class="bx bx-phone-call"></i> اتصال صوتي مشفر</span>
                    <span class="highlight-chip"><i class="bx bx-group"></i> مالك • عامل • مستخدم فرعي</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-banner-card">
                    <div class="hero-banner-inner">
                        <img src="{{ asset('landing/banner.jpeg') }}" alt="تطبيق نجاح ريسنج لرصد وتتبع تدريب الهجن">
                        <a href="#video" class="hero-video-fab" id="heroWatchVideoBtn">
                            <i class="bx bx-play-circle"></i> شاهد الفيديو التعريفي
                        </a>
                    </div>
                </div>

                <div class="hero-stats-bar">
                    <div class="hero-stat">
                        <strong>GPS مباشر</strong>
                        <span>سرعة • مسافة • وقت</span>
                    </div>
                    <div class="hero-stat">
                        <strong>وضع صحراوي</strong>
                        <span>يعمل بدون إنترنت</span>
                    </div>
                    <div class="hero-stat">
                        <strong>3 أدوار ذكية</strong>
                        <span>مالك • عامل • فرعي</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= INTRO VIDEO ================= -->
    <section class="section video-section" id="video">
        <div class="container">
            <div class="section-head">
                <span class="section-kicker"><i class="bx bx-movie-play"></i> جولة مرئية سريعة</span>
                <h2>تعرّف على نجاح ريسنج في دقيقة ونصف</h2>
                <p>شاهد كيف يجمع التطبيق بين سهولة الاستخدام ودقة التتبع الميداني في المضامير والصحراء.</p>
            </div>

            <div class="video-frame-outer">
                <div class="video-wrapper">
                    <video id="introVideo"
                           poster="{{ asset('landing/banner.jpeg') }}"
                           preload="metadata"
                           playsinline
                           controls>
                        <source src="{{ asset('landing/intro-video.mp4') }}" type="video/mp4">
                        متصفحك لا يدعم تشغيل الفيديو.
                    </video>

                    <div class="video-play-overlay" id="videoPlayOverlay">
                        <div class="play-circle">
                            <i class="bx bx-play" style="margin-right: -4px;"></i>
                        </div>
                        <span class="play-label">اضغط لتشغيل الفيديو التعريفي (1:24)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= CORE FEATURES ================= -->
    <section class="section" id="features">
        <div class="container">
            <div class="section-head">
                <span class="section-kicker"><i class="bx bx-star"></i> لماذا نجاح ريسنج؟</span>
                <h2>كل ما تحتاجه لإدارة ومتابعة تدريبات الهجن باحترافية</h2>
                <p>صُمّم التطبيق خصيصاً لطبيعة ميادين الهجن، ليعمل بدقة عالية سواء بتغطية إنترنت كاملة أو في عمق الصحراء.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bx bx-map-pin"></i></div>
                    <h3>تتبع مباشر على الخريطة</h3>
                    <p>تابع موقع المطية لحظة بلحظة مع عرض السرعة الحالية، متوسط السرعة، المسافة المقطوعة، وزمن التدريب أولاً بأول.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bx bx-cloud-download"></i></div>
                    <h3>الوضع الصحراوي وخرائط بدون إنترنت</h3>
                    <p>حمّل خريطة منطقتك مسبقاً بنطاقات مرنة (6 / 9 / 12 كم) ليواصل العامل تسجيل التدريب دون إنترنت وتتم المزامنة تلقائياً فور عودة الشبكة.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bx bx-user-check"></i></div>
                    <h3>حساب مخصص لكل دور</h3>
                    <p>نظام متكامل يربط بين المالك / المدرب، والعمال المسؤولين عن التدريب، والمستخدمين الفرعيين لمتابعة السير الميداني بوضوح تام.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bx bx-phone-call"></i></div>
                    <h3>مكالمات صوتية آمنة ومشفرة</h3>
                    <p>تواصل مباشرة مع العامل بضغطة زر داخل التطبيق أثناء الشوط لتقديم التوجيهات الفورية بأعلى جودة وخصوصية.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bx bx-group"></i></div>
                    <h3>إدارة سهلة للعمال وحالة الاتصال</h3>
                    <p>أضف العمال خلال ثوانٍ، انسخ كود الدخول الخاص بكل عامل، وراقب حالتهم الفورية (متصل الآن / في الخلفية / غير متصل).</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="bx bx-line-chart"></i></div>
                    <h3>سجل تدريبات وتحليلات موثقة</h3>
                    <p>احتفظ بأرشيف كامل لكل جولات التدريب بمساراتها وسرعاتها وملخصات العامل، لتقييم تطور مستوى المطايا في أي وقت.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= INTERACTIVE 7-SCREEN SHOWCASE ================= -->
    <section class="section" id="screens">
        <div class="container">
            <div class="section-head">
                <span class="section-kicker"><i class="bx bx-mobile-alt"></i> داخل التطبيق</span>
                <h2>استكشف شاشات نجاح ريسنج بالتفصيل</h2>
                <p>تنقّل بين شاشات التطبيق للتعرف على تجربة الاستخدام المصممة ببساطة وفخامة عربية.</p>
            </div>

            <div class="showcase-stage">
                <div class="showcase-phone-wrap">
                    <div class="showcase-image-box" id="showcaseImageTrigger" title="اضغط للتكبير">
                        <img id="showcaseMainImage" src="{{ asset('landing/screen-1.jpeg') }}" alt="تابع مطاياك لحظة بلحظة">
                        <span class="zoom-hint"><i class="bx bx-zoom-in"></i> تكبير الصورة</span>
                    </div>
                </div>

                <div class="showcase-info">
                    <span class="showcase-step-pill" id="showcasePill">الشاشة 1 من 7 • نظرة عامة</span>
                    <h3 id="showcaseTitle">تابع مطاياك لحظة بلحظة على الخريطة</h3>
                    <p class="lead" id="showcaseDesc">
                        تطبيقك المتكامل لرصد وتتبع التدريب بدقة عالية للمطايا عبر الخريطة، مع رصد فوري للسرعة ومستوى الأداء وسجل متكامل يمكنك الرجوع إليه في أي وقت.
                    </p>
                    <ul class="showcase-bullets" id="showcaseBullets">
                        <li><i class="bx bx-check-circle"></i><span>تحديد دقيق للموقع وتتبع مباشر على الخريطة.</span></li>
                        <li><i class="bx bx-check-circle"></i><span>رصد السرعة والوقت ومستوى الأداء أولاً بأول.</span></li>
                        <li><i class="bx bx-check-circle"></i><span>سجل متكامل وموثق لجميع التدريبات السابقة حتى لو فاتك البث المباشر.</span></li>
                    </ul>

                    <div class="showcase-nav-btns">
                        <button type="button" class="circle-nav-btn" id="btnPrevScreen" aria-label="الشاشة السابقة">
                            <i class="bx bx-chevron-right"></i>
                        </button>
                        <button type="button" class="circle-nav-btn" id="btnNextScreen" aria-label="الشاشة التالية">
                            <i class="bx bx-chevron-left"></i>
                        </button>
                        <span style="color: var(--text-muted); font-size: 13px; font-weight: 600; margin-right: 6px;">
                            اختر أي شاشة بالأسفل للاستعراض
                        </span>
                    </div>
                </div>
            </div>

            <div class="screens-strip" id="screensStrip">
                <div class="screen-thumb active" data-index="0">
                    <img src="{{ asset('landing/screen-1.jpeg') }}" alt="تابع مطاياك" loading="lazy">
                    <span>1. تابع مطاياك</span>
                </div>
                <div class="screen-thumb" data-index="1">
                    <img src="{{ asset('landing/screen-2.jpeg') }}" alt="حساب لكل دور" loading="lazy">
                    <span>2. حساب لكل دور</span>
                </div>
                <div class="screen-thumb" data-index="2">
                    <img src="{{ asset('landing/screen-3.jpeg') }}" alt="لوحة تحكم كاملة" loading="lazy">
                    <span>3. لوحة التحكم</span>
                </div>
                <div class="screen-thumb" data-index="3">
                    <img src="{{ asset('landing/screen-4.jpeg') }}" alt="تتبع مباشر" loading="lazy">
                    <span>4. تتبع مباشر</span>
                </div>
                <div class="screen-thumb" data-index="4">
                    <img src="{{ asset('landing/screen-5.jpeg') }}" alt="أدر عمالك" loading="lazy">
                    <span>5. إدارة العمال</span>
                </div>
                <div class="screen-thumb" data-index="5">
                    <img src="{{ asset('landing/screen-6.jpeg') }}" alt="تتبع بدون إنترنت" loading="lazy">
                    <span>6. بدون إنترنت</span>
                </div>
                <div class="screen-thumb" data-index="6">
                    <img src="{{ asset('landing/screen-7.jpeg') }}" alt="اتصال مباشر بالعامل" loading="lazy">
                    <span>7. اتصال مباشر</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= HOW IT WORKS ================= -->
    <section class="section" id="how-it-works">
        <div class="container">
            <div class="section-head">
                <span class="section-kicker"><i class="bx bx-rocket"></i> البداية في 3 خطوات</span>
                <h2>ابدأ متابعة تدريباتك خلال دقيقتين</h2>
                <p>خطوات بسيطة وسريعة لتشغيل المنظومة بالكامل مع فريقك في الميدان.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>حمّل التطبيق وسجّل حسابك</h3>
                    <p>نزّل تطبيق نجاح ريسنج من App Store أو Google Play واختر نوع حسابك (مالك / عامل / مستخدم فرعي) بتسجيل دخول آمن وسريع.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>أضف عمالك وحمّل خريطة الميدان</h3>
                    <p>أضف العمال من لوحة المالك وأرسل لكل عامل كود الدخول الخاص به، ويمكن تحميل خريطة المنطقة مسبقاً للعمل في الوضع الصحراوي بدون إنترنت.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>تابع التدريب لحظة بلحظة</h3>
                    <p>راقب المسار والسرعة والزمن مباشرةً، تواصل مع العامل بمكالمة صوتية مشفرة، وارجع لسجل التدريبات الكامل في أي وقت.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= DOWNLOAD CTA ================= -->
    <section class="section" id="download">
        <div class="container">
            <div class="cta-box">
                <img src="{{ asset('backend/assets/images/logo-icon.png') }}" alt="شعار نجاح ريسنج" class="cta-logo">
                <h2>جاهز لرفع كفاءة تدريبات مطاياك؟</h2>
                <p>
                    حمّل تطبيق <strong>نجاح ريسنج</strong> الآن مجاناً على هاتفك واستمتع بتتبع مباشر دقيق، خرائط بدون إنترنت، وتحكم كامل في تدريباتك وعمالك.
                </p>

                <div class="store-buttons">
                    <a href="https://apps.apple.com/us/app/%D9%86%D8%AC%D8%A7%D8%AD-%D8%B1%D9%8A%D8%B3%D9%86%D8%AC/id6818084350"
                       target="_blank" rel="noopener noreferrer" class="store-badge-link" aria-label="Download on the App Store">
                        <img src="{{ asset('landing/app-store-badge.png') }}" alt="Download on the App Store">
                    </a>

                    <a href="https://play.google.com/store/apps/details?id=com.najahracing.app"
                       target="_blank" rel="noopener noreferrer" class="store-badge-link" aria-label="Get it on Google Play">
                        <img src="{{ asset('landing/google-play-badge.png') }}" alt="Get it on Google Play">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="footer">
        <div class="container footer-inner">
            <a href="#top" class="brand" style="font-size: 17px;">
                <img src="{{ asset('backend/assets/images/logo-icon.png') }}" alt="نجاح ريسنج" class="brand-logo" style="width: 36px; height: 36px;">
                <span>نجاح ريسنج</span>
            </a>

            <div class="footer-links">
                <a href="#features">المميزات</a>
                <a href="#video">الفيديو التعريفي</a>
                <a href="#screens">شاشات التطبيق</a>
                <a href="{{ url('/privacy-policy') }}">سياسة الخصوصية</a>
            </div>

            <div class="copyright">
                &copy; {{ date('Y') }} نجاح ريسنج — جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>

    <!-- ================= LIGHTBOX ================= -->
    <div class="lightbox" id="imageLightbox" role="dialog" aria-modal="true">
        <div class="lightbox-inner">
            <button type="button" class="lightbox-close" id="lightboxClose" aria-label="إغلاق">
                <i class="bx bx-x"></i>
            </button>
            <img id="lightboxImg" src="" alt="عرض مكبّر لشاشة التطبيق">
        </div>
    </div>

    <script>
        (function () {
            // 1. Mobile Navigation Drawer
            const menuBtn = document.getElementById('mobileMenuBtn');
            const drawer = document.getElementById('mobileDrawer');
            if (menuBtn && drawer) {
                menuBtn.addEventListener('click', function () {
                    drawer.classList.toggle('open');
                });
                drawer.querySelectorAll('.drawer-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        drawer.classList.remove('open');
                    });
                });
            }

            // 2. Promotional Video Theater Overlay
            const video = document.getElementById('introVideo');
            const overlay = document.getElementById('videoPlayOverlay');
            const heroWatchBtn = document.getElementById('heroWatchVideoBtn');

            function playPromoVideo() {
                if (!video) return;
                if (overlay) overlay.classList.add('hidden');
                const playPromise = video.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () {});
                }
            }

            if (overlay && video) {
                overlay.addEventListener('click', playPromoVideo);
                video.addEventListener('play', function () {
                    overlay.classList.add('hidden');
                });
                video.addEventListener('ended', function () {
                    overlay.classList.remove('hidden');
                });
            }

            if (heroWatchBtn) {
                heroWatchBtn.addEventListener('click', function () {
                    setTimeout(playPromoVideo, 450);
                });
            }

            // 3. Interactive 7-Screen Showcase Data
            const screens = [
                {
                    img: @json(asset('landing/screen-1.jpeg')),
                    pill: 'الشاشة 1 من 7 • نظرة عامة',
                    title: 'تابع مطاياك لحظة بلحظة على الخريطة',
                    desc: 'تطبيقك المتكامل لرصد وتتبع التدريب بدقة عالية للمطايا عبر الخريطة، مع رصد فوري للسرعة ومستوى الأداء وسجل متكامل يمكنك الرجوع إليه في أي وقت.',
                    bullets: [
                        'تحديد دقيق للموقع وتتبع مباشر للمطايا عبر الخريطة.',
                        'رصد السرعة والوقت ومستوى الأداء أولاً بأول طوال فترة التدريب.',
                        'سجل متكامل وموثق لجميع التدريبات السابقة مع تحليلات ذكية لرفع الكفاءة.'
                    ]
                },
                {
                    img: @json(asset('landing/screen-2.jpeg')),
                    pill: 'الشاشة 2 من 7 • تعدد الأدوار',
                    title: 'حساب لكل دور: مالك، عامل أو مستخدم فرعي',
                    desc: 'واجهة اختيار واضحة تنظّم العمل بين جميع أفراد العزبة والفريق الميداني، بحيث يحصل كل مستخدم على الأدوات والصلاحيات المناسبة لدوره.',
                    bullets: [
                        'حساب المالك / المدرب: إدارة كاملة للعمال ومتابعة جميع التدريبات المباشرة والسابقة.',
                        'حساب العامل: واجهة ميدانية سريعة لبدء التدريب وتوثيق السرعة والمسار بكود دخول خاص.',
                        'حساب المستخدم الفرعي: متابعة مساعدة مضافة من قبل المالك بكل سهولة وأمان.'
                    ]
                },
                {
                    img: @json(asset('landing/screen-3.jpeg')),
                    pill: 'الشاشة 3 من 7 • لوحة المالك',
                    title: 'لوحة تحكم كاملة في شاشة واحدة',
                    desc: 'نظرة فورية وشاملة على كل تدريباتك وعمالك بمجرد فتح التطبيق، دون تنقل معقد بين القوائم.',
                    bullets: [
                        'عرض فوري لعدد التدريبات الجارية حالياً وعدد العمال المسجلين ونشاطهم.',
                        'بطاقة «آخر جلسة تدريب» تعرض اسم العامل والموقع والوقت والسرعة والمسافة.',
                        'زر سريع لإضافة عامل جديد والوصول الفوري لسجل كل التدريبات والعمال.'
                    ]
                },
                {
                    img: @json(asset('landing/screen-4.jpeg')),
                    pill: 'الشاشة 4 من 7 • التتبع الحي',
                    title: 'تتبع مباشر للسرعة والمسافة والوقت أولاً بأول',
                    desc: 'راقب مسار التدريب حيّاً على الخريطة مع مؤشر البث المباشر الفوري وجميع قياسات الشوط في بطاقة واضحة ومقروءة.',
                    bullets: [
                        'مؤشر بث مباشر يوضح حالة نشاط العامل وآخر تحديث لحظي على المسار.',
                        'عرض السرعة الحالية، متوسط السرعة، المسافة المقطوعة (كم)، وزمن الشوط.',
                        'انتقال فوري لمشاهدة مسار التدريب الكامل أو الاتصال المباشر بالعامل من نفس الشاشة.'
                    ]
                },
                {
                    img: @json(asset('landing/screen-5.jpeg')),
                    pill: 'الشاشة 5 من 7 • فريق العمل',
                    title: 'أدر عمالك وتابع حالة اتصالهم بسهولة',
                    desc: 'تحكّم كامل ببيانات العمال ومتابعة حالة تواجدهم الفورية مع إنشاء ونسخ كود الدخول الخاص بكل عامل بضغطة زر.',
                    bullets: [
                        'تصفية فورية للعمال حسب الحالة: الكل، متصل الآن، في الخلفية، أو غير متصل.',
                        'بحث سريع باسم العامل مع إمكانية تعديل البيانات وإضافة عمال جدد.',
                        'رمز تسجيل دخول خاص لكل عامل مع زر نسخ فوري لإرساله له مباشرة.'
                    ]
                },
                {
                    img: @json(asset('landing/screen-6.jpeg')),
                    pill: 'الشاشة 6 من 7 • الوضع الصحراوي',
                    title: 'تتبع بدون إنترنت في عمق الصحراء',
                    desc: 'حمّل خريطة منطقتك أو ميدانك مسبقاً بحجم خفيف لتعمل تلقائياً عند انقطاع الشبكة في البر دون أي توقف للتدريب.',
                    bullets: [
                        'اختيار مرن لنطاق تغطية المدينة أو الميدان: 6 كم (خفيف)، 9 كم، أو 12 كم (واسع).',
                        'تعمل الخريطة المحمّلة تلقائياً في الوضع الصحراوي دون التأثير على خريطة جوجل أونلاين.',
                        'حفظ تلقائي لكامل نقاط المسار والسرعة ومزامنتها فور عودة الاتصال بالإنترنت.'
                    ]
                },
                {
                    img: @json(asset('landing/screen-7.jpeg')),
                    pill: 'الشاشة 7 من 7 • التواصل الميداني',
                    title: 'اتصال صوتي مباشر ومشفر بالعامل بضغطة زر',
                    desc: 'لا حاجة لتطبيقات خارجية أثناء التدريب — وجّه العامل فوراً عبر مكالمة صوتية آمنة ومشفرة مدمجة داخل نجاح ريسنج.',
                    bullets: [
                        'مكالمة صوتية آمنة ومشفرة عالية الوضوح تبدأ بضغطة زر من شاشة المتابعة.',
                        'أزرار سريعة للتحكم بمكبر الصوت وكتم الصوت وإنهاء المكالمة بسلاسة.',
                        'تواصل فوري بين المالك والعامل لتعديل وتيرة التدريب أو توجيه المسار في اللحظة المناسبة.'
                    ]
                }
            ];

            let activeIdx = 0;
            const mainImg = document.getElementById('showcaseMainImage');
            const pillEl = document.getElementById('showcasePill');
            const titleEl = document.getElementById('showcaseTitle');
            const descEl = document.getElementById('showcaseDesc');
            const bulletsEl = document.getElementById('showcaseBullets');
            const thumbs = document.querySelectorAll('.screen-thumb');

            function renderScreen(idx) {
                activeIdx = (idx + screens.length) % screens.length;
                const item = screens[activeIdx];
                if (mainImg) {
                    mainImg.style.opacity = '0.35';
                    setTimeout(function () {
                        mainImg.src = item.img;
                        mainImg.alt = item.title;
                        mainImg.style.opacity = '1';
                    }, 110);
                }
                if (pillEl) pillEl.textContent = item.pill;
                if (titleEl) titleEl.textContent = item.title;
                if (descEl) descEl.textContent = item.desc;
                if (bulletsEl) {
                    bulletsEl.innerHTML = item.bullets.map(function (b) {
                        return '<li><i class="bx bx-check-circle"></i><span>' + b + '</span></li>';
                    }).join('');
                }
                thumbs.forEach(function (t, i) {
                    t.classList.toggle('active', i === activeIdx);
                });
            }

            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    const idx = parseInt(this.getAttribute('data-index'), 10) || 0;
                    renderScreen(idx);
                });
            });

            const btnPrev = document.getElementById('btnPrevScreen');
            const btnNext = document.getElementById('btnNextScreen');
            if (btnPrev) btnPrev.addEventListener('click', function () { renderScreen(activeIdx - 1); });
            if (btnNext) btnNext.addEventListener('click', function () { renderScreen(activeIdx + 1); });

            // 4. Image Lightbox
            const trigger = document.getElementById('showcaseImageTrigger');
            const lightbox = document.getElementById('imageLightbox');
            const lightboxImg = document.getElementById('lightboxImg');
            const lightboxClose = document.getElementById('lightboxClose');

            function closeLightbox() {
                if (lightbox) lightbox.classList.remove('open');
            }

            if (trigger && lightbox && lightboxImg) {
                trigger.addEventListener('click', function () {
                    lightboxImg.src = screens[activeIdx].img;
                    lightbox.classList.add('open');
                });
            }
            if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
            if (lightbox) {
                lightbox.addEventListener('click', function (e) {
                    if (e.target === lightbox) closeLightbox();
                });
            }
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeLightbox();
            });
        })();
    </script>
</body>
</html>
