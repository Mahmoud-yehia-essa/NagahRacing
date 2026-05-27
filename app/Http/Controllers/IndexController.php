<?php

namespace App\Http\Controllers;

use App\Models\Groub;
use App\Models\GroupUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{

      public function showSoon()
    {

        return view('frontend.payment.soon');
    }

        public function showHome()
    {

        return view('frontend.home.index');
    }


    public function showPrivacy()
    {
        return view("frontend.payment.privacy");


    }

// public function showRanking(Request $request)
// {
//     $festivalId = $request->query('festival_id');
//     $userId     = $request->query('user_id');

//     // التحقق من وجود البيانات المطلوبة
//     if (!$festivalId || !$userId) {
//         return abort(404, 'Festival ID و User ID مطلوبين');
//     }

//     // إجمالي نقاط المستخدم
//     $user = DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             DB::raw('SUM(nominations.points) AS total_points')
//         )
//         ->where('nominations.festival_id', $festivalId)
//         ->where('users.id', $userId)
//         ->groupBy(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo'
//         )
//         ->first();

//     // لو المستخدم مافيش له نقاط، ننشئ object افتراضي
//     if (!$user) {
//         $user = (object)[
//             'id' => $userId,
//             'fname' => '',
//             'lname' => '',
//             'email' => '',
//             'phone' => '',
//             'country_flag' => '',
//             'photo' => '',
//             'total_points' => 0,
//         ];
//     }

//     // حساب الترتيب بشكل آمن ومتوافق مع ONLY_FULL_GROUP_BY
//     $allUsersPoints = DB::table('nominations')
//         ->select('user_id', DB::raw('SUM(points) as total_points'))
//         ->where('festival_id', $festivalId)
//         ->groupBy('user_id')
//         ->orderByDesc('total_points')
//         ->pluck('total_points');

//     $rank = $allUsersPoints->search($user->total_points) + 1;
//     $user->user_rank = $rank;

//     // اسم المهرجان (اختياري)
//     $festivalName = DB::table('festivals')
//         ->where('id', $festivalId)
//         ->value('name');

//     // عرض الصفحة
//     return view('frontend.user_ranking_info.user_ranking_info', [
//         'user' => $user,
//         'festivalName' => $festivalName ?? 'اسم المهرجان غير متوفر',
//     ]);
// }

// public function showRanking(Request $request)
// {
//     $festivalId = $request->query('festival_id');
//     $userId     = $request->query('user_id');

//     if (!$festivalId || !$userId) {
//         abort(404, 'Festival ID و User ID مطلوبين');
//     }

//     // 1️⃣ جلب جميع المستخدمين مرتبين
//     $usersPoints = DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             DB::raw('SUM(nominations.points) AS total_points')
//         )
//         ->where('nominations.festival_id', $festivalId)
//         ->groupBy(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo'
//         )
//         ->orderByDesc('total_points') // الأعلى نقاط
//         ->orderBy('users.fname')      // أبجدي عند التساوي
//         ->get();

//     // 2️⃣ تحديد المستخدم وترتيبه
//     $userIndex = $usersPoints->search(fn ($u) => $u->id == $userId);

//     if ($userIndex === false) {
//         $user = (object)[
//             'id'           => $userId,
//             'fname'        => '',
//             'lname'        => '',
//             'email'        => '',
//             'phone'        => '',
//             'country_flag' => '',
//             'photo'        => '',
//             'total_points' => 0,
//             'user_rank'    => null,
//         ];
//     } else {
//         $user = $usersPoints[$userIndex];
//         $user->user_rank = $userIndex + 1;
//     }

//     // 3️⃣ اسم المهرجان
//     $festivalName = DB::table('festivals')
//         ->where('id', $festivalId)
//         ->value('name');

//     return view('frontend.user_ranking_info.user_ranking_info', [
//         'user' => $user,
//         'festivalName' => $festivalName ?? 'اسم المهرجان غير متوفر',
//     ]);
// }





// public function showRanking(Request $request)
// {
//     $festivalId = $request->query('festival_id');
//     $userId     = $request->query('user_id');

//     if (!$festivalId || !$userId) {
//         abort(404, 'Festival ID و User ID مطلوبين');
//     }

//     /* =========================
//        ترتيب المستخدم في مهرجان محدد
//     ==========================*/
//     $usersPoints = DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             DB::raw('SUM(nominations.points) AS total_points')
//         )
//         ->where('nominations.festival_id', $festivalId)
//         ->groupBy(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo'
//         )
//         ->orderByDesc('total_points')
//         ->get();

//     $userIndex = $usersPoints->search(fn ($u) => $u->id == $userId);

//     if ($userIndex === false) {
//         $user = (object)[
//             'id' => $userId,
//             'fname' => '',
//             'lname' => '',
//             'email' => '',
//             'phone' => '',
//             'country_flag' => '',
//             'photo' => '',
//             'total_points' => 0,
//             'user_rank' => null,
//         ];
//     } else {
//         $user = $usersPoints[$userIndex];
//         $user->user_rank = $userIndex + 1;
//     }

//     /* =========================
//        جميع المهرجانات التي فاز فيها المستخدم
//     ==========================*/
//     $userFestivals = DB::table('user_positions')
//         ->join('festivals', 'user_positions.festival_id', '=', 'festivals.id')
//         ->where('user_positions.user_id', $userId)
//         ->select(
//             'festivals.name as festival_name',
//             'user_positions.user_position'
//         )
//         ->orderBy('user_positions.user_position')
//         ->get();

//     $festivalName = DB::table('festivals')
//         ->where('id', $festivalId)
//         ->value('name');

//     return view('frontend.user_ranking_info.user_ranking_info', [
//         'user'          => $user,
//         'festivalName'  => $festivalName ?? 'اسم المهرجان غير متوفر',
//         'userFestivals' => $userFestivals
//     ]);
// }



public function showRanking(Request $request)
{
    $festivalId = $request->query('festival_id');
    $userId     = $request->query('user_id');

    if (!$festivalId || !$userId) {
        abort(404, 'Festival ID و User ID مطلوبين');
    }

    // جلب المستخدمين وترتيبهم مباشرة داخل الدالة بدون دالة منفصلة
    $usersPoints = \DB::table('nominations')
        ->join('users', 'nominations.user_id', '=', 'users.id')
        ->select(
            'users.id',
            'users.fname',
            'users.lname',
            'users.email',
            'users.phone',
            'users.country_flag',
            'users.photo',
            \DB::raw('SUM(nominations.points) AS total_points')
        )
        ->where('nominations.festival_id', $festivalId)
        ->groupBy(
            'users.id',
            'users.fname',
            'users.lname',
            'users.email',
            'users.phone',
            'users.country_flag',
            'users.photo'
        )
        ->get();

    // ترتيب النقاط + الأبجدية عند التساوي مع الحفاظ على الرموز
    $sortedCollection = $usersPoints->sort(function ($a, $b) {
        if ($a->total_points != $b->total_points) {
            return $b->total_points <=> $a->total_points; // الأعلى نقاط أولاً
        }
        return $a->fname <=> $b->fname;
    })->values();

    // جلب المستخدم المطلوب
    $userIndex = $sortedCollection->search(fn($u) => $u->id == $userId);

    if ($userIndex === false) {
        $user = (object)[
            'id'           => $userId,
            'fname'        => '',
            'lname'        => '',
            'email'        => '',
            'phone'        => '',
            'country_flag' => '',
            'photo'        => '',
            'total_points' => 0,
            'user_rank'    => null,
        ];
    } else {
        $user = $sortedCollection[$userIndex];
        $user->user_rank = $userIndex + 1;
    }

    // جميع المهرجانات التي فاز فيها المستخدم
    $userFestivals = DB::table('user_positions')
        ->join('festivals', 'user_positions.festival_id', '=', 'festivals.id')
        ->where('user_positions.user_id', $userId)
        ->select(
            'festivals.name as festival_name',
            'user_positions.user_position'
        )
        ->orderBy('user_positions.user_position')
        ->get();

    $festivalName = DB::table('festivals')
        ->where('id', $festivalId)
        ->value('name');

    return view('frontend.user_ranking_info.user_ranking_info', [
        'user'          => $user,
        'festivalName'  => $festivalName ?? 'اسم المهرجان غير متوفر',
        'userFestivals' => $userFestivals
    ]);
}



    //   public function groupJoin($codeNumber)
    // {

    //     return view('frontend.group.join');
    // }



// public function groupJoin($codeNumber)
// {

//     $group = Groub::where('code_number', $codeNumber)
//         ->with(['user', 'festival'])
//         ->withCount('groupUsers')
//         ->first();

//     if (!$group) {
//         abort(404, 'المجموعة غير موجودة');
//     }

//     $member = GroupUser::where('groub_id', $group->id)
//         ->where('is_owner_group', 1)
//         ->with('user')
//         ->first();

//     return view('frontend.group.join', [
//         'group' => $group,
//         'member' => $member,
//         'members_count' => $group->group_users_count
//     ]);
// }

public function groupJoin($codeNumber)
{
    // جلب بيانات المجموعة مع العلاقات المطلوبة
    $group = \App\Models\Groub::where('code_number', $codeNumber)
        ->with(['user', 'festival'])
        ->withCount('groupUsers')
        ->firstOrFail();

    // جلب بيانات مؤسس/مالك المجموعة
    $owner = \App\Models\GroupUser::where('groub_id', $group->id)
        ->where('is_owner_group', 1)
        ->with('user')
        ->first();

    // إرسال البيانات إلى ملف الـ Blade
    return view('frontend.group.join', compact('group', 'owner'));
}

public function groupJoinSteps($codeNumber)
{
    // جلب بيانات المجموعة مع العلاقات المطلوبة
    $group = \App\Models\Groub::where('code_number', $codeNumber)
        ->with(['user', 'festival'])
        ->withCount('groupUsers')
        ->firstOrFail();

    // جلب بيانات مؤسس/مالك المجموعة
    $owner = \App\Models\GroupUser::where('groub_id', $group->id)
        ->where('is_owner_group', 1)
        ->with('user')
        ->first();

    // إرسال البيانات إلى صفحة خطوات الانضمام
    return view('frontend.group.join-steps', compact('group', 'owner'));
}


}
