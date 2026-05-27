<?php

namespace App\Http\Controllers;

use App\Models\Camal;
use App\Models\CamelRoundParticipation;
use App\Models\Festival;
use App\Models\FestivalPoint;
use App\Models\Nomination;
use App\Models\Round;
use App\Models\User;
use App\Models\UserDoublePointUsage;
use App\Notifications\NominationNotification;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;




class NominationController extends Controller
{
    // عرض جميع الترشيحات
//     public function allNomination()
//     {


//         // $nominations = Nomination::with(['user', 'camel', 'festival', 'round'])->get();
// $nominations = Nomination::with(['user', 'camal', 'festival', 'round'])->get();

//                 // $nominations = Nomination::latest()->get();

//                 // return $nominations;
//         return view('admin.nomination.all_nomination', compact('nominations'));
//     }


public function allNomination()
{
    $nominations = Nomination::with([
            'user',
            'camelRoundParticipation',
            'festival',
            'round'
        ])
        ->latest()
        ->paginate(20); // غير الرقم حسب ما تحب

    return view('admin.nomination.all_nomination', compact('nominations'));
}


      // عرض جميع الترشيحات
    public function addNominationUser()
    {

     $users = User::all();
        $camals = Camal::all();
        $festivals = Festival::all();
        $rounds = Round::all();

        return view('admin.nomination.add_nomination_user_for_camal', compact('users', 'camals', 'festivals', 'rounds'));


    }

// addNominationUserStore this is write one
    public function addNominationUserStore(Request $request)
    {








        Nomination::create([
            'user_id' => $request->user_id,
            'festival_id' => $request->festival_id,
            'round_id' => $request->round_id,
            'camel_round_participations_id' => $request->camal_id,

            'is_winner' =>  0,
        ]);


        // For Notification
        // who users recive Notifications
                        $user = User::where('role','admin')->get();
/////
        $userCreatNomination = User::find($request->user_id);
        $camelRoundParticipation = CamelRoundParticipation::find($request->camal_id);
        $festival = Festival::find($request->festival_id);
        $round = Round::find($request->round_id);


        Notification::send($user, new NominationNotification($userCreatNomination , $camelRoundParticipation, $festival,$round ));
        // For Notification


        return redirect()->route('all.nomination')->with('success', 'تم إضافة الترشيح بنجاح');





    }



    // صفحة إضافة ترشيح جديد
    public function addNomination()
    {
        $users = User::all();
        $camals = Camal::all();
        $festivals = Festival::all();
        $rounds = Round::all();
        return view('admin.nomination.add_nomination', compact('users', 'camals', 'festivals', 'rounds'));
    }



      public function addRoundWinner()
    {



        $users = User::all();
        $camals = Camal::all();
        $festivals = Festival::all();
        $rounds = Round::all();
        return view('admin.nomination.add_camal_winner', compact('users', 'camals', 'festivals', 'rounds'));
    }





      public function filterNomination()
    {



        $users = User::all();
        $camals = Camal::all();
        $festivals = Festival::all();
        $rounds = Round::all();
        return view('admin.nomination.filter_nomination', compact('users', 'camals', 'festivals', 'rounds'));
    }



      public function filterNominationFestival()
    {



        $users = User::all();
        $camals = Camal::all();
        $festivals = Festival::all();
        $rounds = Round::all();
        return view('admin.nomination.filter_nomination_festival', compact('users', 'camals', 'festivals', 'rounds'));
    }



//  public function filterNominationFestivalStore(Request $request)
//     {





// $getNomination = Nomination::where('festival_id', $request->festival_id)
//     ->with('user')
//     ->orderByDesc('points')  // ترتيب من الأعلى للنقاط
//     ->get();

// return view('admin.nomination.all_filter_nomination_festival',compact('getNomination'));
//     }



// public function filterNominationFestivalStore(Request $request)
// {
//     $getNomination = Nomination::select('user_id', DB::raw('SUM(points) as total_points'))
//         ->where('festival_id', $request->festival_id)
//         ->groupBy('user_id')
//         ->orderByDesc('total_points')
//         ->with(['user', 'camel']) // ← إضافة علاقة المطية
//         ->get();

//         // return $getNomination;

//     return view('admin.nomination.all_filter_nomination_festival', compact('getNomination'));
// }


// public function filterNominationFestivalStore(Request $request)
// {
//     $getNomination = Nomination::select('user_id', DB::raw('SUM(points) as total_points'), DB::raw('COUNT(id) as total_participations'))
//         ->where('festival_id', $request->festival_id)
//         ->groupBy('user_id')
//         ->orderByDesc('total_points')
//         ->with('user')
//         ->get();

//     return view('admin.nomination.all_filter_nomination_festival', compact('getNomination'));
// }

public function filterNominationFestivalStore(Request $request)
{
    $getNomination = Nomination::select(
            'user_id',
            DB::raw('SUM(points) as total_points'),
            DB::raw('COUNT(id) as total_participations')
        )
        ->where('festival_id', $request->festival_id)
        ->groupBy('user_id')
        ->with('user')
        ->orderByDesc('total_points')        // أولاً ترتيب أعلى نقاط
        ->orderBy(User::select('fname')       // ثانياً ترتيب أبجدي بالاسم
            ->whereColumn('users.id', 'nominations.user_id')
            ->limit(1)
        )
        ->get();

    return view('admin.nomination.all_filter_nomination_festival', compact('getNomination'));
}

       public function filterNominationStore(Request $request)
    {





// $getNomination = Nomination::where('festival_id', $request->festival_id)
//     ->where('round_id', $request->round_id)
//     ->with('user') // تأكد أن العلاقة موجودة
//     ->get();

$getNomination = Nomination::where('festival_id', $request->festival_id)
    ->where('round_id', $request->round_id)
    ->with('user')
    ->orderByDesc('points')  // ترتيب من الأعلى للنقاط
    ->get();

return view('admin.nomination.all_filter_nomination',compact('getNomination'));
    }






