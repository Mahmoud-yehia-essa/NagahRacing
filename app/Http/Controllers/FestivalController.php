<?php

namespace App\Http\Controllers;

use App\Models\Festival;
use App\Models\UserDoublePointUsage;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

class FestivalController extends Controller
{

     //
    public function allFestival()
    {
        $festival = Festival::latest()->get();




        return view('admin.festival.all_festivals',compact('festival'));
    }

    public function addFestival()
    {

        return view('admin.festival.add_festivals');
    }

    // public function storeFestival(Request $request)
    // {



    //     $request->validate([
    //         'category_name' => 'required|string|max:255',
    //         'category_description' => 'nullable|string',
    //         'category_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ], [
    //         'category_name.required' => '⚠️ الرجاء اضافة اسم الفئة',
    //         'category_name.string' => '⚠️ الرجاء التأكد من كتابة الفئة بشكل صحيح',
    //         'category_name.max' => '⚠️ الرجاء التأكد من عدد احرف الفئة لا يتجاوز 255 حرف',

    //         'category_description.string' => '⚠️ الرجاء التأكد من كتابة الوصف بشكل صحيح',

    //         'category_photo.required' => '⚠️ الرجاء اضافة صورة للفئة',
    //         'category_photo.image' => '⚠️ تأكد من اضافة صورة',
    //         'category_photo.mimes' => '⚠️ الصورة يجب ان تكون jpeg, png, jpg, or gif ',
    //         'category_photo.max' => '⚠️  2MB حجم الصورة يجب الا يتعدى',
    //     ]);



    //     if ($request->hasFile('category_photo')) {
    //         $image = $request->file('category_photo');
    //         $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

    //         // Ensure directory exists
    //         $path = public_path('upload/category/');
    //         if (!file_exists($path)) {
    //             mkdir($path, 0777, true);
    //         }

    //         $imageManager = new ImageManager(new Driver()); // Use new Imagick\Driver() for Imagick
    //         // Process and save image
    //         $imageResized = $imageManager->read($image)->resize(364, 176);
    //         $imageResized->save($path . $name_gen);

    //         $save_url = 'upload/category/' . $name_gen;
    //     }

    //     // Insert category
    //     Category::create([
    //         'category_name' => $request->category_name,
    //         'category_description' => $request->category_description,
    //         'category_photo' => $save_url ?? null,
    //         'special' => $request->special,

    //     ]);


    //     $notification = array(
    //         'message' => 'تم اضافة الفئة ',
    //         'alert-type' => 'success'
    //     );

    //     return redirect()->route('all.category')->with($notification);
    // }


