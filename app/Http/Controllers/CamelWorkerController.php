<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CamelWorker;
use Illuminate\Http\Request;

class CamelWorkerController extends Controller
{
    /**
     * Display a listing of camel workers filtered by owner.
     */
    public function index(Request $request)
    {
        // Get all owners in the system
        $owners = User::where('role', 'owner')->latest()->get();

        // Get selected owner_id from request
        $selectedOwnerId = $request->owner_id;

        // Fetch workers for selected owner
        $workers = [];
        $selectedOwner = null;
        if ($selectedOwnerId) {
            $selectedOwner = User::findOrFail($selectedOwnerId);
            $workers = CamelWorker::where('owner_id', $selectedOwnerId)->latest()->get();
        }

        return view('admin.camel_worker.all_camel_workers', compact('owners', 'workers', 'selectedOwnerId', 'selectedOwner'));
    }

    /**
     * Show the form for creating a new camel worker.
     */
    public function create(Request $request)
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $selectedOwnerId = $request->owner_id;
        $generatedCode = $this->generateUniqueLoginCode();

        return view('admin.camel_worker.add_camel_worker', compact('owners', 'selectedOwnerId', 'generatedCode'));
    }

    /**
     * Store a newly created camel worker.
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'status'     => 'required|in:active,inactive',
            'is_online'  => 'required|boolean',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'phone.required'      => 'رقم الهاتف مطلوب.',
            'status.required'     => 'حالة العامل مطلوبة.',
            'is_online.required'  => 'حالة اتصال العامل مطلوبة.',
            'is_online.boolean'   => 'حالة اتصال العامل غير صالحة.',
            'photo.image'         => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.max'           => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
        ]);

        $loginCode = $this->generateUniqueLoginCode();

        $filename = null;
        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
        }

        CamelWorker::create([
            'owner_id'   => $request->owner_id,
            'full_name'  => $request->full_name,
            'login_code' => $loginCode,
            'phone'      => $request->phone,
            'status'     => $request->status,
            'is_online'  => $request->is_online,
            'photo_path' => $filename ? 'upload/camel_workers/' . $filename : null,
        ]);

        $notification = [
            'message'    => 'تم إضافة العامل بنجاح ورمز دخوله هو: ' . $loginCode,
            'alert-type' => 'success',
        ];

        return redirect()->route('all.camel.workers', ['owner_id' => $request->owner_id])->with($notification);
    }

    /**
     * Show the form for editing the specified camel worker.
     */
    public function edit($id)
    {
        $worker = CamelWorker::findOrFail($id);
        $owners = User::where('role', 'owner')->latest()->get();

        return view('admin.camel_worker.edit_camel_worker', compact('worker', 'owners'));
    }

    /**
     * Update the specified camel worker.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:camel_workers,id',
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'status'     => 'required|in:active,inactive',
            'is_online'  => 'required|boolean',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'phone.required'      => 'رقم الهاتف مطلوب.',
            'status.required'     => 'حالة العامل مطلوبة.',
            'is_online.required'  => 'حالة اتصال العامل مطلوبة.',
            'is_online.boolean'   => 'حالة اتصال العامل غير صالحة.',
            'photo.image'         => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.max'           => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
        ]);

        $worker = CamelWorker::findOrFail($request->id);
        
        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);

            // Delete old photo if it exists
            if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
                @unlink(public_path($worker->photo_path));
            }

            $worker->photo_path = 'upload/camel_workers/' . $filename;
        }

        $worker->owner_id = $request->owner_id;
        $worker->full_name = $request->full_name;
        $worker->phone = $request->phone;
        $worker->status = $request->status;
        $worker->is_online = $request->is_online;
        $worker->save();

        $notification = [
            'message'    => 'تم تحديث بيانات العامل بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.camel.workers', ['owner_id' => $request->owner_id])->with($notification);
    }

    /**
     * Remove the specified camel worker.
     */
    public function destroy($id)
    {
        $worker = CamelWorker::findOrFail($id);
        $ownerId = $worker->owner_id;

        // Delete photo from storage
        if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
            @unlink(public_path($worker->photo_path));
        }

        $worker->delete();

        $notification = [
            'message'    => 'تم حذف العامل بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.camel.workers', ['owner_id' => $ownerId])->with($notification);
    }

    /**
     * Activate the specified camel worker.
     */
    public function active($id)
    {
        CamelWorker::findOrFail($id)->update(['status' => 'active']);

        $notification = [
            'message'    => 'تم تنشيط العامل بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Deactivate the specified camel worker.
     */
    public function inactive($id)
    {
        CamelWorker::findOrFail($id)->update(['status' => 'inactive']);

        $notification = [
            'message'    => 'تم إلغاء تنشيط العامل بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Generate a unique 6-digit login code.
     */
    private function generateUniqueLoginCode()
    {
        do {
            $code = mt_rand(100000, 999999);
        } while (CamelWorker::where('login_code', $code)->exists());

        return $code;
    }
}