    /* Very Important function to add Store to users

 public function addRoundWinnerStore(Request $request)
    {







        /// Get point //



        $round = Round::find($request->round_id);

          $round->status = "finished";

          $round->save();

        //   return $round;




           $camelRoundParticipation = CamelRoundParticipation::find($request->camal_id);
//   return $camelRoundParticipation->age_name;

           // get points

// $festivalPoint = FestivalPoint::where('festival_id', $request->festival_id)
//     ->where('age_name', $camelRoundParticipation->camal->age_name)
//     ->first();

$festivalPoint = FestivalPoint::where('age_name', $camelRoundParticipation->camel_age_name)
    ->first();


    //Get point OK
            // return $festivalPoint->points;


/// Get user who anotation
$nomination = Nomination::where('festival_id', $request->festival_id)
    ->where('round_id', $request->round_id)
    ->get();

  foreach ($nomination as $newnomination) {
    if($newnomination->camel_round_participations_id == $request->camal_id)
    {

        $newnomination->points = $festivalPoint->points;
        $newnomination->is_winner = 1;

         $newnomination->save();
    }
    else
    {

        $newnomination->is_winner = 2;
                 $newnomination->save();


    }
}

// return $nomination;
//     return $request->camal_id;

//     return $nomination;





        $camelRoundParticipation->is_winner = 1;
        $camelRoundParticipation->save();

        // return $camelRoundParticipation;

    // return redirect()->route('all.nomination')->with('success', 'تم اضافة المطية الفائزة بنجاح');

        return redirect()->route('filter.users')->with('success', 'تم اضافة المطية الفائزة بنجاح');


    }


    */


public function addRoundWinnerStore(Request $request)
{
    // تحديث حالة الشوط
    $round = Round::find($request->round_id);
    $round->status = "finished";
    $round->save();

    // جلب معلومات الترشيح للمطية
    $camelRoundParticipation = CamelRoundParticipation::find($request->camal_id);

    // جلب نقاط العمر
    $festivalPoint = FestivalPoint::where('age_name', $camelRoundParticipation->camel_age_name)
        ->first();

    // جلب الترشيحات في نفس المهرجان والشوط
    $nominations = Nomination::where('festival_id', $request->festival_id)
        ->where('round_id', $request->round_id)
        ->get();

    foreach ($nominations as $nomination) {

        // تحقق إذا المستخدم استخدم دبل النقاط
        $usedDouble = UserDoublePointUsage::where('user_id', $nomination->user_id)
            ->where('festival_id', $request->festival_id)
            ->where('round_id', $request->round_id)
            ->exists();

        // تعيين النقاط: مضاعفة إذا استخدم الدبل
        $points = $festivalPoint->points;
        if ($usedDouble) {
            $points *= 2; // مضاعفة النقاط
        }

        if ($nomination->camel_round_participations_id == $request->camal_id) {
            // الفائز
            $nomination->points = $points;
            $nomination->is_winner = 1;
        } else {
            // الخاسرين
            $nomination->is_winner = 2;
        }

        $nomination->save();
    }

    // تحديث حالة المطية الفائزة
    $camelRoundParticipation->is_winner = 1;
    $camelRoundParticipation->save();

    return redirect()->route('filter.users')->with('success', 'تم اضافة المطية الفائزة بنجاح');
}





    // تخزين ترشيح جديد
    public function storeNomination(Request $request)
    {


        $request->validate([
            'user_id' => 'required|exists:users,id',
            'camal_id' => 'required|exists:camals,id',
            'festival_id' => 'required|exists:festivals,id',
            'round_id' => 'required|exists:rounds,id',
            'is_winner' => 'nullable|boolean',
        ]);

        Nomination::create([
            'user_id' => $request->user_id,
            'camal_id' => $request->camal_id,
            'festival_id' => $request->festival_id,
            'round_id' => $request->round_id,
            'is_winner' => $request->is_winner ?? 0,
        ]);

        return redirect()->route('all.nomination')->with('success', 'تم إضافة الترشيح بنجاح');
    }

    // صفحة تعديل ترشيح
    public function editNomination($id)
    {
        $nomination = Nomination::findOrFail($id);
        $users = User::all();
        $camals = Camal::all();
        $festivals = Festival::all();
        $rounds = Round::all();
        return view('admin.nomination.edit_nomination', compact('nomination', 'users', 'camals', 'festivals', 'rounds'));
    }

    // تحديث ترشيح
    public function updateNomination(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'camal_id' => 'required|exists:camals,id',
            'festival_id' => 'required|exists:festivals,id',
            'round_id' => 'required|exists:rounds,id',
            'is_winner' => 'nullable|boolean',
        ]);

        $nomination = Nomination::findOrFail($id);
        $nomination->update([
            'user_id' => $request->user_id,
            'camal_id' => $request->camal_id,
            'festival_id' => $request->festival_id,
            'round_id' => $request->round_id,
            'is_winner' => $request->is_winner ?? 0,
        ]);

        return redirect()->route('all.nomination')->with('success', 'تم تحديث الترشيح بنجاح');
    }

    // حذف ترشيح
    public function deleteNomination($id)
    {
        $nomination = Nomination::findOrFail($id);
        $nomination->delete();

        return redirect()->route('all.nomination')->with('success', 'تم حذف الترشيح بنجاح');
    }

    public function getRounds($festivalId)
{
    // $rounds = Round::where('festival_id', $festivalId)->get();

      $rounds = Round::where('festival_id', $festivalId)
                   ->where('status', '!=', 'finished')
                   ->get();

    return response()->json($rounds);
}


   public function getRoundsforFilter($festivalId)
{
    // $rounds = Round::where('festival_id', $festivalId)->get();

      $rounds = Round::where('festival_id', $festivalId)->get();

    return response()->json($rounds);
}

//   public function getCamalsByRound($roundId)
// {


//     $camelRoundParticipation = CamelRoundParticipation::where('round_id', $roundId)->get();






//     return response()->json($camelRoundParticipation);
// }


public function getCamalsByRound($roundId)
{
    // نجيب المشاركات مع بيانات المطية
    $camelRoundParticipation = CamelRoundParticipation::with('camal')
        ->where('round_id', $roundId)
        ->get();

    // نرجع فقط البيانات المهمة (id + name)
    $camals = $camelRoundParticipation->map(function ($item) {
        return [
            'id' => $item->id,
            // 'camal_id' => $item->camal->id,
            'name' => $item->camel_name,
                        'camel_owner_name' => $item->camel_owner_name,

        ];
    });

    return response()->json($camals);
}




/// API



// public function addNominationUserAPI(Request $request)
// {
//     $camalId    = $request->camal_id;
//     $festivalId = $request->festival_id;
//     $roundId    = $request->round_id;
//     $userId     = $request->user_id;

//     // التأكد من وجود سجل في camel_round_participations أو إنشاؤه
//     $camalParticipation = \App\Models\CamelRoundParticipation::firstOrCreate(
//         [
//             'festival_id' => $festivalId,
//             'round_id' => $roundId,
//             'camal_id' => $camalId
//         ],
//         [
//             'registration_number' => 'C-'.$roundId.'-'.$camalId,
//             'is_winner' => 0
//         ]
//     );

//     // إنشاء الترشيح
//     $nomination = Nomination::create([
//         'user_id' => $userId,
//         'festival_id' => $festivalId,
//         'round_id' => $roundId,
//         'camel_round_participations_id' => $camalParticipation->id,
//         'is_winner' => 0,
//     ]);

//     // تحميل بيانات الجمل + صاحبه + الشوط
//     $camalParticipation = $camalParticipation->load(['camal.user', 'round']);

//     // بيانات الجمل + صاحبه
//     $camalData = null;
//     if ($camalParticipation->camal) {
//         $camalData = [
//             'id' => $camalParticipation->camal->id,
//             'name' => $camalParticipation->camal->name,
//             'photo' => $camalParticipation->camal->photo,
//         ];

