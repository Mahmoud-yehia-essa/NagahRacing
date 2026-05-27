<?php

namespace App\Http\Controllers;

use App\Models\Camal;
use App\Models\Round;
use App\Models\Festival;
use Illuminate\Http\Request;
use App\Models\CamelRoundParticipation;

class RoundController extends Controller
{
    /**
     * عرض جميع الجولات
     */
    public function allRound()
    {

                // $allFestival = Festival::latest()->get();


        $values = Round::with('festival')->latest()->get();
        return view('admin.round.all_round', compact('values'));
    }



    /**
     * عرض صفحة اضافة جولة جديدة
     */
    public function addRound()
    {


        $festivals = Festival::where('status', 'active')->get();
        return view('admin.round.add_round', compact('festivals'));
    }



    /**
     * حفظ الجولة الجديدة
     */
    // public function storeRound(Request $request)
    // {





    //     $request->validate([
    //         'festival_id'   => 'required|exists:festivals,id',
    //         'name'          => 'required|string|max:255',
    //         'name_en'       => 'required|string|max:255',
    //         'des'           => 'nullable|string',
    //         'des_en'        => 'nullable|string',
    //         'round_number'  => 'required|integer|min:1',
    //         'start'         => 'required|string',
    //         'end'           => 'required|string',
    //         'round_type'    => 'required|in:بكار,قعدان',
    //     ], [
    //         'festival_id.required' => 'اختيار المهرجان مطلوب',
    //         'festival_id.exists'   => 'المهرجان غير موجود',
    //         'name.required'        => 'اسم الجولة بالعربية مطلوب',
    //         'name_en.required'     => 'اسم الجولة بالانجليزية مطلوب',
    //         'round_number.required'=> 'رقم الجولة مطلوب',
    //         'round_number.integer' => 'رقم الجولة يجب أن يكون رقم صحيح',
    //         'start.required'       => 'بداية الجولة مطلوبة',
    //         'end.required'         => 'نهاية الجولة مطلوبة',
    //         'round_type.required'  => 'نوع الجولة مطلوب',
    //         'round_type.in'        => 'نوع الجولة يجب أن يكون بكار أو قعدان',
    //     ]);

    //     Round::create($request->all());

    //     return redirect()->route('all.round')->with('success', 'تم إضافة الجولة بنجاح');
    // }



//     public function storeRound(Request $request)
// {

//     // تحقق من المدخلات الأساسية
//     $request->validate([
//         'festival_id'   => 'required|exists:festivals,id',
//         'name'          => 'required|string|max:255',
//         'name_en'       => 'nullable|string|max:255',
//         'des'           => 'nullable|string',
//         'des_en'        => 'nullable|string',
//         'round_number'  => 'required|integer',
//         'start'         => 'required|date',
//         'end'           => 'required|date|after_or_equal:start',
//         'round_type'    => 'required|in:بكار,قعدان',
//     ]);

//     // إنشاء الجولة
//     $round = Round::create([
//         'festival_id'  => $request->festival_id,
//         'name'         => $request->name,
//         'name_en'      => $request->name_en,
//         'des'          => $request->des,
//         'des_en'       => $request->des_en,
//         'round_number' => $request->round_number,
//         'start'        => $request->start,
//         'end'          => $request->end,
//         'round_type'   => $request->round_type,
//     ]);

//     // التحقق من المطايا المختارة
//     if ($request->has('camals')) {
//         foreach ($request->camals as $camalId => $data) {
//             if (isset($data['selected'])) {
//                 $number = $data['number'] ?? "";

//                 // 👇 هنا اطبع النتائج (للتجربة)
//                 \Log::info("المطية ID: {$camalId}, رقم التسجيل: {$number}");

//                 // أو تطبع على الشاشة مباشرة
//                 echo "المطية ID: {$camalId} - رقم التسجيل: {$number} <br>";




//                 // مستقبلاً: خزّن في جدول وسيط round_camals
//                 CamelRoundParticipation::insert([
//                     'festival_id'=>$request->festival_id,
//                     'round_id' => $round->id,
//                     'camal_id' => $camalId,
//                     'registration_number'   => $number,
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ]);
//             }
//         }
//     }

//     // للتجربة: وقف التنفيذ بعد الطباعة
//     // dd('تمت إضافة الجولة والمطايا المختارة');


//        $notification = array(
//                     'message' => 'تمت الاضافة بنجاح',
//                     'alert-type' => 'success'
//                 );


//                 // return back()->with($notification);

//         return redirect()->route('all.round')->with($notification);

// }




