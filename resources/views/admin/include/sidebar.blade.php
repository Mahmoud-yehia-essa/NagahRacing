<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{ asset('backend/assets/images/logo-icon.png') }}" class="logo-icon" alt="logo icon">

        </div>
        <div>
            <h4 class="logo-text">نجاح ريسنج</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
        </div>
     </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="{{route('dashboard')}}">
                <div class="parent-icon"><i class='bx bx-home-alt'></i>
                </div>
                <div class="menu-title">الرئيسية</div>
            </a>
        </li>



        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <ion-icon name="person-outline"></ion-icon>
                </div>

                <div class="menu-title"> إدارة الملاك</div>
            </a>
            <ul>
                <li> <a href="{{route('add.user')}}"><i class='bx bx-radio-circle'></i>إضافة مالك جديد</a>
                </li>
                <li> <a href="{{route('all.owners')}}"><i class='bx bx-radio-circle'></i>عرض الملاك</a>
                </li>
                {{-- <li> <a href="{{route('all.users')}}"><i class='bx bx-radio-circle'></i>عرض المستخدمين</a>
                </li> --}}
                <li> <a href="{{route('all.admin')}}"><i class='bx bx-radio-circle'></i>عرض المديرين</a>
                </li>






            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-group'></i>
                </div>
                <div class="menu-title">إدارة العمال</div>
            </a>
            <ul>
                <li> <a href="{{ route('all.camel.workers') }}"><i class='bx bx-radio-circle'></i>عرض العمال</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-run'></i>
                </div>
                <div class="menu-title">جلسات التدريب</div>
            </a>
            <ul>
                <li> <a href="{{ route('all.training.sessions') }}"><i class='bx bx-radio-circle'></i>عرض جلسات التدريب</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='bx bx-credit-card-front'></i>
                </div>
                <div class="menu-title">الاشتراكات والباقات</div>
            </a>
            <ul>
                <li> <a href="{{ route('all.subscription.plans') }}"><i class='bx bx-radio-circle'></i>باقات الاشتراك</a>
                </li>
                <li> <a href="{{ route('all.user.subscriptions') }}"><i class='bx bx-radio-circle'></i>اشتراكات الملاك</a>
                </li>
            </ul>
        </li>


{{--
          <li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon">
<ion-icon name="flag-outline"></ion-icon>


        </div>
        <div class="menu-title"> إدارة المطايا</div>
    </a>
    <ul>
        <li> <a href="{{route('add.camal')}}"><i class='bx bx-radio-circle'></i>إضافة مطية جديدة</a></li>
        <li> <a href="{{route('all.camal')}}"><i class='bx bx-radio-circle'></i>عرض المطايا</a></li>
    </ul>
</li> --}}





{{--
      <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">ادارة المهرجانات</div>
            </a>
            <ul>
                <li> <a href="{{route('all.festival')}}"><i class='bx bx-radio-circle'></i>عرض المهرجانات</a>
                </li>
                <li> <a href="{{route('add.festival')}}"><i class='bx bx-radio-circle'></i>إضافة مهرجان جديد</a>
                </li>





            </ul>
        </li>


           <li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-category"></i></div>
        <div class="menu-title">إدارة الأشواط</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.round') }}">
                <i class='bx bx-radio-circle'></i>عرض  الأشواط
            </a>
        </li>
        <li>
            <a href="{{ route('add.round') }}">
                <i class='bx bx-radio-circle'></i>إضافة شوط جديدة
            </a>
        </li>
    </ul>
</li>



        <li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-purchase-tag-alt"></i></div>
        <div class="menu-title">ادارة نقاط المهرجانات</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.festival.points') }}">
                <i class='bx bx-radio-circle'></i>عرض جميع النقاط
            </a>
        </li>
        <li>
            <a href="{{ route('add.festival.points') }}">
                <i class='bx bx-radio-circle'></i>إضافة نقاط جديدة
            </a>
        </li>
    </ul>
</li>



      <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">ادارة المجموعات</div>
            </a>
            <ul>
                <li> <a href="{{route('all.groub')}}"><i class='bx bx-radio-circle'></i>عرض المجموعات</a>
                </li>
                <li> <a href="{{route('add.festival')}}"><i class='bx bx-radio-circle'></i>إضافة مهرجان جديد</a>
                </li>





            </ul>
        </li>


      <li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-category"></i></div>
        <div class="menu-title">إدارة ترتيب المستخدمين</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('search.position') }}">
                <i class='bx bx-radio-circle'></i>عرض ترتيب المستخدمين
            </a>
        </li>
        <li>
            <a href="{{ route('add.position.user') }}">
                <i class='bx bx-radio-circle'></i>إضافة ترتيب جديدة
            </a>
        </li>
    </ul>