//         if ($camalParticipation->camal->user) {
//             $camalData = array_merge($camalData, [
//                 'user_id'   => $camalParticipation->camal->user->id,
//                 'fname'     => $camalParticipation->camal->user->fname,
//                 'lname'     => $camalParticipation->camal->user->lname,
//                 'email'     => $camalParticipation->camal->user->email,
//                 'phone'     => $camalParticipation->camal->user->phone,
//                 'user_photo'=> $camalParticipation->camal->user->photo,
//             ]);
//         }
//     }

//     // بيانات الشوط
//     $roundData = null;
//     if ($camalParticipation->round) {
//         $roundData = [
//             'id' => $camalParticipation->round->id,
//             'name' => $camalParticipation->round->name,
//             'name_en' => $camalParticipation->round->name_en,
//             'start' => \Carbon\Carbon::parse($camalParticipation->round->start)
//                         ->setTimezone('Asia/Kuwait')
//                         ->toDateTimeString(),
//             'end'   => \Carbon\Carbon::parse($camalParticipation->round->end)
//                         ->setTimezone('Asia/Kuwait')
//                         ->toDateTimeString(),
//             'status' => $camalParticipation->round->status,
//         ];
//     }

//     // بيانات المهرجان
//     $festival = \App\Models\Festival::find($festivalId);
//     $festivalData = null;
//     if ($festival) {
//         $festivalData = [
//             'id' => $festival->id,
//             'name' => $festival->name,
//             'name_en' => $festival->name_en,
//             'des' => $festival->des,
//             'des_en' => $festival->des_en,
//             'location' => $festival->location,
//             'start' => \Carbon\Carbon::parse($festival->start)
//                         ->setTimezone('Asia/Kuwait')
//                         ->toDateTimeString(),
//             'end'   => \Carbon\Carbon::parse($festival->end)
//                         ->setTimezone('Asia/Kuwait')
//                         ->toDateTimeString(),
//             'latitude' => $festival->latitude,
//             'longitude' => $festival->longitude,
//             'photo' => $festival->photo,
//             'status' => $festival->status,
//         ];
//     }

//     // استجابة JSON
//     return response()->json([
//         'success' => true,
//         'message' => 'تم إضافة الترشيح بنجاح',
//         'nomination' => $nomination,
//         'camal' => $camalData,
//         'round' => $roundData,
//         'festival' => $festivalData
//     ], 201);
// }


// public function addNominationUserAPI(Request $request)
// {
//     // التحقق من الحقول المطلوبة
//     $request->validate([
//         'user_id' => 'required|integer|exists:users,id',
//         'festival_id' => 'required|integer|exists:festivals,id',
//         'round_id' => 'required|integer|exists:rounds,id',
//         'camal_id' => 'required|integer|exists:camel_round_participations,id',
//     ]);

//     try {
//         // إنشاء الترشيح
//         $nomination = Nomination::create([
//             'user_id' => $request->user_id,
//             'festival_id' => $request->festival_id,
//             'round_id' => $request->round_id,
//             'camel_round_participations_id' => $request->camal_id,
//             'is_winner' => 0,
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'تم إضافة الترشيح بنجاح',
//             'nomination' => $nomination,
//         ], 201);

//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'حدث خطأ أثناء الإضافة',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }



// public function addNominationUserAPI(Request $request)
// {
//     // ✅ التحقق من الحقول المطلوبة
//     $request->validate([
//         'user_id' => 'required|integer|exists:users,id',
//         'festival_id' => 'required|integer|exists:festivals,id',
//         'round_id' => 'required|integer|exists:rounds,id',
//         'camal_id' => 'required|integer|exists:camel_round_participations,id',
//     ]);

//     try {
//         // ✅ إنشاء الترشيح
//         $nomination = Nomination::create([
//             'user_id' => $request->user_id,
//             'festival_id' => $request->festival_id,
//             'round_id' => $request->round_id,
//             'camel_round_participations_id' => $request->camal_id,
//             'is_winner' => 0,
//         ]);

//         // ✅ جلب بيانات الجمل المشاركة فقط من نفس الجدول
//         $participation = \App\Models\CamelRoundParticipation::find($request->camal_id);

//         if (!$participation) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'لم يتم العثور على بيانات المشاركة',
//             ], 404);
//         }

//         // ✅ تجهيز البيانات النهائية من نفس جدول المشاركة
//         $camelData = [
//             'id' => $participation->id,
//             'festival_id' => $participation->festival_id,
//             'round_id' => $participation->round_id,
//             'camel_name' => $participation->camel_name,
//             'camel_age_name' => $participation->camel_age_name,
//             'camel_owner_name' => $participation->camel_owner_name,
//             'camel_owner_country' => $participation->camel_owner_country,
//             'registration_number' => $participation->registration_number,
//             'is_winner' => $participation->is_winner,
//         ];

//         // ✅ استجابة JSON مبسطة
//         return response()->json([
//             'success' => true,
//             'message' => 'تم إضافة الترشيح بنجاح',
//             'nomination' => $nomination,
//             'camel_participation' => $camelData,
//         ], 201);

//     } catch (\Exception $e) {
//         // ⚠️ في حال وجود خطأ
//         return response()->json([
//             'success' => false,
//             'message' => 'حدث خطأ أثناء الإضافة',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }


// public function addNominationUserAPI(Request $request)
// {
//     // ✅ التحقق من الحقول المطلوبة
//     $request->validate([
//         'user_id' => 'required|integer|exists:users,id',
//         'festival_id' => 'required|integer|exists:festivals,id',
//         'round_id' => 'required|integer|exists:rounds,id',
//         'camal_id' => 'required|integer|exists:camel_round_participations,id',
//     ]);

//     try {
//         // ✅ إنشاء الترشيح
//         $nomination = Nomination::create([
//             'user_id' => $request->user_id,
//             'festival_id' => $request->festival_id,
//             'round_id' => $request->round_id,
//             'camel_round_participations_id' => $request->camal_id,
//             'is_winner' => 0,
//         ]);

//         // ✅ جلب بيانات المشاركة
//         $participation = \App\Models\CamelRoundParticipation::find($request->camal_id);

//         if (!$participation) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'لم يتم العثور على بيانات المشاركة',
//             ], 404);
//         }

//         // ✅ جلب بيانات الجولة والمهرجان
//         $round = \App\Models\Round::find($request->round_id);
//         $festival = \App\Models\Festival::find($request->festival_id);

//         // ✅ تجهيز البيانات النهائية
//         $camelData = [
//             'id' => $participation->id,
//             'festival_id' => $participation->festival_id,
//             'round_id' => $participation->round_id,
//             'camel_name' => $participation->camel_name,
//             'camel_age_name' => $participation->camel_age_name,
//             'camel_owner_name' => $participation->camel_owner_name,
//             'camel_owner_country' => $participation->camel_owner_country,
//             'registration_number' => $participation->registration_number,
//             'is_winner' => $participation->is_winner,
//         ];