// good work
// public function storeRound(Request $request)
// {
//     // ✅ التحقق من الحقول الأساسية للجولة
//     $request->validate([
//         'festival_id'   => 'required|exists:festivals,id',
//         'name'          => 'required|string|max:255',
//         'name_en'       => 'nullable|string|max:255',
//         'des'           => 'nullable|string',
//         'des_en'        => 'nullable|string',
//         'round_number'  => 'required|integer',
//         'start'         => 'required|date',
//         'end'           => 'required|date|after_or_equal:start',
//         'round_type'    => 'required|in:بكار,قعدان',
//     ]);

//     // ✅ إنشاء الجولة
//     $round = Round::create([
//         'festival_id'  => $request->festival_id,
//         'name'         => $request->name,
//         'name_en'      => $request->name_en,
//         'des'          => $request->des,
//         'des_en'       => $request->des_en,
//         'round_number' => $request->round_number,
//         'start'        => $request->start,
//         'end'          => $request->end,
//         'round_type'   => $request->round_type,
//     ]);

//     // ✅ حفظ المطايا المضافة يدويًا
//     if ($request->has('camals') && is_array($request->camals)) {
//         foreach ($request->camals as $index => $camal) {
//             // تجاهل الصفوف الفارغة
//             if (
//                 empty($camal['name']) &&
//                 empty($camal['owner_name']) &&
//                 empty($camal['country'])
//             ) {
//                 continue;
//             }

//             // ✅ إدخال البيانات بأسماء الأعمدة الصحيحة
//             CamelRoundParticipation::create([
//                 'festival_id'          => $request->festival_id,
//                 'round_id'             => $round->id,
//                 'registration_number'  => $camal['registration_number'] ?? $index + 1,
//                 'camel_name'           => $camal['name'] ?? '',
//                 'camel_age_name'       => $camal['age_name'] ?? '',
//                 'camel_owner_name'     => $camal['owner_name'] ?? '',
//                 'camel_owner_country'  => $camal['country'] ?? '',
//                 'created_at'           => now(),
//                 'updated_at'           => now(),
//             ]);
//         }
//     }

//     // ✅ إشعار النجاح
//     $notification = [
//         'message' => 'تمت إضافة الشوط والمطايا بنجاح',
//         'alert-type' => 'success'
//     ];

//     return redirect()->route('all.round')->with($notification);
// }


