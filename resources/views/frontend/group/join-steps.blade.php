<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>نجاح ريسنج - خطوات الانضمام إلى {{ $group->name ?? 'المجموعة' }}</title>
    <meta name="description" content="خطوات الانضمام إلى مجموعة {{ $group->name ?? '' }} في تطبيق نجاح ريسنج - اتبع الخطوات البسيطة للانضمام" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                red: "#DA1212",
                dark: "#050505",
                gray: "#1A1A1A",
              },
            },
            fontFamily: {
              sans: ["Cairo", "sans-serif"],
            },
          },
        },
      };
    </script>
    <style>
      /* Ultra Animations Stylesheet */
      body {
        background-color: #050505;
        color: #ffffff;
        overflow-x: hidden;
      }

      /* 1. Shifting Gradient Background */
      .gradient-shift {
        background: linear-gradient(-45deg, #050505, #da1212, #5a0000, #1a1a1a);
        background-size: 400% 400%;
        animation: gradientAnim 15s ease infinite;
      }
      @keyframes gradientAnim {
        0% {
          background-position: 0% 50%;
        }
        50% {
          background-position: 100% 50%;
        }
        100% {
          background-position: 0% 50%;
        }
      }

      /* 2. Infinite Marquee Text */
      .marquee {
        white-space: nowrap;
        overflow: hidden;
        position: absolute;
        font-size: 10rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.03);
        text-transform: uppercase;
        z-index: 0;
        top: 15%;
        left: 0;
        width: 200%;
      }
      .marquee-content {
        display: inline-block;
        animation: marqueeScroll 40s linear infinite;
      }
      @keyframes marqueeScroll {
        0% {
          transform: translateX(0);
        }
        100% {
          transform: translateX(50%);
        }
      }

      /* 3. Floating 3D Cards */
      .card-3d-wrap {
        perspective: 1000px;
      }
      .card-3d {
        transition:
          transform 0.6s cubic-bezier(0.23, 1, 0.32, 1),
          box-shadow 0.6s ease;
        transform-style: preserve-3d;
      }
      .card-3d:hover {
        transform: rotateY(-5deg) rotateX(5deg) scale(1.02);
        box-shadow:
          -20px 20px 50px rgba(218, 18, 18, 0.3),
          0 0 20px rgba(255, 255, 255, 0.1);
      }

      /* 4. Glowing Pulse Effects */
      .glow-pulse {
        animation: glow 2s ease-in-out infinite alternate;
      }
      @keyframes glow {
        from {
          box-shadow:
            0 0 10px rgba(218, 18, 18, 0.5),
            0 0 20px rgba(218, 18, 18, 0.3);
        }
        to {
          box-shadow:
            0 0 20px rgba(218, 18, 18, 0.8),
            0 0 40px rgba(218, 18, 18, 0.5);
        }
      }

      /* 5. Floating Icons */
      .icon-float-1 {
        animation: floatIcon 4s ease-in-out infinite;
      }
      .icon-float-2 {
        animation: floatIcon 5s ease-in-out infinite 1s;
      }
      .icon-float-3 {
        animation: floatIcon 6s ease-in-out infinite 2s;
      }

      @keyframes floatIcon {
        0%,
        100% {
          transform: translateY(0) rotate(0deg);
        }
        50% {
          transform: translateY(-15px) rotate(10deg);
        }
      }

      /* 6. Liquid Glass Effect */
      .liquid-glass {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 1px solid rgba(255, 255, 255, 0.1);
      }

      /* Custom Scrollbar for sleekness */
      ::-webkit-scrollbar {
        width: 8px;
      }
      ::-webkit-scrollbar-track {
        background: #050505;
      }
      ::-webkit-scrollbar-thumb {
        background: #da1212;
        border-radius: 4px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: #ff1e1e;
      }

      /* Step Number animation */
      .step-number {
        position: relative;
        z-index: 10;
      }
      .step-number::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, #DA1212, transparent, #DA1212);
        animation: spinBorder 3s linear infinite;
        z-index: -1;
      }
      @keyframes spinBorder {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      /* Connected steps line */
      .steps-connector {
        position: absolute;
        top: 40px;
        bottom: 40px;
        right: 39px;
        width: 3px;
        background: linear-gradient(to bottom, #DA1212 0%, rgba(218,18,18,0.1) 100%);
        z-index: 0;
      }

      @media (max-width: 768px) {
        .steps-connector {
          right: 29px;
        }
      }

      /* Copied notification */
      .copy-notification {
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%) translateY(-20px);
        background: #DA1212;
        color: white;
        padding: 12px 32px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1rem;
        z-index: 9999;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        pointer-events: none;
        box-shadow: 0 10px 40px rgba(218,18,18,0.5);
      }
      .copy-notification.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }

      /* QR Code container glow */
      .qr-glow {
        animation: qrGlow 3s ease-in-out infinite alternate;
      }
      @keyframes qrGlow {
        from {
          box-shadow: 0 0 20px rgba(218, 18, 18, 0.2), 0 0 40px rgba(218, 18, 18, 0.1);
        }
        to {
          box-shadow: 0 0 30px rgba(218, 18, 18, 0.4), 0 0 60px rgba(218, 18, 18, 0.2);
        }
      }

      /* Typing effect for step titles */
      .step-card {
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
      }
      .step-card:hover {
        transform: translateX(-8px);
      }
      .step-card:hover .step-icon-box {
        background: #DA1212;
        border-color: #DA1212;
      }
      .step-card:hover .step-icon-box i {
        color: white;
        transform: scale(1.2);
      }
    </style>
  </head>
  <body
    class="font-sans antialiased text-white flex flex-col min-h-screen selection:bg-brand-red selection:text-white"
  >
    <!-- Copy Notification -->
    <div class="copy-notification" id="copyNotification">
      <i class="fa-solid fa-check-circle ml-2"></i>
      تم نسخ الكود بنجاح!
    </div>

    <header
      class="fixed w-full top-0 z-50 transition-all duration-500 bg-brand-dark/80 backdrop-blur-md border-b border-white/5"
      id="navbar"
    >
      <div
        class="container mx-auto px-4 lg:px-8 py-4 flex justify-between items-center"
      >
        <a
          href="{{ route('group.join', $group->code_number) }}"
          class="flex items-center gap-4 cursor-pointer group"
          data-aos="fade-down"
          data-aos-duration="800"
        >
          <div class="relative w-12 h-12 flex items-center justify-center">
            <div
              class="absolute inset-0 rounded-full border border-brand-red opacity-50 group-hover:scale-150 group-hover:opacity-0 transition-all duration-700"
            ></div>
            <div
              class="absolute inset-0 bg-brand-red rounded-lg shadow-[0_0_15px_rgba(218,18,18,0.6)] flex items-center justify-center z-10 group-hover:rotate-12 transition-transform duration-300 overflow-hidden"
            >
              <img
                src="{{ asset('frontend/assets/img/1024x.png') }}"
                alt="شعار نجاح ريسنج"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
          <div>
            <h1 class="text-2xl font-black text-white tracking-tight">
              نجاح ريسنج
            </h1>
            <div
              class="h-0.5 w-0 bg-brand-red group-hover:w-full transition-all duration-500"
            ></div>
          </div>
        </a>

        <a
          href="{{ route('group.join', $group->code_number) }}"
          data-aos="fade-down"
          data-aos-duration="800"
          data-aos-delay="200"
          class="hidden md:flex items-center gap-2 px-6 py-2.5 bg-white/5 border border-white/10 text-white font-bold rounded-full hover:bg-brand-red hover:border-brand-red transition-all duration-300 shadow-lg hover:-translate-y-1"
        >
          <i class="fa-solid fa-arrow-right text-sm"></i>
          <span>العودة للمجموعة</span>
        </a>
      </div>
    </header>

    <!-- Hero Section -->
    <section
      class="relative min-h-[60vh] flex items-center pt-20 overflow-hidden gradient-shift"
    >
      <div class="marquee" dir="ltr">
        <div class="marquee-content">
          نجاح ريسنج &nbsp;&bull;&nbsp; نجاح ريسنج &nbsp;&bull;&nbsp;
          نجاح ريسنج  &nbsp;&bull;&nbsp; نجاح ريسنج  &nbsp;&bull;&nbsp; نجاح ريسنج
        </div>
      </div>

      <i
        class="fa-solid fa-star absolute top-1/4 right-1/4 text-brand-red opacity-30 text-3xl icon-float-1 blur-[1px]"
      ></i>
      <i
        class="fa-solid fa-bolt absolute bottom-1/3 left-1/4 text-white opacity-10 text-5xl icon-float-2 blur-[2px]"
      ></i>
      <i
        class="fa-solid fa-trophy absolute top-1/3 left-10 text-brand-red opacity-20 text-4xl icon-float-3 blur-[1px]"
      ></i>

      <div class="container mx-auto px-4 lg:px-8 relative z-10">
        <div class="text-center max-w-4xl mx-auto">
          <div
            data-aos="fade-down"
            data-aos-duration="1000"
            class="inline-flex items-center gap-2 px-5 py-2 rounded-full liquid-glass text-white font-bold text-sm mb-8 border border-white/20"
          >
            <span class="relative flex h-3 w-3">
              <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-red opacity-75"
              ></span>
              <span
                class="relative inline-flex rounded-full h-3 w-3 bg-brand-red"
              ></span>
            </span>
            مجموعة {{ $group->name }}
          </div>

          <h2
            data-aos="fade-up"
            data-aos-duration="1000"
            data-aos-delay="100"
            class="text-4xl lg:text-6xl font-black mb-6 leading-tight text-white drop-shadow-2xl"
          >
            خطوات الانضمام
            <span
              class="text-transparent bg-clip-text bg-gradient-to-l from-white to-brand-red"
            >للمجموعة</span>
          </h2>

          <p
            data-aos="fade-up"
            data-aos-duration="1000"
            data-aos-delay="300"
            class="text-xl md:text-2xl text-gray-300 font-semibold mb-8 max-w-2xl mx-auto leading-relaxed drop-shadow-lg"
          >
            اتبع الخطوات البسيطة التالية للانضمام إلى مجموعة
            <span class="text-white bg-brand-red/20 px-2 rounded">{{ $group->name ?? 'اسم المجموعة' }}</span>
            عبر تطبيق نجاح ريسنج
          </p>

          <div
            data-aos="fade-up"
            data-aos-delay="500"
            class="flex items-center justify-center gap-2 text-gray-400 font-bold"
          >
            <i class="fa-solid fa-arrow-down animate-bounce text-brand-red"></i>
            <span>اكتشف الخطوات</span>
          </div>
        </div>
      </div>

      <div
        class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-brand-dark to-transparent"
      ></div>
    </section>

    <!-- Steps Section -->
    <section class="py-24 bg-brand-dark relative overflow-hidden" id="stepsSection">
      <div
        class="absolute top-1/4 right-0 w-[500px] h-[500px] bg-brand-red rounded-full mix-blend-screen filter blur-[150px] opacity-10"
      ></div>
      <div
        class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-brand-red rounded-full mix-blend-screen filter blur-[200px] opacity-10"
      ></div>

      <div class="container mx-auto px-4 lg:px-8 relative z-10">
        <div class="text-center mb-20" data-aos="fade-down">
          <span
            class="text-brand-red font-black tracking-widest uppercase text-xl mb-2 block"
          >— خطوات بسيطة —</span>
          <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-white">
            كيف تنضم <span class="text-brand-red">للمجموعة؟</span>
          </h2>
        </div>

        <!-- Steps Container -->
        <div class="max-w-4xl mx-auto relative">
          <!-- Connector Line (desktop) -->
          <div class="steps-connector hidden md:block"></div>

          <!-- Step 1 -->
          <div
            class="step-card relative flex flex-col md:flex-row items-start gap-6 md:gap-8 mb-16"
            data-aos="fade-left"
            data-aos-duration="1200"
          >
            <div class="flex-shrink-0 relative z-10">
              <div class="step-number w-16 h-16 md:w-20 md:h-20 bg-brand-dark rounded-full flex items-center justify-center border-2 border-brand-red shadow-[0_0_30px_rgba(218,18,18,0.4)]">
                <span class="text-3xl md:text-4xl font-black text-brand-red">١</span>
              </div>
            </div>
            <div class="card-3d-wrap flex-1 w-full">
              <div class="card-3d bg-brand-gray rounded-[2rem] p-1 shadow-2xl relative group overflow-hidden w-full">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-0 group-hover:opacity-100 group-hover:animate-[spin_4s_linear_infinite] transition-opacity duration-500 z-0"></div>
                <div class="absolute inset-1 bg-brand-gray rounded-[1.9rem] z-10"></div>
                <div class="relative z-20 p-6 md:p-8">
                  <div class="flex items-center gap-4 mb-4">
                    <div class="step-icon-box w-14 h-14 bg-brand-red/10 rounded-2xl flex items-center justify-center border border-brand-red/20 transition-all duration-300">
                      <i class="fa-solid fa-mobile-screen text-brand-red text-2xl transition-all duration-300"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black text-white">افتح التطبيق</h3>
                  </div>
                  <p class="text-gray-400 font-bold leading-relaxed text-lg">
                    في الشاشة الرئيسية من تطبيق <span class="text-white">نجاح ريسنج</span>، توجه إلى الجزء الخاص بالمجموعات واضغط على زر
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-brand-red/20 border border-brand-red/30 rounded-lg text-brand-red font-black text-base">
                      <i class="fa-solid fa-play text-xs"></i>
                      ابدأ الآن
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div
            class="step-card relative flex flex-col md:flex-row items-start gap-6 md:gap-8 mb-16"
            data-aos="fade-left"
            data-aos-duration="1200"
            data-aos-delay="200"
          >
            <div class="flex-shrink-0 relative z-10">
              <div class="step-number w-16 h-16 md:w-20 md:h-20 bg-brand-dark rounded-full flex items-center justify-center border-2 border-brand-red shadow-[0_0_30px_rgba(218,18,18,0.4)]">
                <span class="text-3xl md:text-4xl font-black text-brand-red">٢</span>
              </div>
            </div>
            <div class="card-3d-wrap flex-1 w-full">
              <div class="card-3d bg-brand-gray rounded-[2rem] p-1 shadow-2xl relative group overflow-hidden w-full">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-0 group-hover:opacity-100 group-hover:animate-[spin_4s_linear_infinite] transition-opacity duration-500 z-0"></div>
                <div class="absolute inset-1 bg-brand-gray rounded-[1.9rem] z-10"></div>
                <div class="relative z-20 p-6 md:p-8">
                  <div class="flex items-center gap-4 mb-4">
                    <div class="step-icon-box w-14 h-14 bg-brand-red/10 rounded-2xl flex items-center justify-center border border-brand-red/20 transition-all duration-300">
                      <i class="fa-solid fa-users text-brand-red text-2xl transition-all duration-300"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black text-white">اختر الانضمام</h3>
                  </div>
                  <p class="text-gray-400 font-bold leading-relaxed text-lg">
                    اختر خيار
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-brand-red/20 border border-brand-red/30 rounded-lg text-brand-red font-black text-base">
                      <i class="fa-solid fa-user-plus text-xs"></i>
                      الانضمام إلى مجموعة
                    </span>
                    من القائمة المتاحة للبدء في عملية الانضمام
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div
            class="step-card relative flex flex-col md:flex-row items-start gap-6 md:gap-8"
            data-aos="fade-left"
            data-aos-duration="1200"
            data-aos-delay="400"
          >
            <div class="flex-shrink-0 relative z-10">
              <div class="step-number w-16 h-16 md:w-20 md:h-20 bg-brand-dark rounded-full flex items-center justify-center border-2 border-brand-red shadow-[0_0_30px_rgba(218,18,18,0.4)]">
                <span class="text-3xl md:text-4xl font-black text-brand-red">٣</span>
              </div>
            </div>
            <div class="card-3d-wrap flex-1 w-full">
              <div class="card-3d bg-brand-gray rounded-[2rem] p-1 shadow-2xl relative group overflow-hidden w-full">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-0 group-hover:opacity-100 group-hover:animate-[spin_4s_linear_infinite] transition-opacity duration-500 z-0"></div>
                <div class="absolute inset-1 bg-brand-gray rounded-[1.9rem] z-10"></div>
                <div class="relative z-20 p-6 md:p-8">
                  <div class="flex items-center gap-4 mb-6">
                    <div class="step-icon-box w-14 h-14 bg-brand-red/10 rounded-2xl flex items-center justify-center border border-brand-red/20 transition-all duration-300">
                      <i class="fa-solid fa-key text-brand-red text-2xl transition-all duration-300"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black text-white">أدخل الكود</h3>
                  </div>
                  <p class="text-gray-400 font-bold leading-relaxed text-lg mb-8">
                    انسخ الكود التالي وأضفه في خانة الكود، أو يمكنك عمل مسح لرمز الـ QR Code الموضح أدناه
                  </p>

                  <!-- Code Box -->
                  <div class="mb-10">
                    <p class="text-gray-500 font-bold text-sm uppercase tracking-widest mb-3">
                      <i class="fa-solid fa-hashtag text-brand-red ml-1"></i>
                      كود المجموعة
                    </p>
                    <div class="flex flex-col sm:flex-row items-stretch gap-3">
                      <div class="flex-1 bg-brand-dark rounded-2xl border-2 border-white/10 p-5 flex items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-30"></div>
                        <span id="groupCode" class="relative text-4xl md:text-5xl font-black text-white tracking-[0.3em] select-all">{{ $group->code_number }}</span>
                      </div>
                      <button
                        id="copyCodeBtn"
                        onclick="copyCode()"
                        class="group flex items-center justify-center gap-3 px-8 py-5 bg-brand-red hover:bg-red-700 text-white font-black text-lg rounded-2xl transition-all duration-300 hover:scale-105 hover:shadow-[0_0_30px_rgba(218,18,18,0.5)] active:scale-95"
                      >
                        <i class="fa-solid fa-copy text-xl group-hover:animate-bounce"></i>
                        <span>نسخ الكود</span>
                      </button>
                    </div>
                  </div>

                  <!-- Divider -->
                  <div class="flex items-center gap-4 mb-10">
                    <div class="flex-1 h-px bg-gradient-to-l from-white/20 to-transparent"></div>
                    <span class="text-gray-500 font-black text-sm uppercase tracking-widest">أو</span>
                    <div class="flex-1 h-px bg-gradient-to-r from-white/20 to-transparent"></div>
                  </div>

                  <!-- QR Code -->
                  <div class="text-center">
                    <p class="text-gray-500 font-bold text-sm uppercase tracking-widest mb-6">
                      <i class="fa-solid fa-qrcode text-brand-red ml-1"></i>
                      امسح رمز الـ QR Code
                    </p>
                    <div class="inline-block qr-glow rounded-3xl p-1 bg-gradient-to-br from-brand-red/30 via-white/10 to-brand-red/30">
                      <div class="bg-white rounded-[1.4rem] p-6 relative">
                        <div id="qrcode" class="flex items-center justify-center" style="min-width: 200px; min-height: 200px;"></div>
                        <!-- Logo overlay on QR -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                          <div class="w-12 h-12 bg-white rounded-xl shadow-lg flex items-center justify-center overflow-hidden border-2 border-brand-red/20">
                            <img
                              src="{{ asset('frontend/assets/img/1024x.png') }}"
                              alt="Logo"
                              class="w-full h-full object-cover"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                    <p class="text-gray-500 font-bold mt-4 text-sm">
                      في حالة اختيار فتح الكاميرا لمسح رمز QR Code في التطبيق، وجّه الكاميرا نحو رمز الـ QR Code في الأعلى
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section
      class="py-16 bg-brand-gray relative z-20 shadow-[0_-20px_50px_rgba(0,0,0,0.5)]"
    >
      <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
          <div
            class="text-center group"
            data-aos="zoom-in-up"
            data-aos-delay="100"
          >
            <div
              class="w-20 h-20 mx-auto bg-brand-dark rounded-full flex items-center justify-center border border-white/5 group-hover:border-brand-red/50 group-hover:shadow-[0_0_30px_rgba(218,18,18,0.3)] transition-all duration-300 mb-4"
            >
              <i
                class="fa-solid fa-users text-3xl text-gray-500 group-hover:text-brand-red transition-colors"
              ></i>
            </div>
            <h3
              class="text-5xl font-black text-white mb-1 group-hover:scale-110 transition-transform inline-block"
            >
              {{ $group->group_users_count ?? 0 }}
            </h3>
            <p class="text-gray-500 font-bold text-sm tracking-wide">عدد أعضاء المجموعة</p>
          </div>

          <div class="text-center group" data-aos="zoom-in-up" data-aos-delay="200">
            <div class="w-20 h-20 mx-auto bg-brand-dark rounded-full flex items-center justify-center border border-white/5 group-hover:border-brand-red/50 group-hover:shadow-[0_0_30px_rgba(218,18,18,0.3)] transition-all duration-300 mb-4">
              <i class="fa-solid fa-hashtag text-3xl text-gray-500 group-hover:text-brand-red transition-colors"></i>
            </div>
            <h3 class="text-5xl font-black text-white mb-1 group-hover:scale-110 transition-transform inline-block">
              {{ $group->code_number ?? 0 }}
            </h3>
            <p class="text-gray-500 font-bold text-sm tracking-wide">كود المجموعة</p>
          </div>

          <div
            class="text-center group"
            data-aos="zoom-in-up"
            data-aos-delay="400"
          >
            <div
              class="w-20 h-20 mx-auto bg-brand-dark rounded-full flex items-center justify-center border border-white/5 group-hover:border-brand-red/50 group-hover:shadow-[0_0_30px_rgba(218,18,18,0.3)] transition-all duration-300 mb-4"
            >
              <i
                class="fa-solid fa-calendar-check text-3xl text-gray-500 group-hover:text-brand-red transition-colors"
              ></i>
            </div>
            <h3
              class="text-5xl font-black text-white mb-1 group-hover:scale-110 transition-transform inline-block"
            >
              {{ $group->created_at ? $group->created_at->format('Y') : '2023' }}
            </h3>
            <p class="text-gray-500 font-bold text-sm tracking-wide">
              تأسيس المجموعة
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section
      class="py-24 relative bg-brand-dark overflow-hidden flex items-center"
    >
      <div
        class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"
      ></div>
      <div
        class="absolute inset-0 bg-gradient-to-b from-transparent via-brand-red/20 to-brand-red border-t border-brand-red/30 shadow-[inset_0_100px_100px_rgba(0,0,0,0.8)]"
      ></div>

      <div class="container mx-auto px-4 lg:px-8 text-center relative z-20">
        <div
          data-aos="zoom-in"
          data-aos-duration="1500"
          class="flex flex-col items-center"
        >
          <div class="relative w-40 h-40 mb-8 group cursor-pointer">
            <div
              class="absolute inset-0 bg-brand-red rounded-full blur-[30px] opacity-60 group-hover:opacity-100 group-hover:blur-[50px] transition-all duration-700 animate-pulse"
            ></div>
            <div
              class="absolute inset-3 bg-white rounded-full flex items-center justify-center shadow-2xl relative z-10 transform group-hover:scale-110 transition-transform duration-500 overflow-hidden"
            >
              <img src="{{ asset('upload/group/'.$group->photo) }}" alt="{{ $group->name }}" class="w-full h-full object-cover" />
            </div>
          </div>

          <h2
            class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-6 tracking-tight drop-shadow-lg"
          >
            حمّل التطبيق
            <span
              class="text-transparent bg-clip-text bg-gradient-to-l from-white to-brand-red"
            >وانضم الآن!</span>
          </h2>
          <p
            class="text-xl text-red-100 font-bold mb-12 max-w-2xl mx-auto opacity-90"
          >
            حمّل تطبيق نجاح ريسنج من متجر التطبيقات واتبع الخطوات السابقة للانضمام إلى المجموعة
          </p>

          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              class="group bg-[#0a0a0a] hover:bg-brand-red border border-white/10 text-white w-[200px] py-4 rounded-[1.2rem] flex justify-center items-center gap-3 transition-all duration-500 shadow-lg overflow-hidden relative hover:scale-105"
            >
              <i
                class="fa-brands fa-apple text-3xl relative z-10 group-hover:-translate-y-1 transition-transform"
              ></i>
              <div
                class="text-right leading-none relative z-10 group-hover:-translate-y-1 transition-transform delay-75"
              >
                <span
                  class="block text-[10px] text-gray-500 group-hover:text-white/80"
                >Download on the</span>
                <span class="block text-lg font-black mt-0.5">App Store</span>
              </div>
            </button>
            <button
              class="group bg-[#0a0a0a] hover:bg-brand-red border border-white/10 text-white w-[200px] py-4 rounded-[1.2rem] flex justify-center items-center gap-3 transition-all duration-500 shadow-lg overflow-hidden relative hover:scale-105"
            >
              <i
                class="fa-brands fa-google-play text-3xl relative z-10 group-hover:-translate-y-1 transition-transform"
              ></i>
              <div
                class="text-right leading-none relative z-10 group-hover:-translate-y-1 transition-transform delay-75"
              >
                <span
                  class="block text-[10px] text-gray-500 group-hover:text-white/80"
                >GET IT ON</span>
                <span class="block text-lg font-black mt-0.5">Google Play</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer
      class="bg-black text-white pt-20 pb-10 border-t border-white/5 relative z-30"
    >
      <div class="container mx-auto px-4 lg:px-8">
        <div
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-8 items-center mb-16"
        >
          <div
            class="flex gap-4 justify-center md:justify-start order-3 lg:order-1"
            data-aos="fade-right"
          >
            <a
              href="#"
              class="w-14 h-14 rounded-2xl bg-[#111111] flex items-center justify-center text-gray-500 hover:text-white hover:bg-brand-red hover:-translate-y-2 hover:-rotate-6 transition-all duration-300 shadow-lg border border-white/5"
            >
              <i class="fa-brands fa-snapchat text-2xl"></i>
            </a>
            <a
              href="#"
              class="w-14 h-14 rounded-2xl bg-[#111111] flex items-center justify-center text-gray-500 hover:text-white hover:bg-brand-red hover:-translate-y-2 hover:rotate-6 transition-all duration-300 shadow-lg border border-white/5"
            >
              <i class="fa-brands fa-instagram text-2xl"></i>
            </a>
          </div>

          <div
            class="flex flex-col items-center justify-center text-center order-1 lg:order-2"
            data-aos="zoom-in"
          >
            <div
              class="w-16 h-16 bg-brand-red/10 rounded-2xl flex items-center justify-center text-brand-red mb-4 border border-brand-red/20 shadow-[0_0_30px_rgba(218,18,18,0.2)] overflow-hidden"
            >
              <img
                src="{{ asset('frontend/assets/img/1024x.png') }}"
                alt="شعار نجاح ريسنج"
                class="w-full h-full object-cover"
              />
            </div>
            <span class="font-black text-3xl tracking-tight text-white"
              >نجاح ريسنج</span
            >
          </div>

          <div
            class="flex flex-col items-center lg:items-end gap-4 order-2 lg:order-3"
            data-aos="fade-left"
          >
            <span
              class="font-bold text-gray-400 uppercase tracking-widest text-xs"
            >التطبيق متاح على</span>
            <div class="flex flex-row gap-3">
              <button
                class="group bg-[#0a0a0a] hover:bg-brand-red border border-white/5 text-white w-[160px] py-3 rounded-[1rem] flex justify-center items-center gap-3 transition-all duration-500 shadow-lg overflow-hidden relative"
              >
                <i
                  class="fa-brands fa-apple text-2xl relative z-10 group-hover:-translate-y-1 transition-transform"
                ></i>
                <div
                  class="text-right leading-none relative z-10 group-hover:-translate-y-1 transition-transform delay-75"
                >
                  <span
                    class="block text-[10px] text-gray-500 group-hover:text-white/80"
                  >Download on the</span>
                  <span class="block text-base font-black mt-0.5">App Store</span>
                </div>
              </button>
              <button
                class="group bg-[#0a0a0a] hover:bg-brand-red border border-white/5 text-white w-[160px] py-3 rounded-[1rem] flex justify-center items-center gap-3 transition-all duration-500 shadow-lg overflow-hidden relative"
              >
                <i
                  class="fa-brands fa-google-play text-2xl relative z-10 group-hover:-translate-y-1 transition-transform"
                ></i>
                <div
                  class="text-right leading-none relative z-10 group-hover:-translate-y-1 transition-transform delay-75"
                >
                  <span
                    class="block text-[10px] text-gray-500 group-hover:text-white/80"
                  >GET IT ON</span>
                  <span class="block text-base font-black mt-0.5">Google Play</span>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div
          class="border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center px-4"
          data-aos="fade-in"
          data-aos-offset="0"
        >
          <p class="text-gray-600 font-bold text-sm">
            جميع الحقوق محفوظة &copy; {{ date('Y') }} نجاح ريسنج.
          </p>
          <div
            class="flex gap-6 mt-6 md:mt-0 text-gray-500 font-bold text-sm uppercase tracking-wide"
          >
            <a href="#" class="hover:text-white transition-colors">مساعدة</a>
            <a href="#" class="hover:text-white transition-colors">الشروط والأحكام</a>
            <a href="#" class="hover:text-white transition-colors">الخصوصية</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      AOS.init({
        once: false,
        offset: 120,
        easing: "ease-out-cubic",
      });

      // Generate QR Code
      document.addEventListener('DOMContentLoaded', function() {
        var groupCode = "{{ $group->code_number }}";
        var qrContainer = document.getElementById('qrcode');

        if (qrContainer) {
          new QRCode(qrContainer, {
            text: groupCode,
            width: 200,
            height: 200,
            colorDark: "#050505",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H,
          });
        }
      });

      // Copy Code Function
      function copyCode() {
        var code = document.getElementById('groupCode').innerText.trim();
        navigator.clipboard.writeText(code).then(function() {
          // Show notification
          var notification = document.getElementById('copyNotification');
          notification.classList.add('show');

          // Change button text
          var btn = document.getElementById('copyCodeBtn');
          var originalHTML = btn.innerHTML;
          btn.innerHTML = '<i class="fa-solid fa-check text-xl"></i><span>تم النسخ!</span>';
          btn.classList.remove('bg-brand-red');
          btn.classList.add('bg-green-600');

          setTimeout(function() {
            notification.classList.remove('show');
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-brand-red');
          }, 2500);
        }).catch(function(err) {
          // Fallback for older browsers
          var textArea = document.createElement('textarea');
          textArea.value = code;
          textArea.style.position = 'fixed';
          textArea.style.left = '-9999px';
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);

          var notification = document.getElementById('copyNotification');
          notification.classList.add('show');
          setTimeout(function() {
            notification.classList.remove('show');
          }, 2500);
        });
      }
    </script>
  </body>
</html>