//         // ✅ استجابة JSON شاملة
//         return response()->json([
//             'success' => true,
//             'message' => 'تم إضافة الترشيح بنجاح',
//             'nomination' => $nomination,
//             'camel_participation' => $camelData,
//             'round' => $round,
//             'festival' => $festival,
//         ], 201);

//     } catch (\Exception $e) {
//         // ⚠️ في حال وجود خطأ
//         return response()->json([
//             'success' => false,
//             'message' => 'حدث خطأ أثناء الإضافة',
//             'error' => $e->getMessage(),
//         ], 500);
//     }
// }


// Very Importan backup for addNomitation 

/*

public function addNominationUserAPI(Request $request)
{
   
    // ✅ التحقق من الحقول المطلوبة
    $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'festival_id' => 'required|integer|exists:festivals,id',
        'round_id' => 'required|integer|exists:rounds,id',
        'camal_id' => 'required|integer|exists:camel_round_participations,id',
    ]);

    try {
        // ✅ إنشاء الترشيح
        $nomination = Nomination::create([
            'user_id' => $request->user_id,
            'festival_id' => $request->festival_id,
            'round_id' => $request->round_id,
            'camel_round_participations_id' => $request->camal_id,
            'is_winner' => 0,
        ]);

        // ✅ جلب بيانات المشاركة
        $participation = \App\Models\CamelRoundParticipation::find($request->camal_id);

        if (!$participation) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات المشاركة',
            ], 404);
        }

        // ✅ جلب بيانات الجولة والمهرجان
        $round = \App\Models\Round::find($request->round_id);
        $festival = \App\Models\Festival::find($request->festival_id);

        // ✅ تجهيز البيانات النهائية
        $camelData = [
            'id' => $participation->id,
            'festival_id' => $participation->festival_id,
            'round_id' => $participation->round_id,
            'camel_name' => $participation->camel_name,
            'camel_age_name' => $participation->camel_age_name,
            'camel_owner_name' => $participation->camel_owner_name,
            'camel_owner_country' => $participation->camel_owner_country,
            'registration_number' => $participation->registration_number,
            'is_winner' => $participation->is_winner,
        ];



            // For Notification
        // who users recive Notifications
                        $user = User::where('role','admin')->get();
/////
        $userCreatNomination = User::find($request->user_id);
        $camelRoundParticipation = CamelRoundParticipation::find($request->camal_id);
        $festival = Festival::find($request->festival_id);
        $round = Round::find($request->round_id);


        Notification::send($user, new NominationNotification($userCreatNomination , $camelRoundParticipation, $festival,$round ));
        // For Notification

        // ✅ استجابة JSON شاملة
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الترشيح بنجاح',
            'nomination' => $nomination,
            'camel_participation' => $camelData,
            'round' => $round,
            'festival' => $festival,
        ], 201);

    } catch (\Exception $e) {
        // ⚠️ في حال وجود خطأ
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء الإضافة',
            'error' => $e->getMessage(),
        ], 500);
    }
}

*/

// Very Importan backup for addNomitation 



public function addNominationUserAPI(Request $request)
{
    // ✅ التحقق من الحقول المطلوبة
    $request->validate([
        'user_id'    => 'required|integer|exists:users,id',
        'festival_id'=> 'required|integer|exists:festivals,id',
        'round_id'   => 'required|integer|exists:rounds,id',
        'camal_id'   => 'required|integer|exists:camel_round_participations,id',
    ]);

    try {
        // ✅ الحل النهائي لمنع التكرار حتى في حالة الطلبات المتزامنة (Race Condition)
        // نستخدم DB::transaction مع lockForUpdate لضمان أن طلب واحد فقط يُكمل في كل مرة
        $result = DB::transaction(function () use ($request) {

            // 🔒 قفل الصف الموجود (إن وجد) لمنع أي طلب آخر من القراءة حتى تنتهي العملية
            $existingNomination = Nomination::where('user_id', $request->user_id)
                ->where('festival_id', $request->festival_id)
                ->where('round_id', $request->round_id)
                ->lockForUpdate()
                ->first();

            if ($existingNomination) {
                // ✅ ترشيح موجود مسبقاً — نعيد نفس البيانات بدون إضافة جديدة
                $participation = \App\Models\CamelRoundParticipation::find($existingNomination->camel_round_participations_id);
                $round         = \App\Models\Round::find($existingNomination->round_id);
                $festival      = \App\Models\Festival::find($existingNomination->festival_id);

                $camelData = null;
                if ($participation) {
                    $camelData = [
                        'id'                  => $participation->id,
                        'festival_id'         => $participation->festival_id,
                        'round_id'            => $participation->round_id,
                        'camel_name'          => $participation->camel_name,
                        'camel_age_name'      => $participation->camel_age_name,
                        'camel_owner_name'    => $participation->camel_owner_name,
                        'camel_owner_country' => $participation->camel_owner_country,
                        'registration_number' => $participation->registration_number,
                        'is_winner'           => $participation->is_winner,
                    ];
                }

                return [
                    'is_duplicate' => true,
                    'nomination'   => $existingNomination,
                    'camelData'    => $camelData,
                    'round'        => $round,
                    'festival'     => $festival,
                ];
            }

            // ✅ إنشاء الترشيح (أول مرة فقط — داخل نفس القفل)
            $nomination = Nomination::create([
                'user_id'                       => $request->user_id,
                'festival_id'                   => $request->festival_id,
                'round_id'                      => $request->round_id,
                'camel_round_participations_id' => $request->camal_id,
                'is_winner'                     => 0,
            ]);

            // ✅ جلب بيانات المشاركة
            $participation = \App\Models\CamelRoundParticipation::find($request->camal_id);
            $round         = \App\Models\Round::find($request->round_id);
            $festival      = \App\Models\Festival::find($request->festival_id);

            $camelData = null;
            if ($participation) {
                $camelData = [
                    'id'                  => $participation->id,
                    'festival_id'         => $participation->festival_id,
                    'round_id'            => $participation->round_id,
                    'camel_name'          => $participation->camel_name,
                    'camel_age_name'      => $participation->camel_age_name,
                    'camel_owner_name'    => $participation->camel_owner_name,
                    'camel_owner_country' => $participation->camel_owner_country,
                    'registration_number' => $participation->registration_number,
                    'is_winner'           => $participation->is_winner,
                ];
            }

            return [
                'is_duplicate'         => false,
                'nomination'           => $nomination,
                'camelData'            => $camelData,
                'round'                => $round,
                'festival'             => $festival,
                'camelRoundParticip'   => $participation,
            ];
        }); // نهاية DB::transaction

        // ✅ إرسال الإشعار فقط عند الإضافة الجديدة (خارج الـ transaction لتجنب تأخير القفل)
        if (!$result['is_duplicate']) {
            $admins               = User::where('role', 'admin')->get();
            $userCreatNomination  = User::find($request->user_id);
            $camelRoundParticip   = $result['camelRoundParticip'] ?? CamelRoundParticipation::find($request->camal_id);
            $festivalForNotif     = Festival::find($request->festival_id);
            $roundForNotif        = Round::find($request->round_id);

            if ($admins->isNotEmpty() && $userCreatNomination) {
                Notification::send($admins, new NominationNotification(
                    $userCreatNomination,
                    $camelRoundParticip,
                    $festivalForNotif,
                    $roundForNotif
                ));
            }
        }

        // ✅ استجابة JSON شاملة (نفس الاستجابة سواء جديد أو مكرر)
        return response()->json([
            'success'           => true,
            'message'           => 'تم إضافة الترشيح بنجاح',
            'nomination'        => $result['nomination'],
            'camel_participation'=> $result['camelData'],
            'round'             => $result['round'],
            'festival'          => $result['festival'],
        ], 201);

    } catch (\Exception $e) {
        // ⚠️ في حال وجود خطأ
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء الإضافة',
            'error'   => $e->getMessage(),
        ], 500);
    }
}





