<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\NotificationForApp;
use App\Services\FirebaseNotificationService;

use Carbon\Carbon;

class NotificationForAppController extends Controller
{
    //


//       public function sendNotification()
//     {
// return view('admin.notification.send_notification');
//     }


      public function sendNotification()
    {

// $users = User::latest()->where('token', '!=', '')->get();

$users = User::where('token', '!=', '')->get();


return view('admin.notification.send_notification',compact('users'));
    }

//     public function alldNotification()
//     {
// return view('admin.notification.all_notification');
//     }

//    public function alldNotification()
//     {

//         $notification = NotificationForApp::latest()->get();

// return view('admin.notification.all_notification',compact('notification'));
//     }


public function alldNotification()
{
    $notification = NotificationForApp::with('user')
        ->latest()
        ->paginate(20); // عدد العناصر في الصفحة

    return view('admin.notification.all_notification', compact('notification'));
}


       public function deleteAllNotifications()
{
  // return "run";
    NotificationForApp::query()->delete(); // أو استخدم Notification::truncate();

    $notification = array(
        'message' => 'تم الحذف',
        'alert-type' => 'success'
    );

    return redirect()->route('all.notification')->with($notification);
}



public function sendPush()
{
    $token = 'ckmYSOB8Q7-MMlIf9dzXhb:APA91bFFRW3JwlbuwIcac5v-tn4h49V0IVQLG05yxA6i1syO7fJemn_nn7QTvQRvs00BQkH4fCC0Rk00pGajlhgGwt1wesW6SoQ3rxrbrkc7SHfs0_w4ufk'; // Get this from DB or user
    $title = 'Hello from Laravel';
    $body = 'This is a test notification';
    $postId = '123';
    $status = 'active';

    $response = (new FirebaseNotificationService())->sendNotification($token, $title, $body, $postId, $status);

    return response()->json($response);
}




// public function sendNotificationStore(Request $request)
// {

//     // return "test";
//     $request->validate([
//         'title' => 'required|string',
//         'description' => 'required|string',
//         'user_select' => 'required|array|min:1',
//     ], [
//         'title.required' => 'حقل العنوان مطلوب.',
//         'title.string' => 'يجب أن يكون العنوان نصًا.',
//         'description.required' => 'حقل الوصف مطلوب.',
//         'description.string' => 'يجب أن يكون الوصف نصًا.',
//         'user_select.required' => 'يجب اختيار مستخدم واحد على الأقل.',
//         'user_select.array' => 'صيغة غير صحيحة لاختيارات المستخدم.',
//         'user_select.min' => 'يجب اختيار مستخدم واحد على الأقل.',
//     ]);



//     // تجهيز وصف الإشعار مع الصورة إن وُجدت
//     $modifiedDescription = "";

//     if ($request->hasFile('Photo')) {
//         $file = $request->file('Photo');
//         $filenamePhoto = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
//         $file->move(public_path('upload/notification'), $filenamePhoto);

//         $imagePath = url('upload/notification/'.$filenamePhoto);
//         $imageHtml = '<img src="' . $imagePath . '" alt="Notification Image" style="max-width: 100%; height: auto; display: block; margin-bottom: 10px;">';
//         $modifiedDescription = $imageHtml . $request->description;
//     } else {
//         $modifiedDescription = $request->description;
//     }

//     $selectedUsers = $request->input('user_select'); // هذا مصفوفة

//     // إذا اختار المستخدم "الكل" (أي في المصفوفة القيمة 'all')
//     if (in_array('all', $selectedUsers)) {
//         // جلب كل المستخدمين الذين لديهم توكن صالح (غير فارغ)
//         $users = User::whereNotNull('token')->where('token', '!=', '')->get();
//     } else {
//         // جلب المستخدمين المختارين فقط
//         $users = User::whereIn('id', $selectedUsers)->whereNotNull('token')->where('token', '!=', '')->get();
//     }

//     $tokenArray = [];
//     $usersIds = [];

//     foreach ($users as $user) {


//         $tokenArray[] = $user->token;
//         $usersIds[] = $user->id;

//         NotificationForApp::insert([
//             'user_id' => $user->id,
//             'title' => $request->title,
//             'des' => $modifiedDescription,
//             'created_at' => now(),
//         ]);



//     }

//     $title = $request->title;
//     $body = "";
//     $postId = '123';
//     $status = 'active';

//     foreach ($tokenArray as $index => $token) {
//         $userId = $usersIds[$index] ?? null;
//         $getNotificationsByUserCount = NotificationForApp::where('user_id', $userId)->where('user_view', 'no')->count();



//         (new FirebaseNotificationService())->sendNotification($token, $title, $body, $postId, $status, $getNotificationsByUserCount);


//     }

//     $notification = [
//         'message' => 'تم إرسال الإشعار بنجاح للمستخدمين المحددين.',
//         'alert-type' => 'success'
//     ];

//     return redirect()->route('all.notification')->with($notification);
// }


public function sendNotificationStore(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'description' => 'required|string',
        'user_select' => 'required|array|min:1',
    ], [
        'title.required' => 'حقل العنوان مطلوب.',
        'title.string' => 'يجب أن يكون العنوان نصًا.',
        'description.required' => 'حقل الوصف مطلوب.',
        'description.string' => 'يجب أن يكون الوصف نصًا.',
        'user_select.required' => 'يجب اختيار مستخدم واحد على الأقل.',
        'user_select.array' => 'صيغة غير صحيحة لاختيارات المستخدم.',
        'user_select.min' => 'يجب اختيار مستخدم واحد على الأقل.',
    ]);

    $modifiedDescription = "";

    if ($request->hasFile('Photo')) {
        $file = $request->file('Photo');
        $filenamePhoto = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('upload/notification'), $filenamePhoto);

        $imagePath = url('upload/notification/'.$filenamePhoto);
        $imageHtml = '<img src="' . $imagePath . '" alt="Notification Image" style="max-width: 100%; height: auto; display: block; margin-bottom: 10px;">';
        $modifiedDescription = $imageHtml . $request->description;
    } else {
        $modifiedDescription = $request->description;
    }

    $selectedUsers = $request->input('user_select');

    if (in_array('all', $selectedUsers)) {
        $users = User::whereNotNull('token')->where('token', '!=', '')->get();
    } else {
        $users = User::whereIn('id', $selectedUsers)
                     ->whereNotNull('token')
                     ->where('token', '!=', '')
                     ->get();
    }

    $title = $request->title;
    $body = "";
    $postId = '123';
    $status = 'active';

    foreach ($users as $user) {
        // حفظ الإشعار في قاعدة البيانات
        NotificationForApp::insert([
            'user_id' => $user->id,
            'title' => $request->title,
            'des' => $modifiedDescription,
            'created_at' => now(),
        ]);

        // احسب عدد الاشعارات غير المقروءة للمستخدم (لو تحتاجه في الإرسال)
        $getNotificationsByUserCount = NotificationForApp::where('user_id', $user->id)
            ->where('user_view', 'no')
            ->count();

        // أرسل Job للخلفية بدلاً من الإرسال المباشر
        dispatch(new \App\Jobs\SendFirebaseNotificationJob(
            $user->token,
            $title,
            $body,
            $postId,
            $status,
            $getNotificationsByUserCount
        ));
    }

    $notification = [
        'message' => 'تم جدولة الإشعارات للإرسال في الخلفية.',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.notification')->with($notification);
}


