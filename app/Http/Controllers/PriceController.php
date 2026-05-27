<?php

namespace App\Http\Controllers;

use App\Models\price;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;

class PriceController extends Controller
{


//     public function getPriceAttribute($value)
// {
//     return rtrim(rtrim($value, '0'), '.');
// }

       public function allPrice()
    {
        $price = price::latest()->get();




        return view('admin.price.all_price',compact('price'));
    }

          public function addPrice()
    {

        return view('admin.price.add_price');
    }





           public function addPriceStore(Request $request)
    {


          $request->validate([
            'title' => 'required',
            'price' => 'required|numeric',
            'games_number' => 'required|numeric',
            'color1' => 'required',
            'color2' => 'required',


        ], [
            'title.required' => 'الرجاء اضافة الوصف',
            'price.required' => 'الرجاء اضافة السعر',
                        'price.numeric' => 'الرجاء اضافة السعر رقما',

                          'games_number.required' => 'الرجاء اضافة عدد الألعاب',
                        'games_number.numeric' => 'الرجاء اضافة عدد الألعاب رقما',

            'color1.required' => 'الرجاء اضافة اللون الأول',
            'color2.required' => 'الرجاء اضافة اللون الثاني',
        ]);

            // Convert colors
    $color1 = $this->convertColorToFlutter($request->color1);
    $color2 = $this->convertColorToFlutter($request->color2);


        // Example return (just for checking)
    Price::insert([

            'title' => $request->title,
            'price' => $request->price,
            'color1' => $color1,
            'color2' => $color2,
            'games_number' => $request->games_number,



            'created_at' =>Carbon::now()


        ]);


        $notification = array(
            'message' => 'تم اضافة سعر جديد',
            'alert-type' => 'success'
        );

        return redirect()->route('all.price')->with($notification);



    }


   public function editPrice($id)
{
    $price = Price::findOrFail($id);

    // Convert Flutter color format to standard hex
    $price->color1 = $this->convertFlutterToHex($price->color1);
    $price->color2 = $this->convertFlutterToHex($price->color2);

    //  $price->price = getPriceAttribute($price->price);

    return view('admin.price.edit_price', compact('price'));
}

 public function editPriceStore(Request $request){


          $request->validate([
            'title' => 'required',
            'price' => 'required|numeric',
            'color1' => 'required',
            'color2' => 'required',
                        'games_number' => 'required|numeric',



        ], [
            'title.required' => 'الرجاء اضافة الوصف',
            'price.required' => 'الرجاء اضافة السعر',
                        'price.numeric' => 'الرجاء اضافة السعر رقما',
                              'games_number.required' => 'الرجاء اضافة عدد الألعاب',
                        'games_number.numeric' => 'الرجاء اضافة عدد الألعاب رقما',

            'color1.required' => 'الرجاء اضافة اللون الأول',
            'color2.required' => 'الرجاء اضافة اللون الثاني',
        ]);

            // Convert colors
    $color1 = $this->convertColorToFlutter($request->color1);
    $color2 = $this->convertColorToFlutter($request->color2);

        $price_id = $request->id;

         Price::findOrFail($price_id)->update([
                  'title' => $request->title,
            'price' => $request->price,
            'color1' => $color1,
            'color2' => $color2,
                              'games_number' => $request->games_number,


            'created_at' =>Carbon::now()



        ]);

       $notification = array(
            'message' => 'تم تعديل السعر',
            'alert-type' => 'success'
        );

        return redirect()->route('all.price')->with($notification);


    }// E

       public function deletePrice($id){

        Price::findOrFail($id)->delete();

         $notification = array(
            'message' => 'تم حذف السعر',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);


    }// End Method


    private function convertFlutterToHex($flutterColor)
{
    // Remove '0x' or '0X' prefix if present
    $hex = strtoupper(ltrim($flutterColor, '0x'));

    // Remove alpha (first 2 characters: 'FF' for full opacity)
    $rgb = substr($hex, 2);

    // Return with '#' and lowercase format
    return '#' . strtolower($rgb);
}
    private function convertColorToFlutter($hexColor)
{
    // Remove '#' if present
    $hex = ltrim($hexColor, '#');

    // Prepend 'FF' for full opacity alpha
    return '0xFF' . strtoupper($hex);
}



// api




  public function getAllPrice(Request $request) {




    $price = Price::latest()->get()->map(function ($item) {
    $item->price_after_coupon = "0";
    return $item;
});

return response()->json($price);

    //     $price = Price::latest()->get();
    // return response()->json($price);



    }


    // Api




}
