<!DOCTYPE html>
<html lang="ar" dir="rtl">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />

    <!-- Bootstrap Min CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.rtl.min.css') }}" />

<!-- Animate Min CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/animate.min.css') }}" />

<!-- Owl Carousel Min CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.carousel.min.css') }}" />

<!-- Fontawesome CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/fontawesome.min.css') }}" />

<!-- Odometer CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/odometer.css') }}" />

<!-- Popup CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/magnific-popup.min.css') }}" />

<!-- Slick CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/slick.min.css') }}" />

<!-- Style CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}" />

<!-- Dark CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/dark.css') }}" />

<!-- Responsive CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/responsive.css') }}" />

<!-- RTL CSS -->
<link rel="stylesheet" href="{{ asset('frontend/assets/css/rtl.css') }}" />

    <title>نجاح ريسنج</title>

    {{-- <link rel="icon" type="image/png" href="assets/img/favicon.png" /> --}}
  </head>

  <body data-bs-spy="scroll" data-bs-offset="120">
    <!-- Start Preloader Area -->
    <div class="preloader">
      <div class="preloader">
        <span></span>
        <span></span>
      </div>
    </div>
    <!-- End Preloader Area -->

    <!-- Start Navbar Area -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container-fluid">
        <div class="logo">
          <a href="index.html">
            <h3>نجاح ريسنج</h3>
          </a>
        </div>
      </div>
    </nav>
    <!-- End Navbar Area -->

    <!-- Start Main Banner Area -->
    <div id="home" class="main-banner">
      <div class="d-table">
        <div class="d-table-cell">
          <div class="container-fluid">
            <div class="row align-items-center">
              <div class="col-lg-6">
                <div class="banner-content">
                  <h1>نجاح ريسنج</h1>
                  <p>
                    أول تطبيق يجعل الجمهور جزء من سباقات الهجن من خلال الترشيح
                    والمنافسة الممتعة يقدم التطبيق تجربة تفاعلية فريدة تمكن
                    المستخدم من المشاركة في ترشيح المطايا (الإبل) في مختلف
                    الأشواط ضمن هذه المهرجانات، مما يضفي روح المنافسة والحماس
                    على تجربة المتابعة. 🎉
                  </p>

                  <div class="banner-holder">
                    <a
                      href="https://play.google.com/store/apps/details?id=com.app.fantasy_alhajin"
                    >
                      {{-- <img src="assets/img/store/1.png" alt="image" /> --}}
                      <img src="{{ asset('frontend/assets/img/store/1.png') }}" alt="image" />

                    </a>
                    <a
                      href="https://apps.apple.com/kw/app/fantasy-alhajin-%D9%81%D8%A7%D9%86%D8%AA%D8%B3%D9%8A-%D8%A7%D9%84%D9%87%D8%AC%D9%86/id6754517310"
                    >
                      {{-- <img src="assets/img/store/2.png" alt="image" /> --}}
                      <img src="{{ asset('frontend/assets/img/store/2.png') }}" alt="image" />
                    </a>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="banner-image">
                  {{-- <img src="assets/img/1024.png" alt="image" /> --}}
                  <img src="{{ asset('frontend/assets/img/1024.png') }}" alt="image" />

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="default-shape">
        <div class="shape-1">
<img src="{{ asset('frontend/assets/img/shape/1.png') }}" alt="image" />
        </div>

        <div class="shape-3">
<img src="{{ asset('frontend/assets/img/shape/3.svg') }}" alt="image" />
        </div>

        <div class="shape-4">
<img src="{{ asset('frontend/assets/img/shape/4.svg') }}" alt="image" />
        </div>

        <div class="shape-5">
