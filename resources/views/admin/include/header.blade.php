<header>

    <style>
          /* الاستايل الخاص بالاشعارات*/

  /* إجبار ظهور التمرير داخل القائمة */
.dropdown-menu {
    overflow: visible; /* تأكد أن الوالد لا يخفي المحتوى */
}

/* العنصر الذي يحتوي الإشعارات */
.header-notifications-list.ps,
.header-notifications-list {
    max-height: 300px !important;      /* ارتفاع أقصى */
    overflow-y: auto !important;       /* تفعيل التمرير العمودي */
    -webkit-overflow-scrolling: touch; /* smooth scrolling على iOS */
}

/* لو كانت مكتبة PerfectScrollbar تضيف عناصر داخلية (ps__rail-y) */
.ps__rail-y {
    display: block !important;
}

/* ✅ تحسين التفاف النص داخل الإشعارات */
.header-notifications-list a,
.header-notifications-list p,
.header-notifications-list h6 {
    white-space: normal !important;   /* يسمح بتعدد الأسطر */
    word-wrap: break-word !important; /* يكسر الكلمات الطويلة */
    overflow-wrap: break-word !important; /* دعم إضافي للمتصفحات الحديثة */
}


/* إصلاح حجم وموقع قائمة الإشعارات */
.dropdown-menu {
    max-height: 350px !important; /* يمكنك تغيير الارتفاع */
    overflow-y: auto !important;  /* تشغيل التمرير */
    overflow-x: hidden !important;
}


/* خصوصاً لقائمة الإشعارات */
.header-notifications-list {
    max-height: 300px !important;
    overflow-y: auto !important;
}


.alert-count {
    position: absolute;
    top: 0;
    right: 0;
    background: red;
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    padding: 0.15rem 0.4rem;
    border-radius: 50%;
    min-width: 23px;
    height: 23px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    white-space: nowrap;
}

          /* انتهاء الاستايل الخاص بالاشعارات*/