// public function checkNominationApi(Request $request)
// {

//     // return "Hello";
//     $userId     = $request->input('user_id');
//     $festivalId = $request->input('festival_id');
//     $roundId    = $request->input('round_id');

//     // جلب الترشيح مع بيانات الجمل وصاحب الجمل والشوط والمهرجان
//     $nomination = \App\Models\Nomination::with([
//         'camelRoundParticipation.camal.user',
//         'camelRoundParticipation.round',
//         'festival'
//     ])
//     ->where('user_id', $userId)
//     ->where('festival_id', $festivalId)
//     ->where('round_id', $roundId)
//     ->first();

//     $alreadyNominated = $nomination ? true : false;

//     $data = null;
//     if ($alreadyNominated) {
//         $camalData = null;
//         $roundData = null;
//         $festivalData = null;

//         if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->camal) {
//             $camal = $nomination->camelRoundParticipation->camal;
//             $camalData = [
//                 'id' => $camal->id,
//                 'name' => $camal->name,
//                 'photo' => $camal->photo,
//                 'owner' => $camal->user ? [
//                     'id' => $camal->user->id,
//                     'fname' => $camal->user->fname,
//                     'lname' => $camal->user->lname,
//                     'email' => $camal->user->email,
//                     'phone' => $camal->user->phone,
//                     'photo' => $camal->user->photo,
//                 ] : null,
//             ];
//         }

//         if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->round) {
//             $round = $nomination->camelRoundParticipation->round;
//             $roundData = [
//                 'id' => $round->id,
//                 'name' => $round->name,
//                 'name_en' => $round->name_en,
//                 'des' => $round->des,
//                 'des_en' => $round->des_en,
//                 'start' => $round->start,
//                 'end' => $round->end,
//                 'status' => $round->status,
//                 'round_type' => $round->round_type,
//             ];
//         }

//         if ($nomination->festival) {
//             $festival = $nomination->festival;
//             $festivalData = [
//                 'id' => $festival->id,
//                 'name' => $festival->name,
//                 'name_en' => $festival->name_en,
//                 'des' => $festival->des,
//                 'des_en' => $festival->des_en,
//                 'start' => $festival->start,
//                 'end' => $festival->end,
//                 'location' => $festival->location,
//                 'status' => $festival->status,
//             ];
//         }

//         $data = [
//             'nomination_id' => $nomination->id,
//             'is_winner' => $nomination->is_winner,
//             'created_at' => $nomination->created_at,
//             'created_at_human' => $nomination->created_at
//                 ? \Carbon\Carbon::parse($nomination->created_at)
//                     ->locale('ar') // تعريب
//                     ->diffForHumans()
//                 : null,
//             'updated_at' => $nomination->updated_at,
//             // 'camal' => $camalData,
//             'round' => $roundData,
//             'festival' => $festivalData,
//         ];
//     }

//     return response()->json([
//         'success' => true,
//         'already_nominated' => $alreadyNominated,
//         'message' => $alreadyNominated
//             ? 'المستخدم قام بالترشيح مسبقاً'
//             : 'المستخدم لم يقم بالترشيح بعد',
//         'data' => $data
//     ]);
// }




public function checkNominationApi(Request $request)
{
    $userId     = $request->input('user_id');
    $festivalId = $request->input('festival_id');
    $roundId    = $request->input('round_id');

    // البحث عن الترشيح بناءً على المستخدم والمهرجان والشوط
    $nomination = \App\Models\Nomination::where('user_id', $userId)
        ->where('festival_id', $festivalId)
        ->where('round_id', $roundId)
        ->first();

    $alreadyNominated = $nomination ? true : false;
    $data = null;

    if ($alreadyNominated) {

        // ✅ جلب بيانات الجمل من جدول camel_round_participations
        $camelParticipation = null;
        if ($nomination->camel_round_participations_id) {
            $camelParticipation = \App\Models\CamelRoundParticipation::find($nomination->camel_round_participations_id);
        }

        // ✅ إعداد بيانات الجمل إن وجدت
        $camelData = null;
        if ($camelParticipation) {
            $camelData = [
                'id' => $camelParticipation->id,
                'camel_name' => $camelParticipation->camel_name,
                'camel_age_name' => $camelParticipation->camel_age_name,
                'camel_owner_name' => $camelParticipation->camel_owner_name,
                'camel_owner_country' => $camelParticipation->camel_owner_country,
                'registration_number' => $camelParticipation->registration_number,
                'is_winner' => $camelParticipation->is_winner,

            ];
        }

        // ✅ إعداد بيانات الجولة من العلاقة round (إن وجدت)
        $round = $nomination->round ?? null;
        $roundData = $round ? [
            'id' => $round->id,
            'name' => $round->name,
            'name_en' => $round->name_en,
            'des' => $round->des,
            'des_en' => $round->des_en,
            'start' => $round->start,
            'end' => $round->end,
            'status' => $round->status,
            'round_type' => $round->round_type,
        ] : null;

        // ✅ إعداد بيانات المهرجان من العلاقة festival (إن وجدت)
        $festival = $nomination->festival ?? null;
        $festivalData = $festival ? [
            'id' => $festival->id,
            'name' => $festival->name,
            'name_en' => $festival->name_en,
            'des' => $festival->des,
            'des_en' => $festival->des_en,
            'start' => $festival->start,
            'end' => $festival->end,
            'location' => $festival->location,
            'status' => $festival->status,
        ] : null;

        // ✅ بناء الاستجابة النهائية
        $data = [
            'nomination_id' => $nomination->id,
            'is_winner' => $nomination->is_winner,
            'points' => $nomination->points,

            'created_at' => $nomination->created_at,
            'created_at_human' => $nomination->created_at
                ? \Carbon\Carbon::parse($nomination->created_at)->locale('ar')->diffForHumans()
                : null,
            'updated_at' => $nomination->updated_at,
            'camel' => $camelData,
            'round' => $roundData,
            'festival' => $festivalData,
        ];
    }

    return response()->json([
        'success' => true,
        'already_nominated' => $alreadyNominated,
        'message' => $alreadyNominated
            ? 'المستخدم قام بالترشيح مسبقاً'
            : 'المستخدم لم يقم بالترشيح بعد',
        'data' => $data
    ]);
}





