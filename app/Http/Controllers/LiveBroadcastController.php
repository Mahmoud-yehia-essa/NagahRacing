<?php

namespace App\Http\Controllers;

use App\Models\LiveBroadcast;
use Illuminate\Http\Request;

class LiveBroadcastController extends Controller
{
    // عرض كل البث المباشر
    public function allLiveBroadcast()
    {
        $values = LiveBroadcast::latest()->get();
        return view('admin.live_broadcast.all_live_broadcast', compact('values'));
    }

    // صفحة إضافة بث مباشر
    public function addLiveBroadcast()
    {
        return view('admin.live_broadcast.add_live_broadcast');
    }

    // تخزين البث المباشر
    public function storeLiveBroadcast(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
        ]);

        LiveBroadcast::create([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'more_des' => $request->more_des,
            'more_des_en' => $request->more_des_en,
            'status' => 'active',
        ]);

        return redirect()->route('all.live.broadcast')->with('success', 'تمت إضافة البث المباشر بنجاح');
    }

    // صفحة تعديل البث المباشر
    public function editLiveBroadcast($id)
    {
        $value = LiveBroadcast::findOrFail($id);
        return view('admin.live_broadcast.edit_live_broadcast', compact('value'));
    }

    // تحديث البث المباشر
    public function editLiveBroadcastStore(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:live_broadcasts,id',
            'title' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
        ]);

        $broadcast = LiveBroadcast::findOrFail($request->id);

        $broadcast->update([
            'title' => $request->title,
            'title_en' => $request->title_en,
            'more_des' => $request->more_des,
            'more_des_en' => $request->more_des_en,
        ]);

        return redirect()->route('all.live.broadcast')->with('success', 'تم تعديل البث المباشر بنجاح');
    }

    // حذف البث المباشر
    public function deleteLiveBroadcast($id)
    {
        LiveBroadcast::findOrFail($id)->delete();
        return redirect()->route('all.live.broadcast')->with('success', 'تم حذف البث المباشر بنجاح');
    }

    // جعل البث غير نشط
    public function liveBroadcastInactive($id)
    {
        LiveBroadcast::findOrFail($id)->update(['status' => 'inactive']);
        return redirect()->route('all.live.broadcast')->with('success', 'تم إخفاء البث المباشر');
    }

    // جعل البث نشط
    public function liveBroadcastActive($id)
    {
        LiveBroadcast::findOrFail($id)->update(['status' => 'active']);
        return redirect()->route('all.live.broadcast')->with('success', 'تم إظهار البث المباشر');
    }


    /// API

public function getLiveBroadcastApi(Request $request)
{
    $limit = $request->input('limit'); // جاي من POST body

    $query = LiveBroadcast::latest();

    if (!is_null($limit) && $limit > 0) {
        $query->take($limit);
    }

    $liveBroadcast = $query->get();

    return response()->json($liveBroadcast);
}


}
