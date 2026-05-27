<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>{{ $festivalName ?? 'اسم المهرجان' }} - ترتيب المستخدم</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #BF281B;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            min-height: 100vh;
        }

        /* ========== LOGO ========== */
        .app-logo {
            width: 120px;
            margin: 30px auto 10px;
            display: block;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            font-weight: bold;
            text-shadow: 2px 2px 6px rgba(0,0,0,.4);
        }

        .header h3 {
            color: #FFD700;
        }

        /* ========== USER CARD ========== */
        .user-card {
            background: #fff;
            color: #333;
            border-radius: 22px;
            padding: 30px 20px;
            max-width: 520px;
            margin: auto;
            box-shadow: 0 12px 30px rgba(0,0,0,.35);
            text-align: center;
            animation: fadeUp .8s ease;
        }

        .user-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #FFC107;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .user-name {
            font-size: 24px;
            font-weight: bold;
        }

        .country-flag {
            font-size: 22px;
            margin-inline-start: 8px;
        }

        .user-email {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        /* ========== STATS ========== */
        .stats {
            display: flex;
            gap: 15px;
            justify-content: space-between;
        }

        .stat-box {
            flex: 1;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,.15);
            transition: transform .3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-title {
            font-size: 15px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 34px;
            font-weight: bold;
            color: #007bff;
        }

        .stat-rank .stat-value {
            color: #28a745;
        }

        /* ========== MEDALS ========== */
        .medals-section {
            margin-top: 30px;
        }

        .medal-card {
            position: relative;
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,.25);
            text-align: center;
            overflow: hidden;
            animation: fadeUp .8s ease both;
        }

        .medal-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 60%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255,255,255,.5),
                transparent
            );
            animation: shine 3s infinite;
        }

        .medal-gold { background: linear-gradient(135deg,#FFD700,#C9A000); }
        .medal-silver { background: linear-gradient(135deg,#E0E0E0,#BDBDBD); }
        .medal-bronze { background: linear-gradient(135deg,#CD7F32,#A05A2C); }

        .medal-icon {
            font-size: 44px;
            margin-bottom: 8px;
        }

        .medal-title {
            font-size: 18px;
            font-weight: bold;
            color: #212529;
        }

        .medal-rank {
            font-size: 14px;
            opacity: .9;
        }

        /* ========== DOWNLOAD ========== */
        .download-section {
            margin: 40px 0;
            text-align: center;
        }

        .store-buttons img {
            width: 160px;
            margin: 10px;
            transition: transform .2s;
        }

        .store-buttons img:hover {
            transform: scale(1.05);
        }

        /* ========== ANIMATION ========== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shine {
            0% { left: -120%; }
            40% { left: 120%; }
            100% { left: 120%; }
        }
    </style>
</head>

<body>

<img src="{{ asset('frontend/assets/img/logo_maragin.png') }}" class="app-logo">

<div class="header">
    <h1>ترتيب المستخدم</h1>
    <h3>{{ $festivalName }}</h3>
</div>

{{-- USER CARD --}}
<div class="user-card">

    {{-- PHOTO --}}
    <img class="user-photo"
         src="{{ $user->photo != 'non'
                ? asset('upload/user_images/'.$user->photo)
                : asset('frontend/assets/img/1024X.png') }}">

    {{-- NAME --}}
    <div class="user-name">
        {{ $user->fname }} {{ $user->lname }}
        <span class="country-flag">
            {{ $user->country_flag ?: '🌍' }}
        </span>
    </div>

    <div class="user-email">{{ $user->email }}</div>

    {{-- STATS --}}
    <div class="stats">
        <div class="stat-box">
            <div class="stat-title">إجمالي النقاط</div>
            <div class="stat-value">{{ $user->total_points }}</div>
        </div>
        <div class="stat-box stat-rank">
            <div class="stat-title">الترتيب</div>
            <div class="stat-value">{{ $user->user_rank ?? '-' }}</div>
        </div>
    </div>
</div>

{{-- MEDALS --}}
@if($userFestivals->count())
<div class="user-card medals-section mt-4">
    <h4 class="mb-3">🏅 إنجازات المستخدم</h4>

    @foreach($userFestivals as $i => $festival)
        @php
            if ($festival->user_position == 1) {
                $class='medal-gold'; $icon='🥇'; $rank='المركز الأول';
            } elseif ($festival->user_position == 2) {
                $class='medal-silver'; $icon='🥈'; $rank='المركز الثاني';
            } elseif ($festival->user_position == 3) {
                $class='medal-bronze'; $icon='🥉'; $rank='المركز الثالث';
            } else continue;
        @endphp

        <div class="medal-card {{ $class }}" style="animation-delay: {{ $i * .2 }}s">
            <div class="medal-icon">{{ $icon }}</div>
            <div class="medal-title">{{ $festival->festival_name }}</div>
            <div class="medal-rank">{{ $rank }}</div>
        </div>
    @endforeach
</div>
@endif

{{-- DOWNLOAD --}}
<div class="download-section">
    <p>حمّل تطبيق فانتسي وشارك بترشيحاتك</p>
    <div class="store-buttons">
        <a href="https://play.google.com/store/apps/details?id=com.app.fantasy_alhajin">
            <img src="{{ asset('frontend/assets/img/store/1.png') }}">
        </a>
        <a href="https://apps.apple.com/kw/app/fantasy-alhajin/id6754517310">
            <img src="{{ asset('frontend/assets/img/store/2.png') }}">
        </a>
    </div>
</div>

</body>
</html>