// public function getUserNominationsApi(Request $request)
// {
//     $userId = $request->input('user_id');

//     // جلب كل الترشيحات للمستخدم مع العلاقات اللازمة
//     $nominations = \App\Models\Nomination::with([
//         'camelRoundParticipation.camal.user',
//         'camelRoundParticipation.round',
//         'festival'
//     ])
//     ->where('user_id', $userId)
//     ->orderBy('created_at', 'desc')
//     ->get();

//     $result = [];

//     foreach ($nominations as $nomination) {
//         $camalData = null;
//         $roundData = null;
//         $festivalData = null;

//         // camal + owner
//         if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->camal) {
//             $camal = $nomination->camelRoundParticipation->camal;
//             $owner = $camal->user ?? null; // العلاقة عندك اسمها user
//             $camalData = [
//                 'id' => $camal->id,
//                 'name' => $camal->name,
//                 'age_name' => $camal->age_name,
//                 'photo' => $camal->photo,
//                 'owner' => $owner ? [
//                     'id' => $owner->id,
//                     'fname' => $owner->fname,
//                     'lname' => $owner->lname,
//                     'email' => $owner->email,
//                     'phone' => $owner->phone,
//                     'photo' => $owner->photo,
//                 ] : null,
//             ];
//         }

//         // round (مع age_name لو موجود)
//         if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->round) {
//             $round = $nomination->camelRoundParticipation->round;
//             $roundData = [
//                 'id' => $round->id,
//                 'name' => $round->name,
//                 'name_en' => $round->name_en,
//                 'des' => $round->des,
//                 'des_en' => $round->des_en,
//                 'start' => $round->start,
//                 'end' => $round->end,
//                 'status' => $round->status,
//                 'round_type' => $round->round_type,
//             ];
//         }

//         // festival
//         if ($nomination->festival) {
//             $festival = $nomination->festival;
//             $festivalData = [
//                 'id' => $festival->id,
//                 'name' => $festival->name,
//                 'name_en' => $festival->name_en,
//                 'des' => $festival->des,
//                 'des_en' => $festival->des_en,
//                 'start' => $festival->start, // كما هو من الجدول
//                 'end' => $festival->end,     // كما هو من الجدول
//                 'start_date_human' => $festival->start
//                     ? \Carbon\Carbon::parse($festival->start)
//                         ->timezone(config('app.timezone'))
//                         ->format('d/m/Y')
//                     : null,
//                 'end_date_human' => $festival->end
//                     ? \Carbon\Carbon::parse($festival->end)
//                         ->timezone(config('app.timezone'))
//                         ->format('d/m/Y')
//                     : null,
//                 'location' => $festival->location,
//                 'status' => $festival->status,
//             ];
//         }

//         $result[] = [
//             'nomination_id' => $nomination->id,
//             'is_winner' => $nomination->is_winner,
//             'created_at' => $nomination->created_at,
//             'created_at_human' => $nomination->created_at
//                 ? \Carbon\Carbon::parse($nomination->created_at)
//                     ->locale('ar')
//                     ->diffForHumans()
//                 : null,
//             'updated_at' => $nomination->updated_at,
//             'camal' => $camalData,
//             'round' => $roundData,
//             'festival' => $festivalData,
//         ];
//     }

//     return response()->json([
//         'success' => true,
//         'user_id' => $userId,
//         'count' => count($result),
//         'nominations' => $result,
//     ]);
// }



// public function getUserNominationsApi(Request $request)
// {
//     $userId = $request->input('user_id');
//     $festivalId = $request->input('festival_id');


//     // جلب كل الترشيحات للمستخدم مع العلاقات اللازمة
//     $nominations = \App\Models\Nomination::with([
//         'camelRoundParticipation.camal.user',
//         'camelRoundParticipation.round',
//         'festival'
//     ])
//     ->where('user_id', $userId)
//     ->where('festival_id', $festivalId)
//     ->orderBy('created_at', 'desc')
//     ->get();

//     $result = [];

//     foreach ($nominations as $nomination) {
//         $camalData = null;
//         $roundData = null;
//         $festivalData = null;

//         // camal + owner
//         if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->camal) {
//             $camal = $nomination->camelRoundParticipation->camal;
//             $owner = $camal->user ?? null;
//             $camalData = [
//                 'id' => $camal->id,
//                 'name' => $camal->name,
//                 'age_name' => $camal->age_name,
//                 'photo' => $camal->photo,
//                 'owner' => $owner ? [
//                     'id' => $owner->id,
//                     'fname' => $owner->fname,
//                     'lname' => $owner->lname,
//                     'email' => $owner->email,
//                     'phone' => $owner->phone,
//                     'photo' => $owner->photo,
//                 ] : null,
//             ];
//         }

//         // round
//         if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->round) {
//             $round = $nomination->camelRoundParticipation->round;
//             $roundData = [
//                 'id' => $round->id,
//                 'name' => $round->name,
//                 'name_en' => $round->name_en,
//                 'des' => $round->des,
//                 'des_en' => $round->des_en,
//                 'start' => $round->start,
//                 'end' => $round->end,
//                 'status' => $round->status,
//                 'round_type' => $round->round_type,
//             ];
//         }

//         // festival
//         if ($nomination->festival) {
//             $festival = $nomination->festival;
//             $festivalData = [
//                 'id' => $festival->id,
//                 'name' => $festival->name,
//                 'name_en' => $festival->name_en,
//                 'des' => $festival->des,
//                 'des_en' => $festival->des_en,
//                 'start' => $festival->start,
//                 'end' => $festival->end,
//                 'start_date_human' => $festival->start
//                     ? \Carbon\Carbon::parse($festival->start)
//                         ->timezone(config('app.timezone'))
//                         ->format('d/m/Y')
//                     : null,
//                 'end_date_human' => $festival->end
//                     ? \Carbon\Carbon::parse($festival->end)
//                         ->timezone(config('app.timezone'))
//                         ->format('d/m/Y')
//                     : null,
//                 'location' => $festival->location,
//                 'status' => $festival->status,
//             ];
//         }

//         $result[] = [
//             'nomination_id' => $nomination->id,
//                         'points' => $nomination->points,