    public function storeFestival(Request $request)
{
    $request->validate([
        'name'       => 'required|string|max:255',
        'name_en'    => 'required|string|max:255',
        'des'        => 'nullable|string',
        'des_en'     => 'nullable|string',
        'location'   => 'nullable|string|max:255',
        'count_double_used'  => 'nullable|numeric',

        'start'      => 'required|date|after_or_equal:today',
        'end'        => 'required|date|after_or_equal:start',
        'latitude'   => 'nullable|numeric',
        'longitude'  => 'nullable|numeric',
        'photo'      => 'required|image|mimes:jpeg,png,jpg,gif,webp',
    ], [
        'name.required'      => '⚠️ الرجاء اضافة اسم المهرجان',
        'name.string'        => '⚠️ الرجاء كتابة الاسم بشكل صحيح',
        'name.max'           => '⚠️ الاسم يجب ألا يتجاوز 255 حرف',

        'name_en.required'   => '⚠️ الرجاء اضافة اسم المهرجان بالانجليزية',
        'name_en.string'     => '⚠️ الرجاء كتابة الاسم بالانجليزية بشكل صحيح',
        'name_en.max'        => '⚠️ الاسم بالانجليزية يجب ألا يتجاوز 255 حرف',


                'count_double_used.numeric'   => '⚠️ الرجاء إدخال قيمة صحيحة للـ عدد مرات استخدام دبل النقاط',


        'des.string'         => '⚠️ الرجاء كتابة الوصف بشكل صحيح',
        'des_en.string'      => '⚠️ الرجاء كتابة الوصف بالانجليزية بشكل صحيح',

        'location.max'       => '⚠️ الموقع يجب ألا يتجاوز 255 حرف',

        'start.required'     => '⚠️ الرجاء اضافة تاريخ البداية',
        'start.after_or_equal' => '⚠️ تاريخ البداية يجب أن يكون اليوم أو بعده',

        'end.required'       => '⚠️ الرجاء اضافة تاريخ النهاية',
        'end.after_or_equal' => '⚠️ تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',

        'latitude.numeric'   => '⚠️ الرجاء إدخال قيمة صحيحة للـ latitude',
        'longitude.numeric'  => '⚠️ الرجاء إدخال قيمة صحيحة للـ longitude',

        'photo.required'     => '⚠️ الرجاء اضافة صورة للمهرجان',
        'photo.image'        => '⚠️ تأكد من رفع صورة صحيحة',
        'photo.mimes'        => '⚠️ الصورة يجب أن تكون jpeg, png, jpg, أو gif',
        // 'photo.max'          => '⚠️ حجم الصورة يجب ألا يتعدى 2MB',
    ]);

    if ($request->hasFile('photo')) {
        $image = $request->file('photo');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

        $path = public_path('upload/festival/');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        // $imageResized = $imageManager->read($image)->resize(600, 400);
                $imageResized = $imageManager->read($image);

        $imageResized->save($path . $name_gen);

        $save_url = 'upload/festival/' . $name_gen;
    }

    Festival::create([
        'name'       => $request->name,
        'name_en'    => $request->name_en,
        'des'        => $request->des,
        'des_en'     => $request->des_en,
        'location'   => $request->location,
        'start'      => $request->start,
        'end'        => $request->end,
        'latitude'   => $request->latitude,
        'longitude'  => $request->longitude,
                'count_double_used'  => $request->count_double_used,

        'photo'      => $save_url ?? null,
    ]);


           $notification = array(
            'message' => '✅ تم اضافة المهرجان بنجاح',
            'alert-type' => 'success'
        );
        return redirect()->route('all.festival')->with($notification);

}


    public function editFestival($id){
        $festival = Festival::findOrFail($id);
        return view('admin.festival.edit_festivals',compact('festival'));
    }// End Method



