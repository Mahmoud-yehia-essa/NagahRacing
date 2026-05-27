<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Camal;
use Illuminate\Http\Request;

class CamalController extends Controller
{






     public function getAllCamal()
    {
        // $users = User::latest()->get();
        // $users = User::where('role', '!=', 'admin')->latest()->get();
        $camals = Camal::with('user')->latest()->get();


        return view('admin.camal.all_camal',compact('camals'));


    }


    public function addCamal()
    {
        // return view('admin.cam')

        // return view()

                $owners = User::where('role', 'owner')->latest()->get();


        return view('admin.camal.add_camal',compact('owners'));
    }

//    public function addCamalStore(Request $request)
// {
//     // Validate request
//     $request->validate([
//         'owner_id' => 'required|not_in:non', // owner must be selected
//         'names'    => 'required|array|min:1', // at least one name must be provided
//         'names.*'  => 'required|string|max:255', // each name is required and max 255 chars
//     ], [
//         'owner_id.required' => 'يرجى اختيار المالك.',
//         'owner_id.not_in'   => 'يرجى اختيار المالك.',
//         'names.required'    => 'يجب إدخال اسم المطية واحد على الأقل.',
//         'names.array'       => 'حدث خطأ في البيانات.',
//         'names.*.required'  => 'اسم المطية لا يمكن أن يكون فارغًا.',
//         'names.*.max'       => 'اسم المطية طويل جدًا.',
//     ]);

//     // Loop through each name and insert
//     foreach ($request->names as $name) {
//         Camal::insert([
//             'owner_id' => $request->owner_id,
//             'name'     => $name,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);
//     }



//       $notification = array(
//             'message' => 'تمت الاضافة بنجاح',
//             'alert-type' => 'success'
//         );
//     // return redirect()->back()->with('success', 'تم إضافة المطايا بنجاح.');

//     return redirect()->route('all.camal')->with($notification);

// }

public function addCamalStore(Request $request)
{
    // Validate request
    $request->validate([
        'owner_id'    => 'required|not_in:non', // owner must be selected
        'names'       => 'required|array|min:1', // at least one name must be provided
        'names.*'     => 'required|string|max:255', // each name is required and max 255 chars
        'age_name'    => 'required|array|min:1', // at least one age must be provided
        'age_name.*'  => 'required|string|not_in:non', // each age_name is required and cannot be 'non'
        'gender'      => 'required|array|min:1', // at least one gender must be provided
        'gender.*'    => 'required|string|not_in:non', // each gender is required and cannot be 'non'
    ], [
        'owner_id.required'   => 'يرجى اختيار المالك.',
        'owner_id.not_in'     => 'يرجى اختيار المالك.',

        'names.required'      => 'يجب إدخال اسم مطية واحد على الأقل.',
        'names.array'         => 'حدث خطأ في البيانات.',
        'names.*.required'    => 'اسم المطية لا يمكن أن يكون فارغًا.',
        'names.*.max'         => 'اسم المطية طويل جدًا.',

        'age_name.required'   => 'يجب اختيار الفئة العمرية لكل مطية.',
        'age_name.array'      => 'حدث خطأ في البيانات.',
        'age_name.*.required' => 'يجب اختيار الفئة العمرية لكل مطية.',
        'age_name.*.not_in'   => 'يرجى اختيار فئة عمرية صحيحة لكل مطية.',

        'gender.required'     => 'يجب اختيار النوع لكل مطية.',
        'gender.array'        => 'حدث خطأ في البيانات.',
        'gender.*.required'   => 'يجب اختيار النوع لكل مطية.',
        'gender.*.not_in'     => 'يرجى اختيار نوع صحيح لكل مطية.',
    ]);

    // Loop through each name and age_name and gender together and insert
    foreach ($request->names as $index => $name) {
        Camal::insert([
            'owner_id'   => $request->owner_id,
            'name'       => $name,
            'age_name'   => $request->age_name[$index] ?? null,
            'gender'     => $request->gender[$index] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $notification = [
        'message'    => 'تمت الإضافة بنجاح',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.camal')->with($notification);
}




   public function editCamal($id)
{
    $camal = Camal::findOrFail($id); // مطية واحدة
    $owners = User::where('role', 'owner')->latest()->get();

    return view('admin.camal.edit_camal', compact('owners', 'camal'));
}



//    public function editAllCamal($owner_id)
// {
//     $camals = Camal::findOrFail($id); //
//     $owner = User::where('id', $owner_id)->latest()->get(); // get one owner


//     return view('admin.camal.edit_all_camal', compact('camals','owner'));
// }


public function editAllCamal($owner_id)
{
    // جيب المالك
    $owner = User::findOrFail($owner_id);

    // جيب جميع المطايا الخاصة بهذا المالك
    $camals = Camal::where('owner_id', $owner_id)->get();

    return view('admin.camal.edit_all_camal', compact('camals','owner'));
}

// public function editAllCamalStore(Request $request)
// {
//     $request->validate([
//         'owner_id' => 'required|exists:users,id',
//         'names'    => 'required|array',
//         'names.*'  => 'nullable|string|max:255',
//     ]);

//     $owner_id = $request->owner_id;

//     // نحذف المطايا القديمة ونعيد إدخال الجديدة (ممكن تعدلها للتحديث بدال الحذف إذا تحب)
//     Camal::where('owner_id', $owner_id)->delete();

//     foreach ($request->names as $name) {
//         if (!empty($name)) {
//             Camal::create([
//                 'owner_id' => $owner_id,
//                 'name'     => $name,
//             ]);
//         }
//     }

//     return redirect()->route('edit.all.camal', $owner_id)->with('success', 'تم تحديث المطايا بنجاح');
// }


// public function editAllCamalStore(Request $request)
// {
//  $request->validate([
//     'owner_id' => 'required|exists:users,id',
//     'names'    => 'required|array',
//     'names.*'  => 'nullable|string|max:255',
// ], [
//     'owner_id.required' => 'المالك مطلوب.',
//     'owner_id.exists'   => 'المالك المحدد غير موجود.',

//     'names.required'    => 'يجب إدخال اسم واحد على الأقل.',
//     'names.array'       => 'يجب أن تكون الأسماء في شكل قائمة.',

//     'names.*.string'    => 'يجب أن يكون الاسم نصًا.',
//     'names.*.max'       => 'يجب ألا يزيد طول الاسم عن 255 حرفًا.',
// ]);


//     $owner_id = $request->owner_id;

//     foreach ($request->names as $camal_id => $name) {
//         if ($camal_id && is_numeric($camal_id)) {
//             // تحديث مطية موجودة
//             $camal = Camal::find($camal_id);
//             if ($camal) {
//                 $camal->update(['name' => $name]);
//             }
//         } else {
//             // إضافة مطية جديدة
//             if (!empty($name)) {
//                 Camal::create([
//                     'owner_id' => $owner_id,
//                     'name'     => $name,
//                 ]);
//             }
//         }
//     }


//      $notification = array(
//             'message' => 'تمت التعديل بنجاح',
//             'alert-type' => 'success'
//         );
//     // return redirect()->back()->with('success', 'تم إضافة المطايا بنجاح.');

//     return redirect()->route('all.camal')->with($notification);

//     // return redirect()->route('edit.all.camal', $owner_id)->with('success', 'تم تحديث المطايا بنجاح');
// }


// public function editAllCamalStore(Request $request)
// {
//     $request->validate([
//         'owner_id'             => 'required|exists:users,id',
//         'names'                => 'nullable|array',
//         'names.*'              => 'nullable|string|max:255',
//         'age_name'             => 'nullable|array',
//         'age_name.*'           => 'nullable|string|not_in:non',
//         'names.new'            => 'nullable|array',
//         'names.new.*'          => 'nullable|string|max:255',
//         'age_name.new'         => 'nullable|array',
//         'age_name.new.*'       => 'nullable|string|not_in:non',
//     ], [
//         'owner_id.required'         => 'المالك مطلوب.',
//         'owner_id.exists'           => 'المالك المحدد غير موجود.',
//         'names.*.string'            => 'اسم المطية يجب أن يكون نصًا صحيحًا.',
//         'names.*.max'               => 'اسم المطية يجب ألا يتجاوز 255 حرفًا.',
//         'age_name.*.not_in'         => 'يرجى اختيار فئة عمرية صحيحة لكل مطية.',
//         'names.new.*.string'        => 'اسم المطية الجديدة يجب أن يكون نصًا صحيحًا.',
//         'names.new.*.max'           => 'اسم المطية الجديدة يجب ألا يتجاوز 255 حرفًا.',
//         'age_name.new.*.not_in'     => 'يرجى اختيار فئة عمرية صحيحة لكل مطية جديدة.',
//     ]);

//     $owner_id = $request->owner_id;

//     // تحديث المطايا القديمة
//     if (isset($request->names) && is_array($request->names)) {
//         foreach ($request->names as $camal_id => $name) {
//             if ($camal_id !== "new") {
//                 $camal = Camal::find($camal_id);
//                 if ($camal) {
//                     $camal->update([
//                         'name'     => $name,
//                         'age_name' => $request->age_name[$camal_id] ?? 'non'
//                     ]);
//                 }
//             }
//         }
//     }

//     // إضافة مطايا جديدة
//     if (isset($request->names['new'])) {
//         foreach ($request->names['new'] as $index => $newName) {
//             if (!empty($newName)) {
//                 Camal::create([
//                     'owner_id' => $owner_id,
//                     'name'     => $newName,
//                     'age_name' => $request->age_name['new'][$index] ?? 'non',
//                 ]);
//             }
//         }
//     }

//     // الحذف
//     if ($request->has('deleted')) {
//         Camal::whereIn('id', $request->deleted)->delete();
//     }

//     $notification = [
//         'message'    => 'تم تحديث المطايا بنجاح',
//         'alert-type' => 'success'
//     ];

//     return redirect()->route('all.camal')->with($notification);
// }



public function editAllCamalStore(Request $request)
{
    $request->validate([
        'owner_id'             => 'required|exists:users,id',
        'names'                => 'nullable|array',
        'names.*'              => 'nullable|string|max:255',
        'age_name'             => 'nullable|array',
        'age_name.*'           => 'nullable|string|not_in:non',
        'gender'               => 'nullable|array',
        'gender.*'             => 'nullable|string|not_in:non',
        'names.new'            => 'nullable|array',
        'names.new.*'          => 'nullable|string|max:255',
        'age_name.new'         => 'nullable|array',
        'age_name.new.*'       => 'nullable|string|not_in:non',
        'gender.new'           => 'nullable|array',
        'gender.new.*'         => 'nullable|string|not_in:non',
    ], [
        'owner_id.required'         => 'المالك مطلوب.',
        'owner_id.exists'           => 'المالك المحدد غير موجود.',
        'names.*.string'            => 'اسم المطية يجب أن يكون نصًا صحيحًا.',
        'names.*.max'               => 'اسم المطية يجب ألا يتجاوز 255 حرفًا.',
        'age_name.*.not_in'         => 'يرجى اختيار فئة عمرية صحيحة لكل مطية.',
        'gender.*.not_in'           => 'يرجى اختيار نوع صحيح لكل مطية.',
        'names.new.*.string'        => 'اسم المطية الجديدة يجب أن يكون نصًا صحيحًا.',
        'names.new.*.max'           => 'اسم المطية الجديدة يجب ألا يتجاوز 255 حرفًا.',
        'age_name.new.*.not_in'     => 'يرجى اختيار فئة عمرية صحيحة لكل مطية جديدة.',
        'gender.new.*.not_in'       => 'يرجى اختيار نوع صحيح لكل مطية جديدة.',
    ]);

    $owner_id = $request->owner_id;

    // تحديث المطايا القديمة
    if (isset($request->names) && is_array($request->names)) {
        foreach ($request->names as $camal_id => $name) {
            if ($camal_id !== "new") {
                $camal = Camal::find($camal_id);
                if ($camal) {
                    $camal->update([
                        'name'     => $name,
                        'age_name' => $request->age_name[$camal_id] ?? 'non',
                        'gender'   => $request->gender[$camal_id] ?? 'non',
                    ]);
                }
            }
        }
    }

    // إضافة مطايا جديدة
    if (isset($request->names['new'])) {
        foreach ($request->names['new'] as $index => $newName) {
            if (!empty($newName)) {
                Camal::create([
                    'owner_id' => $owner_id,
                    'name'     => $newName,
                    'age_name' => $request->age_name['new'][$index] ?? 'non',
                    'gender'   => $request->gender['new'][$index] ?? 'non',
                ]);
            }
        }
    }

    // الحذف
    if ($request->has('deleted')) {
        Camal::whereIn('id', $request->deleted)->delete();
    }

    $notification = [
        'message'    => 'تم تحديث المطايا بنجاح',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.camal')->with($notification);
}



public function editCamalStore(Request $request)
{
    // validate
    $request->validate([
        'id'       => 'required|exists:camals,id',
        'owner_id' => 'required|not_in:non',
        'name'     => 'required|string|max:255',
    ], [
        'owner_id.required' => 'يرجى اختيار المالك.',
        'owner_id.not_in'   => 'يرجى اختيار المالك.',
        'name.required'     => 'يرجى إدخال اسم المطية.',
    ]);

    // get camal
    $camal = Camal::findOrFail($request->id);

    // update owner + name
    $camal->owner_id = $request->owner_id;
    $camal->name     = $request->name;


    $camal->save();


     $notification = array(
            'message' => 'تم التعديل ',
            'alert-type' => 'success'
        );
    return redirect()->route('all.camal')->with( $notification);
}




    public function deleteCamal($id){
        $camal = Camal::findOrFail($id);


        Camal::findOrFail($id)->delete();
        $notification = array(
            'message' => 'تم الحذف ',
            'alert-type' => 'success'
        );
        return redirect()->route('all.camal')->with($notification);

        // return redirect()->back()->with($notification);
    }// End Method





}
