<?php

namespace App\Http\Controllers;

use App\Models\coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{

      public function AllCoupon()
    {
        $coupon = Coupon::latest()->get();
        return view('admin.coupon.coupon_all', compact('coupon'));
    } // End Method

    public function AddCoupon()
    {
        return view('admin.coupon.coupon_add');

    }


    public function StoreCoupon(Request $request)
    {

          $request->validate([
            'coupon_name' => 'required',
            'coupon_discount' => 'required|numeric',
            'coupon_validity' => 'required',

        ], [
            'coupon_name.required' => 'الرجاء اضافة اسم الكوبون',
            'coupon_discount.required' => 'الرجاء اضافة نسبة الخصم',
                    'coupon_discount.numeric' => 'نسبة الخصم يجب أن تكون رقمًا',

            'coupon_validity.required' => 'الرجاء تحديد صلاحية الكوبون',
        ]);


        Coupon::insert([

            'coupon_name' => strtoupper($request->coupon_name),
            'coupon_discount' => $request->coupon_discount,
            'coupon_validity' => $request->coupon_validity,
            'created_at' =>Carbon::now()


        ]);


        $notification = array(
            'message' => 'تم اضافة الكوبون',
            'alert-type' => 'success'
        );

        return redirect()->route('all.coupon')->with($notification);



    }

    public function EditCoupon($id){

        $coupon = Coupon::findOrFail($id);
        return view('admin.coupon.edit_coupon',compact('coupon'));

    }// End Method


    public function UpdateCoupon(Request $request){

           $request->validate([
            'coupon_name' => 'required',
            'coupon_discount' => 'required|numeric',
            'coupon_validity' => 'required',

        ], [
            'coupon_name.required' => 'الرجاء اضافة اسم الكوبون',
            'coupon_discount.required' => 'الرجاء اضافة نسبة الخصم',
                    'coupon_discount.numeric' => 'نسبة الخصم يجب أن تكون رقمًا',

            'coupon_validity.required' => 'الرجاء تحديد صلاحية الكوبون',
        ]);

        $coupon_id = $request->id;

         Coupon::findOrFail($coupon_id)->update([
            'coupon_name' => strtoupper($request->coupon_name),
            'coupon_discount' => $request->coupon_discount,
            'coupon_validity' => $request->coupon_validity,
            'created_at' => Carbon::now(),
        ]);

       $notification = array(
            'message' => 'تم تعديل الكوبون',
            'alert-type' => 'success'
        );

        return redirect()->route('all.coupon')->with($notification);


    }// End Method

     public function DeleteCoupon($id){

        Coupon::findOrFail($id)->delete();

         $notification = array(
            'message' => 'تم حذف الكوبون',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);


    }// End Method



// Api

public function getCouponByNameApi(Request $request)
{
    $name = $request->name;

    $coupon = Coupon::where('coupon_name', $name)
        ->whereDate('coupon_validity', '>=', Carbon::today()->format('Y-m-d'))
        ->first();

    if (!$coupon) {
        return response()->json([
            'isValid' => false
        ]);
    }

    $couponData = $coupon->toArray();
    $couponData['isValid'] = true;

    return response()->json($couponData);
}


}
