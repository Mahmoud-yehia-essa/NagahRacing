<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class NotificationController extends Controller
{
    public function sendNotification()
    {
return view('admin.notification.send_notification');
    }

    public function alldNotification()
    {
return view('admin.notification.all_notification');
    }



//     public function loadNotifications(Request $request)
// {
//     $user = Auth::user();
//     $page = $request->page ?? 1;
//     $limit = 10; // عدد الإشعارات في كل طلب

//     $notifications = $user->unreadNotifications()
//         ->orderBy('created_at', 'desc')
//         ->skip(($page - 1) * $limit)
//         ->take($limit)
//         ->get();

//     $hasMore = $user->unreadNotifications()->count() > ($page * $limit);

//     return response()->json([
//         'notifications' => $notifications,
//         'hasMore' => $hasMore
//     ]);
// }


 public function loadMore(Request $request)
    {
        // $user = Auth::user();
        // $notifications = $user->unreadNotifications()->paginate(10);

        // return response()->json([
        //     'notifications' => $notifications->items(),
        //     'hasMore' => $notifications->hasMorePages()
        // ]);



$notifications = Auth::user()->unreadNotifications()->latest()->simplePaginate(10);

$notifications->getCollection()->transform(function ($notification) {
    // تحويل الوقت لتوقيت الكويت
    $notification->timeAgo = Carbon::parse($notification->created_at)
                                ->timezone('Asia/Kuwait')
                                ->locale('ar')
                                ->diffForHumans();
    return $notification;
});

return response()->json([
    'notifications' => $notifications->items(),
    'hasMore' => $notifications->hasMorePages(),
]);


    }


    public function read($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        session()->forget('unread_notifications_count');

        return redirect()->back();
    }
}
