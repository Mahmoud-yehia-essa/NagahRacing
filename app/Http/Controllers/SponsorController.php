<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Request;

use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
use Intervention\Image\Drivers\Gd\Driver; // Use GD driver (or use Intervention\Image\Drivers\Imagick\Driver for Imagick)


class SponsorController extends Controller
{



    public function addSponsorNew(){

        return view('admin.sponsor.sponsor_add_new');


    }

    public function allSponsor()
    {

        $sponsors = Sponsor::latest()->get();


        return view('admin.sponsor.all_sponsor',compact('sponsors'));
    }






      public function addSponsorStore(Request $request){
  $request->validate([
            'title' => 'required|string|max:255',
                        'title_en' => 'required|string|max:255',

            'des' => 'nullable|string',
                        'des_en' => 'nullable|string',


                        'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

        ], [
               'title.required' => '⚠️ الرجاء اضافة اسم الراعي',
            'title.string' => '⚠️ الرجاء التأكد من كتابة الراعي بشكل صحيح',
    'title_en.required' => '⚠️  الرجاء اضافة اسم الراعي بالانجليزية' ,
            'title_en.string' => '⚠️ الرجاء التأكد من كتابة الراعي بالانجليزية بشكل صحيح',


            'photo.required' => '⚠️ الرجاء اضافة صورة ',
            'photo.image' => '⚠️ تأكد من اضافة صورة',
            'photo.mimes' => '⚠️ الصورة يجب ان تكون jpeg, png, jpg, or gif ',
            'photo.max' => '⚠️  2MB حجم الصورة يجب الا يتعدى',
        ]);



        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            // Ensure directory exists

                    $path = public_path('upload/sponsor/');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $imageManager = new ImageManager(new Driver()); // Use new Imagick\Driver() for Imagick
            // Process and save image
            // $imageResized = $imageManager->read($image)->resize(364, 176);

                    $imageResized = $imageManager->read($image);

            $imageResized->save($path . $name_gen);

            $save_url = 'upload/sponsor/' . $name_gen;
        }

        // Insert game type

        Sponsor::create([
            'title' => $request->title,
                        'title_en' => $request->title_en,

            'des' => $request->des,
            'des_en' => $request->des_en,

                'link'  => $request->link,


            'photo' => $save_url ?? null,
            // 'special' => $request->special,

        ]);


        $notification = array(
            'message' => 'تم اضافة الراعي ',
            'alert-type' => 'success'
        );

        return redirect()->route('sponsor.all')->with($notification);


    }
        public function editSponsor($id){

            $getSponsorHome = Sponsor::findOrFail($id);

        return view('admin.sponsor.sponsor_home_cate_add',compact('getSponsorHome'));
    }


       public function addSponsorQuestion(){

            $getSponsorHome = Sponsor::findOrFail(2);

        return view('admin.sponsor.sponsor_home_cate_add',compact('getSponsorHome'));
    }



       public function editHomeCateStore(Request $request){




        $request->validate([
            'title' => 'required|string|max:255',
                        'title_en' => 'required|string|max:255',

            'photo' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'title.required' => '⚠️ الرجاء اضافة اسم الراعي',
            'title.string' => '⚠️ الرجاء التأكد من كتابة الراعي بشكل صحيح',

              'title_en.required' => '⚠️ الرجاء اضافة اسم الراعي بالانجليزية' ,
            'title_en.string' => '⚠️ الرجاء التأكد من كتابة الراعي بالانجليزية بشكل صحيح',


            'photo.required' => '⚠️ الرجاء اضافة صورة ',
            'photo.image' => '⚠️ تأكد من اضافة صورة',
            'photo.mimes' => '⚠️ الصورة يجب ان تكون jpeg, png, jpg, or gif ',
            'photo.max' => '⚠️  2MB حجم الصورة يجب الا يتعدى',
        ]);


        $id = $request->id;
        $old_img = $request->old_image;
        if ($request->file('photo')) {
        $image = $request->file('photo');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();



        $path = public_path('upload/sponsor/');

        $imageManager = new ImageManager(new Driver()); // Use new Imagick\Driver() for Imagick
        // Process and save image
        // $imageResized = $imageManager->read($image)->resize(364, 176);
        $imageResized = $imageManager->read($image);

        $imageResized->save($path . $name_gen);

        $save_url = 'upload/sponsor/' . $name_gen;


        if (file_exists($old_img)) {
           unlink($old_img);
        }
        Sponsor::findOrFail($id)->update([
            'title' => $request->title,
                        'title_en' => $request->title_en,

            'des' => $request->des,

                        'des_en' => $request->des_en,

            'photo' => $save_url ,
            'link'  => $request->link,

        ]);
       $notification = array(
            'message' => 'تم تعديل الراعي',
            'alert-type' => 'success'
        );
        return redirect()->route('sponsor.all')->with($notification);

        } else {
            Sponsor::findOrFail($id)->update([
               'title' => $request->title,
                                       'title_en' => $request->title_en,

            'des' => $request->des,
                                    'des_en' => $request->des_en,

            'link'  => $request->link,


        ]);
       $notification = array(
            'message' => 'تم تعديل الراعي',
            'alert-type' => 'success'
        );
        return redirect()->route('sponsor.all')->with($notification);
        } // end else
    }// End Method


       public function deleteSponsor($id){
        $sponsor = Sponsor::findOrFail($id);
        $img = $sponsor->photo;

        // unlink($img );

        if ($sponsor->photo && file_exists(public_path($sponsor->photo))) {
            unlink(public_path($sponsor->photo));
        }
        Sponsor::findOrFail($id)->delete();
        $notification = array(
            'message' => 'تم حذف الراعي ',
            'alert-type' => 'success'
        );
       // return redirect()->route('all.category')->with($notification);

        return redirect()->route('sponsor.all')->with($notification);

        // return redirect()->back()->with($notification);
    }// End Method

    /// API

      public function getSponsor()
    {


    $sponsor = Sponsor::latest()->get();



    return response()->json($sponsor);
    }




    public function getAllsponsorApi(Request $request)
{
    $limit = $request->input('limit'); // جاي من POST body

    $query = Sponsor::latest();

    if (!is_null($limit) && $limit > 0) {
        $query->take($limit);
    }

    $sponsor = $query->get();

    return response()->json($sponsor);
}


}