    public function updateFestival(Request $request)
{
    $festival = Festival::findOrFail($request->id);

    $request->validate([
        'name'       => 'required|string|max:255',
        'name_en'    => 'required|string|max:255',
        'des'        => 'nullable|string',
        'des_en'     => 'nullable|string',
        'location'   => 'nullable|string|max:255',
        'start'      => 'required|date',
        'end'        => 'required|date|after_or_equal:start',
        'latitude'   => 'nullable|numeric',
        'longitude'  => 'nullable|numeric',
        'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    // معالجة الصورة
    if ($request->hasFile('photo')) {
        // حذف الصورة القديمة إذا موجودة
        if ($request->old_image && file_exists(public_path($request->old_image))) {
            @unlink(public_path($request->old_image));
        }

        $image = $request->file('photo');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

        $path = public_path('upload/festival/');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $imageManager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        // $imageResized = $imageManager->read($image)->resize(600, 400);
                $imageResized = $imageManager->read($image);

        $imageResized->save($path . $name_gen);


        $save_url = 'upload/festival/' . $name_gen;
    } else {
        $save_url = $request->old_image;
    }

    $festival->update([
        'name'       => $request->name,
        'name_en'    => $request->name_en,
        'des'        => $request->des,
        'des_en'     => $request->des_en,
        'location'   => $request->location,
        'start'      => $request->start,
        'end'        => $request->end,
        'latitude'   => $request->latitude,
        'longitude'  => $request->longitude,
        'photo'      => $save_url,
    ]);

    return redirect()->route('all.festival')->with([
        'message' => '✅ تم تحديث المهرجان بنجاح',
        'alert-type' => 'success'
    ]);
}







    public function deleteFestival($id){
        $festival = Festival::findOrFail($id);
        $img = $festival->photo;

        // unlink($img );

        if ($festival->photo && file_exists(public_path($festival->photo))) {
            unlink(public_path($festival->photo));
        }
        Festival::findOrFail($id)->delete();
        $notification = array(
            'message' => 'تم حذف المهرجان',
            'alert-type' => 'success'
        );
        return redirect()->route('all.festival')->with($notification);

        // return redirect()->back()->with($notification);
    }// End Method




    public function festivalInactive($id){
        Festival::findOrFail($id)->update(['status' => 'inactive']);
        $notification = array(
            'message' => ' غير مفعل',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method
      public function festivalActive($id){
        Festival::findOrFail($id)->update(['status' => 'active']);
        $notification = array(
            'message' => 'مفعل',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }


    /// API

//     public function getFestivalsApi(Request $request)
// {
//     $type  = $request->input('type', 'all'); // current, upcoming, ended, all
//     $limit = $request->input('limit'); // 0 = all

//     $query = Festival::query();
//     $today = now()->toDateString();

//     if ($type === 'current') {
//         // المهرجانات الحالية
//         $query->where('start', '<=', $today)
//               ->where('end', '>=', $today);
//     } elseif ($type === 'upcoming') {
//         // القادمة
//         $query->where('start', '>', $today);
//     } elseif ($type === 'ended') {
//         // المنتهية
//         $query->where('end', '<', $today);
//     }

//     $query->orderBy('start', 'asc');

//     if (!is_null($limit) && $limit > 0) {
//         $query->take($limit);
//     }

//     $festivals = $query->get();

//     return response()->json($festivals);
// }



// public function getFestivalsApi(Request $request)
// {
//     $type  = $request->input('type', 'all'); // current, upcoming, ended, all
//     $limit = $request->input('limit'); // 0 = all

//     $today = now()->toDateString();

//     // helper function
//     $addType = function ($festivals, $status) {
//         return $festivals->map(function ($festival) use ($status) {
//             $festival->type = $status;
//             return $festival;
//         });
//     };

//     if ($type === 'current') {
//         $festivals = Festival::whereDate('start', '<=', $today)
//             ->whereDate('end', '>=', $today)
//             ->orderBy('start', 'asc');

//         if (!is_null($limit) && $limit > 0) {
//             $festivals->take($limit);
//         }

//         return response()->json(
//             $addType($festivals->get(), 'current')
//         );

//     } elseif ($type === 'upcoming') {
//         $festivals = Festival::whereDate('start', '>', $today)
//             ->orderBy('start', 'asc');

//         if (!is_null($limit) && $limit > 0) {
//             $festivals->take($limit);
//         }

//         return response()->json(
//             $addType($festivals->get(), 'upcoming')
//         );

//     } elseif ($type === 'ended') {
//         $festivals = Festival::whereDate('end', '<', $today)
//             ->orderBy('start', 'desc');

//         if (!is_null($limit) && $limit > 0) {
//             $festivals->take($limit);
//         }

//         return response()->json(
//             $addType($festivals->get(), 'ended')
//         );

//     } else {
//         // all → return current, then upcoming, then ended
//         $current  = Festival::whereDate('start', '<=', $today)
//             ->whereDate('end', '>=', $today)
//             ->orderBy('start', 'asc')
//             ->get();

//         $upcoming = Festival::whereDate('start', '>', $today)
//             ->orderBy('start', 'asc')
//             ->get();

//         $ended    = Festival::whereDate('end', '<', $today)
//             ->orderBy('start', 'desc')
//             ->get();

//         $festivals = collect()
//             ->merge($addType($current, 'current'))
//             ->merge($addType($upcoming, 'upcoming'))
//             ->merge($addType($ended, 'ended'));

//         // لو limit موجود > 0 نطبق على الناتج النهائي كله
//         if (!is_null($limit) && $limit > 0) {
//             $festivals = $festivals->take($limit);
//         }

//         return response()->json($festivals->values());
//     }
// }



// public function getFestivalsApi(Request $request)
// {
//     $type  = $request->input('type', 'all'); // current, upcoming, ended, all
//     $limit = $request->input('limit'); // 0 = all

//     $today = \Carbon\Carbon::now('Asia/Kuwait')->startOfDay();

//     // helper function لإضافة نوع المهرجان وحساب human
//     $addType = function ($festivals, $status) use ($today) {
//         return $festivals->map(function ($festival) use ($status, $today) {
//             $festival->type = $status;

//             if ($festival->start) {
//                 $start = \Carbon\Carbon::parse($festival->start)->setTimezone('Asia/Kuwait')->startOfDay();
//                 $festival->start = $start->format('Y-m-d'); // التاريخ فقط
//                 $festival->start_day = $start->locale('ar')->dayName; // اسم اليوم
//                 $festival->start_month = $start->locale('ar')->translatedFormat('F'); // اسم الشهر
//             }

//             if ($festival->end) {
//                 $end = \Carbon\Carbon::parse($festival->end)->setTimezone('Asia/Kuwait')->startOfDay();
//                 $festival->end = $end->format('Y-m-d'); // التاريخ فقط
//                 $festival->end_day = $end->locale('ar')->dayName; // اسم اليوم
//                 $festival->end_month = $end->locale('ar')->translatedFormat('F'); // اسم الشهر
//             }

//             // حساب human
//             if ($today->lt($start)) {
//                 // لم يبدأ بعد
//                 $festival->human = $today->diffForHumans($start, ['locale' => 'ar']);
//             } elseif ($today->between($start, $end)) {
//                 // جاري الآن
//                 $festival->human = 'يجري الآن';
//             } else {
//                 // انتهى
//                 $festival->human = $end->diffForHumans($today, ['locale' => 'ar']);
//             }

//             return $festival;
//         });
//     };

//     if ($type === 'current') {
//         $festivals = Festival::whereDate('start', '<=', $today)
//             ->whereDate('end', '>=', $today)
//             ->orderBy('start', 'asc');

//         if (!is_null($limit) && $limit > 0) {
//             $festivals->take($limit);
//         }

//         return response()->json($addType($festivals->get(), 'current'));

//     } elseif ($type === 'upcoming') {
//         $festivals = Festival::whereDate('start', '>', $today)
//             ->orderBy('start', 'asc');

//         if (!is_null($limit) && $limit > 0) {
//             $festivals->take($limit);
//         }

//         return response()->json($addType($festivals->get(), 'upcoming'));

//     } elseif ($type === 'ended') {
//         $festivals = Festival::whereDate('end', '<', $today)
//             ->orderBy('start', 'desc');

//         if (!is_null($limit) && $limit > 0) {
//             $festivals->take($limit);
//         }

//         return response()->json($addType($festivals->get(), 'ended'));

//     } else {
//         // all → return current, then upcoming, then ended
//         $current  = Festival::whereDate('start', '<=', $today)
//             ->whereDate('end', '>=', $today)
//             ->orderBy('start', 'asc')
//             ->get();

//         $upcoming = Festival::whereDate('start', '>', $today)
//             ->orderBy('start', 'asc')
//             ->get();

//         $ended    = Festival::whereDate('end', '<', $today)
//             ->orderBy('start', 'desc')
//             ->get();

//         $festivals = collect()
//             ->merge($addType($current, 'current'))
//             ->merge($addType($upcoming, 'upcoming'))
//             ->merge($addType($ended, 'ended'));

//         if (!is_null($limit) && $limit > 0) {
//             $festivals = $festivals->take($limit);
//         }

//         return response()->json($festivals->values());
//     }
// }



/*

public function getFestivalsApi(Request $request)
{
    $type  = $request->input('type', 'all'); // current, upcoming, ended, all
    $limit = $request->input('limit'); // 0 = all

    $today = \Carbon\Carbon::now('Asia/Riyadh')->startOfDay();

    // helper function لإضافة نوع المهرجان وحساب human
    $addType = function ($festivals, $status) use ($today) {
        return $festivals->map(function ($festival) use ($status, $today) {
            $festival->type = $status;

            if ($festival->start) {
                $start = \Carbon\Carbon::parse($festival->start)->setTimezone('Asia/Riyadh')->startOfDay();
                $festival->start_day   = $start->locale('ar')->dayName;
                $festival->start_month = $start->locale('ar')->translatedFormat('F');
            }

            if ($festival->end) {
                $end = \Carbon\Carbon::parse($festival->end)->setTimezone('Asia/Riyadh')->startOfDay();
                $festival->end_day   = $end->locale('ar')->dayName;
                $festival->end_month = $end->locale('ar')->translatedFormat('F');
            }

            // حساب human
            if (isset($start) && $today->lt($start)) {
                $festival->human = $today->diffForHumans($start, ['locale' => 'ar']);
            } elseif (isset($start, $end) && $today->between($start, $end)) {
                $festival->human = 'يجري الآن';
            } elseif (isset($end)) {
                $festival->human = $end->diffForHumans($today, ['locale' => 'ar']);
            }

            return $festival;
        });
    };

    if ($type === 'current') {
        $festivals = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'current'));

    } elseif ($type === 'upcoming') {
        $festivals = Festival::whereDate('start', '>', $today)->where('status', 'active')
            ->orderBy('start', 'asc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'upcoming'));

    } elseif ($type === 'ended') {
        $festivals = Festival::whereDate('end', '<', $today)->where('status', 'active')
            ->orderBy('start', 'desc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'ended'));

    } else {
        // all → return current, then upcoming, then ended
        $current  = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $upcoming = Festival::whereDate('start', '>', $today)
            ->orderBy('start', 'asc')
            ->where('status', 'active')
            ->get();

        $ended    = Festival::whereDate('end', '<', $today)
            ->orderBy('start', 'desc')
            ->where('status', 'active')
            ->get();

        $festivals = collect()
            ->merge($addType($current, 'current'))
            ->merge($addType($upcoming, 'upcoming'))
            ->merge($addType($ended, 'ended'));

        if (!is_null($limit) && $limit > 0) {
            $festivals = $festivals->take($limit);
        }

        return response()->json($festivals->values());
    }
}
    */


/*
public function getFestivalsApi(Request $request)
{
    $type  = $request->input('type', 'all'); // current, upcoming, ended, active, all
    $limit = $request->input('limit'); // 0 = all

    $today = \Carbon\Carbon::now('Asia/Riyadh')->startOfDay();

    // helper function لإضافة نوع المهرجان وحساب human
    $addType = function ($festivals, $status) use ($today) {
        return $festivals->map(function ($festival) use ($status, $today) {
            $festival->type = $status;

            if ($festival->start) {
                $start = \Carbon\Carbon::parse($festival->start)->setTimezone('Asia/Riyadh')->startOfDay();
                $festival->start_day   = $start->locale('ar')->dayName;
                $festival->start_month = $start->locale('ar')->translatedFormat('F');
            }

            if ($festival->end) {
                $end = \Carbon\Carbon::parse($festival->end)->setTimezone('Asia/Riyadh')->startOfDay();
                $festival->end_day   = $end->locale('ar')->dayName;
                $festival->end_month = $end->locale('ar')->translatedFormat('F');
            }

            // حساب human
            if (isset($start) && $today->lt($start)) {
                $festival->human = $today->diffForHumans($start, ['locale' => 'ar']);
            } elseif (isset($start, $end) && $today->between($start, $end)) {
                $festival->human = 'يجري الآن';
            } elseif (isset($end)) {
                $festival->human = $end->diffForHumans($today, ['locale' => 'ar']);
            }

            return $festival;
        });
    };

    if ($type === 'current') {

        $festivals = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'current'));

    } elseif ($type === 'upcoming') {

        $festivals = Festival::whereDate('start', '>', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'upcoming'));

    } elseif ($type === 'active') {

        // الحالية
        $current = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        // القادمة
        $upcoming = Festival::whereDate('start', '>', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $festivals = collect()
            ->merge($addType($current, 'current'))
            ->merge($addType($upcoming, 'upcoming'));

        if (!is_null($limit) && $limit > 0) {
            $festivals = $festivals->take($limit);
        }

        return response()->json($festivals->values());

    } elseif ($type === 'ended') {

        $festivals = Festival::whereDate('end', '<', $today)
            ->where('status', 'active')
            ->orderBy('start', 'desc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'ended'));

    } else {

        // all → return current, then upcoming, then ended
        $current  = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $upcoming = Festival::whereDate('start', '>', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $ended    = Festival::whereDate('end', '<', $today)
            ->where('status', 'active')
            ->orderBy('start', 'desc')
            ->get();

        $festivals = collect()
            ->merge($addType($current, 'current'))
            ->merge($addType($upcoming, 'upcoming'))
            ->merge($addType($ended, 'ended'));

        if (!is_null($limit) && $limit > 0) {
            $festivals = $festivals->take($limit);
        }

        return response()->json($festivals->values());
    }
}
    */



public function getFestivalsApi(Request $request)
{
    $type  = $request->input('type', 'all'); // current, upcoming, ended, active, all
    $limit = $request->input('limit'); // 0 = all

    $today = \Carbon\Carbon::now('Asia/Riyadh')->startOfDay();

    // helper function لإضافة نوع المهرجان وحساب human
    $addType = function ($festivals, $status) use ($today) {
        return $festivals->map(function ($festival) use ($status, $today) {

            $festival->type = $status;
            $festival->is_selected = false;

            if ($festival->start) {
                $start = \Carbon\Carbon::parse($festival->start)->setTimezone('Asia/Riyadh')->startOfDay();
                $festival->start_day   = $start->locale('ar')->dayName;
                $festival->start_month = $start->locale('ar')->translatedFormat('F');
            }

            if ($festival->end) {
                $end = \Carbon\Carbon::parse($festival->end)->setTimezone('Asia/Riyadh')->startOfDay();
                $festival->end_day   = $end->locale('ar')->dayName;
                $festival->end_month = $end->locale('ar')->translatedFormat('F');
            }

            // حساب human
            if (isset($start) && $today->lt($start)) {
                $festival->human = $today->diffForHumans($start, ['locale' => 'ar']);
            } elseif (isset($start, $end) && $today->between($start, $end)) {
                $festival->human = 'يجري الآن';
            } elseif (isset($end)) {
                $festival->human = $end->diffForHumans($today, ['locale' => 'ar']);
            }

            return $festival;
        });
    };

    if ($type === 'current') {

        $festivals = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'current'));

    } elseif ($type === 'upcoming') {

        $festivals = Festival::whereDate('start', '>', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'upcoming'));

    } elseif ($type === 'active') {

        // الحالية
        $current = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        // القادمة
        $upcoming = Festival::whereDate('start', '>', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $festivals = collect()
            ->merge($addType($current, 'current'))
            ->merge($addType($upcoming, 'upcoming'));

        if (!is_null($limit) && $limit > 0) {
            $festivals = $festivals->take($limit);
        }

        return response()->json($festivals->values());

    } elseif ($type === 'ended') {

        $festivals = Festival::whereDate('end', '<', $today)
            ->where('status', 'active')
            ->orderBy('start', 'desc');

        if (!is_null($limit) && $limit > 0) {
            $festivals->take($limit);
        }

        return response()->json($addType($festivals->get(), 'ended'));

    } else {

        // all → return current, then upcoming, then ended
        $current  = Festival::whereDate('start', '<=', $today)
            ->whereDate('end', '>=', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $upcoming = Festival::whereDate('start', '>', $today)
            ->where('status', 'active')
            ->orderBy('start', 'asc')
            ->get();

        $ended    = Festival::whereDate('end', '<', $today)
            ->where('status', 'active')
            ->orderBy('start', 'desc')
            ->get();

        $festivals = collect()
            ->merge($addType($current, 'current'))
            ->merge($addType($upcoming, 'upcoming'))
            ->merge($addType($ended, 'ended'));

        if (!is_null($limit) && $limit > 0) {
            $festivals = $festivals->take($limit);
        }

        return response()->json($festivals->values());
    }
}


public function addUserDoublePointUsagesApi(Request $request)
{
    // التحقق من المدخلات
    $request->validate([
        'user_id'     => 'required|exists:users,id',
        'festival_id' => 'required|exists:festivals,id',
        'round_id'    => 'required|exists:rounds,id',
    ]);

    $userId     = $request->input('user_id');
    $festivalId = $request->input('festival_id');
    $roundId    = $request->input('round_id');

    // التحقق إذا المستخدم استخدم الدبل مسبقًا في نفس الشوط
    $alreadyUsed = UserDoublePointUsage::where('user_id', $userId)
        ->where('festival_id', $festivalId)
        ->where('round_id', $roundId)
        ->exists();

    if ($alreadyUsed) {
        return response()->json([
            'status' => false,
            'message' => 'لقد استخدمت دبل النقاط في هذا الشوط مسبقًا.'
        ], 200);
    }

    // إضافة السجل
    $doubleUsage = UserDoublePointUsage::create([
        'user_id'     => $userId,
        'festival_id' => $festivalId,
        'round_id'    => $roundId,
    ]);

    // جلب معلومات المستخدم والمهرجان والشوط
    $user     = \App\Models\User::find($userId);
    $festival = \App\Models\Festival::find($festivalId);
    $round    = \App\Models\Round::find($roundId);

    return response()->json([
        'status' => true,
        'message' => 'تم تسجيل استخدام دبل النقاط بنجاح.',
        'data' => [
            'double_usage' => $doubleUsage,
            'user'         => $user,
            'festival'     => $festival,
            'round'        => $round,
        ]
    ], 201);
}

public function getUserDoublePointsRemainingApi(Request $request)
{
    // التحقق من المدخلات
    $request->validate([
        'user_id'     => 'required|exists:users,id',
        'festival_id' => 'required|exists:festivals,id',
    ]);

    $userId     = $request->input('user_id');
    $festivalId = $request->input('festival_id');

    // جلب معلومات المستخدم والمهرجان
    $user     = \App\Models\User::find($userId);
    $festival = \App\Models\Festival::find($festivalId);

    if (!$festival) {
        return response()->json([
            'status' => false,
            'message' => 'المهرجان غير موجود.'
        ], 404);
    }

    // إذا count_double_used = null → الوسيلة غير مفعلة
    if (is_null($festival->count_double_used)) {
        return response()->json([
            'status' => false,
            'message' => 'وسيلة الدبل غير متاحة لهذا المهرجان.'
        ], 200);
    }

    // العدد الإجمالي المسموح من الدبل في المهرجان
    $totalAllowed = $festival->count_double_used;

    // عدد مرات استخدام المستخدم
    $usedCount = \App\Models\UserDoublePointUsage::where('user_id', $userId)
        ->where('festival_id', $festivalId)
        ->count();

    // عدد المرات المتبقية
    $remaining = max(0, $totalAllowed - $usedCount);

    return response()->json([
        'status' => true,
        'message' => 'تم جلب بيانات استخدام الدبل بنجاح.',
        'data' => [
            'user'        => $user,
            'festival'    => $festival,
            'used_count'  => $usedCount,
            'remaining'   => $remaining,
            'total_allowed' => $totalAllowed
        ]
    ], 200);
}


}
