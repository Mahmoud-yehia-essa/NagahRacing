<?php

namespace App\Http\Controllers;

use App\Models\FestivalPoint;
use App\Models\Festival;
use Illuminate\Http\Request;

class FestivalPointController extends Controller
{
    // عرض جميع النقاط
    public function index()
    {
        $points = FestivalPoint::with('festival')->get();
        return view('admin.festival_point.all_festival_points', compact('points'));
    }

    // صفحة إضافة نقطة جديدة
    public function create()
    {
        $festivals = Festival::all();
        $ages = ['الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'];
        return view('admin.festival_point.add_festival_points', compact('festivals', 'ages'));
    }

    // حفظ نقطة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'festival_id' => 'required|exists:festivals,id',
            'age_name'    => 'required|in:الحقايق,اللقايا,الجذاع,الثنايا,زمول,الحيل',
            'points'      => 'required|numeric|min:0',
        ]);

        FestivalPoint::create([
            'festival_id' => $request->festival_id,
            'age_name'    => $request->age_name,
            'points'      => $request->points,
        ]);

        return redirect()->route('all.festival.points')->with('success', 'تم إضافة النقطة بنجاح');
    }

    // صفحة تعديل النقطة
    public function edit($id)
    {
        $point = FestivalPoint::findOrFail($id);
        $festivals = Festival::all();
        // $ages = ['الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'];
        $ages = ['مفاريد','الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'];

        return view('admin.festival_point.edit_festival_points', compact('point', 'festivals', 'ages'));
    }

    // تحديث النقطة
    public function update(Request $request, $id)
    {
        $request->validate([
            // 'festival_id' => 'required|exists:festivals,id',
            // 'age_name'    => 'required|in:الحقايق,اللقايا,الجذاع,الثنايا,زمول,الحيل',
            'age_name' => 'required|in:مفاريد,الحقايق,اللقايا,الجذاع,الثنايا,زمول,الحيل',

            'points'      => 'required|numeric|min:0',
        ]);

        $point = FestivalPoint::findOrFail($id);
        $point->update([
            // 'festival_id' => $request->festival_id,
            'age_name'    => $request->age_name,
            'points'      => $request->points,
        ]);

        return redirect()->route('all.festival.points')->with('success', 'تم تحديث النقطة بنجاح');
    }

    // حذف النقطة
    public function destroy($id)
    {
        $point = FestivalPoint::findOrFail($id);
        $point->delete();

        return redirect()->route('all.festival.points')->with('success', 'تم حذف النقطة بنجاح');
    }



    // Api

//     public function getFestivalPoints(Request $request)
// {
//     $festivalId = $request->input('festival_id');
//     $ageName    = $request->input('age_name');

//     $point = \App\Models\FestivalPoint::where('festival_id', $festivalId)
//         ->where('age_name', $ageName)
//         ->first();

//     return response()->json([
//         'success' => true,
//         'festival_id' => $festivalId,
//         'age_name' => $ageName,
//         'points' => $point ? $point->points : 0,
//     ]);
// }


    public function getFestivalPoints(Request $request)
{
    $festivalId = $request->input('festival_id');
    $ageName    = $request->input('age_name');

    $point = \App\Models\FestivalPoint::where('age_name', $ageName)
        ->first();

    return response()->json([
        'success' => true,
        'festival_id' => $festivalId,
        'age_name' => $ageName,
        'points' => $point ? $point->points : 0,
    ]);
}

}