<img src="{{ asset('frontend/assets/img/shape/5.png') }}" alt="image" />
        </div>
      </div>
    </div>
    <!-- End Main Banner Area -->

    <!-- Start Fun Facts Area -->

    <!-- End Fun Facts Area -->

    <!-- Start About Area -->
    <section id="about" class="about-area pb-100">
      <div class="container">
        <div class="section-title">
          <h2>المهرجانات والترشيحات</h2>
          <div class="bar"></div>
          <p>
            يستطيع المستخدم استعراض جميع المهرجانات، الأشواط، والمشاركين في
            الترشيحات بسهولة. كما يمكنه متابعة ترتيبه بين المتسابقين، مما يضيف
            روح التنافس والحماس بين المستخدمين ويجعل تجربة المشاركة أكثر إثارة.
          </p>
        </div>

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-content">
              <h3>الأشواط</h3>
              <div class="bar"></div>
              <b>
                يستطيع المستخدم استعراض تفاصيل كل شوط بدقة، بما في ذلك تاريخ
                البداية والنهاية، مع إمكانية مشاهدة جميع المطايا المشاركة في
                الشوط. يمكنك أيضًا التعرّف على اسم المطية، واسم المالك، والدولة،
                والفئة العمرية، بالإضافة إلى إمكانية ترشيح أي مطية ترغب بها ضمن
                الشوط. وبعد انتهاء الشوط، يقوم التطبيق بتحليل نتائج الترشيح
                لتحديد ما إذا كان اختيارك صحيحًا أم لا، وفي حال كان الترشيح
                صحيحًا، يتم عرض رسالة تهنئة مخصصة توضح النتيجة والدرجة التي حصلت
                عليها بناءً على الفئة العمرية للمطية التي قمت بترشيحها، مما يضيف
                طابعًا من التفاعل والحماس والمنافسة الودية بين المستخدمين.
              </b>
              <!-- <div class="about-btn">
                <a href="#" class="default-btn">
                  Download Now
                  <span></span>
                </a>
              </div> -->
            </div>
          </div>

          <div class="col-lg-6">
            <div class="about-image">
<img src="{{ asset('frontend/assets/img/about.png') }}" alt="image" />
            </div>
          </div>
        </div>
      </div>

      <div class="default-shape">
        <div class="shape-1">
<img src="{{ asset('frontend/assets/img/shape/1.png') }}" alt="image" />
        </div>

        <div class="shape-3">
<img src="{{ asset('frontend/assets/img/shape/3.svg') }}" alt="image" />
        </div>

        <div class="shape-4">
<img src="{{ asset('frontend/assets/img/shape/4.svg') }}" alt="image" />
        </div>

        <div class="shape-5">
            <img src="{{ asset('frontend/assets/img/shape/5.png') }}" alt="image" />

        </div>
      </div>
    </section>
    <!-- End About Area -->

    <section id="about" class="about-area pb-100">
      <div class="container">
        <div class="section-title">
          <h2>متابعة ترتيب المتسابقين</h2>
          <div class="bar"></div>
          <p>
            يتيح التطبيق للمستخدم مشاهدة ترتيب المتسابقين الآخرين في جميع
            المهرجانات، بالإضافة إلى إجمالي النقاط التي حصلوا عليها.
          </p>
        </div>

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-content">
              <h3>النقاط</h3>
              <div class="bar"></div>
              <b>
                كما يمكن الاطلاع على تفاصيل النقاط لكل شوط من أشواط المهرجان،
                بما في ذلك: اسم الشوط وتاريخ إقامته المطية التي تم ترشيحها
                وتاريخ الترشيح عدد النقاط التي حصل عليها المستخدم حالة الترشيح،
                سواء كان صحيحًا أو خاطئًا تساعد هذه الميزة على تعزيز روح
                المنافسة والمصداقية، مع منح المستخدمين متابعة دقيقة لأدائهم
                وأداء الآخرين في كل مهرجان.
              </b>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="about-image">
<img src="{{ asset('frontend/assets/img/about2.png') }}" alt="image" />
            </div>
          </div>
        </div>
      </div>

      <div class="default-shape">
        <div class="shape-1">
<img src="{{ asset('frontend/assets/img/shape/1.png') }}" alt="image" />
        </div>

        <div class="shape-3">
<img src="{{ asset('frontend/assets/img/shape/3.svg') }}" alt="image" />
        </div>

        <div class="shape-4">
<img src="{{ asset('frontend/assets/img/shape/4.svg') }}" alt="image" />
        </div>

        <div class="shape-5">
