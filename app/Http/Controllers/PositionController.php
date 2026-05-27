<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Festival;
use App\Models\UserPosition;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Validator;
class PositionController extends Controller
{
      // عرض جميع الترشيحات
    public function addPositionUser()
    {

     $users = User::all();
        $userPosition = UserPosition::all();
        $festivals = Festival::all();
        // $rounds = Round::all();

        return view('admin.position.add_position_user', compact('users', 'festivals','userPosition'));


    }


     public function searchPosition()
    {

        $festivals = Festival::all();

        return view('admin.position.search_position_user', compact('festivals'));


    }


       public function searchPositionResult(Request $request)
    {



    $festivalId = $request->festival_id;


// $userPosition = UserPosition::where('festival_id',$festivalId)->get();
$userPosition = UserPosition::where('festival_id', $festivalId)
    ->orderBy('user_position')
    ->take(3)
    ->get();


        return view('admin.position.search_position_user_result', compact('userPosition'));


    }




    //  public function addPositionUserStore(Request $request)
    // {


    // }


public function addPositionUserStore(Request $request)
{
    // 1️⃣ Validation
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'festival_id' => 'required|exists:festivals,id',
        'user_position' => 'required|integer|min:1',
    ], [
        'user_id.required' => 'الرجاء اختيار المستخدم.',
        'user_id.exists' => 'المستخدم المختار غير موجود.',
        'festival_id.required' => 'الرجاء اختيار المهرجان.',
        'festival_id.exists' => 'المهرجان المختار غير موجود.',
        'user_position.required' => 'الرجاء إدخال ترتيب المستخدم.',
        'user_position.integer' => 'الترتيب يجب أن يكون رقم.',
        'user_position.min' => 'الترتيب يجب أن يكون على الأقل 1.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
                         ->withErrors($validator)
                         ->withInput();
    }

    $userId = $request->input('user_id');
    $festivalId = $request->input('festival_id');
    $userPosition = $request->input('user_position');

    // 2️⃣ التأكد من عدم تكرار نفس المستخدم في نفس المهرجان
    $userExists = UserPosition::where('user_id', $userId)
                              ->where('festival_id', $festivalId)
                              ->first();

    if ($userExists) {


  $notification = array(
            'message' => 'هذا المستخدم تم إدخاله مسبقًا في نفس المهرجان.',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);



        // return redirect()->back()
        //                  ->with('error', 'هذا المستخدم تم إدخاله مسبقًا في نفس المهرجان.');
    }

    // 3️⃣ التأكد من عدم تكرار نفس الترتيب في نفس المهرجان (تم إيقافه للسماح بتكرار نفس الترتيب لأكثر من مستخدم)
    /*
    $positionExists = UserPosition::where('festival_id', $festivalId)
                                  ->where('user_position', $userPosition)
                                  ->first();

    if ($positionExists) {


    $notification = array(
            'message' => 'هذا الترتيب مستخدم بالفعل في هذا المهرجان. يرجى اختيار ترتيب آخر.',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);


    }
    */

    // 4️⃣ إضافة المستخدم إلى الجدول
    $newPosition = new UserPosition();
    $newPosition->user_id = $userId;
    $newPosition->festival_id = $festivalId;
    $newPosition->user_position = $userPosition;
    $newPosition->save();

    // 5️⃣ إعادة التوجيه مع رسالة نجاح
    // return redirect()->back()->with('success', 'تم إضافة ترتيب المستخدم بنجاح.');

      $notification = array(
            'message' => 'تم إضافة ترتيب المستخدم بنجاح.',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
}


        public function getRounds($festivalId)
{
    // $rounds = Round::where('festival_id', $festivalId)->get();

      $rounds = Round::where('festival_id', $festivalId)
                   ->where('status', '!=', 'finished')
                   ->get();

    return response()->json($rounds);
}

}