//             'is_winner' => $nomination->is_winner,
//             'created_at' => $nomination->created_at,
//             'created_at_human' => $nomination->created_at
//                 ? \Carbon\Carbon::parse($nomination->created_at)
//                     ->locale('ar')
//                     ->diffForHumans()
//                 : null,
//             'updated_at' => $nomination->updated_at,
//             'camal' => $camalData,
//             'round' => $roundData,
//             'festival' => $festivalData,
//         ];
//     }

//     return response()->json($result);
// }

public function getUserNominationsApi(Request $request)
{
    $userId = $request->input('user_id');
    $festivalId = $request->input('festival_id');

    // جلب كل الترشيحات للمستخدم مع العلاقات اللازمة
    $nominations = \App\Models\Nomination::with(['festival', 'camelRoundParticipation.round'])
        ->where('user_id', $userId)
        ->where('festival_id', $festivalId)
        ->orderBy('created_at', 'desc')
        ->get();

    $result = [];

    foreach ($nominations as $nomination) {
        $camelData = null;
        $roundData = null;
        $festivalData = null;

        // ✅ جلب بيانات الجمل من جدول camel_round_participations
        if ($nomination->camelRoundParticipation) {
            $camel = $nomination->camelRoundParticipation;
            $camelData = [
                'id' => $camel->id,
                'camel_name' => $camel->camel_name,
                'camel_age_name' => $camel->camel_age_name,
                'camel_owner_name' => $camel->camel_owner_name,
                'camel_owner_country' => $camel->camel_owner_country,
                'registration_number' => $camel->registration_number,
                'is_winner' => $camel->is_winner,
            ];
        }

        // ✅ بيانات الشوط
        if ($nomination->camelRoundParticipation && $nomination->camelRoundParticipation->round) {
            $round = $nomination->camelRoundParticipation->round;
            $roundData = [
                'id' => $round->id,
                'name' => $round->name,
                'name_en' => $round->name_en,
                'des' => $round->des,
                'des_en' => $round->des_en,
                'start' => $round->start,
                'end' => $round->end,
                'status' => $round->status,
                'round_type' => $round->round_type,
            ];
        }

        // ✅ بيانات المهرجان
        if ($nomination->festival) {
            $festival = $nomination->festival;
            $festivalData = [
                'id' => $festival->id,
                'name' => $festival->name,
                'name_en' => $festival->name_en,
                'des' => $festival->des,
                'des_en' => $festival->des_en,
                'start' => $festival->start,
                'end' => $festival->end,
                'start_date_human' => $festival->start
                    ? \Carbon\Carbon::parse($festival->start)
                        ->timezone(config('app.timezone'))
                        ->format('d/m/Y')
                    : null,
                'end_date_human' => $festival->end
                    ? \Carbon\Carbon::parse($festival->end)
                        ->timezone(config('app.timezone'))
                        ->format('d/m/Y')
                    : null,
                'location' => $festival->location,
                'status' => $festival->status,
            ];
        }

        // ✅ بناء النتيجة النهائية
        $result[] = [
            'nomination_id' => $nomination->id,
            'points' => $nomination->points,
            'is_winner' => $nomination->is_winner,
            'created_at' => $nomination->created_at,
            'created_at_human' => $nomination->created_at
                ? \Carbon\Carbon::parse($nomination->created_at)
                    ->locale('ar')
                    ->diffForHumans()
                : null,
            'updated_at' => $nomination->updated_at,
            'camal' => $camelData, // ← بيانات الجمل الآن من جدول camel_round_participations
            'round' => $roundData,
            'festival' => $festivalData,
        ];
    }

    return response()->json($result);
}




public function getUserFestivalsWithPoints(Request $request)
{
    

    $userId = $request->input('user_id');

    // نجيب مجموع النقاط لكل مهرجان شارك فيه المستخدم
    $festivals = \App\Models\Nomination::select(
            'festival_id',
            \DB::raw('SUM(points) as total_points')
        )
        ->where('user_id', $userId)
        ->groupBy('festival_id')
        ->with('festival') // علاقة المهرجان
        ->get();

    $result = [];

    foreach ($festivals as $row) {
        $festival = $row->festival;

        if ($festival) {
            $result[] = [
                'festival_id' => $festival->id,
                'name' => $festival->name,
                'name_en' => $festival->name_en,
                'des' => $festival->des,
                'des_en' => $festival->des_en,
                'start' => $festival->start,
                'end' => $festival->end,
                'start_date_human' => $festival->start
                    ? \Carbon\Carbon::parse($festival->start)->format('d/m/Y')
                    : null,
                'end_date_human' => $festival->end
                    ? \Carbon\Carbon::parse($festival->end)->format('d/m/Y')
                    : null,
                'location' => $festival->location,
                'status' => $festival->status,
                'total_points' => (int) $row->total_points, // مجموع النقاط
            ];
        }
    }

    return response()->json($result);
}


// public function getFestivalLeaderboard(Request $request)
// {
//     $festivalId = $request->input('festival_id');

//     // جلب المستخدمين مع مجموع النقاط
//     $usersPoints = \DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             \DB::raw('SUM(nominations.points) as total_points')
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

//     return response()->json($usersPoints);
// }



// public function getFestivalLeaderboard(Request $request)
// {

//     $festivalId = $request->input('festival_id');


//     $usersPoints = \DB::table('nominations')
//     ->join('users', 'nominations.user_id', '=', 'users.id')
//     ->select(
//         'users.id',
//         'users.fname',
//         'users.lname',
//         'users.email',
//         'users.phone',
//         'users.country_flag',
//         'users.photo',
//         \DB::raw('SUM(nominations.points) as total_points')
//     )
//     ->where('nominations.festival_id', $festivalId)
//     ->groupBy(
//         'users.id',
//         'users.fname',
//         'users.lname',
//         'users.email',
//         'users.phone',
//         'users.country_flag',
//         'users.photo'
//     )
//     ->orderByDesc('total_points')       // ترتيب تنازلي حسب النقاط
//     ->orderBy('fname')                  // ترتيب تصاعدي أبجدي حسب الاسم الأول عند التساوي
//     ->get();


//     return response()->json($usersPoints);
// }




public function getFestivalLeaderboard(Request $request)
{

    $festivalId = $request->input('festival_id');


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
        \DB::raw('SUM(nominations.points) as total_points')
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
    ->orderByDesc('total_points')       // ترتيب تنازلي حسب النقاط
    ->orderBy('fname')                  // ترتيب تصاعدي أبجدي حسب الاسم الأول عند التساوي
    ->get();


    return response()->json($usersPoints);
}




// public function getUserInfoNomination(Request $request)
// {

//     $festivalId = $request->input('festival_id');

//     $user_id = $request->input('user_id');