</style>




    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
            </div>




              <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    <li class="nav-item mobile-search-icon d-flex d-lg-none" data-bs-toggle="modal" data-bs-target="#SearchModal">

                    </li>

                    <li class="nav-item dark-mode d-none d-sm-flex">
                        {{-- <a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
                        </a> --}}
                    </li>

                    <li class="nav-item dropdown dropdown-app">



                        <div class="dropdown-menu dropdown-menu-end p-0">
                            <div class="app-container p-2 my-2">
                              <div class="row gx-0 gy-2 row-cols-3 justify-content-center p-2">
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/slack.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Slack</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/behance.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Behance</p>
                                      </div>
                                      </div>
                                  </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                        <img src="assets/images/app/google-drive.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Dribble</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/outlook.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Outlook</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/github.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">GitHub</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/stack-overflow.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Stack</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/figma.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Stack</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/twitter.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Twitter</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/google-calendar.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Calendar</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/spotify.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Spotify</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/google-photos.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Photos</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/pinterest.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Photos</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/linkedin.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">linkedin</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/dribble.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Dribble</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/youtube.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">YouTube</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/google.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">News</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/envato.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Envato</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>
                                 <div class="col">
                                  <a href="javascript:;">
                                    <div class="app-box text-center">
                                      <div class="app-icon">
                                          <img src="assets/images/app/safari.png" width="30" alt="">
                                      </div>
                                      <div class="app-name">
                                          <p class="mb-0 mt-1">Safari</p>
                                      </div>
                                      </div>
                                    </a>
                                 </div>

                              </div><!--end row-->

                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown dropdown-large">

                        <div class="dropdown-menu dropdown-menu-end">

                            <div class="header-notifications-list">
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-1.png" class="msg-avatar" alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Daisy Anderson<span class="msg-time float-end">5 sec
                                        ago</span></h6>
                                            <p class="msg-info">The standard chunk of lorem</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="notify bg-light-danger text-danger">dc
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">New Orders <span class="msg-time float-end">2 min
                                        ago</span></h6>
                                            <p class="msg-info">You have recived new orders</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-2.png" class="msg-avatar" alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Althea Cabardo <span class="msg-time float-end">14
                                        sec ago</span></h6>
                                            <p class="msg-info">Many desktop publishing packages</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="notify bg-light-success text-success">
                                            <img src="assets/images/app/outlook.png" width="25" alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Account Created<span class="msg-time float-end">28 min
                                        ago</span></h6>
                                            <p class="msg-info">Successfully created new email</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="notify bg-light-info text-info">Ss
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">New Product Approved <span
                                        class="msg-time float-end">2 hrs ago</span></h6>
                                            <p class="msg-info">Your new product has approved</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="{{ asset('backend/assets/images/avatars/avatar-4.png') }}" class="msg-avatar" alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Katherine Pechon <span class="msg-time float-end">15
                                        min ago</span></h6>
                                            <p class="msg-info">Making this the first true generator</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="notify bg-light-success text-success"><i class='bx bx-check-square'></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Your item is shipped <span class="msg-time float-end">5 hrs
                                        ago</span></h6>
                                            <p class="msg-info">Successfully shipped your item</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="notify bg-light-primary">
                                            <img src="assets/images/app/github.png" width="25" alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">New 24 authors<span class="msg-time float-end">1 day
                                        ago</span></h6>
                                            <p class="msg-info">24 new authors joined last week</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-8.png" class="msg-avatar" alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Peter Costanzo <span class="msg-time float-end">6 hrs
                                        ago</span></h6>
                                            <p class="msg-info">It was popularised in the 1960s</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <a href="javascript:;">
                                <div class="text-center msg-footer">
                                    <button class="btn btn-primary w-100">View All Notifications</button>
                                </div>
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown dropdown-large">

                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:;">
                                <div class="msg-header">
                                    <p class="msg-header-title">My Cart</p>
                                    <p class="msg-header-badge">10 Items</p>
                                </div>
                            </a>
                            <div class="header-message-list">
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/11.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/02.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/03.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/04.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/05.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/06.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/07.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/08.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="cart-product rounded-circle bg-light">
                                                <img src="assets/images/products/09.png" class="" alt="product image">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="cart-product-title mb-0">Men White T-Shirt</h6>
                                            <p class="cart-product-price mb-0">1 X $29.00</p>
                                        </div>
                                        <div class="">
                                            <p class="cart-price mb-0">$250</p>
                                        </div>
                                        <div class="cart-product-cancel"><i class="bx bx-x"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <a href="javascript:;">
                                <div class="text-center msg-footer">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h5 class="mb-0">Total</h5>
                                        <h5 class="mb-0 ms-auto">$489.00</h5>
                                    </div>
                                    <button class="btn btn-primary w-100">Checkout</button>
                                </div>
                            </a>
                        </div>
                    </li>

    @php
        $ncount = session()->get('unread_notifications_count');
        $ncount_time = session()->get('unread_notifications_time', 0);
        if ($ncount === null || $ncount_time < time() - 30) {
            $ncount = Auth::user()->unreadNotifications()->count();
            session()->put('unread_notifications_count', $ncount);
            session()->put('unread_notifications_time', time());
        }
    @endphp
{{--
<li class="nav-item dropdown dropdown-large">
    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
        @if ($ncount != 0)
            <span class="alert-count">{{ $ncount }}</span>
        @endif
        <i class="bx bx-bell"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-end">
        <a href="javascript:;">
            <div class="msg-header">
                <p class="msg-header-title">الإشعارات</p>
            </div>
        </a>

        <!-- ✅ قائمة الإشعارات مع scroll -->
        <div class="header-notifications-list ps"
             style="max-height: 300px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #ED7032 #f1f1f1;">

            @php
                $user = Auth::user();
            @endphp

            @forelse ($user->unreadNotifications as $notification)

                <a class="dropdown-item" href="{{ route('notification.read', $notification) }}">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="msg-name">
                                {{ $notification->data['type'] }}
                                <span class="msg-time float-end">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </h6>

                            <p class="msg-info">
                                {{ $notification->data['message'] }} بواسطة المستخدم
                                <span style="color: red">
                                    {{ $notification->data['userCreatNomination']['fname'] ?? '—' }}
                                </span>
                            </p>

                            <p class="msg-info">
                                رشح المطية
                                <span style="color: red">
                                    {{ $notification->data['camelRoundParticipation']['camel_name'] ?? '—' }}
                                </span>
                            </p>

                            <p class="msg-info">
                                في الشوط
                                <span style="color: red">
                                    {{ $notification->data['round']['name'] ?? '—' }}
                                </span>
                            </p>

                            <p class="msg-info">
                                المهرجان
                                <span style="color: red">
                                    {{ $notification->data['festival']['name'] ?? '—' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </a>


            @empty
                <p class="text-center text-muted p-3">لا توجد إشعارات جديدة</p>
            @endforelse
        </div>
    </div>
</li> --}}



@if(false)
<li class="nav-item dropdown dropdown-large">
    {{-- <a id="notificationsToggle"
       class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
       href="#" data-bs-toggle="dropdown">
        @if ($ncount != 0)
            <span class="alert-count">{{ $ncount }}</span>
        @endif
        <i class="bx bx-bell"></i>
    </a> --}}

    <a id="notificationsToggle"
   class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
   href="#" data-bs-toggle="dropdown">
    @if ($ncount != 0)
        <span class="alert-count">
            {{ $ncount > 99 ? '99+' : $ncount }}
        </span>
    @endif
    <i class="bx bx-bell"></i>
</a>

    <div class="dropdown-menu dropdown-menu-end">
        <div class="msg-header">
            <p class="msg-header-title">الإشعارات</p>
        </div>

        <div id="notifications-container"
             class="header-notifications-list ps"
             style="max-height: 300px; overflow-y: auto;">
        </div>

        <div id="loading-spinner"
             class="text-center py-2"
             style="display:none;">
            <small>جارِ التحميل...</small>
        </div>

        <p id="no-data"
           class="text-center text-muted p-3"
           style="display:none;">
            لا توجد إشعارات جديدة
        </p>
    </div>
</li>
@endif






                </ul>
            </div>
            <div class="user-box dropdown px-3">
                <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ (!empty(Auth()->user()->photo)) ? url('upload/admin_images/'.Auth()->user()->photo):url('upload/no_image.jpg') }}" class="user-img" alt="user avatar">
                    <div class="user-info">
                        <p class="user-name mb-0">{{Auth()->user()->fname}}</p>
                        <p class="designattion mb-0">{{Auth()->user()->email}}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a  class="dropdown-item d-flex align-items-center" href="{{route('admin.profile')}}"><i class="bx bx-user fs-5"></i><span>الملف الشخصي</span></a>
                    </li>
                    <li><a class="dropdown-item" href="{{ route('admin.change.password') }}"><i class="bx bx-cog"></i><span>تعديل كلمة المرور</span></a>
                    </li>



                    <li>
                        <div class="dropdown-divider mb-0"></div>
                    </li>
                    <li><a href="{{route('admin.logout')}}" class="dropdown-item d-flex align-items-center" href="javascript:;"><i class="bx bx-log-out-circle"></i><span>تسجيل الخروج</span></a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>


