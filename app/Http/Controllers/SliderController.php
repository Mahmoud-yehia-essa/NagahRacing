<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SliderController extends Controller
{
    // عرض كل السلايدر
    public function allSlider()
    {
        $values = Slider::latest()->get();
        return view('admin.slider.all_slider', compact('values'));
    }

    // صفحة إضافة سلايدر جديد
    public function addSlider()
    {
        return view('admin.slider.add_slider');
    }

    // تخزين سلايدر جديد
    public function storeSlider(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'des' => 'nullable|string',
            'des_en' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        $save_url = null;

        if ($request->file('photo')) {
            $image = $request->file('photo');
            $name_gen = date('YmdHi') . '_' . $image->getClientOriginalName();
            $path = public_path('upload/slider/');

            $imageManager = new ImageManager(new Driver());
            $imageResized = $imageManager->read($image);
            $imageResized->save($path . $name_gen);

            $save_url = 'upload/slider/' . $name_gen;
        }

        Slider::create([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'des' => $request->des,
            'des_en' => $request->des_en,
            'photo' => $save_url,
            'more_des' => $request->more_des,
            'more_des_en' => $request->more_des_en,
            'status' => 'active',
        ]);

        return redirect()->route('all.slider')->with('success', 'تمت إضافة السلايدر بنجاح');
    }

    // صفحة تعديل السلايدر
    public function editSlider($id)
    {
        $value = Slider::findOrFail($id);
        return view('admin.slider.edit_slider', compact('value'));
    }

    // تحديث السلايدر
    public function editSliderStore(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sliders,id',
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'des' => 'nullable|string',
            'des_en' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        $slider = Slider::findOrFail($request->id);
        $save_url = $slider->photo;

        if ($request->file('photo')) {
            if ($slider->photo && file_exists(public_path($slider->photo))) {
                unlink(public_path($slider->photo));
            }

            $image = $request->file('photo');
            $name_gen = date('YmdHi') . '_' . $image->getClientOriginalName();
            $path = public_path('upload/slider/');

            $imageManager = new ImageManager(new Driver());
            $imageResized = $imageManager->read($image);
            $imageResized->save($path . $name_gen);

            $save_url = 'upload/slider/' . $name_gen;
        }

        $slider->update([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'des' => $request->des,
            'des_en' => $request->des_en,
            'photo' => $save_url,
            'more_des' => $request->more_des,
            'more_des_en' => $request->more_des_en,
        ]);

        return redirect()->route('all.slider')->with('success', 'تم تعديل السلايدر بنجاح');
    }

    // حذف السلايدر
    public function deleteSlider($id)
    {
        $slider = Slider::findOrFail($id);
        if ($slider->photo && file_exists(public_path($slider->photo))) {
            unlink(public_path($slider->photo));
        }
        $slider->delete();

        return redirect()->route('all.slider')->with('success', 'تم حذف السلايدر بنجاح');
    }

    // جعل السلايدر غير نشط
    public function sliderInactive($id)
    {
        Slider::findOrFail($id)->update(['status' => 'inactive']);
        return redirect()->route('all.slider')->with('success', 'تم إخفاء السلايدر');
    }

    // جعل السلايدر نشط
    public function sliderActive($id)
    {
        Slider::findOrFail($id)->update(['status' => 'active']);
        return redirect()->route('all.slider')->with('success', 'تم إظهار السلايدر');
    }





    /// API

public function getSlidersApi(Request $request)
{
    $limit = $request->input('limit'); // جاي من POST body

    $query = Slider::latest();

    if (!is_null($limit) && $limit > 0) {
        $query->take($limit);
    }

    $sliders = $query->get();

    return response()->json($sliders);
}


}

