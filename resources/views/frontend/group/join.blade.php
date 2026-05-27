<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>نجاح ريسنج - {{ $group->name ?? 'التجربة التفاعلية' }}</title>
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
        } /* Positive to go left in RTL */
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
    </style>
  </head>
  <body
    class="font-sans antialiased text-white flex flex-col min-h-screen selection:bg-brand-red selection:text-white"
  >
    <header
      class="fixed w-full top-0 z-50 transition-all duration-500 bg-brand-dark/80 backdrop-blur-md border-b border-white/5"
      id="navbar"
    >
      <div
        class="container mx-auto px-4 lg:px-8 py-4 flex justify-between items-center"
      >
        <div
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
        </div>

        <button
          data-aos="fade-down"
          data-aos-duration="800"
          data-aos-delay="200"
          class="hidden md:flex items-center gap-2 px-6 py-2.5 bg-white/5 border border-white/10 text-white font-bold rounded-full hover:bg-brand-red hover:border-brand-red transition-all duration-300 shadow-lg hover:-translate-y-1"
        >
          <span>تواصل معنا</span>
        </button>
      </div>
    </header>

    <section
      class="relative min-h-screen flex items-center pt-20 overflow-hidden gradient-shift"
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
        <div class="flex flex-col lg:flex-row items-center gap-16">
          <div class="flex-1 text-center lg:text-right">
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
              مجموعة {{$group->name}}
            </div>

            <h2
              class="text-5xl lg:text-5xl xl:text-5xl font-black mb-6 leading-tight text-white drop-shadow-2xl"
            >
  <span
                data-aos="fade-up"
                data-aos-duration="1000"
                data-aos-delay="100"
                class="block"
                >المجموعات</span
              >



              <span
                data-aos="fade-up"
                data-aos-duration="1000"
                data-aos-delay="100"
                class="block"
                >تجربة مختلفة</span
              >
              <span
                data-aos="fade-up"
                data-aos-duration="1000"
                data-aos-delay="300"
                class="text-5xl lg:text-5xl xl:text-5xl font-black mb-6 leading-tight text-white drop-shadow-2xl"
                >في عالم الهجن</span
              >
            </h2>

            <p
              data-aos="fade-up"
              data-aos-duration="1000"
              data-aos-delay="500"
              class="text-xl md:text-2xl text-gray-300 font-semibold mb-12 max-w-2xl mx-auto lg:mx-0 leading-relaxed drop-shadow-lg"
            >
              انضم إلى مجموعة
              <span class="text-white bg-brand-red/20 px-2 rounded"
                >{{ $group->name ?? 'اسم المجموعة' }}</span
              >! حلل، نافس، وتوقع أبطال الميادين في المجموعة الحصرية.
            </p>

            <div
              data-aos="zoom-in"
              data-aos-duration="1000"
              data-aos-delay="700"
            >
              <a
                href="{{ route('group.join.steps', $group->code_number) }}"
                class="relative inline-flex group w-full sm:w-auto"
              >
                <div
                  class="absolute transition-all duration-1000 opacity-70 -inset-px bg-gradient-to-r from-[#440000] via-brand-red to-[#ff0000] rounded-full blur-lg group-hover:opacity-100 group-hover:-inset-1 group-hover:duration-200 animate-pulse"
                ></div>


                <button

                  class="relative inline-flex items-center justify-center gap-4 px-10 py-5 bg-brand-dark/90 backdrop-blur-xl border border-white/10 text-white text-xl font-black rounded-full overflow-hidden w-full transition-transform hover:scale-105"
                >

                  <span
                    class="relative z-10 group-hover:translate-x-2 transition-transform duration-300"
                    >

                    انضم للمجموعة فوراً
                    </span
                  >

                  <i
                    class="fa-solid fa-chevron-left text-brand-red relative z-10 group-hover:-translate-x-2 transition-transform duration-300"
                  ></i>
                </button>


              </a>
            </div>
          </div>

          <div
            class="flex-1 hidden lg:flex justify-center card-3d-wrap"
            data-aos="fade-left"
            data-aos-duration="1500"
            data-aos-delay="400"
          >
            <div
              class="relative w-full max-w-md aspect-square card-3d liquid-glass rounded-3xl p-8 flex flex-col items-center justify-center border-t border-l border-white/20"
            >
              <div
                class="absolute inset-0 rounded-3xl border-2 border-brand-red/30 animate-[spin_10s_linear_infinite]"
              ></div>
              <div
                class="absolute inset-4 rounded-3xl border-2 border-white/10 animate-[spin_15s_linear_infinite_reverse]"
              ></div>

              <div
                class="relative z-10 w-40 h-40 bg-brand-red rounded-full flex items-center justify-center shadow-[0_0_50px_rgba(218,18,18,0.8)] glow-pulse mb-6"
              >
                <i class="fa-solid fa-flag-checkered text-white text-7xl"></i>
              </div>
              <h3 class="text-3xl font-black text-white glow-text text-center">
                {{ $group->name ?? 'مجموعة ابطال الميادين' }}
              </h3>
              <p class="text-gray-400 font-bold mt-2"></p>
            </div>
          </div>
        </div>
      </div>

      <div
        class="absolute bottom-10 left-1/2 -translate-x-1/2 text-center"
        data-aos="fade-up"
        data-aos-delay="1200"
        data-aos-offset="0"
      >
        <p
          class="text-xs text-brand-red font-black tracking-widest uppercase mb-2"
        >
          اكتشف المزيد
        </p>
        <div
          class="w-1 h-12 bg-white/10 mx-auto rounded-full overflow-hidden relative"
        >
          <div
            class="w-full h-1/2 bg-brand-red absolute top-0 animate-[bounce_2s_infinite]"
          ></div>
        </div>
      </div>
    </section>

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

    <section class="py-32 bg-brand-dark relative overflow-hidden">
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
            >— {{$group->name}} —</span
          >
          <h2 class="text-4xl md:text-5xl lg:text-6xl font-black text-white">
            اخطف الصدارة في التصنيف
          </h2>
        </div>

        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-16 card-3d-wrap"
        >
          <div
            class="card-3d bg-brand-gray rounded-[2.5rem] p-1 shadow-2xl relative group overflow-hidden"
            data-aos="fade-left"
            data-aos-duration="1200"
          >
            <div
              class="absolute inset-0 bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-0 group-hover:opacity-100 group-hover:animate-[spin_4s_linear_infinite] transition-opacity duration-500 z-0"
            ></div>
            <div
              class="absolute inset-1 bg-brand-gray rounded-[2.4rem] z-10"
            ></div>

            <div
              class="relative z-20 p-8 flex flex-col h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"
            >
              <div
                class="flex flex-col sm:flex-row items-center sm:items-start gap-6 mb-8 text-center sm:text-right"
              >
                <div
                  class="w-28 h-28 rounded-[2rem] overflow-hidden border-2 border-white/10 group-hover:border-brand-red transition-colors duration-500 shrink-0 relative"
                >
                  <div
                    class="absolute inset-0 bg-brand-red opacity-0 group-hover:opacity-20 transition-opacity z-10"
                  ></div>
                  <img

                    src="{{  (!empty($owner?->user?->photo) && $owner?->user?->photo != 'non' )  ? url('upload/user_images/'.$owner?->user?->photo):url('upload/no_image.jpg') }}"
                    alt="Founder"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                </div>
                <div class="mt-2">
                  <div
                    class="inline-flex items-center justify-center p-2 bg-brand-red/10 rounded-lg mb-3"
                  >
                    <i class="fa-solid fa-crown text-brand-red"></i>
                  </div>
                  <h4 class="text-3xl font-black text-white mb-1">
                    {{ $owner?->user?->fname }} {{ $owner?->user?->lname }}

                  </h4>
                   <h3 class="text-3xl font-black text-white mb-1">
                    {{ $owner?->user?->country_flag }}

                  </h3>
                  <span
                    class="text-gray-400 font-bold uppercase text-xs tracking-widest"
                    >مؤسس المجموعة</span

                  >

                </div>
              </div>
              <div
                class="mt-auto relative p-6 bg-brand-dark/50 rounded-2xl border border-white/5"
              >
                <i
                  class="fa-solid fa-quote-right absolute -top-5 left-6 text-brand-red opacity-20 text-5xl"
                ></i>
                <p class="text-gray-300 font-bold leading-relaxed text-lg">
                  "في مجموعتنا لا نكتفي بالمتابعة، بل نصنع الحدث. انضم إلينا
                  اليوم لتضع بصمتك في ترشيحات أضخم المهرجانات الخاصة بسباقات
                  الهجن."
                </p>
              </div>
            </div>
          </div>

          <div
            class="card-3d bg-brand-gray rounded-[2.5rem] p-1 shadow-2xl relative group overflow-hidden"
            data-aos="fade-right"
            data-aos-duration="1200"
            data-aos-delay="200"
          >
            <div
              class="absolute inset-0 bg-gradient-to-r from-transparent via-brand-red to-transparent opacity-0 group-hover:opacity-100 group-hover:animate-[spin_4s_linear_infinite] transition-opacity duration-500 z-0"
            ></div>
            <div
              class="absolute inset-1 bg-brand-gray rounded-[2.4rem] z-10"
            ></div>

            <div class="relative z-20 h-full flex flex-col p-2">
              <div
                class="h-64 rounded-t-[2.2rem] w-full relative overflow-hidden bg-brand-dark"
              >
                <img
                  src="{{ asset($group?->festival?->photo) }}"
                  alt="Festival"
                  class="w-full h-full object-cover opacity-60 group-hover:scale-125 group-hover:opacity-100 transition-all duration-[10s] ease-out"
                />
                <div
                  class="absolute inset-0 bg-gradient-to-t from-brand-gray to-transparent"
                ></div>

                <div
                  class="absolute top-6 right-6 flex items-center gap-2 bg-red-600/20 backdrop-blur-md border border-red-500/50 text-white px-4 py-2 rounded-full font-bold shadow-[0_0_15px_rgba(220,38,38,0.5)]"
                >
                  <span
                    class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"
                  ></span>
                  تغطية المجموعة
                </div>
              </div>
              <div class="p-8 flex flex-col justify-center flex-grow">
                <div
                  class="w-14 h-14 bg-brand-red/10 rounded-2xl flex items-center justify-center mb-6 group-hover:-translate-y-2 transition-transform duration-300"
                >
                  <i
                    class="fa-solid fa-fire-flame-curved text-brand-red text-2xl"
                  ></i>
                </div>
                <h4 class="text-3xl font-black text-white mb-4">
                   {{ $group?->festival?->name ?? 'غير محدد' }}
                </h4>
                <p class="text-gray-400 font-bold leading-relaxed text-lg">
                  تابع التغطية الحية والشاملة لأقوى سباقات الهجن والتحديات،
                  وكن الأول في حصاد النقاط والمراكز.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      class="py-32 relative bg-brand-dark overflow-hidden flex items-center"
      id="joinSection"
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
          <div class="relative w-60 h-60 mb-10 group cursor-pointer">
            <div
              class="absolute inset-0 bg-brand-red rounded-full blur-[30px] opacity-60 group-hover:opacity-100 group-hover:blur-[50px] transition-all duration-700 animate-pulse"
            ></div>
            <div
              class="absolute inset-4 bg-white rounded-full flex items-center justify-center shadow-2xl relative z-10 transform group-hover:scale-110 transition-transform duration-500"
            >
              {{-- <i class="fa-solid fa-gamepad text-brand-red text-5xl"></i> --}}
              <a href="#" class="btn btn-info">
    <img                   src="{{ asset("upload/group/".$group?->photo) }}"
 >
