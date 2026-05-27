<?php

namespace App\Http\Controllers;

use App\Models\Prize;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PrizeController extends Controller
{
    // عرض كل الجوائز
    public function allPrizes()
    {
        $values = Prize::latest()->get();
        return view('admin.prize.all_prizes', compact('values'));
    }

    // صفحة إضافة جائزة جديدة
    public function addPrize()
    {
        return view('admin.prize.add_prize');
    }

    // تخزين جائزة جديدة
    public function storePrize(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'des' => 'nullable|string',
            'des_en' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $save_url = null;

        if ($request->file('photo')) {
            $image = $request->file('photo');
            $name_gen = date('YmdHi') . '_' . $image->getClientOriginalName();
            $path = public_path('upload/prizes/');

            $imageManager = new ImageManager(new Driver());
            $imageResized = $imageManager->read($image);
            $imageResized->save($path . $name_gen);

            $save_url = 'upload/prizes/' . $name_gen;
        }

        Prize::create([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'des' => $request->des,
            'des_en' => $request->des_en,
            'photo' => $save_url,
            'status' => 'active',
        ]);

        return redirect()->route('all.prizes')->with('success', 'تمت إضافة الجائزة بنجاح');
    }

    // صفحة تعديل الجائزة
    public function editPrize($id)
    {
        $value = Prize::findOrFail($id);
        return view('admin.prize.edit_prize', compact('value'));
    }

    // تحديث الجائزة
    public function editPrizeStore(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:prizes,id',
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'des' => 'nullable|string',
            'des_en' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $prize = Prize::findOrFail($request->id);
        $save_url = $prize->photo;

        if ($request->file('photo')) {
            if ($prize->photo && file_exists(public_path($prize->photo))) {
                unlink(public_path($prize->photo));
            }

            $image = $request->file('photo');
            $name_gen = date('YmdHi') . '_' . $image->getClientOriginalName();
            $path = public_path('upload/prizes/');

            $imageManager = new ImageManager(new Driver());
            $imageResized = $imageManager->read($image);
            $imageResized->save($path . $name_gen);

            $save_url = 'upload/prizes/' . $name_gen;
        }

        $prize->update([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'des' => $request->des,
            'des_en' => $request->des_en,
            'photo' => $save_url,
        ]);

        return redirect()->route('all.prizes')->with('success', 'تم تعديل الجائزة بنجاح');
    }

    // حذف الجائزة
    public function deletePrize($id)
    {
        $prize = Prize::findOrFail($id);
        if ($prize->photo && file_exists(public_path($prize->photo))) {
            unlink(public_path($prize->photo));
        }
        $prize->delete();

        return redirect()->route('all.prizes')->with('success', 'تم حذف الجائزة بنجاح');
    }

    // جعل الجائزة غير نشطة
    public function prizeInactive($id)
    {
        Prize::findOrFail($id)->update(['status' => 'inactive']);
        return redirect()->route('all.prizes')->with('success', 'تم إخفاء الجائزة');
    }

    // جعل الجائزة نشطة
    public function prizeActive($id)
    {
        Prize::findOrFail($id)->update(['status' => 'active']);
        return redirect()->route('all.prizes')->with('success', 'تم إظهار الجائزة');
    }


    // API


    public function getPrizeApi(Request $request)
{
    $limit = $request->input('limit'); // جاي من POST body

    $query = Prize::latest();

    if (!is_null($limit) && $limit > 0) {
        $query->take($limit);
    }

    $prize = $query->get();

    return response()->json($prize);
}
}