</li>



<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-trophy"></i></div>
        <div class="menu-title">إدارة الترشيحات</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.nomination') }}">
                <i class='bx bx-radio-circle'></i>عرض الترشيحات
            </a>
        </li>
        <li>
            <a href="{{ route('add.nomination') }}">
                <i class='bx bx-radio-circle'></i>إضافة ترشيح جديد
            </a>
        </li>

         <li>
            <a href="{{ route('add.round.winner') }}">
                <i class='bx bx-radio-circle'></i>إضافة المطية الفائزة في الجولة
            </a>
        </li>




          <li>
            <a href="{{ route('add.nomination.user') }}">
                <i class='bx bx-radio-circle'></i>تحديد المستخدم ورشح اي مطية
            </a>
        </li>


         <li>
            <a href="{{ route('filter.users') }}">
                <i class='bx bx-radio-circle'></i>

  فرز المشاريكن
            </a>
        </li>


          <li>
            <a href="{{ route('filter.users.fesitval') }}">
                <i class='bx bx-radio-circle'></i>

  فرز المشاريكن في المهرجان
            </a>
        </li>



    </ul>
</li>
--}}




<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-news"></i></div>
        <div class="menu-title">ادارة الأخبار</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.news') }}">
                <i class='bx bx-radio-circle'></i>عرض الأخبار
            </a>
        </li>
        <li>
            <a href="{{ route('add.news') }}">
                <i class='bx bx-radio-circle'></i>إضافة خبر جديد
            </a>
        </li>
    </ul>
</li>


<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-images"></i></div>
        <div class="menu-title">إدارة السلايدر</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.slider') }}">
                <i class='bx bx-radio-circle'></i>عرض السلايدر
            </a>
        </li>
        <li>
            <a href="{{ route('add.slider') }}">
                <i class='bx bx-radio-circle'></i>إضافة سلايدر جديد
            </a>
        </li>
    </ul>
</li>