</header>
@if(false)
<script>
    let notifyPage = 1;
    let isLoading = false;
    let hasMoreData = true;

    const container = document.getElementById('notifications-container');
    const toggleBtn = document.getElementById('notificationsToggle');

    function loadNotifications() {
        if (isLoading || !hasMoreData) return;
        isLoading = true;
        document.getElementById('loading-spinner').style.display = 'block';

        fetch(`{{ route('notifications.load') }}?page=${notifyPage}`)
            .then(res => res.json())
            .then(data => {
                const items = data.notifications;

                if (items.length === 0 && notifyPage === 1) {
                    document.getElementById('no-data').style.display = 'block';
                }

                items.forEach(n => {
                    container.insertAdjacentHTML('beforeend', `
                        <a class="dropdown-item" href="/notification/read/${n.id}">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="msg-name">
                                        ${n.data.type ?? ''}
                                        <span class="msg-time float-end">
                                                ${n.timeAgo}

                                        </span>
                                    </h6>

                                    <p class="msg-info">
                                        ${n.data.message ?? ''} بواسطة المستخدم
                                        <span style="color: red">
                                            ${n.data.userCreatNomination?.fname ?? '—'}
                                        </span>
                                    </p>

                                    <p class="msg-info">
                                        رشح المطية
                                        <span style="color: red">
                                            ${n.data.camelRoundParticipation?.camel_name ?? '—'}
                                        </span>
                                    </p>

                                    <p class="msg-info">
                                        في الشوط
                                        <span style="color: red">
                                            ${n.data.round?.name ?? '—'}
                                        </span>
                                    </p>

                                    <p class="msg-info">
                                        المهرجان
                                        <span style="color: red">
                                            ${n.data.festival?.name ?? '—'}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    `);
                });

                hasMoreData = data.hasMore;
                notifyPage++;
                isLoading = false;
                document.getElementById('loading-spinner').style.display = 'none';
            });
    }

    toggleBtn.addEventListener('click', () => {
        if (notifyPage === 1) {
            loadNotifications();
        }
    });

    container.addEventListener('scroll', function () {
        if (this.scrollTop + this.clientHeight >= this.scrollHeight - 10) {
            loadNotifications();
        }
    });
</script>
@endif