<img src="{{ asset('frontend/assets/img/shape/5.png') }}" alt="image" />
        </div>
      </div>
    </section>
    <!-- Start Features Area -->
    <section id="features" class="features-area pb-70">
      <div class="container">
        <div class="section-title">
          <h2>مميزات التطبيق</h2>
          <div class="bar"></div>
          <p>
            يوفّر التطبيق تجربة تسجيل بسيطة وسريعة باستخدام رقم الهاتف فقط، مع
            إرسال رمز التفعيل (OTP) مباشرة عبر الواتس آب أو الرسائل القصيرة
            (SMS) لضمان وصول فوري وسلس.
          </p>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-6">
            <div class="single-features">
              <div class="icon">
                <i class="fa fa-sun"></i>
              </div>
              <h3>تجربة استخدام سلسة</h3>
              <p>
                تم تطوير التطبيق باستخدام أحدث الأدوات والتقنيات البرمجية لضمان
                واجهة سهلة الاستخدام تناسب جميع الفئات، وتقدّم تجربة تفاعلية
                مريحة وواضحة.
              </p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="single-features">
              <div class="icon">
                <i class="fa fa-lightbulb"></i>
              </div>
              <h3>نظام إشعارات ذكي</h3>
              <p>
                يتيح النظام إرسال إشعارات فورية للمستخدمين حول آخر الأخبار
                والتحديثات ومهرجانات الهجن، لضمان تواصل مستمر وتفاعل دائم مع كل
                جديد.
              </p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="single-features">
              <div class="icon">
                <i class="fa fa-desktop"></i>
              </div>
              <h3>-ترتيب المتسابقين والمصداقية</h3>
              <p>
                بعد انتهاء كل شوط، يعرض التطبيق نتائج الترشيحات وترتيب
                المتسابقين، مما يعزز الشفافية والمصداقية ويضيف روح المنافسة
                والحماس بين المشاركين
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="default-shape">
        <div class="shape-1">
<img src="{{ asset('frontend/assets/img/shape/1.png') }}" alt="image" />
        </div>

        <div class="shape-2 rotateme">
<img src="{{ asset('frontend/assets/img/shape/2.png') }}" alt="image" />
        </div>

        <div class="shape-3">
<img src="{{ asset('frontend/assets/img/shape/3.svg') }}" alt="image" />
        </div>

        <div class="shape-4">
<img src="{{ asset('frontend/assets/img/shape/4.svg') }}" alt="image" />
        </div>

        <div class="shape-5">
<img src="{{ asset('frontend/assets/img/shape/5.png') }}" alt="image" />
        </div>
      </div>
    </section>
    <!-- End Features Area -->

    <!-- End Screenshot Area -->
    <section id="screenshots" class="screenshot-area ptb-100">
      <div class="container-fluid">
        <div class="section-title">
          <h2>صور من التطبيق</h2>
          <div class="bar"></div>
          <p>بعض من لقطات شاشة التطبيق</p>
        </div>

        <div class="screenshot-slider owl-carousel owl-theme">
          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/1.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/2.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/3.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/4.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/5.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/6.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/1.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/2.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/3.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/4.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/5.png') }}" alt="image" />
            </div>
          </div>

          <div class="screenshot-item">
            <div class="image">
<img src="{{ asset('frontend/assets/img/screenshot/6.png') }}" alt="image" />
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Screenshot Area -->

    <!-- Start Testimonial Area -->

    <!-- End Testimonial Area -->

    <!-- Start Overview Area -->
    <section class="overview-area ptb-100">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="overview-content">
              <h3>
                حصل على الترتيب الرابع من افضل التطبيقات بعد اليوم الأول من
                الاطلاق على الابل استور
              </h3>
              <div class="bar"></div>

              <!-- <div class="overview-btn">
                <a href="#" class="default-btn">
                  Get It Now
                  <span></span>
                </a>
              </div> -->
            </div>
          </div>

          <div class="col-lg-6">
            <div class="overview-image">
<img src="{{ asset('frontend/assets/img/overview.png') }}" alt="image" />
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Overview Area -->

    <!-- Start Pricing Area -->

    <!-- End Pricing Area -->

    <!-- Start Faq Area -->

    <!-- End Faq Area -->

    <!-- Start Team Area -->

    <!-- End Team Area -->

    <!-- Start App Download Area -->
    <section class="app-download ptb-100">
      <div class="container">
        <div class="app-download-content">
          <h3>حمل التطبيق الان</h3>

          <div class="bar"></div>
          <p>متاح الآن على App Store وGoogle Play</p>
            <br>
                        <br>
          <div class="app-holderx">
            <a
              href="https://play.google.com/store/apps/details?id=com.app.fantasy_alhajin"
            >
<img src="{{ asset('frontend/assets/img/store/1.png') }}" alt="image" />
            </a>
            <br>
                        <br>

            <a
              href="https://apps.apple.com/kw/app/fantasy-alhajin-%D9%81%D8%A7%D9%86%D8%AA%D8%B3%D9%8A-%D8%A7%D9%84%D9%87%D8%AC%D9%86/id6754517310"
            >
