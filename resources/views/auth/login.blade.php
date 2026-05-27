<!doctype html>
<html lang="en" class="semi-dark" dir="rtl">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	{{-- <link rel="icon" href="{{ asset('backend/assets/images/favicon-32x32.png') }}" type="image/png" /> --}}
	<!--plugins-->
	<link href="{{ asset('backend/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
	<link href="{{ asset('backend/assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{ asset('backend/assets/css/pace.min.css') }}" rel="stylesheet" />
	<script src="{{ asset('backend/assets/js/pace.min.js') }}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/bootstrap-extended.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('backend/assets/css/icons.css') }}" rel="stylesheet">
	<title>لوحة تحكم - تطبيق نجاح ريسنج</title>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >

    <style>
		body {
			font-family: "Cairo", sans-serif;
			background-color: #faf9f6;
		}

		/* Left side cover styling */
		.bg-gradient-cosmic {
			background-image: linear-gradient(135deg, rgba(150, 17, 72, 0.92) 0%, rgba(75, 8, 35, 0.96) 100%), url('{{ asset("backend/assets/images/login-images/bg-login-img.png") }}') !important;
			background-size: cover !important;
			background-position: center !important;
			position: relative;
		}

		.bg-gradient-cosmic::before {
			content: '';
			position: absolute;
			top: 0; right: 0; bottom: 0; left: 0;
			background: radial-gradient(circle at 30% 30%, rgba(212, 175, 55, 0.15) 0%, transparent 70%);
			pointer-events: none;
		}

		.auth-cover-left img {
			filter: drop-shadow(0px 15px 30px rgba(0, 0, 0, 0.4));
			transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
		}

		.auth-cover-left img:hover {
			transform: scale(1.04);
		}

		/* Right side cover container styling */
		.auth-cover-right {
			background-color: #0d0107 !important;
		}

		.auth-cover-right .card {
			background-image: linear-gradient(135deg, rgba(20, 2, 10, 0.95) 0%, rgba(76, 8, 37, 0.92) 50%, rgba(150, 17, 72, 0.85) 100%), url('{{ asset("backend/assets/images/login-images/bg-login-img.png") }}') !important;
			background-size: cover !important;
			background-position: center !important;
			border-radius: 20px !important;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
			border: 1px solid rgba(255, 255, 255, 0.08) !important;
			transition: transform 0.3s ease, box-shadow 0.3s ease;
		}

		.auth-cover-right .card:hover {
			box-shadow: 0 24px 48px rgba(0, 0, 0, 0.45), 0 1px 3px rgba(0, 0, 0, 0.1) !important;
		}

		.auth-cover-right img:not(.logo) {
			filter: drop-shadow(0px 6px 12px rgba(150, 17, 72, 0.1));
			transition: transform 0.4s ease;
		}

		.auth-cover-right img:not(.logo):hover {
			transform: rotate(5deg) scale(1.05);
		}

		.auth-cover-right h5 {
			color: #ffffff !important;
			font-weight: 700 !important;
			font-size: 22px !important;
			margin-top: 10px;
		}

		.auth-cover-right p {
			color: #cbd5e1 !important;
			font-weight: 500 !important;
			font-size: 15px !important;
		}

		/* Form inputs styling */
		.form-label {
			font-weight: 600 !important;
			color: #f1f5f9 !important;
			font-size: 14px !important;
			margin-bottom: 8px !important;
		}

		.form-control {
			border: 1.5px solid rgba(255, 255, 255, 0.15) !important;
			border-radius: 10px !important;
			padding: 12px 16px !important;
			font-size: 15px !important;
			color: #ffffff !important;
			background-color: rgba(255, 255, 255, 0.05) !important;
			transition: all 0.25s ease !important;
		}

		.form-control::placeholder {
			color: #94a3b8 !important;
			opacity: 0.8;
		}

		.form-control:focus {
			border-color: #d4af37 !important;
			background-color: rgba(255, 255, 255, 0.08) !important;
			box-shadow: 0 0 0 3.5px rgba(212, 175, 55, 0.2) !important;
		}

		/* Password reveal group adjustments */
		.input-group {
			border-radius: 10px !important;
		}

		.input-group .form-control {
			border-top-right-radius: 10px !important;
			border-bottom-right-radius: 10px !important;
			border-top-left-radius: 0px !important;
			border-bottom-left-radius: 0px !important;
			border-left: 0px !important;
		}

		.input-group-text {
			border: 1.5px solid rgba(255, 255, 255, 0.15) !important;
			border-right: 0px !important;
			border-top-left-radius: 10px !important;
			border-bottom-left-radius: 10px !important;
			border-top-right-radius: 0px !important;
			border-bottom-right-radius: 0px !important;
			background-color: rgba(255, 255, 255, 0.05) !important;
			color: #94a3b8 !important;
			transition: all 0.25s ease !important;
			padding: 0 16px !important;
		}

		.input-group:focus-within .form-control {
			border-color: #d4af37 !important;
			background-color: rgba(255, 255, 255, 0.08) !important;
		}

		.input-group:focus-within .input-group-text {
			border-color: #d4af37 !important;
			background-color: rgba(255, 255, 255, 0.08) !important;
			color: #d4af37 !important;
		}

		/* Submit button styling */
		.btn-danger {
			background: linear-gradient(135deg, #961148 0%, #b61c5b 100%) !important;
			border: none !important;
			border-radius: 10px !important;
			padding: 14px 28px !important;
			font-size: 16px !important;
			font-weight: 700 !important;
			color: #ffffff !important;
			box-shadow: 0 6px 20px rgba(150, 17, 72, 0.25) !important;
			transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
		}

		.btn-danger:hover {
			background: linear-gradient(135deg, #7b0c39 0%, #961148 100%) !important;
			box-shadow: 0 8px 24px rgba(150, 17, 72, 0.35) !important;
			transform: translateY(-2px) !important;
		}

		.btn-danger:active {
			transform: translateY(0px) !important;
			box-shadow: 0 4px 12px rgba(150, 17, 72, 0.2) !important;
		}

		/* Logo pulsating gold/burgundy frame */
		.logo-container {
			position: relative;
			width: 140px;
			height: 140px;
			margin-bottom: 25px;
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
	</style>
</head>

<body class="">
	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-cover">
			<div class="">
				<div class="row g-0">

					<div class="col-12 col-xl-7 col-xxl-8 auth-cover-left bg-gradient-cosmic align-items-center justify-content-center d-none d-xl-flex">
						<div class="card shadow-none bg-transparent shadow-none rounded-0 mb-0">
							<div class="card-body">
                                 <img src="{{ asset('backend/assets/images/login-images/logo_fantacy.png') }}" class="img-fluid" width="400" alt=""/>
							</div>
						</div>
					</div>

					<div class="col-12 col-xl-5 col-xxl-4 auth-cover-right align-items-center justify-content-center">
						<div class="card rounded-0 m-3 shadow-none bg-transparent mb-0">
							<div class="card-body p-sm-5">
								<div class="">
									<div class="mb-3 d-flex justify-content-center">
										<div class="logo-container">
											<img src="{{ asset('backend/assets/images/login-images/logo_maragin.png') }}" class="logo" alt="شعار نجاح ريسنج"/>
										</div>
									</div>
									<div class="text-center mb-4">
										<h5 class="">لوحة تحكم - تطبيق نجاح ريسنج</h5>
										<p class="mb-0">الرجاء تسجيل الدخول</p>
									</div>
									<div class="form-body">

                                            <form class="row g-3" method="POST" action="{{ route('login') }}">
                                                @csrf



											<div class="col-12">
												<label for="inputEmailAddress" class="form-label">البريد الإلكتروني</label>
												<input type="email" id="email" name="email" :value="old('email')" required class="form-control" id="inputEmailAddress" placeholder="الرجاء ادخال البريد الإلكتروني">
											</div>
											<div class="col-12">
												<label for="inputChoosePassword" class="form-label">كلمة المرور</label>
												<div class="input-group" id="show_hide_password">
													<input required id="password" name="password" type="password" class="form-control border-end-0" id="inputChoosePassword"  placeholder="الرجاء ادخال كلمة المرور">
													<a href="javascript:;" class="input-group-text bg-transparent"><i class="bx bx-hide"></i></a>
												</div>
											</div>
											<div class="col-md-6">

											</div>
											<div class="col-md-6 text-end">
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-danger">تسجيل الدخول</button>
												</div>
											</div>
											<div class="col-12">
												<div class="text-center">
												</div>
											</div>
										</form>
									</div>
									<div class="login-separater text-center mb-5">
										<hr>
									</div>

								</div>
							</div>
						</div>
					</div>

				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>
	<!--plugins-->
	<script src="{{ asset('backend/assets/js/jquery.min.js') }}"></script>
	<script src="{{ asset('backend/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
	<script src="{{ asset('backend/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
	<script src="{{ asset('backend/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<!--app JS-->
	<script src="{{ asset('backend/assets/js/app.js') }}"></script>



    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
 @if(Session::has('message'))
 var type = "{{ Session::get('alert-type','info') }}"
 switch(type){
    case 'info':
    toastr.info(" {{ Session::get('message') }} ");
    break;
    case 'success':
    toastr.success(" {{ Session::get('message') }} ");
    break;
    case 'warning':
    toastr.warning(" {{ Session::get('message') }} ");
    break;
    case 'error':
    toastr.error(" {{ Session::get('message') }} ");
    break;
 }
 @endif
</script>
</body>

</html>