{{--
<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-tv"></i></div>
        <div class="menu-title">ادارة البث المباشر</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.live.broadcast') }}">
                <i class='bx bx-radio-circle'></i>عرض البث المباشر
            </a>
        </li>
        <li>
            <a href="{{ route('add.live.broadcast') }}">
                <i class='bx bx-radio-circle'></i>إضافة بث مباشر جديد
            </a>
        </li>
    </ul>
</li>



<li>
    <a href="javascript:;" class="has-arrow">
        <div class="parent-icon"><i class="bx bx-gift"></i></div>
        <div class="menu-title">إدارة الجوائز</div>
    </a>
    <ul>
        <li>
            <a href="{{ route('all.prizes') }}">
                <i class='bx bx-radio-circle'></i>عرض الجوائز
            </a>
        </li>
        <li>
            <a href="{{ route('add.prize') }}">
                <i class='bx bx-radio-circle'></i>إضافة جائزة جديدة
            </a>
        </li>
    </ul>
</li>




        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-category"></i>
                </div>
                <div class="menu-title">الفئات الرئيسية</div>
            </a>
            <ul>
                <li> <a href="{{route('all.category')}}"><i class='bx bx-radio-circle'></i>عرض الفئات</a>
                </li>
                <li> <a href="{{route('add.category')}}"><i class='bx bx-radio-circle'></i>إضافة الفئات</a>
                </li>





            </ul>
        </li>


        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <ion-icon name="help-circle-outline"></ion-icon>
                </div>

                <div class="menu-title">الأسئلة</div>
            </a>
            <ul>
                <li> <a href="{{route('all.question')}}"><i class='bx bx-radio-circle'></i>عرض الأسئلة</a>
                </li>
                <li> <a href="{{route('add.question')}}"><i class='bx bx-radio-circle'></i>إضافة سؤال جديد</a>
                </li>





            </ul>
        </li>



        <li>
            <a href="javascript:;" class="has-arrow">
             <div class="parent-icon">
            <i class='bx bx-bot'></i> <!-- Robot icon from Boxicons -->

        </div>

                <div class="menu-title"> الأسئلة بإستخدام AI</div>
            </a>
            <ul>
                <li> <a href="{{route('all.question.ai')}}"><i class='bx bx-radio-circle'></i>إنشاء الأسئلة</a>
                </li>






            </ul>
        </li>






        <li>
            <a href="{{route('all.games')}}">
                <div class="parent-icon">
                    <ion-icon name="game-controller-outline"></ion-icon>

                </div>


                <div class="menu-title">الألعاب المسجلة</div>
            </a>
        </li>
--}}



        <li>
            <a href="{{route('add.ads')}}">
                <div class="parent-icon">
                    <ion-icon name="megaphone-outline"></ion-icon>

                </div>
                <div class="menu-title">ادارة الإعلانات</div>
            </a>
        </li>


        <li>
            <a href="{{route('report.view')}}">
                <div class="parent-icon">
                    <ion-icon name="stats-chart-outline"></ion-icon>

                </div>
                <div class="menu-title">الاحصائيات</div>
            </a>
        </li>


        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
                    <ion-icon name="notifications-outline"></ion-icon>
                </div>

                <div class="menu-title">ادارة الإشعارات</div>
            </a>
            <ul>
                <li> <a href="{{route('all.notification')}}"><i class='bx bx-radio-circle'></i>عرض الاشعارات</a>
                </li>
                <li> <a href="{{route('send.notification')}}"><i class='bx bx-radio-circle'></i>ارسال اشعار جديد</a>
                </li>





            </ul>
        </li>



                 {{-- <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><ion-icon name="cash-outline"></ion-icon>


                </i>
                </div>
                <div class="menu-title">ادارة الأسعار</div>
            </a>
            <ul>

                <li> <a href="{{ route('all.price') }}"><i class="bx bx-right-arrow-alt"></i>جميع الأسعار</a>
                </li>


                <li> <a href="{{ route('add.price') }}"><i class="bx bx-right-arrow-alt"></i>إضافة سعر جديد</a>
                </li>


            </ul>
        </li> --}}

            <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><ion-icon name="happy-outline"></ion-icon></i>
                </div>
                <div class="menu-title">ادارة الكوبونات</div>
            </a>
            <ul>

                <li> <a href="{{ route('all.coupon') }}"><i class="bx bx-right-arrow-alt"></i>جميع الكوبونات</a>
                </li>


                <li> <a href="{{ route('add.coupon') }}"><i class="bx bx-right-arrow-alt"></i>إضافة كوبون</a>
                </li>


            </ul>
        </li>


        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon">
<ion-icon name="phone-portrait-outline"></ion-icon>
                </div>

                <div class="menu-title">ادارة التطبيق</div>
            </a>
            <ul>
                <li> <a href="{{route('add.versions')}}"><i class='bx bx-radio-circle'></i>اعدادات التطبيق</a>
                </li>






            </ul>
        </li>





               <li>
            <a href="javascript:;" class="has-arrow">
<div class="parent-icon"><ion-icon name="business-outline"></ion-icon></div>


                <div class="menu-title">ادارة الرعاة</div>
            </a>
            <ul>


                   <li> <a href="{{ route('sponsor.all') }}"><i class="bx bx-right-arrow-alt"></i>كل الرعاة</a>
                </li>

                  <li> <a href="{{ route('sponsor.add.new') }}"><i class="bx bx-right-arrow-alt"></i>اضافة جديد</a>
                </li>

                {{-- <li> <a href="{{ route('sponsor.add.cate') }}"><i class="bx bx-right-arrow-alt"></i> في شاشة الفئات</a>
                </li>

                  <li> <a href="{{ route('sponsor.add.question') }}"><i class="bx bx-right-arrow-alt"></i> في شاشة السؤال</a>
                </li> --}}





            </ul>
        </li>


    </ul>
    <!--end navigation-->
</div>
