<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationDashboardController extends Controller
{



      public function setNotificationRead($notificationId)
    {



       $current_user_id = Auth::User()->id;

       $user = User::find($current_user_id);


$notification = $user->unreadNotifications()->where('id', $notificationId)->first();
if ($notification) {
    $notification->markAsRead();
    
    session()->forget('unread_notifications_count');


    if($notification->type == "App\Notifications\NominationNotification")
    {

       // return "OrderComplete";
    //    $order_id = $notification->data['order_id'];


        return redirect()->route('all.nomination');

    }
    else if($notification->type == "App\Notifications\ContactUsNotification")
    {


        // $vendorId = $notification->data['vendorId'];


        return redirect()->route('all.contact.us');


        //$this->vendorId
    }
    else
    {
        // $userId = $notification->data['userId'];


        return redirect()->route('dashboard');


    }

  //  return $notification->type;





} else {
    // Handle case where the notification with the specified ID was not found
    // This could be logging an error, showing a message to the user, etc.
}









    }
}