//     $usersPoints = \DB::table('nominations')
//     ->join('users', 'nominations.user_id', '=', 'users.id')
//     ->select(
//         'users.id',
//         'users.fname',
//         'users.lname',
//         'users.email',
//         'users.phone',
//         'users.country_flag',
//         'users.photo',
//         \DB::raw('SUM(nominations.points) as total_points')
//     )
//     ->where('nominations.festival_id', $festivalId)
//     ->groupBy(
//         'users.id',
//         'users.fname',
//         'users.lname',
//         'users.email',
//         'users.phone',
//         'users.country_flag',
//         'users.photo'
//     )
//     ->orderByDesc('total_points')       // ترتيب تنازلي حسب النقاط
//     ->orderBy('fname')                  // ترتيب تصاعدي أبجدي حسب الاسم الأول عند التساوي
//     ->get();


//     return response()->json($usersPoints);
// }


// public function getUserInfoNomination(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $userId     = $request->input('user_id');

//     // 1️⃣ إجمالي نقاط المستخدم
//     $user = \DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             \DB::raw('SUM(nominations.points) AS total_points')
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

//     if (!$user) {
//         return response()->json(null);
//     }

//     // 2️⃣ حساب الترتيب
//     $rank = \DB::table('nominations')
//         ->select(\DB::raw('SUM(points) as total_points'))
//         ->where('festival_id', $festivalId)
//         ->groupBy('user_id')
//         ->havingRaw('SUM(points) > ?', [$user->total_points])
//         ->count() + 1;

//     $user->user_rank = $rank;

//     return response()->json($user);
// }


//Important
public function getUserInfoNomination(Request $request)
{
    $festivalId = $request->input('festival_id');
    $userId     = $request->input('user_id');

    // 1️⃣ جلب كل المستخدمين مع إجمالي النقاط (نفس دالة getFestivalLeaderboardLazy)
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
            \DB::raw('SUM(nominations.points) as total_points')
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

    // 2️⃣ ترتيب النقاط + الأبجدية عند تساوي النقاط (με الحفاظ على الرموز - نفس دالة getFestivalLeaderboardLazy)
    $sortedCollection = $usersPoints->sort(function ($a, $b) {
        if ($a->total_points != $b->total_points) {
            return $b->total_points <=> $a->total_points; // الأعلى نقاط أولاً
        }
        return $a->fname <=> $b->fname; // أبجدي عند تساوي النقاط بدون تنظيف الرموز
    })->values();

    // جلب المستخدم المطلوب
    $userIndex = $sortedCollection->search(fn($u) => $u->id == $userId);

    if ($userIndex === false) {
        return response()->json([
            'message' => 'User not found in this festival.'
        ], 404);
    }

    // المستخدم + إضافة الترتيب
    $user = $sortedCollection[$userIndex];
    $user->user_rank = $userIndex + 1; // الترتيب يبدأ من 1

    return response()->json($user);
}



// Stop important for lazyLoading
// public function getFestivalLeaderboardLazy(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $page = $request->input('page', 1);      // رقم الصفحة الحالية
//     $limit = $request->input('limit', 10);   // عدد العناصر في الصفحة

//     $usersPoints = \DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             \DB::raw('SUM(nominations.points) as total_points')
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
//         ->orderBy('fname')
//         ->paginate($limit, ['*'], 'page', $page);

//     return response()->json($usersPoints);
// }


// important 2
// public function getFestivalLeaderboardLazy(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $page       = $request->input('page', 1);
//     $limit      = $request->input('limit', 10);

//     // 1️⃣ جلب كل المستخدمين مع إجمالي النقاط
//     $usersPoints = \DB::table('nominations')
//         ->join('users', 'nominations.user_id', '=', 'users.id')
//         ->select(
//             'users.id',
//             'users.fname',
//             'users.lname',
//             'users.email',
//             'users.phone',
//             'users.country_flag',
//             'users.photo',
//             \DB::raw('SUM(nominations.points) as total_points')
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
//         ->get();

//     // 2️⃣ ترتيب النقاط + الأبجدية عند تساوي النقاط (مع الحفاظ على الرموز)
//     $sortedCollection = $usersPoints->sort(function ($a, $b) {
//         if ($a->total_points != $b->total_points) {
//             return $b->total_points <=> $a->total_points; // الأعلى نقاط أولاً
//         }
//         return $a->fname <=> $b->fname; // أبجدي عند تساوي النقاط بدون تنظيف الرموز
//     })->values();

//     // 3️⃣ Pagination يدوي
//     $total = $sortedCollection->count();
//     $itemsForCurrentPage = $sortedCollection->slice(($page - 1) * $limit, $limit)->values();

//     $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
//         $itemsForCurrentPage,
//         $total,
//         $limit,
//         $page,
//         ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
//     );

//     // 4️⃣ إضافة الترتيب لكل عنصر
//     foreach ($itemsForCurrentPage as $index => $item) {
//         $item->user_rank = ($page - 1) * $limit + $index + 1;
//     }

//     return response()->json($paginated);
// }



public function getFestivalLeaderboardLazy(Request $request)
{
    $festivalId = $request->input('festival_id');
    $page       = $request->input('page', 1);
    $limit      = $request->input('limit', 10);

    // 1️⃣ جلب كل المستخدمين مع إجمالي النقاط
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
            \DB::raw('SUM(nominations.points) as total_points')
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

    // 2️⃣ ترتيب النقاط + الأبجدية عند تساوي النقاط (مع الحفاظ على الرموز)
    $sortedCollection = $usersPoints->sort(function ($a, $b) {
        if ($a->total_points != $b->total_points) {
            return $b->total_points <=> $a->total_points; // الأعلى نقاط أولاً
        }
        return $a->fname <=> $b->fname; // أبجدي عند تساوي النقاط بدون تنظيف الرموز
    })->values();

    // 3️⃣ Pagination يدوي
    $total = $sortedCollection->count();
    $itemsForCurrentPage = $sortedCollection->slice(($page - 1) * $limit, $limit)->values();

    // 4️⃣ إضافة الترتيب وحقل has_any_position لكل عنصر
    foreach ($itemsForCurrentPage as $index => $item) {
        $item->user_rank = ($page - 1) * $limit + $index + 1;

        // التحقق إذا حصل المستخدم على أي مركز في أي مهرجان
        $item->has_any_position = \DB::table('user_positions')
            ->where('user_id', $item->id)
            ->exists();
    }

    $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
        $itemsForCurrentPage,
        $total,
        $limit,
        $page,
        ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
    );

    return response()->json($paginated);
}







public function getUserWinningFestivals(Request $request)
{
    $userId = $request->input('user_id');

    if (!$userId) {
        return response()->json([
            'status'  => false,
            'message' => 'user_id مطلوب',
        ], 422);
    }

    $festivals = DB::table('user_positions')
        ->join('festivals', 'user_positions.festival_id', '=', 'festivals.id')
        ->where('user_positions.user_id', $userId)
        ->select(
            'festivals.id as festival_id',
            'festivals.name as festival_name',
            'user_positions.user_position'
        )
        ->orderBy('user_positions.user_position') // المركز الأول ثم الثاني ...
        ->get();

    return response()->json([
        'status' => true,
        'count'  => $festivals->count(),
        'data'   => $festivals,
    ]);
}







}