public function storeRound(Request $request)
{
    // ✅ التحقق من الحقول الأساسية للجولة
    $request->validate([
        'festival_id'   => 'required|exists:festivals,id',
        'name'          => 'required|string|max:255',
        'name_en'       => 'nullable|string|max:255',
        'des'           => 'nullable|string',
        'des_en'        => 'nullable|string',
        'round_number'  => 'required|integer',
        'start'         => 'required|date',
        'end'           => 'required|date|after_or_equal:start',
        'round_type'    => 'required|in:بكار,قعدان',
    ]);

    // ✅ تحقق من المطايا داخل الجدول
    if (!$request->has('camals') || !is_array($request->camals) || count($request->camals) === 0) {
        return back()->withErrors(['camals' => 'يجب إضافة مطية واحدة على الأقل.'])->withInput();
    }

    foreach ($request->camals as $index => $camal) {
        $row = $index + 1;

        // تحقق من كل عمود داخل الجدول
        if (
            empty($camal['name']) ||
            empty($camal['age_name']) ||
            empty($camal['owner_name']) ||
            empty($camal['country'])
        ) {
            return back()->withErrors([
                'camals' => "يجب إدخال جميع البيانات في الصف رقم {$row}."
            ])->withInput();
        }
    }

    // ✅ إنشاء الجولة
    $round = Round::create([
        'festival_id'  => $request->festival_id,
        'name'         => $request->name,
        'name_en'      => $request->name_en,
        'des'          => $request->des,
        'des_en'       => $request->des_en,
        'round_number' => $request->round_number,
        'start'        => $request->start,
        'end'          => $request->end,
        'round_type'   => $request->round_type,
        'open_nomination' => 'close',
    ]);

    // ✅ حفظ المطايا
    foreach ($request->camals as $index => $camal) {
        CamelRoundParticipation::create([
            'festival_id'          => $request->festival_id,
            'round_id'             => $round->id,
            'registration_number'  => $camal['registration_number'] ?? $index + 1,
            'camel_name'           => $camal['name'],
            'camel_age_name'       => $camal['age_name'],
            'camel_owner_name'     => $camal['owner_name'],
            'camel_owner_country'  => $camal['country'],
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    // ✅ إشعار النجاح
    $notification = [
        'message' => 'تمت إضافة الشوط والمطايا بنجاح',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.round')->with($notification);
}



    /**
     * عرض صفحة تعديل الجولة
     */
    // public function editRound($id)
    // {
    //     $round = Round::findOrFail($id);
    //     $festivals = Festival::where('status', 'active')->get();
    //     return view('admin.round.edit_round', compact('round', 'festivals'));
    // }


//     public function editRound($id)
// {




//     $round = Round::with(['camals'])->findOrFail($id);
//     $festivals = Festival::all();

//     return view('admin.round.edit_round', compact('round', 'festivals'));
// }


// ✅ عرض صفحة تعديل الشوط
public function editRound($id)
{
    $round = Round::findOrFail($id);
    $festivals = Festival::where('status', 'active')->get();

    // جلب المطايا التابعة لهذا الشوط
    $camals = CamelRoundParticipation::where('round_id', $id)->get();

    return view('admin.round.edit_round', compact('round', 'festivals', 'camals'));
}



    /**
     * تحديث بيانات الجولة
     */




// // ✅ تحديث بيانات الشوط والمطايا
// public function updateRound(Request $request)
// {


//     $request->validate([
//         'round_id'      => 'required|exists:rounds,id',
//         'festival_id'   => 'required|exists:festivals,id',
//         'name'          => 'required|string|max:255',
//         'name_en'       => 'nullable|string|max:255',
//         'des'           => 'nullable|string',
//         'des_en'        => 'nullable|string',
//         'round_number'  => 'required|integer',
//         'start'         => 'required|date',
//         'end'           => 'required|date|after_or_equal:start',
//         'round_type'    => 'required|in:بكار,قعدان',
//     ]);

//     if (!$request->has('camals') || !is_array($request->camals) || count($request->camals) === 0) {
//         return back()->withErrors(['camals' => 'يجب إضافة مطية واحدة على الأقل.'])->withInput();
//     }

//     foreach ($request->camals as $index => $camal) {
//         $row = $index + 1;
//         if (
//             empty($camal['name']) ||
//             empty($camal['age_name']) ||
//             empty($camal['owner_name']) ||
//             empty($camal['country'])
//         ) {
//             return back()->withErrors([
//                 'camals' => "يجب إدخال جميع البيانات في الصف رقم {$row}."
//             ])->withInput();
//         }
//     }

//     // ✅ تحديث بيانات الجولة
//     $round = Round::findOrFail($request->round_id);
//     $round->update([
//         'festival_id'  => $request->festival_id,
//         'name'         => $request->name,
//         'name_en'      => $request->name_en,
//         'des'          => $request->des,
//         'des_en'       => $request->des_en,
//         'round_number' => $request->round_number,
//         'start'        => $request->start,
//         'end'          => $request->end,
//         'round_type'   => $request->round_type,
//     ]);

//     // ✅ حذف المطايا القديمة وإعادة إدخال الجديدة
//     CamelRoundParticipation::where('round_id', $round->id)->delete();

//     foreach ($request->camals as $index => $camal) {
//         CamelRoundParticipation::create([
//             'festival_id'          => $request->festival_id,
//             'round_id'             => $round->id,
//             'registration_number'  => $camal['registration_number'] ?? $index + 1,
//             'camel_name'           => $camal['name'],
//             'camel_age_name'       => $camal['age_name'],
//             'camel_owner_name'     => $camal['owner_name'],
//             'camel_owner_country'  => $camal['country'],
//             'created_at'           => now(),
//             'updated_at'           => now(),
//         ]);
//     }

//     $notification = [
//         'message' => 'تم تعديل الشوط والمطايا بنجاح',
//         'alert-type' => 'success'
//     ];

//     return redirect()->route('all.round')->with($notification);
// }


// public function updateRound(Request $request)
// {
//     $request->validate([
//         'round_id'      => 'required|exists:rounds,id',
//         'festival_id'   => 'required|exists:festivals,id',
//         'name'          => 'required|string|max:255',
//         'name_en'       => 'nullable|string|max:255',
//         'des'           => 'nullable|string',
//         'des_en'        => 'nullable|string',
//         'round_number'  => 'required|integer',
//         'start'         => 'required|date',
//         'end'           => 'required|date|after_or_equal:start',
//         'round_type'    => 'required|in:بكار,قعدان',
//     ]);

//     if (!$request->has('camals') || !is_array($request->camals) || count($request->camals) === 0) {
//         return back()->withErrors(['camals' => 'يجب إضافة مطية واحدة على الأقل.'])->withInput();
//     }

//     foreach ($request->camals as $index => $camal) {
//         $row = $index + 1;
//         if (
//             empty($camal['name']) ||
//             empty($camal['age_name']) ||
//             empty($camal['owner_name']) ||
//             empty($camal['country'])
//         ) {
//             return back()->withErrors([
//                 'camals' => "يجب إدخال جميع البيانات في الصف رقم {$row}."
//             ])->withInput();
//         }
//     }

//     // ===========================
//     //  ❗ تحديث بيانات الجولة
//     // ===========================
//     $round = Round::findOrFail($request->round_id);
//     $round->update([
//         'festival_id'  => $request->festival_id,
//         'name'         => $request->name,
//         'name_en'      => $request->name_en,
//         'des'          => $request->des,
//         'des_en'       => $request->des_en,
//         'round_number' => $request->round_number,
//         'start'        => $request->start,
//         'end'          => $request->end,
//         'round_type'   => $request->round_type,
//     ]);

//     // ===========================
//     //  ❗ جلب ids القديمة
//     // ===========================
//     $oldIds = CamelRoundParticipation::where('round_id', $round->id)->pluck('id')->toArray();

//     // سنتتبع ids القادمة من الفورم
//     $newIds = [];

//     // ===========================
//     //  ❗ تحديث الموجود + إضافة الجديد
//     // ===========================
//     foreach ($request->camals as $index => $camal) {

//         if (!empty($camal['id'])) {
//             // تحديث
//             $newIds[] = $camal['id'];

//             CamelRoundParticipation::where('id', $camal['id'])
//                 ->update([
//                     'festival_id'          => $request->festival_id,
//                     'registration_number'  => $camal['registration_number'] ?? $index + 1,
//                     'camel_name'           => $camal['name'],
//                     'camel_age_name'       => $camal['age_name'],
//                     'camel_owner_name'     => $camal['owner_name'],
//                     'camel_owner_country'  => $camal['country'],
//                     'updated_at'           => now(),
//                 ]);

//         } else {
//             // إضافة جديد
//             $newCamel = CamelRoundParticipation::create([
//                 'festival_id'          => $request->festival_id,
//                 'round_id'             => $round->id,
//                 'registration_number'  => $camal['registration_number'] ?? $index + 1,
//                 'camel_name'           => $camal['name'],
//                 'camel_age_name'       => $camal['age_name'],
//                 'camel_owner_name'     => $camal['owner_name'],
//                 'camel_owner_country'  => $camal['country'],
//                 'created_at'           => now(),
//                 'updated_at'           => now(),
//             ]);

//             $newIds[] = $newCamel->id;
//         }
//     }

//     // ===========================
//     //   🟥 حذف المطايا التي لم تعد موجودة
//     // ===========================
//     $idsToDelete = array_diff($oldIds, $newIds);
//     if (!empty($idsToDelete)) {
//         CamelRoundParticipation::whereIn('id', $idsToDelete)->delete();
//     }

//     $notification = [
//         'message' => 'تم تعديل الشوط والمطايا بنجاح',
//         'alert-type' => 'success'
//     ];

//     return redirect()->route('all.round')->with($notification);
// }




public function updateRound(Request $request)
{
    $request->validate([
        'round_id'      => 'required|exists:rounds,id',
        'festival_id'   => 'required|exists:festivals,id',
        'name'          => 'required|string|max:255',
        'name_en'       => 'nullable|string|max:255',
        'des'           => 'nullable|string',
        'des_en'        => 'nullable|string',
        'round_number'  => 'required|integer',
        'start'         => 'required|date',
        'end'           => 'required|date|after_or_equal:start',
        'round_type'    => 'required|in:بكار,قعدان',
        // 'camals' validation is checked below
    ]);

    if (!$request->has('camals') || !is_array($request->camals) || count($request->camals) === 0) {
        return back()->withErrors(['camals' => 'يجب إضافة مطية واحدة على الأقل.'])->withInput();
    }

    foreach ($request->camals as $index => $camal) {
        $row = $index + 1;
        if (
            empty($camal['name']) ||
            empty($camal['age_name']) ||
            empty($camal['owner_name']) ||
            empty($camal['country'])
        ) {
            return back()->withErrors([
                'camals' => "يجب إدخال جميع البيانات في الصف رقم {$row}."
            ])->withInput();
        }
    }

    \DB::beginTransaction();
    try {
        // تحديث بيانات الجولة
        $round = \App\Models\Round::findOrFail($request->round_id);
        $round->update([
            'festival_id'  => $request->festival_id,
            'name'         => $request->name,
            'name_en'      => $request->name_en,
            'des'          => $request->des,
            'des_en'       => $request->des_en,
            'round_number' => $request->round_number,
            'start'        => $request->start,
            'end'          => $request->end,
            'round_type'   => $request->round_type,
        ]);

        // ===========================
        // معالجة حذف محدد (إذا أرسل المستخدم ids للحذف)
        // ===========================
        if ($request->has('delete_ids') && is_array($request->delete_ids) && count($request->delete_ids) > 0) {
            \App\Models\CamelRoundParticipation::whereIn('id', $request->delete_ids)
                ->where('round_id', $round->id)
                ->delete();
        }

        // سنتتبّع الـ ids التي تم التعامل معها (محدثة أو مضافة)
        $handledIds = [];

        foreach ($request->camals as $index => $camal) {
            $registrationNumber = $camal['registration_number'] ?? ($index + 1);

            // حالة: المستخدم وضع حقل delete على صف معين
            $wantsDelete = !empty($camal['delete']) && ($camal['delete'] == 1 || $camal['delete'] === '1' || $camal['delete'] === true);

            if (!empty($camal['id'])) {
                // تم إرسال id -> إما تحديث أو حذف
                $participation = \App\Models\CamelRoundParticipation::where('id', $camal['id'])
                    ->where('round_id', $round->id)
                    ->first();

                if ($participation) {
                    if ($wantsDelete) {
                        $participation->delete();
                        // لا نضيف الى handledIds لأن الحذف تم
                        continue;
                    } else {
                        $participation->update([
                            'festival_id'          => $request->festival_id,
                            'registration_number'  => $registrationNumber,
                            'camel_name'           => $camal['name'],
                            'camel_age_name'       => $camal['age_name'],
                            'camel_owner_name'     => $camal['owner_name'],
                            'camel_owner_country'  => $camal['country'],
                            'updated_at'           => now(),
                        ]);
                        $handledIds[] = $participation->id;
                        continue;
                    }
                }
                // إن لم نجد الـ id (مثلاً تم حذفه سابقاً) نستمر ونقوم بإنشاء/تحديث بناءً على التطابق
            }

            // إذا لم يُرسل id أو لم يُوجد، نتحقق إن كان يوجد سجل مطابق لتجنّب التكرار
            $existing = \App\Models\CamelRoundParticipation::where('round_id', $round->id)
                ->where(function($q) use ($registrationNumber, $camal) {
                    // إما مطابق برقم التسجيل أو مطابق بالاسم + المالك + الفئة
                    $q->where('registration_number', $registrationNumber)
                      ->orWhere(function($q2) use ($camal) {
                          $q2->where('camel_name', $camal['name'])
                             ->where('camel_owner_name', $camal['owner_name'])
                             ->where('camel_age_name', $camal['age_name']);
                      });
                })
                ->first();

            if ($existing) {
                if ($wantsDelete) {
                    $existing->delete();
                    continue;
                } else {
                    // حدث الموجود ليطابق البيانات الجديدة
                    $existing->update([
                        'festival_id'          => $request->festival_id,
                        'registration_number'  => $registrationNumber,
                        'camel_name'           => $camal['name'],
                        'camel_age_name'       => $camal['age_name'],
                        'camel_owner_name'     => $camal['owner_name'],
                        'camel_owner_country'  => $camal['country'],
                        'updated_at'           => now(),
                    ]);
                    $handledIds[] = $existing->id;
                }
            } else {
                // لم يوجد -> إنشاء سجل جديد
                $new = \App\Models\CamelRoundParticipation::create([
                    'festival_id'          => $request->festival_id,
                    'round_id'             => $round->id,
                    'registration_number'  => $registrationNumber,
                    'camel_name'           => $camal['name'],
                    'camel_age_name'       => $camal['age_name'],
                    'camel_owner_name'     => $camal['owner_name'],
                    'camel_owner_country'  => $camal['country'],
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
                $handledIds[] = $new->id;
            }
        }

        // **مهم**: لا نحذف أي سجلات أخرى تلقائياً — حذف فقط عند إرسال delete flag أو delete_ids

        \DB::commit();

        $notification = [
            'message' => 'تم تعديل الشوط والمطايا بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.round')->with($notification);

    } catch (\Throwable $e) {
        \DB::rollBack();
        // سجّل الخطأ لسهولة التعقب
        \Log::error('updateRound error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return back()->withErrors(['error' => 'حدث خطأ أثناء تعديل الشوط.'])->withInput();
    }
}



    /**
     * حذف الجولة
     */
    public function deleteRound($id)
    {
        $round = Round::findOrFail($id);
        $round->delete();

        return redirect()->route('all.round')->with('success', 'تم حذف الجولة بنجاح');
    }




//     public function getCamals($gender)
// {
//     // $camals = Camal::where('gender', $gender)->get();

//       $camals = Camal::with('user') // <-- هنا نستدعي العلاقة
//         ->where('gender', $gender)
//         ->get();

//     return response()->json($camals);
// }


// public function getCamals(Request $request)
// {
//     $gender = $request->gender;
//     $age = $request->age;

//     $camals = Camal::with('user')
//         ->when($gender, fn($q) => $q->where('gender', $gender))
//         ->when($age, fn($q) => $q->where('age_name', $age))
//         ->get();

//     return response()->json($camals);
// }


// public function getCamals(Request $request)
// {
//     $gender = $request->gender;
//     $age = $request->age;

//     $camals = Camal::with('user')
//         ->when($gender, fn($q) => $q->where('gender', $gender))
//         ->when($age && $age !== 'all', fn($q) => $q->where('age_name', $age))
//         ->get();

//     return response()->json($camals);
// }

// public function getCamals($gender, Request $request)
// {
//     $age = $request->age;

//     $query = Camal::with('user')->where('round_type', $gender);

//     if ($age && $age != 'all') {
//         $query->where('age_name', $age);
//     }

//     $camals = $query->get();

//     return response()->json($camals);
// }

public function getCamals($gender, Request $request)
{
    $age = $request->age; // age من AJAX

    $query = Camal::with('user')->where('gender', $gender);

    // إذا age ليس 'all' أو فارغ
    if ($age && $age != 'all') {
        $query->where('age_name', $age);
    }

    $camals = $query->get();

    return response()->json($camals);
}


/// API

public function getRoundsDatesApi(Request $request)
{

    // return "ddd";
    $festivalId = $request->input('festival_id');

    $dates = Round::where('festival_id', $festivalId)
        ->selectRaw('DATE(start) as date, MIN(id) as round_id')
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->get()
        ->map(function ($item) {
            return [
                'date' => $item->date,
                'day' => \Carbon\Carbon::parse($item->date)->translatedFormat('l'), // السبت, الأحد...
                'round_id' => $item->round_id,
            ];
        });

    return response()->json($dates->values());
}


/* very important stop
public function getRoundsByDateApi(Request $request)
{
    $festivalId = $request->input('festival_id');
    $date = $request->input('date'); // صيغة YYYY-MM-DD


    // الوقت الحالي بتوقيت الكويت
    $now = now('Asia/Riyadh');

    $rounds = Round::where('festival_id', $festivalId)
        ->whereDate('start', $date)
        ->orderBy('start', 'asc')
        ->get()
        ->map(function ($item) use ($now) {
            // $start = \Carbon\Carbon::parse($item->start)->setTimezone('Asia/Kuwait');
            // $end   = \Carbon\Carbon::parse($item->end)->setTimezone('Asia/Kuwait');

            $start = \Carbon\Carbon::parse($item->start)->setTimezone('Asia/Riyadh');
$end   = \Carbon\Carbon::parse($item->end)->setTimezone('Asia/Riyadh');


            // صيغة الوقت (12 ساعة AM/PM)
            $startTime = $start->format('h:i A');
            $endTime   = $end->format('h:i A');

            // اليوم بالاسم
            $startDay = $start->locale('ar')->isoFormat('dddd');
            $endDay   = $end->locale('ar')->isoFormat('dddd');

            // حساب الوقت المتبقي
            if ($now->lt($start)) {
                $diff = $now->diff($start);
                $timeStatus = sprintf(
                    'سيبدأ بعد %d يوم %d ساعة %d دقيقة',
                    $diff->d,
                    $diff->h,
                    $diff->i
                );
                $type = 'upcoming';
            } elseif ($now->between($start, $end)) {
                $diff = $now->diff($end);
                $timeStatus = sprintf(
                    'سينتهي بعد %d يوم %d ساعة %d دقيقة',
                    $diff->d,
                    $diff->h,
                    $diff->i
                );
                $type = 'current';
            } else {
                $timeStatus = 'انتهى الشوط';
                $type = 'ended';
            }

            // نحافظ على الأعمدة الأصلية + نضيف الجديد
            $data = $item->toArray();
            $data['start_time']  = $startTime;
            $data['end_time']    = $endTime;
            $data['time_status'] = $timeStatus;
            $data['start_day']   = $startDay;
            $data['end_day']     = $endDay;
            $data['start_date']  = $start->toDateString();
            $data['end_date']    = $end->toDateString();
            $data['type']        = $type;

            return $data;
        });

    return response()->json($rounds);
}
*/



//////

public function getRoundsByDateApi(Request $request)
{
    $festivalId = $request->input('festival_id');
    $date = $request->input('date'); // صيغة YYYY-MM-DD


    // الوقت الحالي بتوقيت الكويت
    $now = now('Asia/Riyadh');

    $rounds = Round::where('festival_id', $festivalId)
        ->whereDate('start', $date)
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($item) use ($now) {
            // $start = \Carbon\Carbon::parse($item->start)->setTimezone('Asia/Kuwait');
            // $end   = \Carbon\Carbon::parse($item->end)->setTimezone('Asia/Kuwait');

            $start = \Carbon\Carbon::parse($item->start)->setTimezone('Asia/Riyadh');
$end   = \Carbon\Carbon::parse($item->end)->setTimezone('Asia/Riyadh');


            // صيغة الوقت (12 ساعة AM/PM)
            $startTime = $start->format('h:i A');
            $endTime   = $end->format('h:i A');

            // اليوم بالاسم
            $startDay = $start->locale('ar')->isoFormat('dddd');
            $endDay   = $end->locale('ar')->isoFormat('dddd');

            // حساب الوقت المتبقي
            if ($now->lt($start)) {
                $diff = $now->diff($start);
                $timeStatus = sprintf(
                    'سيبدأ بعد %d يوم %d ساعة %d دقيقة',
                    $diff->d,
                    $diff->h,
                    $diff->i
                );
                $type = 'upcoming';
            } elseif ($now->between($start, $end)) {
                $diff = $now->diff($end);
                $timeStatus = sprintf(
                    'سينتهي بعد %d يوم %d ساعة %d دقيقة',
                    $diff->d,
                    $diff->h,
                    $diff->i
                );
                $type = 'current';
            } else {
                $timeStatus = 'انتهى الشوط';
                $type = 'ended';
            }

            // نحافظ على الأعمدة الأصلية + نضيف الجديد
            $data = $item->toArray();
            $data['start_time']  = $startTime;
            $data['end_time']    = $endTime;
            $data['time_status'] = $timeStatus;
            $data['start_day']   = $startDay;
            $data['end_day']     = $endDay;
            $data['start_date']  = $start->toDateString();
            $data['end_date']    = $end->toDateString();
            $data['type']        = $type;

            return $data;
        });

    return response()->json($rounds);
}



public function getRoundsByDateAndRoundIdApi(Request $request)
{
    $festivalId = $request->input('festival_id');
    $date = $request->input('date'); // صيغة YYYY-MM-DD
    $round_id = $request->input('round_id');

    // الوقت الحالي بتوقيت الكويت أو الرياض حسب اختيارك
    $now = now('Asia/Riyadh'); // إذا أردت الكويت استخدم 'Asia/Kuwait'

    // جلب الشوط الواحد
    $round = Round::where('festival_id', $festivalId)
        ->where('id', $round_id)
        ->whereDate('start', $date)
        ->orderBy('start', 'asc')
        ->first();

    // إذا لم يوجد أي شوط
    if (!$round) {
        return response()->json([]);
    }

    // تحويل الوقت للتوقيت المطلوب
    $start = \Carbon\Carbon::parse($round->start)->setTimezone('Asia/Riyadh');
    $end   = \Carbon\Carbon::parse($round->end)->setTimezone('Asia/Riyadh');

    // صيغة الوقت 12 ساعة AM/PM
    $startTime = $start->format('h:i A');
    $endTime   = $end->format('h:i A');

    // اليوم بالاسم باللغة العربية
    $startDay = $start->locale('ar')->isoFormat('dddd');
    $endDay   = $end->locale('ar')->isoFormat('dddd');

    // حساب الوقت المتبقي
    if ($now->lt($start)) {
        $diff = $now->diff($start);
        $timeStatus = sprintf(
            'سيبدأ بعد %d يوم %d ساعة %d دقيقة',
            $diff->d,
            $diff->h,
            $diff->i
        );
        $type = 'upcoming';
    } elseif ($now->between($start, $end)) {
        $diff = $now->diff($end);
        $timeStatus = sprintf(
            'سينتهي بعد %d يوم %d ساعة %d دقيقة',
            $diff->d,
            $diff->h,
            $diff->i
        );
        $type = 'current';
    } else {
        $timeStatus = 'انتهى الشوط';
        $type = 'ended';
    }

    // إنشاء المصفوفة النهائية
    $data = $round->toArray();
    $data['start_time']  = $startTime;
    $data['end_time']    = $endTime;
    $data['time_status'] = $timeStatus;
    $data['start_day']   = $startDay;
    $data['end_day']     = $endDay;
    $data['start_date']  = $start->toDateString();
    $data['end_date']    = $end->toDateString();
    $data['type']        = $type;

    return response()->json($data);
}



// public function getRoundsByDateAndRoundIdApi(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $date = $request->input('date'); // صيغة YYYY-MM-DD
//     $round_id = $request->input('round_id');

//     // الوقت الحالي بتوقيت الكويت
//     $now = now('Asia/Riyadh');

//     $rounds = Round::where('festival_id', $festivalId)
//     ->where('id',$round_id)
//         ->whereDate('start', $date)
//         ->orderBy('start', 'asc')
//         ->get()->first()
//         ->map(function ($item) use ($now) {
//             // $start = \Carbon\Carbon::parse($item->start)->setTimezone('Asia/Kuwait');
//             // $end   = \Carbon\Carbon::parse($item->end)->setTimezone('Asia/Kuwait');

//             $start = \Carbon\Carbon::parse($item->start)->setTimezone('Asia/Riyadh');
// $end   = \Carbon\Carbon::parse($item->end)->setTimezone('Asia/Riyadh');


//             // صيغة الوقت (12 ساعة AM/PM)
//             $startTime = $start->format('h:i A');
//             $endTime   = $end->format('h:i A');

//             // اليوم بالاسم
//             $startDay = $start->locale('ar')->isoFormat('dddd');
//             $endDay   = $end->locale('ar')->isoFormat('dddd');

//             // حساب الوقت المتبقي
//             if ($now->lt($start)) {
//                 $diff = $now->diff($start);
//                 $timeStatus = sprintf(
//                     'سيبدأ بعد %d يوم %d ساعة %d دقيقة',
//                     $diff->d,
//                     $diff->h,
//                     $diff->i
//                 );
//                 $type = 'upcoming';
//             } elseif ($now->between($start, $end)) {
//                 $diff = $now->diff($end);
//                 $timeStatus = sprintf(
//                     'سينتهي بعد %d يوم %d ساعة %d دقيقة',
//                     $diff->d,
//                     $diff->h,
//                     $diff->i
//                 );
//                 $type = 'current';
//             } else {
//                 $timeStatus = 'انتهى الشوط';
//                 $type = 'ended';
//             }

//             // نحافظ على الأعمدة الأصلية + نضيف الجديد
//             $data = $item->toArray();
//             $data['start_time']  = $startTime;
//             $data['end_time']    = $endTime;
//             $data['time_status'] = $timeStatus;
//             $data['start_day']   = $startDay;
//             $data['end_day']     = $endDay;
//             $data['start_date']  = $start->toDateString();
//             $data['end_date']    = $end->toDateString();
//             $data['type']        = $type;

//             return $data;
//         });

//     return response()->json($rounds);
// }


// public function getCamelParticipationsApi(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $roundId    = $request->input('round_id');

//     $query = \App\Models\CamelRoundParticipation::query();

//     if ($festivalId) {
//         $query->where('festival_id', $festivalId);
//     }

//     if ($roundId) {
//         $query->where('round_id', $roundId);
//     }

//     $participations = $query->orderBy('id', 'asc')->get();

//     return response()->json($participations);
// }


// public function getCamelParticipationsApi(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $roundId    = $request->input('round_id');

//     $query = \App\Models\CamelRoundParticipation::with([
//         'camal.user' // تجيب بيانات الجمل + صاحب الجمل
//     ]);

//     if ($festivalId) {
//         $query->where('festival_id', $festivalId);
//     }

//     if ($roundId) {
//         $query->where('round_id', $roundId);
//     }

//     $participations = $query->orderBy('id', 'asc')->get();

//     return response()->json($participations);
// }



// public function getCamelParticipationsApi(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $roundId    = $request->input('round_id');

//     $query = \App\Models\CamelRoundParticipation::with([
//         'camal.user'
//     ]);

//     if ($festivalId) {
//         $query->where('festival_id', $festivalId);
//     }

//     if ($roundId) {
//         $query->where('round_id', $roundId);
//     }

//     $participations = $query->orderBy('id', 'asc')->get()->map(function($item) {
//         $data = $item->toArray();

//         // احذف user من داخل camal وارفعة لمستوى أعلى
//         if(isset($data['camal']['user'])){
//             $data['user'] = $data['camal']['user'];
//             unset($data['camal']['user']);
//         }

//         return $data;
//     });

//     return response()->json($participations);
// }


public function getCamelParticipationsApi(Request $request)
{
    $festivalId = $request->input('festival_id');
    $roundId    = $request->input('round_id');

    // استعلام أساسي بدون علاقات
    $query = \App\Models\CamelRoundParticipation::query();

    if ($festivalId) {
        $query->where('festival_id', $festivalId);
    }

    if ($roundId) {
        $query->where('round_id', $roundId);
    }

    // جلب النتائج فقط من جدول CamelRoundParticipation
    $participations = $query->orderBy('id', 'asc')->get([
        'id',
        'festival_id',
        'round_id',
        'camel_name',
        'camel_age_name',
        'camel_owner_name',
        'camel_owner_country',
        'registration_number',
        'is_winner',
        'created_at',
        'updated_at'
    ]);

    return response()->json($participations);
}





//// Open or close round




         public function roundClose($id){
        Round::findOrFail($id)->update(['open_nomination' => 'close']);
        $notification = array(
            'message' => 'الترشيح مغلق',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method
      public function roundOpen($id){
        Round::findOrFail($id)->update(['open_nomination' => 'open']);
        $notification = array(
            'message' => 'الترشيح مفتوح',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }



//// Open or close round


}
