<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SubUser;
use Illuminate\Http\Request;

class SubUserController extends Controller
{
    /**
     * Display a listing of sub users filtered by owner.
     */
    public function index(Request $request)
    {
        // Get all owners in the system
        $owners = User::where('role', 'owner')->latest()->get();

        // Get selected owner_id from request
        $selectedOwnerId = $request->owner_id;

        // Fetch sub users for selected owner
        $subUsers = [];
        $selectedOwner = null;
        if ($selectedOwnerId) {
            $selectedOwner = User::findOrFail($selectedOwnerId);
            $subUsers = SubUser::where('owner_id', $selectedOwnerId)->latest()->get();
        }

        return view('admin.sub_user.all_sub_users', compact('owners', 'subUsers', 'selectedOwnerId', 'selectedOwner'));
    }

    /**
     * Show the form for creating a new sub user.
     */
    public function create(Request $request)
    {
        $owners = User::where('role', 'owner')->latest()->get();
        $selectedOwnerId = $request->owner_id;
        $generatedCode = $this->generateUniqueLoginCode();

        return view('admin.sub_user.add_sub_user', compact('owners', 'selectedOwnerId', 'generatedCode'));
    }

    /**
     * Store a newly created sub user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'status.required'     => 'حالة المستفيد مطلوبة.',
        ]);

        $loginCode = $this->generateUniqueLoginCode();

        SubUser::create([
            'owner_id'   => $request->owner_id,
            'full_name'  => $request->full_name,
            'login_code' => $loginCode,
            'phone'      => $request->phone,
            'status'     => $request->status,
        ]);

        $notification = [
            'message'    => 'تم إضافة المستفيد بنجاح ورمز دخوله هو: ' . $loginCode,
            'alert-type' => 'success',
        ];

        return redirect()->route('all.sub.users', ['owner_id' => $request->owner_id])->with($notification);
    }

    /**
     * Show the form for editing the specified sub user.
     */
    public function edit($id)
    {
        $subUser = SubUser::findOrFail($id);
        $owners = User::where('role', 'owner')->latest()->get();

        return view('admin.sub_user.edit_sub_user', compact('subUser', 'owners'));
    }

    /**
     * Update the specified sub user.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'         => 'required|exists:sub_users,id',
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
            'status.required'     => 'حالة المستفيد مطلوبة.',
        ]);

        $subUser = SubUser::findOrFail($request->id);
        
        $subUser->owner_id = $request->owner_id;
        $subUser->full_name = $request->full_name;
        $subUser->phone = $request->phone;
        $subUser->status = $request->status;
        $subUser->save();

        $notification = [
            'message'    => 'تم تحديث بيانات المستفيد بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.sub.users', ['owner_id' => $request->owner_id])->with($notification);
    }

    /**
     * Remove the specified sub user.
     */
    public function destroy($id)
    {
        $subUser = SubUser::findOrFail($id);
        $ownerId = $subUser->owner_id;

        $subUser->delete();

        $notification = [
            'message'    => 'تم حذف المستفيد بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.sub.users', ['owner_id' => $ownerId])->with($notification);
    }

    /**
     * Activate the specified sub user.
     */
    public function active($id)
    {
        SubUser::findOrFail($id)->update(['status' => 'active']);

        $notification = [
            'message'    => 'تم تنشيط المستفيد بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * Deactivate the specified sub user.
     */
    public function inactive($id)
    {
        SubUser::findOrFail($id)->update(['status' => 'inactive']);

        $notification = [
            'message'    => 'تم إلغاء تنشيط المستفيد بنجاح',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * API to add a new beneficiary. Only owners can perform this action.
     */
    public function addSubUserApi(Request $request)
    {
        // Validate the incoming fields
        $request->validate([
            'owner_id'   => 'required|exists:users,id',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'status'     => 'nullable|in:active,inactive',
        ], [
            'owner_id.required'   => 'حقل المالك مطلوب.',
            'owner_id.exists'     => 'المالك المختار غير موجود.',
            'full_name.required'  => 'حقل الاسم الكامل مطلوب.',
        ]);

        // Check if the provided owner_id belongs to a user with the owner role
        $owner = User::find($request->owner_id);
        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'The provided owner_id does not belong to a valid owner.'
            ], 403);
        }

        $loginCode = $this->generateUniqueLoginCode();

        $subUser = SubUser::create([
            'owner_id'   => $request->owner_id,
            'full_name'  => $request->full_name,
            'login_code' => $loginCode,
            'phone'      => $request->phone,
            'status'     => $request->status ?? 'active',
        ]);

        $subUser->load('owner');

        return response()->json([
            'success' => true,
            'message' => 'Sub user added successfully',
            'login_code' => $loginCode,
            'sub_user' => $subUser
        ], 201);
    }

    /**
     * API for sub user login via login_code.
     */
    public function loginSubUserApi(Request $request)
    {
        $request->validate([
            'login_code' => 'required|string',
        ], [
            'login_code.required' => 'رمز الدخول مطلوب.',
        ]);

        // Find the sub user by login_code and load their owner details
        $subUser = SubUser::where('login_code', $request->login_code)->first();

        if (!$subUser) {
            return response()->json([
                'success' => false,
                'message' => 'رمز الدخول غير صحيح.'
            ], 404);
        }

        // Check if status is active
        if ($subUser->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب غير نشط حالياً.'
            ], 403);
        }

        // Load owner details
        $subUser->load('owner');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'sub_user' => $subUser
        ], 200);
    }

    /**
     * API to delete a beneficiary.
     */
    public function deleteSubUserApi(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sub_users,id',
        ], [
            'id.required' => 'معرف المستفيد مطلوب.',
            'id.exists'   => 'المستفيد غير موجود.',
        ]);

        $subUser = SubUser::findOrFail($request->id);
        $subUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sub user deleted successfully'
        ], 200);
    }

    /**
     * API to fetch all sub users belonging to a specific owner.
     */
    public function getSubUsersByOwnerApi(Request $request)
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

        // Fetch sub users belonging to this owner
        $subUsers = SubUser::where('owner_id', $request->owner_id)->latest()->get();

        return response()->json([
            'success' => true,
            'sub_users' => $subUsers
        ], 200);
    }

    /**
     * Generate a unique 6-digit login code.
     */
    private function generateUniqueLoginCode()
    {
        do {
            $code = mt_rand(100000, 999999);
        } while (SubUser::where('login_code', $code)->exists());

        return $code;
    }
}
