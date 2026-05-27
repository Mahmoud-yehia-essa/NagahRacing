<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment Successful</title>
  <style>
   body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to left, #69e3c4, #153870) !important;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: white;
    }
    .container {
      text-align: center;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      max-width: 400px;
      width: 90%;
    }
    .logo {
      width: 250px;
      margin-bottom: 20px;
    }
    .checkmark {
      font-size: 48px;
      color: #00ffae;
      margin-bottom: 20px;
    }
    h1 {
      font-size: 26px;
      margin-bottom: 10px;
    }
    p {
      font-size: 16px;
      opacity: 0.9;
    }
    .btn {
      margin-top: 30px;
      padding: 12px 24px;
      background-color: #f2ff00;
      color: #000;
      border: none;
      border-radius: 30px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }
    .btn:hover {
      background-color: #00e6a0;
    }

    body
		{
			font-family: "Cairo", sans-serif;
		}

  </style>
</head>
<body>
  <div class="container">
    <img src="{{ asset('backend/assets/images/login-images/logo_chramba.png') }}" class="logo" alt=""/>

    {{-- <img src="{{asset('/assets/images/logo-icon.png')}}" alt="App Logo" class="logo" /> --}}
    {{-- <div class="checkmark">✅</div> --}}
    <h1>تمت عملية الدفع</h1>
    <h2>شكرا للشراء</h2>
    <a href="myapp://payment-callback" class="btn">الرجوع للتطبيق</a>
  </div>
</body>
</html>
