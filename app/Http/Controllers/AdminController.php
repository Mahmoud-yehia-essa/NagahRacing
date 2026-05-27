<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;


class AdminController extends Controller
{
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message' => 'تم تسجيل الخروج',
            'alert-type' => 'success'
        );

        return redirect('/login')->with($notification);
    }

    public function adminProfile()
    {
        $id = Auth::user()->id;
        $userAdmin = User::find($id);
        return view('admin.admin_profile',compact('userAdmin'));
    }

    public function adminProfileStore(Request $request)
    {

        $id = Auth::user()->id;
        $userAdmin = User::find($id);

        $userAdmin->fname = $request->fname;

        $userAdmin->lname = $request->lname;

        $userAdmin->email = $request->email;
        $userAdmin->phone = $request->phone;

        $userAdmin->address = $request->address;


        if ($request->file('photo')) {
            $file = $request->file('photo');
            @unlink(public_path('upload/admin_images/'.$userAdmin->photo));
            $filename = date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/admin_images'),$filename);
            $userAdmin['photo'] = $filename;
        }


        $userAdmin->save();


        $notification = array(
            'message' => 'تم تعديل البيانات',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.profile')->with($notification);
    }


    public function AdminChangePassword(){
        return view('admin.admin_change_password');
    } // End Mehtod


    public function AdminUpdatePassword(Request $request){
        // Validation
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed',
        ],[
            'old_password.required' => 'الرجاء التأكد من كتابة كلمة المرور القديمة',
            'new_password.required' => 'الرجاء التأكد من كتابة كلمة المرور الجديدة',
            'new_password.confirmed' => 'الرجاء التأكد من تطابق كلمة المرور',
        ]);
        // Match The Old Password
        if (!Hash::check($request->old_password, auth::user()->password)) {
            return back()->with("error", "الرجاء التحقق من كتابة كلمة المرور القديمة بطريقة صحيحة");
        }
        // Update The new password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)
        ]);
        return back()->with("status", "تم تعديل كلمة المرور");
    } // End Mehtod
}
