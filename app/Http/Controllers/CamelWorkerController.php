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
     * API to add a new camel worker. Only owners can perform this action.
     */
    public function addWorkerApi(Request $request)
    {
        // Validate the incoming fields
        $request->validate([
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'status'     => 'nullable|in:active,inactive',
            'is_online'  => 'nullable|boolean',
            'photo'      => 'nullable', // Can be file, string path, or null/""
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'phone.required'      => 'رقم الهاتف مطلوب.',
        ]);

        // If photo is an uploaded file, apply image validations
        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'photo.image' => 'يجب أن يكون الملف المرفوع صورة.',
                'photo.max'   => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
            ]);
        }

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        $loginCode = $this->generateUniqueLoginCode();

        // Process photo field based on type
        $photoPath = null;
        if ($request->hasFile('photo')) {
            // Case 1: Uploaded image file
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
            $photoPath = 'upload/camel_workers/' . $filename;
        } elseif ($request->filled('photo') && is_string($request->photo) && $request->photo !== "") {
            // Case 2: photo is a non-empty string path
            $photoPath = $request->photo;
        } else {
            // Case 3: photo is empty string "" or null
            $photoPath = null;
        }

        $worker = CamelWorker::create([
            'owner_id'   => $request->owner_id, // The owner ID passed in request
            'full_name'  => $request->full_name,
            'login_code' => $loginCode,
            'phone'      => $request->phone,
            'status'     => $request->status ?? 'active',
            'is_online'  => $request->is_online ?? 0,
            'photo_path' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Camel worker added successfully',
            'login_code' => $loginCode,
            'worker' => $worker
        ], 201);
    }

    /**
     * API for camel worker login via login_code.
     */
    public function loginWorkerApi(Request $request)
    {
        $request->validate([
            'login_code' => 'required|string',
        ], [
            'login_code.required' => 'رمز الدخول مطلوب.',
        ]);

        // Find the worker by login_code and load their owner details
        $worker = CamelWorker::where('login_code', $request->login_code)->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => 'رمز الدخول غير صحيح.'
            ], 404);
        }

        // Check if worker status is active
        if ($worker->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب غير نشط حالياً.'
            ], 403);
        }

        // Update worker status to online
        $worker->is_online = 1;
        $worker->save();

        // Load owner details
        $worker->load('owner');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'worker' => $worker
        ], 200);
    }

    /**
     * API to update camel worker details (except login_code).
     */
    public function updateWorkerApi(Request $request)
    {
        // Validate inputs
        $request->validate([
            'id'         => 'required|exists:camel_workers,id',
            'owner_id'   => 'nullable|exists:users,id',
            'full_name'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'nullable|in:active,inactive',
            'is_online'  => 'nullable|boolean',
            'photo'      => 'nullable', // file, string, or empty
        ], [
            'id.required'         => 'معرف العامل مطلوب.',
            'id.exists'           => 'العامل غير موجود.',
            'owner_id.exists'     => 'المالك غير موجود.',
        ]);

        $worker = CamelWorker::findOrFail($request->id);

        // If owner_id is provided, verify it belongs to a user with role 'owner'
        if ($request->filled('owner_id')) {
            $owner = User::find($request->owner_id);
            if (!$owner || $owner->role !== 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided owner_id does not belong to a valid owner.'
                ], 403);
            }
            $worker->owner_id = $request->owner_id;
        }

        // If photo is uploaded file, apply image validations
        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'photo.image' => 'يجب أن يكون الملف المرفوع صورة.',
                'photo.max'   => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
            ]);
        }

        // Handle photo update
        if ($request->hasFile('photo')) {
            // Delete old photo file if it exists
            if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
                @unlink(public_path($worker->photo_path));
            }
            
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
            $worker->photo_path = 'upload/camel_workers/' . $filename;
        } elseif ($request->has('photo')) {
            // If photo is passed in input (even if it's "" or null or a string)
            if ($request->filled('photo') && is_string($request->photo) && $request->photo !== "") {
                // If it is a non-empty string path, update it
                $worker->photo_path = $request->photo;
            } else {
                // If it is empty string "" or null, remove the image and delete old photo file
                if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
                    @unlink(public_path($worker->photo_path));
                }
                $worker->photo_path = null;
            }
        }

        // Update other fields if provided
        if ($request->has('full_name')) {
            $worker->full_name = $request->full_name;
        }
        if ($request->has('phone')) {
            $worker->phone = $request->phone;
        }
        if ($request->has('status')) {
            $worker->status = $request->status;
        }
        if ($request->has('is_online')) {
            $worker->is_online = $request->is_online;
        }

        $worker->save();

        return response()->json([
            'success' => true,
            'message' => 'Camel worker updated successfully',
            'worker' => $worker
        ], 200);
    }

    /**
     * API to fetch all camel workers belonging to a specific owner.
     */
    public function getWorkersByOwnerApi(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
        ], [
            'owner_id.required' => 'حقل المالك مطلوب.',
            'owner_id.exists'   => 'المالك غير موجود.',
        ]);

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        // Fetch workers belonging to this owner
        $workers = CamelWorker::where('owner_id', $request->owner_id)->latest()->get();

        return response()->json([
            'success' => true,
            'workers' => $workers
        ], 200);
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
