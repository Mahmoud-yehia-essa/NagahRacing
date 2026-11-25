<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة ربط الواتساب (OTP) - نجاح ريسنج</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at top right, #3a0c1b 0%, #290612 45%, #160309 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }
        .gate-card {
            width: 100%;
            max-width: 480px;
            background: rgba(52, 14, 26, 0.92);
            border: 1.5px solid rgba(233, 195, 73, 0.35);
            border-radius: 22px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55);
            padding: 36px 32px;
        }
        .wa-badge {
            width: 74px;
            height: 74px;
            border-radius: 50%;
            background: linear-gradient(135deg, #128C7E, #25D366);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #fff;
            box-shadow: 0 10px 28px rgba(37, 211, 102, 0.35);
            margin-bottom: 18px;
        }
        .gold-text {
            color: #E9C349;
        }
        .api-input {
            background: #1a040b !important;
            border: 1px solid rgba(233, 195, 73, 0.35) !important;
            color: #fff !important;
            border-radius: 12px !important;
            padding: 13px 16px !important;
            font-family: monospace;
            font-size: 0.95rem;
        }
        .api-input:focus {
            border-color: #E9C349 !important;
            box-shadow: 0 0 0 3px rgba(233, 195, 73, 0.18) !important;
        }
        .btn-unlock {
            background: linear-gradient(90deg, #128C7E, #25D366);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 13px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.28);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-unlock:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(37, 211, 102, 0.4);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="gate-card text-center">
        <div class="wa-badge">
            <i class="bx bxl-whatsapp"></i>
        </div>

        <h3 class="fw-bold mb-1">بوابة ربط الواتساب (OTP)</h3>
        <p class="gold-text fw-semibold mb-3">نجاح ريسنج — صفحة مستقلة ومحمية</p>
        <p class="small mb-4" style="color: #e0bfbf; line-height: 1.7;">
            لا يمكن استخدام صفحة الربط ورمز QR إلا من خلال <strong>مفتاح الـ API</strong> الذي يتم إنشاؤه من لوحة التحكم.
        </p>

        @if(!empty($gateError))
            <div class="alert alert-danger py-2 px-3 small text-start rounded-3 mb-3">
                <i class="bx bx-error-circle me-1"></i> {{ $gateError }}
            </div>
        @endif

        @if(session('gate_success'))
            <div class="alert alert-success py-2 px-3 small text-start rounded-3 mb-3">
                <i class="bx bx-check-circle me-1"></i> {{ session('gate_success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small text-start rounded-3 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('whatsapp.gateway.authenticate') }}" class="text-start">
            @csrf
            <div class="mb-3">
                <label for="api_key" class="form-label small fw-bold gold-text">
                    <i class="bx bx-key me-1"></i> مفتاح الـ API السري (من لوحة التحكم)
                </label>
                <input type="password"
                       id="api_key"
                       name="api_key"
                       dir="ltr"
                       class="form-control api-input"
                       placeholder="nr_wa_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                       required
                       autofocus>
            </div>

            <button type="submit" class="btn btn-unlock w-100 mt-2">
                <i class="bx bx-lock-open-alt me-1 fs-5 align-middle"></i>
                دخول إلى بوابة ربط الواتساب (QR Code)
            </button>
        </form>

        <div class="mt-4 pt-3 border-top" style="border-color: rgba(233, 195, 73, 0.2) !important;">
            <small style="color: #c9a6ad;">
                <i class="bx bx-shield-quarter text-success me-1"></i>
                عند تجديد المفتاح من لوحة التحكم يتم إغلاق أي جلسة مفتوحة تلقائياً.
            </small>
        </div>
    </div>
</body>
</html>