</a>
            </div>
          </div>

          <h2
            class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-8 tracking-tight drop-shadow-lg"
          >
            حان وقت
            <span
              class="text-transparent bg-clip-text bg-gradient-to-l from-white to-brand-red"
              >التحدي!</span
            >
          </h2>
          <p
            class="text-2xl text-red-100 font-bold mb-16 max-w-3xl mx-auto opacity-90"
          >
            ضع بصمتك الآن، شارك ترشيحاتك وتنافس مع  الاخرين في نجاح ريسنج
            عبر تطبيق نجاح ريسنج .
          </p>

          <button
            class="relative group w-full sm:w-auto overflow-hidden rounded-full p-[3px]"
            data-aos="fade-up"
            data-aos-duration="1000"
            data-aos-delay="200"
          >
            <span
              class="absolute inset-0 bg-gradient-to-r from-brand-red via-white to-brand-red rounded-full opacity-70 group-hover:opacity-100 group-hover:animate-[spin_2s_linear_infinite] transition-opacity duration-300"
            ></span>
            <div
              class="relative flex items-center justify-center gap-6 px-16 py-8 bg-brand-dark rounded-full group-hover:bg-black transition-colors duration-300"
            >
              <a    href="{{ route('group.join.steps', $group->code_number) }}"
                class="text-3xl font-black text-white tracking-wide group-hover:text-brand-red transition-colors"
                >انضم للمجموعة الان</a
              >
              <i
                class="fa-solid fa-arrow-left text-2xl text-white bg-brand-red w-12 h-12 rounded-full flex items-center justify-center group-hover:-translate-x-3 transition-transform duration-300 shadow-[0_0_20px_rgba(218,18,18,0.5)]"
              ></i>
            </div>
          </button>
        </div>
      </div>
    </section>

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
        {{-- <a
          href="#"
          class="w-14 h-14 rounded-2xl bg-[#111111] flex items-center justify-center text-gray-500 hover:text-white hover:bg-brand-red hover:-translate-y-2 hover:rotate-6 transition-all duration-300 shadow-lg border border-white/5"
        >
          <i class="fa-brands fa-x-twitter text-2xl"></i>
        </a> --}}
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
          >التطبيق متاح على</span
        >
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
                >Download on the</span
              >
              <span class="block text-base font-black mt-0.5"
                >App Store</span
              >
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
                >GET IT ON</span
              >
              <span class="block text-base font-black mt-0.5"
                >Google Play</span
              >
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
        <a href="#" class="hover:text-white transition-colors"
          >الشروط والأحكام</a
        >
        <a href="#" class="hover:text-white transition-colors">الخصوصية</a>
      </div>
    </div>
  </div>
</footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      AOS.init({
        once: false,
        offset: 120,
        easing: "ease-out-cubic",
      });
    </script>
  </body>
</html>
