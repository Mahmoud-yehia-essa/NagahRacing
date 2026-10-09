<?php

namespace App\Http\Controllers;

use App\Models\CamelWorker;
use App\Models\User;
use App\Events\WorkerStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CamelWorkerController extends Controller
{
    /**
     * Display a listing of camel workers filtered by owner.
     */
    public function index(Request $request)
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $selectedOwnerId = $request->owner_id;

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

        return view('admin.camel_worker.add_camel_worker', compact('owners', 'selectedOwnerId'));
    }

    /**
     * Store a newly created camel worker.
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
            'is_online'  => 'nullable|in:0,1',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'status.required'     => 'حالة العامل مطلوبة.',
            'photo.image'         => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.max'           => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
            $photoPath = 'upload/camel_workers/' . $filename;
        }

        $loginCode = $this->generateUniqueLoginCode();
        $isOnline = (int) $request->input('is_online', 0);

        CamelWorker::create([
            'owner_id'      => $request->owner_id,
            'full_name'     => $request->full_name,
            'login_code'    => $loginCode,
            'photo_path'    => $photoPath,
            'phone'         => $request->phone,
            'status'        => $request->status,
            'is_online'     => $isOnline,
            'online_status' => $isOnline ? 'online' : 'offline',
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
            'phone'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
            'is_online'  => 'nullable|in:0,1',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'status.required'     => 'حالة العامل مطلوبة.',
            'photo.image'         => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.max'           => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
        ]);

        $worker = CamelWorker::findOrFail($request->id);

        if ($request->hasFile('photo')) {
            if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
                @unlink(public_path($worker->photo_path));
            }
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
            $worker->photo_path = 'upload/camel_workers/' . $filename;
        }

        $isOnline = (int) $request->input('is_online', $worker->is_online);

        $worker->owner_id = $request->owner_id;
        $worker->full_name = $request->full_name;
        $worker->phone = $request->phone;
        $worker->status = $request->status;
        $worker->is_online = $isOnline;
        $worker->online_status = $isOnline ? 'online' : 'offline';
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
     * API to add a new camel worker.
     */
    public function addWorkerApi(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'nullable|in:active,inactive',
            'is_online'  => 'nullable|boolean',
            'photo'      => 'nullable',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك غير موجود.',
            'full_name.required'  => 'اسم العامل مطلوب.',
            'photo.image'         => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.max'           => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
            $photoPath = 'upload/camel_workers/' . $filename;
        }

        // Generate unique 6-digit login code
        $loginCode = $this->generateUniqueLoginCode();

        // Create the camel worker
        $worker = CamelWorker::create([
            'owner_id'      => $request->owner_id,
            'full_name'     => $request->full_name,
            'login_code'    => $loginCode,
            'photo_path'    => $photoPath,
            'phone'         => $request->phone,
            'status'        => $request->status ?? 'active',
            'is_online'     => false,
            'online_status' => 'offline',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Camel worker added successfully',
            'worker' => $worker
        ], 201);
    }

    /**
     * API to login a camel worker using a 6-digit login code.
     */
    public function loginWorkerApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_code' => 'required|digits:6',
        ], [
            'login_code.required' => 'رمز الدخول مطلوب.',
            'login_code.digits'   => 'رمز الدخول يجب أن يتكون من 6 أرقام.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the worker by login code
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
        $worker->online_status = 'online';
        $worker->last_activity_at = now();
        $worker->save();

        try {
            broadcast(new WorkerStatusUpdated($worker));
        } catch (\Exception $e) {}

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
            'photo'      => 'nullable',
        ], [
            'id.required'         => 'معرف العامل مطلوب.',
            'id.exists'           => 'العامل غير موجود.',
            'owner_id.exists'     => 'المالك غير موجود.',
        ]);

        $worker = CamelWorker::findOrFail($request->id);

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

        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'photo.image' => 'يجب أن يكون الملف المرفوع صورة.',
                'photo.max'   => 'حجم الصورة لا يجب أن يتخطى 2 ميجابايت.',
            ]);
        }

        if ($request->hasFile('photo')) {
            if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
                @unlink(public_path($worker->photo_path));
            }
            
            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/camel_workers'), $filename);
            $worker->photo_path = 'upload/camel_workers/' . $filename;
        } elseif ($request->has('photo')) {
            if ($request->filled('photo') && is_string($request->photo) && $request->photo !== "") {
                $worker->photo_path = $request->photo;
            } else {
                if ($worker->photo_path && file_exists(public_path($worker->photo_path))) {
                    @unlink(public_path($worker->photo_path));
                }
                $worker->photo_path = null;
            }
        }

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
            $worker->online_status = $request->is_online ? 'online' : 'offline';
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

        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        // Auto-sync worker online/background/offline statuses
        CamelWorker::syncOnlineStatuses($request->owner_id);
        $workers = CamelWorker::where('owner_id', $request->owner_id)->latest()->get();

        return response()->json([
            'success' => true,
            'workers' => $workers
        ], 200);
    }

    /**
     * API for worker heartbeat/ping to maintain active online/background status.
     */
    public function heartbeatWorkerApi(Request $request)
    {
        $workerId = $request->input('worker_id') ?? $request->input('id');
        $loginCode = $request->input('login_code');
        $status = $request->input('online_status', 'online');
        if (!in_array($status, ['online', 'background', 'offline'])) {
            $status = 'online';
        }

        $worker = null;
        if ($workerId) {
            $worker = CamelWorker::find($workerId);
        } elseif ($loginCode) {
            $worker = CamelWorker::where('login_code', $loginCode)->first();
        }

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => 'العامل غير موجود.'
            ], 404);
        }

        $worker->online_status = $status;
        $worker->is_online = ($status !== 'offline') ? 1 : 0;
        $worker->last_activity_at = ($status === 'offline') ? null : now();
        $worker->save();

        try {
            broadcast(new WorkerStatusUpdated($worker));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'online_status' => $worker->online_status,
            'is_online' => (bool) $worker->is_online,
            'last_activity_at' => $worker->last_activity_at ? $worker->last_activity_at->toDateTimeString() : null,
        ], 200);
    }

    /**
     * API for worker explicit state updates (online, background, offline).
     */
    public function updateStatusWorkerApi(Request $request)
    {
        $workerId = $request->input('worker_id') ?? $request->input('id');
        $loginCode = $request->input('login_code');
        $status = $request->input('online_status', 'online');
        if (!in_array($status, ['online', 'background', 'offline'])) {
            $status = 'online';
        }

        $worker = null;
        if ($workerId) {
            $worker = CamelWorker::find($workerId);
        } elseif ($loginCode) {
            $worker = CamelWorker::where('login_code', $loginCode)->first();
        }

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => 'العامل غير موجود.'
            ], 404);
        }

        $worker->online_status = $status;
        $worker->is_online = ($status !== 'offline') ? 1 : 0;
        $worker->last_activity_at = ($status === 'offline') ? null : now();
        $worker->save();

        try {
            broadcast(new WorkerStatusUpdated($worker));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'online_status' => $worker->online_status,
            'is_online' => (bool) $worker->is_online,
        ], 200);
    }

    /**
     * API for worker logout to set online status to offline.
     */
    public function logoutWorkerApi(Request $request)
    {
        $workerId = $request->input('worker_id') ?? $request->input('id');
        $loginCode = $request->input('login_code');

        $worker = null;
        if ($workerId) {
            $worker = CamelWorker::find($workerId);
        } elseif ($loginCode) {
            $worker = CamelWorker::where('login_code', $loginCode)->first();
        }

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => 'العامل غير موجود.'
            ], 404);
        }

        $worker->online_status = 'offline';
        $worker->is_online = 0;
        $worker->last_activity_at = null;
        $worker->save();

        try {
            broadcast(new WorkerStatusUpdated($worker));
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل خروج العامل بنجاح.'
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