public function UpdateNotificationUserView(Request $request)
    {



        // return "hello";

        $notification_id = $request->id;

       $Notification_user_view = $request->user_view;


        $notification = NotificationForApp::findOrFail($notification_id);


        $notification->user_view = $Notification_user_view;


        $notification->save();







        return response()->json([
            'success' => true,
        ], 200);







    }



    public function getNotificationApiForUser(Request $request)
{
    $user_id = $request->id;

    // $notifications = Notification::where("user_id", $user_id)
    //     ->where('user_view', 'no')
    //     ->get();



        $notifications = NotificationForApp::where("user_id", $user_id)->latest()->get();



    // تعديل كل عنصر لإضافة created_at بصيغة "منذ ساعة"
    $formatted = $notifications->map(function ($item) {
        return [
            'id' => $item->id,
            'user_id' => $item->user_id,
            'title' => $item->title,
            'des' => $item->des,
            'user_view' => $item->user_view,
            'date' => $item->date,
            'created_at' => Carbon::parse($item->created_at)->diffForHumans(), // التنسيق هنا
        ];
    });

    return response()->json($formatted);
}



   public function deleteNotification($id){
        // $notification = User::findOrFail($id);
        NotificationForApp::findOrFail($id)->delete();
        $notification = array(
            'message' => 'تم الحذف',
            'alert-type' => 'success'
        );
        return redirect()->route('all.notification')->with($notification);

        // return redirect()->back()->with($notification);
    }// End Method



         public function updateTokenApi(Request $request)
    {





        $user_id = $request->id;

        $user = User::findOrFail($user_id);


        $user->token = $request->token;


        $user->save();





        $token = "Non";

        return response()->json([
            'success' => true,
            'message' => 'updated user successful',
            'user' => $user, // Return all user data
            'token' => $token
        ], 200);







    }


}
