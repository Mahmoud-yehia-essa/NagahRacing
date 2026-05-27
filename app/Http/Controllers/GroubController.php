<?php

namespace App\Http\Controllers;

use App\Models\Groub;
use App\Models\GroupUser;
use App\Models\Nomination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GroubController extends Controller
{
     public function allGroub()
    {
        $groub = Groub::latest()->get();


        return view('admin.groub.all_groub',compact('groub'));
    }

      public function groubInactive($id){
        Groub::findOrFail($id)->update(['status' => 'inactive']);
        $notification = array(
            'message' => ' غير مفعل',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method
      public function groubActive($id){
        Groub::findOrFail($id)->update(['status' => 'active']);
        $notification = array(
            'message' => 'مفعل',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }




//     public function groubDetails($id)
// {

//     $groub = Groub::with([
//         'user',
//         'groupUsers.user',
//         'festivals'
//     ])->findOrFail($id);

//     return view('admin.groub.details_groub',compact('groub'));

// }

public function groubDetails($id)
{
    $groub = Groub::with([
        'user',
        'groupUsers.user',
        'festival'
    ])->findOrFail($id);

    // جلب الترشيحات للأعضاء في المجموعة فقط
    // $nominations = Nomination::with(['user', 'camal', 'round'])
    //     ->where('festival_id', $groub->festival_id)
    //     ->whereIn('user_id', $groub->groupUsers->pluck('user_id'))
    //     ->orderByDesc('is_winner') // إذا تريد ترتيب حسب الفائزين أولاً
    //     ->get();

    // $nominations = Nomination::with(['user', 'camelRoundParticipation.camal', 'round'])
    // ->where('festival_id', $groub->festival_id)
    // ->whereIn('user_id', $groub->groupUsers->pluck('user_id'))
    // ->get()
    // ->sortByDesc('points') // درجات من الأعلى
    // ->sortBy('user.fname'); // ترتيب أبجدي بعد النقاط

    // $nominations = Nomination::with(['user', 'camelRoundParticipation.camal', 'round'])
    // ->where('festival_id', $groub->festival_id)
    // ->whereIn('user_id', $groub->groupUsers->pluck('user_id'))
    // ->get()
    // ->groupBy('user_id') // تجميع حسب العضو
    // ->map(function($userNoms) {
    //     $firstNom = $userNoms->first(); // نأخذ بيانات العضو من أول ترشيح
    //     return (object) [
    //         'user' => $firstNom->user,
    //         'points' => $userNoms->sum('points'), // مجموع النقاط لكل الترشيحات
    //         'is_winner' => $userNoms->contains(fn($n) => $n->is_winner), // إذا فاز في أي شوط
    //         'nominations' => $userNoms, // يمكنك استخدامها لاحقاً إذا احتجت التفاصيل
    //     ];
    // })
    // ->sortByDesc('points') // ترتيب حسب مجموع النقاط
    // ->sortBy('user.fname'); // ترتيب أبجدياً إذا تساوت النقاط

    $nominations = Nomination::with(['user', 'camelRoundParticipation.camal.user', 'round'])
    ->where('festival_id', $groub->festival_id)
    ->whereIn('user_id', $groub->groupUsers->pluck('user_id'))
    ->get()
    ->groupBy('user_id')
    ->map(function($userNoms) {
        $firstNom = $userNoms->first();
        return (object) [
            'user' => $firstNom->user,
            'points' => $userNoms->sum('points'), // مجموع النقاط
            'nominations' => $userNoms,           // كل الترشيحات لتفاصيل الـ Modal
        ];
    })
    ->sortByDesc('points');

    return view('admin.groub.details_groub', compact('groub', 'nominations'));
}

    public function deleteGroub($id){
        $groub = Groub::findOrFail($id);
        $img = $groub->photo;

        // unlink($img );

        if ($groub->photo && file_exists(public_path($groub->photo))) {
            unlink(public_path($groub->photo));
        }
        Groub::findOrFail($id)->delete();
        $notification = array(
            'message' => 'تم حذف المجموعة',
            'alert-type' => 'success'
        );
        return redirect()->route('all.groub')->with($notification);

        // return redirect()->back()->with($notification);
    }// End Method

    // API
//     public function createGroup(Request $request)
// {

// $validator = Validator::make($request->all(), [

// 'user_id' => 'required|exists:users,id',
// 'festival_id' => 'required|exists:festivals,id',
// 'name' => 'required|string|max:255',
// 'des' => 'nullable|string',
// // 'photo' => 'nullable|image|mimes:jpg,jpeg,png',

// ]);

// if ($validator->fails()) {

// return response()->json([
// 'status' => false,
// 'message' => $validator->errors()
// ], 422);

// }


// $photoPath = null;

// if ($request->hasFile('photo')) {

// $file = $request->file('photo');

// $filename = time().'_'.$file->getClientOriginalName();

// $file->move(public_path('upload/groups'), $filename);

// $photoPath = 'upload/groups/'.$filename;

// }


// $group = Groub::create([

// 'user_id' => $request->user_id,
// 'festival_id' => $request->festival_id,
// 'name' => $request->name,
// 'des' => $request->des,
// 'code_number' => rand(100000,999999),
// 'photo' => $photoPath,

// ]);


// return response()->json([

// 'status' => true,
// 'message' => 'تم إنشاء المجموعة بنجاح',
// 'data' => $group

// ]);

// }



/*
public function createGroup(Request $request)
{

$group = Groub::create([

'user_id' => $request->user_id,
'festival_id' => $request->festival_id,
'name' => $request->name,
'des' => $request->des,
'code_number' => Str::upper(Str::random(6)),
'photo' => $request->photo ?? null,

]);

$group->load(['user','festival']);

return response()->json([

'status' => true,
'message' => 'تم إنشاء المجموعة بنجاح',
'data' => $group

]);

}
*/

 public function createGroup(Request $request)
{
    $group = Groub::create([
        'user_id' => $request->user_id,
        'festival_id' => $request->festival_id,
        'status' => $request->status,

        'name' => $request->name,
        'des' => $request->des,
        'code_number' => Str::upper(Str::random(6)),
        'photo' => $request->photo ?? null,
    ]);

    // 👇 إضافة صاحب المجموعة كعضو (Owner)
    $member = GroupUser::create([
        'groub_id' => $group->id,
        'user_id' => $request->user_id,
        'is_owner_group' => 1
    ]);

    $group->load(['user','festival']);
    $member->load('user');

    return response()->json([
        'status' => true,
        'message' => 'تم إنشاء المجموعة بنجاح',
        'data' => $group,
        'member' => $member
    ]);
}

// public function getGroupByCode(Request $request)
// {
//     $request->validate([
//         'code_number' => 'required'
//     ]);

//     $group = Groub::where('code_number', $request->code_number)
//         ->with(['user', 'festival'])
//         ->first();

//     if (!$group) {
//         return response()->json([
//             'status' => false,
//             'message' => 'المجموعة غير موجودة'
//         ], 404);
//     }

//     // 👇 نجيب صاحب المجموعة (owner)
//     $member = GroupUser::where('groub_id', $group->id)
//         ->where('is_owner_group', 1)
//         ->with('user')
//         ->first();

//     return response()->json([
//         'status' => true,
//         'message' => 'تم جلب بيانات المجموعة بنجاح',
//         'data' => $group,
//         'member' => $member
//     ]);
// }


public function getGroupByCode(Request $request)
{
    $request->validate([
        'code_number' => 'required'
    ]);

    $group = Groub::where('code_number', $request->code_number)
        ->with(['user', 'festival'])
        ->withCount('groupUsers')
        ->first();

    if (!$group) {
        return response()->json([
            'status' => false,
            'message' => 'المجموعة غير موجودة'
        ], 200);
    }

    $member = GroupUser::where('groub_id', $group->id)
        ->where('is_owner_group', 1)
        ->with('user')
        ->first();

    return response()->json([
        'status' => true,
        'message' => 'تم جلب بيانات المجموعة بنجاح',
        'data' => array_merge($group->toArray(), [
            'members_count' => $group->group_users_count
        ]),
        'member' => $member
    ]);
}




// public function joinGroup(Request $request)
// {

// $request->validate([
// 'user_id' => 'required|exists:users,id',
// 'code_number' => 'required'
// ]);

// $group = Groub::where('code_number',$request->code_number)->first();

// if(!$group){

// return response()->json([
// 'status'=>false,
// 'message'=>'كود المجموعة غير صحيح'
// ],404);

// }

// $exists = GroupUser::where('groub_id',$group->id)
//                    ->where('user_id',$request->user_id)
//                    ->exists();

// if($exists){

// return response()->json([
// 'status'=>false,
// 'message'=>'المستخدم مشترك بالفعل في المجموعة'
// ]);

// }

// $member = GroupUser::create([

// 'groub_id'=>$group->id,
// 'user_id'=>$request->user_id

// ]);

// return response()->json([

// 'status'=>true,
// 'message'=>'تم الانضمام للمجموعة بنجاح',
// 'group'=>$group,
// 'member'=>$member

// ]);

// }


public function joinGroup(Request $request)
{

$request->validate([
'user_id' => 'required|exists:users,id',
'code_number' => 'required'
]);

$group = Groub::where('code_number',$request->code_number)->first();

if(!$group){

return response()->json([
'status'=>false,
'message'=>'كود المجموعة غير صحيح'
],404);

}

$exists = GroupUser::where('groub_id',$group->id)
                   ->where('user_id',$request->user_id)
                   ->exists();

if($exists){

return response()->json([
'status'=>false,
'message'=>'المستخدم مشترك بالفعل في المجموعة'
]);

}

$member = GroupUser::create([

'groub_id'=>$group->id,
'user_id'=>$request->user_id,
'is_owner_group'=>$request->is_owner_group

]);

$member->load('user');

return response()->json([

'status'=>true,
'message'=>'تم الانضمام للمجموعة بنجاح',
'group'=>$group,
'member'=>$member

]);

}



public function myGroups(Request $request)
{

$request->validate([
'user_id' => 'required|exists:users,id'
]);

$groups = GroupUser::with([
'groub.user:id,fname,lname',
'groub.festival:id,name'
])
->where('user_id',$request->user_id)
->whereHas('groub', function ($query) {
    $query->where('status', '!=', 'archived');
})
->get()
->map(function($item) use ($request){

$group = $item->groub;

return [

'id' => $group->id,
'name' => $group->name,
'photo' => $group->photo,
'code_number' => $group->code_number,
'status' => $group->status,


'festival' => $group->festival->name ?? null,

'owner' => $group->user->fname.' '.$group->user->lname,

'members_count' => $group->groupUsers()->count(),

'is_owner' => $group->user_id == $request->user_id ? true : false

];

});

return response()->json([

'status'=>true,
'groups'=>$groups

]);

}


public function myCreatedGroups(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id'
    ]);

    // جلب كل المجموعات التي أنشأها المستخدم
    $groups = Groub::with([
        'festival:id,name',
        'groupUsers' // لحساب عدد الأعضاء
    ])
    ->where('user_id', $request->user_id)
    ->get()
    ->map(function($group){

        return [
            'id' => $group->id,
            'name' => $group->name,
            'photo' => $group->photo,
            'code_number' => $group->code_number,
            'festival' => $group->festival->name ?? null,
            'members_count' => $group->groupUsers()->count(),
        ];
    });

    return response()->json([
        'status' => true,
        'groups' => $groups
    ]);
}

/*
public function groubDetailsApi(Request $request)
{
    $request->validate([
        'id' => 'required|exists:groubs,id'
    ]);

    $groub = Groub::with([
        'user:id,fname,lname,email,photo',
        'groupUsers.user:id,fname,lname,email,photo',
        'festival:id,name'
    ])->findOrFail($request->id);

    $nominations = Nomination::with(['user:id,fname,lname,email,photo', 'camelRoundParticipation.camal'])
        ->where('festival_id', $groub->festival_id)
        ->whereIn('user_id', $groub->groupUsers->pluck('user_id'))
        ->get()
        ->groupBy('user_id')
        ->map(function($userNoms) {
            $firstNom = $userNoms->first();
            return [
                'user' => [
                    'id' => $firstNom->user->id,
                    'fname' => $firstNom->user->fname,
                    'lname' => $firstNom->user->lname,
                    'email' => $firstNom->user->email,
                    'photo' => $firstNom->user->photo,
                ],
                'points' => $userNoms->sum('points'),
                'nominations' => $userNoms->map(function($nom){
                    return [
                        'id' => $nom->id,
                        'round_name' => $nom->round->name ?? null,
                        'points' => $nom->points,
                        'camel_name' => $nom->camelRoundParticipation?->camal?->name ?? null,
                        'owner_name' => $nom->camelRoundParticipation?->camal?->owner?->name ?? null,
                        'is_winner' => $nom->is_winner,
                        'created_at' => $nom->created_at?->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        })
        ->sortByDesc('points')
        ->values();

    return response()->json([
        'status' => true,
        'groub' => [
            'id' => $groub->id,
            'name' => $groub->name,
            'photo' => $groub->photo,
            'code_number' => $groub->code_number,
            'description' => $groub->des,
            'owner' => [
                'id' => $groub->user->id,
                'fname' => $groub->user->fname,
                'lname' => $groub->user->lname,
                'email' => $groub->user->email,
                'photo' => $groub->user->photo,
            ],
            'festival' => [
                'id' => $groub->festival->id ?? null,
                'name' => $groub->festival->name ?? null,
            ],
            'members_count' => $groub->groupUsers->count(),
        ],
        'leaderboard' => $nominations,
    ]);
}
    */


// public function groubDetailsApi(Request $request)
// {
//     $request->validate([
//         'id' => 'required|exists:groubs,id'
//     ]);

//     $groub = Groub::with([
//         'user:id,fname,lname,email,photo',
//         'groupUsers.user:id,fname,lname,email,photo',
//         'festival:id,name'
//     ])->findOrFail($request->id);

//     // كل أعضاء الجروب
//     $members = $groub->groupUsers;

//     $leaderboard = $members->map(function ($member) use ($groub) {

//         // كل الترشيحات الخاصة بالعضو
//         $userNoms = Nomination::with([
//                 'user:id,fname,lname,email,photo',
//                 'camelRoundParticipation.camal',
//                 'round'
//             ])
//             ->where('festival_id', $groub->festival_id)
//             ->where('user_id', $member->user_id)
//             ->get();

//         // إخفاء الترشيحات اللي is_winner = 0
//         $visibleNoms = $userNoms->where('is_winner', '!=', 0);

//         return [
//             'user' => [
//                 'id' => $member->user->id,
//                 'fname' => $member->user->fname,
//                 'lname' => $member->user->lname,
//                 'email' => $member->user->email,
//                 'photo' => $member->user->photo,
//             ],
//             'points' => $visibleNoms->sum('points'),
//             'nominations' => $visibleNoms->values()->map(function ($nom) {
//                 return [
//                     'id' => $nom->id,
//                     'round_name' => $nom->round->name ?? null,
//                     'points' => $nom->points,
//                     'camel_name' => $nom->camelRoundParticipation?->camel_name ?? null,
//                     'owner_name' => $nom->camelRoundParticipation?->camel_owner_name ?? null,
//                     'is_winner' => $nom->is_winner,
//                     'created_at' => $nom->created_at?->format('Y-m-d H:i:s'),
//                     'created_at_human' => $nom->created_at
//                         ? $nom->created_at->diffForHumans()
//                         : null,
//                 ];
//             }),
//         ];
//     })
//     ->sortByDesc('points')
//     ->values();

//     return response()->json([
//         'status' => true,
//         'groub' => [
//             'id' => $groub->id,
//             'name' => $groub->name,
//             'photo' => $groub->photo,
//             'code_number' => $groub->code_number,
//             'description' => $groub->des,
//             'owner' => [
//                 'id' => $groub->user->id,
//                 'fname' => $groub->user->fname,
//                 'lname' => $groub->user->lname,
//                 'email' => $groub->user->email,
//                 'photo' => $groub->user->photo,
//             ],
//             'festival' => [
//                 'id' => $groub->festival->id ?? null,
//                 'name' => $groub->festival->name ?? null,
//             ],
//             'members_count' => $groub->groupUsers->count(),
//         ],
//         'leaderboard' => $leaderboard,
//     ]);
// }

// public function groubDetailsApi(Request $request)
// {
//     $request->validate([
//         'id' => 'required|exists:groubs,id',
//         'user_id' => 'required|exists:users,id',
//     ]);

//     $currentUserId = $request->user_id;

//     $groub = Groub::with([
//         'user:id,fname,lname,email,photo',
//         'groupUsers.user:id,fname,lname,email,photo',
//         'festival:id,name'
//     ])->findOrFail($request->id);

//     $members = $groub->groupUsers;

//     $leaderboard = $members->map(function ($member) use ($groub, $currentUserId) {

//         $userNoms = Nomination::with([
//                 'camelRoundParticipation.camal',
//                 'round'
//             ])
//             ->where('festival_id', $groub->festival_id)
//             ->where('user_id', $member->user_id)
//             ->get();

//         // إخفاء الترشيحات الغير فائزة
//         $visibleNoms = $userNoms->where('is_winner', '!=', 0);

//         return [
//             'user' => [
//                 'id' => $member->user->id,
//                 'fname' => $member->user->fname,
//                 'lname' => $member->user->lname,
//                 'email' => $member->user->email,
//                 'photo' => $member->user->photo,
//             ],

//             // ⭐ تحديد هل هذا هو المستخدم الحالي
//             'is_me' => $member->user->id == $currentUserId,

//             'points' => $visibleNoms->sum('points'),

//             'nominations' => $visibleNoms->values()->map(function ($nom) {
//                 return [
//                     'id' => $nom->id,
//                     'round_name' => $nom->round->name ?? null,
//                     'points' => $nom->points,
//                     'camel_name' => $nom->camelRoundParticipation?->camel_name ?? null,
//                     'owner_name' => $nom->camelRoundParticipation?->camel_owner_name ?? null,
//                     'is_winner' => $nom->is_winner,
//                     'created_at' => $nom->created_at?->format('Y-m-d H:i:s'),
//                     'created_at_human' => $nom->created_at
//                         ? $nom->created_at->diffForHumans()
//                         : null,
//                 ];
//             }),
//         ];
//     })
//     ->sortByDesc('points')
//     ->values()
//     ->map(function ($item, $index) {
//         // ⭐ إضافة الترتيب
//         $item['rank'] = $index + 1;
//         return $item;
//     });

//     return response()->json([
//         'status' => true,
//         'groub' => [
//             'id' => $groub->id,
//             'name' => $groub->name,
//             'photo' => $groub->photo,
//             'code_number' => $groub->code_number,
//             'description' => $groub->des,
//             'owner' => [
//                 'id' => $groub->user->id,
//                 'fname' => $groub->user->fname,
//                 'lname' => $groub->user->lname,
//                 'email' => $groub->user->email,
//                 'photo' => $groub->user->photo,
//             ],
//             'festival' => [
//                 'id' => $groub->festival->id ?? null,
//                 'name' => $groub->festival->name ?? null,
//             ],
//             'members_count' => $groub->groupUsers->count(),
//         ],

//         'leaderboard' => $leaderboard,

//         // ⭐ بيانات المستخدم الحالي بشكل مباشر
//         'my_data' => $leaderboard->firstWhere('is_me', true),
//     ]);
// }

public function groubDetailsApi(Request $request)
{
    $request->validate([
        'id' => 'required|exists:groubs,id',
        'user_id' => 'required|exists:users,id',
    ]);

    $currentUserId = $request->user_id;

    $groub = Groub::with([
        'user:id,fname,lname,email,photo,country_flag',
        'groupUsers.user:id,fname,lname,email,photo,country_flag',
        'festival:id,name'
    ])->findOrFail($request->id);

    $members = $groub->groupUsers;

    $leaderboard = $members->map(function ($member) use ($groub, $currentUserId) {

        $userNoms = Nomination::with([
                'camelRoundParticipation.camal',
                'round'
            ])
            ->where('festival_id', $groub->festival_id)
            ->where('user_id', $member->user_id)
            ->get();

        // إخفاء الترشيحات الغير فائزة
        $visibleNoms = $userNoms->where('is_winner', '!=', 0);

        return [
            'user' => [
                'id' => $member->user->id,
                'fname' => $member->user->fname,
                'lname' => $member->user->lname,
                'email' => $member->user->email,
                'photo' => $member->user->photo,
                'country_flag' => $member->user->country_flag,


            ],

            // تحديد هل هذا هو المستخدم الحالي
            'is_me' => $member->user->id == $currentUserId,

            'points' => $visibleNoms->sum('points'),

            'nominations' => $visibleNoms->values()->map(function ($nom) {
                return [
                    'id' => $nom->id,
                    'round_name' => $nom->round->name ?? null,
                    'points' => $nom->points,
                    'camel_name' => $nom->camelRoundParticipation?->camel_name ?? null,
                    'camel_age' => $nom->camelRoundParticipation?->camel_age_name ?? null,

                    'owner_name' => $nom->camelRoundParticipation?->camel_owner_name ?? null,
                    'is_winner' => $nom->is_winner,
                    'created_at' => $nom->created_at?->format('Y-m-d H:i:s'),
                    'created_at_human' => $nom->created_at
                        ? $nom->created_at->diffForHumans()
                        : null,
                ];
            }),
        ];
    })
    ->sortByDesc('points')
    ->values()
    ->map(function ($item, $index) {
        $item['rank'] = $index + 1; // إضافة الترتيب
        return $item;
    });

    // بيانات المستخدم الحالي
    $myData = $leaderboard->firstWhere('is_me', true);

    return response()->json([
        'status' => true,

        'groub' => [
            'id' => $groub->id,
            'name' => $groub->name,
            'photo' => $groub->photo,
            'code_number' => $groub->code_number,
            'description' => $groub->des,
            'owner' => [
                'id' => $groub->user->id,
                'fname' => $groub->user->fname,
                'lname' => $groub->user->lname,
                'email' => $groub->user->email,
                'photo' => $groub->user->photo,
                'country_flag' => $groub->user->country_flag,


            ],
            'festival' => [
                'id' => $groub->festival->id ?? null,
                'name' => $groub->festival->name ?? null,
            ],
            'members_count' => $groub->groupUsers->count(),
        ],

        'leaderboard' => $leaderboard,

        // ⭐ بيانات وترتيب المستخدم الحالي
        'my_data' => $myData,
        'my_rank' => $myData['rank'] ?? null,
        'my_points' => $myData['points'] ?? 0,
    ]);
}

    public function updateGroupStatus(Request $request)
    {
        $request->validate([
            'groub_id' => 'required|exists:groubs,id',
            'is_open' => 'required|in:0,1',
        ]);

        $group = Groub::findOrFail($request->groub_id);
        $group->update([
            'is_open' => $request->is_open
        ]);

        return response()->json([
            'status' => true,
            'message' => $request->is_open == 1 ? 'تم فتح المجموعة بنجاح' : 'تم غلق المجموعة بنجاح',
            'data' => $group
        ]);
    }

    public function changeGroupStatusApi(Request $request)
    {
        $request->validate([
            'groub_id' => 'required|exists:groubs,id',
            'status' => 'required|in:active,inactive,closed,archived',
        ]);

        $group = Groub::findOrFail($request->groub_id);
        $group->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تغيير حالة المجموعة بنجاح',
            'data' => $group
        ]);
    }

    public function removeMemberApi(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'groub_id' => 'required|exists:groubs,id',
        ]);

        GroupUser::where('groub_id', $request->groub_id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف المستخدم من المجموعة بنجاح'
        ]);
    }

    public function editGroupApi(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:groubs,id',
            'name' => 'required|string|max:255',
        ]);

        $group = Groub::findOrFail($request->id);

        $group->name = $request->name;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($group->photo) {
                @unlink(public_path('upload/group/'.$group->photo));
            }
            $filename = 'app-'.date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/group'),$filename);
            
            $group->photo = $filename;
        } else if ($request->has('photo') && !empty($request->photo)) {
            $group->photo = $request->photo;
        }

        $group->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تعديل المجموعة بنجاح',
            'data' => $group
        ]);
    }

}