<img src="{{ asset('frontend/assets/img/store/2.png') }}" alt="image" />
            </a>
          </div>
        </div>
      </div>
    </section>
    <!-- End App Download Area -->

    <!-- Start Blog Area -->

    <!-- End Blog Area -->

    <!-- Start Contact Area -->

    <!-- End Subscribe Area -->

    <!-- Start Footer Area -->
    <section class="footer-area pt-100 pb-70">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-sm-6">
            <div class="single-footer-widget">
              <a href="#" class="logo">
                <h2>التواصل الاجتماعي</h2>
              </a>
              {{-- <p>تايع أخبار التطبيق من خلال صفحات التواصل الاجتماعي</p> --}}
              <ul class="social-list">
                {{-- <li>
                  <a href="" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                  </a>
                </li> --}}
                {{-- <li>
                  <a href="" target="_blank">
                    <i class="fab fa-twitter"></i>
                  </a>
                </li> --}}
                <li>
                  <a
                    href="https://www.instagram.com/fantasy_hajen/?hl=en"
                    target="_blank"
                  >
                    <i class="fab fa-instagram"></i>
                  </a>
                </li>

              </ul>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6">
            <div class="single-footer-widget pl-5"></div>
          </div>

          <div class="col-lg-3 col-sm-6"></div>

          <div class="col-lg-3 col-sm-6">
            <div class="single-footer-widget">
              <h3>التطبيق متاح الان</h3>

              <ul class="footer-holder">
                <li>
                  <a
                    href="https://play.google.com/store/apps/details?id=com.app.fantasy_alhajin"
                  >
<img src="{{ asset('frontend/assets/img/store/1.png') }}" alt="image" />
                  </a>
                </li>
                <li>
                  <a
                    href="https://apps.apple.com/kw/app/fantasy-alhajin-%D9%81%D8%A7%D9%86%D8%AA%D8%B3%D9%8A-%D8%A7%D9%84%D9%87%D8%AC%D9%86/id6754517310"
                  >
<img src="{{ asset('frontend/assets/img/store/2.png') }}" alt="image" />
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Footer Area -->

    <!-- Start Copy Right Area -->
    <div class="copy-right">
      <div class="container">
        <div class="copy-right-content">
          <p>
            © جميع الحقوق محفوظة
            <a href="" target="_blank">نجاح ريسنج</a>
          </p>
        </div>
      </div>
    </div>
    <!-- End Copy Right Area -->

    <!-- Start Go Top Section -->
    <div class="go-top">
      <i class="fa fa-chevron-up"></i>
      <i class="fa fa-chevron-up"></i>
    </div>
    <!-- End Go Top Section -->

   <!-- jQuery Min JS -->
<script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>

<!-- Bootstrap Min JS -->
<script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>

<!-- Owl Carousel Min JS -->
<script src="{{ asset('frontend/assets/js/owl.carousel.min.js') }}"></script>

<!-- Appear JS -->
<script src="{{ asset('frontend/assets/js/jquery.appear.js') }}"></script>

<!-- Odometer JS -->
<script src="{{ asset('frontend/assets/js/odometer.min.js') }}"></script>

<!-- Slick JS -->
<script src="{{ asset('frontend/assets/js/slick.min.js') }}"></script>

<!-- Particles JS -->
<script src="{{ asset('frontend/assets/js/particles.min.js') }}"></script>

<!-- Ripples JS -->
<script src="{{ asset('frontend/assets/js/jquery.ripples-min.js') }}"></script>

<!-- Popup JS -->
<script src="{{ asset('frontend/assets/js/jquery.magnific-popup.min.js') }}"></script>

<!-- WOW Min JS -->
<script src="{{ asset('frontend/assets/js/wow.min.js') }}"></script>

<!-- AjaxChimp Min JS -->
<script src="{{ asset('frontend/assets/js/jquery.ajaxchimp.min.js') }}"></script>

<!-- Form Validator Min JS -->
<script src="{{ asset('frontend/assets/js/form-validator.min.js') }}"></script>

<!-- Contact Form Min JS -->
<script src="{{ asset('frontend/assets/js/contact-form-script.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('frontend/assets/js/main.js') }}"></script>



<!-- WhatsApp Floating Button -->
<style>
    .whatsapp-float {
        position: fixed;
        bottom: 20px;
        right: 20px; /* يمين الشاشة */
        z-index: 99999;
    }
    .whatsapp-float img {
        width: 60px;
        height: 60px;
    }
</style>

<a
    href="https://wa.me/+96551673464"
    class="whatsapp-float"
    target="_blank"
>
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
</a>



  </body>
</html>
