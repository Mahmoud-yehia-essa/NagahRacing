<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Google_Client;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Str;          // 👈 استدعاء أداة النصوص (حل مشكلتك)

// use PragmaRX\Countries\Package\Countries;




class UserController extends Controller
{
    /**
     * التحقق من توكن جوجل باستخدام مكتبة google/apiclient بدون فايربيز
     */
    protected function verifyGoogleToken($token)
    {
        if (empty($token)) {
            return null;
        }

        // 1. التحقق عبر مكتبة Google_Client الرسمية
        try {
            $client = new \Google_Client();
            $clientId = config('services.google.client_id') ?? env('GOOGLE_CLIENT_ID');
            if (!empty($clientId)) {
                $client->setClientId($clientId);
            }
            $payload = $client->verifyIdToken($token);
            if ($payload && !empty($payload['email'])) {
                return [
                    'id'          => $payload['sub'] ?? null,
                    'email'       => $payload['email'],
                    'name'        => $payload['name'] ?? null,
                    'given_name'  => $payload['given_name'] ?? null,
                    'family_name' => $payload['family_name'] ?? null,
                    'picture'     => $payload['picture'] ?? null,
                ];
            }
        } catch (\Exception $e) {}

        // 2. التحقق المباشر من Google tokeninfo (في حال كان التوكن id_token)
        try {
            $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $token,
            ]);
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['email'])) {
                    return [
                        'id'          => $data['sub'] ?? null,
                        'email'       => $data['email'],
                        'name'        => $data['name'] ?? null,
                        'given_name'  => $data['given_name'] ?? null,
                        'family_name' => $data['family_name'] ?? null,
                        'picture'     => $data['picture'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {}

        // 3. التحقق في حال كان التوكن access_token
        try {
            $response = Http::timeout(10)->withToken($token)->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['email'])) {
                    return [
                        'id'          => $data['sub'] ?? null,
                        'email'       => $data['email'],
                        'name'        => $data['name'] ?? null,
                        'given_name'  => $data['given_name'] ?? null,
                        'family_name' => $data['family_name'] ?? null,
                        'picture'     => $data['picture'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * التحقق من توكن آبل (Apple Identity Token) بدون فايربيز
     */
    protected function verifyAppleToken($token)
    {
        if (empty($token)) {
            return null;
        }

        try {
            // جلب المفاتيح العامة لآبل وتخزينها في الكاش لمدة 24 ساعة
            $keys = \Illuminate\Support\Facades\Cache::remember('apple_public_keys', 86400, function () {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://appleid.apple.com/auth/keys');
                if ($response->successful()) {
                    return $response->json();
                }
                return null;
            });

            if (!$keys || empty($keys['keys'])) {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://appleid.apple.com/auth/keys');
                if ($response->successful()) {
                    $keys = $response->json();
                }
            }

            if (!$keys || empty($keys['keys'])) {
                \Illuminate\Support\Facades\Log::error('تعذر جلب المفاتيح العامة لشركة آبل.');
                return null;
            }

            // تحليل المفاتيح بصيغة JWK
            $parsedKeys = \Firebase\JWT\JWK::parseKeySet($keys, 'RS256');

            // فك التشفير والتحقق من صحة التوقيع وصلاحية التوكن
            $decoded = \Firebase\JWT\JWT::decode($token, $parsedKeys);

            // التحقق من جهة الإصدار
            if (!isset($decoded->iss) || $decoded->iss !== 'https://appleid.apple.com') {
                \Illuminate\Support\Facades\Log::error('Apple token invalid issuer: ' . ($decoded->iss ?? 'null'));
                return null;
            }

            return [
                'id'             => $decoded->sub ?? null,
                'email'          => $decoded->email ?? null,
                'email_verified' => $decoded->email_verified ?? false,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Apple Token Verification Error: ' . $e->getMessage());
            return null;
        }
    }

    public function getAllUsers()
    {
        // $users = User::latest()->get();
        // $users = User::where('role', '!=', 'admin')->latest()->get();
        $users = User::where('role', 'user')->latest()->get();


        return view('admin.users.all_users',compact('users'));


    }


      public function getAllOwners()
    {
        // $users = User::latest()->get();
        $users = User::where('role', 'owner')->latest()->get();


        return view('admin.users.all_owners',compact('users'));


    }



      public function getAllAdmin()
    {
        // $users = User::latest()->get();
        $users = User::where('role', 'admin')->latest()->get();


        return view('admin.users.all_admin',compact('users'));


    }





    // public function addUser()
    // {


    //     $countryList = $countries->all()->map(function ($country) {
    //     return [
    //         'name'  => $country->name['common'] ?? '',
    //         'code'  => $country->cca2,  // ISO Alpha-2 (e.g. KW, SA)
    //         'dial'  => $country->callingCodes[0] ?? '',
    //         'flag'  => $country->flag['emoji'] ?? '',
    //     ];
    // })->filter(fn($c) => !empty($c['dial'])) // keep only countries with dial codes
    //   ->values();

    //     return view('admin.users.add_user',compact('countryList'));


    // }



   public static $countryList = [
    // دول الخليج أولاً
    ['name' => 'Kuwait', 'code' => 'KW', 'dial' => '+965', 'flag' => '🇰🇼'],
    ['name' => 'Saudi Arabia', 'code' => 'SA', 'dial' => '+966', 'flag' => '🇸🇦'],
    ['name' => 'United Arab Emirates', 'code' => 'AE', 'dial' => '+971', 'flag' => '🇦🇪'],
    ['name' => 'Qatar', 'code' => 'QA', 'dial' => '+974', 'flag' => '🇶🇦'],
    ['name' => 'Oman', 'code' => 'OM', 'dial' => '+968', 'flag' => '🇴🇲'],
    ['name' => 'Bahrain', 'code' => 'BH', 'dial' => '+973', 'flag' => '🇧🇭'],

    // الدول العربية الأخرى
    ['name' => 'Egypt', 'code' => 'EG', 'dial' => '+20', 'flag' => '🇪🇬'],
    ['name' => 'Iraq', 'code' => 'IQ', 'dial' => '+964', 'flag' => '🇮🇶'],
    ['name' => 'Jordan', 'code' => 'JO', 'dial' => '+962', 'flag' => '🇯🇴'],
    ['name' => 'Lebanon', 'code' => 'LB', 'dial' => '+961', 'flag' => '🇱🇧'],
    ['name' => 'Syria', 'code' => 'SY', 'dial' => '+963', 'flag' => '🇸🇾'],
    ['name' => 'Yemen', 'code' => 'YE', 'dial' => '+967', 'flag' => '🇾🇪'],
    ['name' => 'Algeria', 'code' => 'DZ', 'dial' => '+213', 'flag' => '🇩🇿'],
    ['name' => 'Morocco', 'code' => 'MA', 'dial' => '+212', 'flag' => '🇲🇦'],
    ['name' => 'Tunisia', 'code' => 'TN', 'dial' => '+216', 'flag' => '🇹🇳'],
    ['name' => 'Libya', 'code' => 'LY', 'dial' => '+218', 'flag' => '🇱🇾'],
    ['name' => 'Sudan', 'code' => 'SD', 'dial' => '+249', 'flag' => '🇸🇩'],

    // باقي العالم (أبجديًا)
    ['name' => 'Afghanistan', 'code' => 'AF', 'dial' => '+93', 'flag' => '🇦🇫'],
    ['name' => 'Albania', 'code' => 'AL', 'dial' => '+355', 'flag' => '🇦🇱'],
    ['name' => 'Andorra', 'code' => 'AD', 'dial' => '+376', 'flag' => '🇦🇩'],
    ['name' => 'Angola', 'code' => 'AO', 'dial' => '+244', 'flag' => '🇦🇴'],
    ['name' => 'Argentina', 'code' => 'AR', 'dial' => '+54', 'flag' => '🇦🇷'],
    ['name' => 'Armenia', 'code' => 'AM', 'dial' => '+374', 'flag' => '🇦🇲'],
    ['name' => 'Australia', 'code' => 'AU', 'dial' => '+61', 'flag' => '🇦🇺'],
    ['name' => 'Austria', 'code' => 'AT', 'dial' => '+43', 'flag' => '🇦🇹'],
    ['name' => 'Azerbaijan', 'code' => 'AZ', 'dial' => '+994', 'flag' => '🇦🇿'],
    ['name' => 'Bangladesh', 'code' => 'BD', 'dial' => '+880', 'flag' => '🇧🇩'],
    ['name' => 'Belarus', 'code' => 'BY', 'dial' => '+375', 'flag' => '🇧🇾'],
    ['name' => 'Belgium', 'code' => 'BE', 'dial' => '+32', 'flag' => '🇧🇪'],
    ['name' => 'Bhutan', 'code' => 'BT', 'dial' => '+975', 'flag' => '🇧🇹'],
    ['name' => 'Bolivia', 'code' => 'BO', 'dial' => '+591', 'flag' => '🇧🇴'],
    ['name' => 'Brazil', 'code' => 'BR', 'dial' => '+55', 'flag' => '🇧🇷'],
    ['name' => 'Bulgaria', 'code' => 'BG', 'dial' => '+359', 'flag' => '🇧🇬'],
    ['name' => 'Canada', 'code' => 'CA', 'dial' => '+1', 'flag' => '🇨🇦'],
    ['name' => 'China', 'code' => 'CN', 'dial' => '+86', 'flag' => '🇨🇳'],
    ['name' => 'France', 'code' => 'FR', 'dial' => '+33', 'flag' => '🇫🇷'],
    ['name' => 'Germany', 'code' => 'DE', 'dial' => '+49', 'flag' => '🇩🇪'],
    ['name' => 'India', 'code' => 'IN', 'dial' => '+91', 'flag' => '🇮🇳'],
    ['name' => 'Italy', 'code' => 'IT', 'dial' => '+39', 'flag' => '🇮🇹'],
    ['name' => 'Japan', 'code' => 'JP', 'dial' => '+81', 'flag' => '🇯🇵'],
    ['name' => 'United States', 'code' => 'US', 'dial' => '+1', 'flag' => '🇺🇸'],
    ['name' => 'United Kingdom', 'code' => 'GB', 'dial' => '+44', 'flag' => '🇬🇧'],
    // يمكنك متابعة إضافة باقي الدول حسب الحاجة…
];

   public function addUser()
{

        $countryList = self::$countryList;



    return view('admin.users.add_user', compact('countryList'));
}






    public function addUserStore(Request $request)
    {


        $request->validate([

                    'role' => 'required|not_in:non',

            'fname' => 'required|string|max:255',
            // 'lname' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email',
            // 'password' => 'required|min:6|confirmed',

                    //   'phone'  => 'required|regex:/^\+?[0-9]{7,15}$/',

                    //   'phone'  => 'required',

'phone' => [
    'required',
    'regex:/^[0-9]+$/',
    'max:15'
],





            // 'password_confirmation' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'fname.required' => 'حقل الاسم  مطلوب.',
            'fname.string' => 'يجب أن يكون الاسم الأول نصًا.',
            'fname.max' => 'يجب ألا يزيد الاسم الأول عن 255 حرفًا.',

            // 'lname.required' => 'حقل اسم العائلة مطلوب.',
            // 'lname.string' => 'يجب أن يكون اسم العائلة نصًا.',
            // 'lname.max' => 'يجب ألا يزيد اسم العائلة عن 255 حرفًا.',

            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب إدخال بريد إلكتروني صالح.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',

            'password.required' => 'حقل كلمة المرور مطلوب.',
            'password.min' => 'يجب أن تكون كلمة المرور على الأقل 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'password_confirmation.required' => 'حقل تأكيد كلمة المرور مطلوب.',



            'photo.image' => 'يجب أن يكون الملف صورة.',
            'photo.mimes' => 'يجب أن تكون الصورة من نوع jpeg أو png أو jpg أو gif.',
            'photo.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميغابايت.',



              'role.required' => 'الرجاء اختيار نوع الحساب.',
        'role.not_in' => 'الرجاء اختيار نوع الحساب.',

             'phone.required' => 'يرجى إدخال رقم الهاتف.',


        'phone.integer' => 'الرجاء ادخال رقم الهاتف',

         'phone.required' => 'يرجى إدخال رقم الهاتف.',
    'phone.regex'    => 'يجب إدخال الأرقام باللغة الإنجليزية فقط.',
    // 'phone.min'      => 'رقم الهاتف يجب أن لا يقل عن 8 أرقام.',
    'phone.max'      => 'رقم الهاتف يجب أن لا يتجاوز 15 رقم.',
        ]);


        $filename = "";
    $countryData = json_decode($request->input('country_data'), true);

    $dialCode = $countryData['dial'] ?? null;
    $countryCode = $countryData['code'] ?? null;
    $flag = $countryData['flag'] ?? null;

    $cName = $countryData['name'] ?? null;



        if ($request->file('photo')) {
            // $file = $request->file('photo');
            // $filename = date('YmdHi').$file->getClientOriginalName();
            // $file->move(public_path('upload/user_images'),$filename);


            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);
        }


        User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,

            'role' => $request->role,


            'email' => $request->email,

                        'country_code' => $dialCode,
                        'country_flag' => $flag,
                        'country_name' => $cName,





            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'photo' => $filename,


        ]);

        $notification = array(
            'message' => 'تمت الاضافة بنجاح',
            'alert-type' => 'success'
        );

        if($request->role === 'user')
        {

                    return redirect()->route('all.users')->with($notification);

        }


        else if($request->role === 'owner')
        {

                    return redirect()->route('all.owners')->with($notification);

        }

        else
        {
                                return redirect()->route('all.admin')->with($notification);

        }



    }





    public function editUser($id)
    {

        $user = User::findOrFail($id);
        $countryList = self::$countryList;






        return view('admin.users.edit_user',compact('user','countryList'));





    }

    public function editUserStore(Request $request)
    {

        $user_id = $request->id;
        $old_img = $request->old_image;
        $old_email = $request->old_email;

        $user = User::findOrFail($user_id);


// Check if the email hasn't changed
if ($old_email == $request->email) {
    // Validate without the unique rule
    $rules = [
         'role' => 'required|not_in:non',

            'fname' => 'required|string|max:255',
            // 'lname' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email',
            // 'password' => 'required|min:6|confirmed',

                    //   'phone'  => 'required|regex:/^\+?[0-9]{7,15}$/',

                    //   'phone'  => 'required',



'phone' => [
    'required',
    'regex:/^[0-9]+$/',
    'max:15'
],





            // 'password_confirmation' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];
} else {
    // Validate with the unique rule for a new email
    $rules = [
         'role' => 'required|not_in:non',

            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email',
            // 'password' => 'required|min:6|confirmed',

                    //   'phone'  => 'required|regex:/^\+?[0-9]{7,15}$/',

                      'phone'  => 'required',







            // 'password_confirmation' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];


     $user->email = $request->email;

}



$request->validate($rules, [
     'fname.required' => 'حقل الاسم  مطلوب.',
            'fname.string' => 'يجب أن يكون الاسم الأول نصًا.',
            'fname.max' => 'يجب ألا يزيد الاسم الأول عن 255 حرفًا.',

            'lname.required' => 'حقل اسم العائلة مطلوب.',
            'lname.string' => 'يجب أن يكون اسم العائلة نصًا.',
            'lname.max' => 'يجب ألا يزيد اسم العائلة عن 255 حرفًا.',

            'email.required' => 'حقل البريد الإلكتروني مطلوب.',
            'email.email' => 'يجب إدخال بريد إلكتروني صالح.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',

            'password.required' => 'حقل كلمة المرور مطلوب.',
            'password.min' => 'يجب أن تكون كلمة المرور على الأقل 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'password_confirmation.required' => 'حقل تأكيد كلمة المرور مطلوب.',



            'photo.image' => 'يجب أن يكون الملف صورة.',
            'photo.mimes' => 'يجب أن تكون الصورة من نوع jpeg أو png أو jpg أو gif.',
            'photo.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميغابايت.',



              'role.required' => 'الرجاء اختيار نوع الحساب.',
        'role.not_in' => 'الرجاء اختيار نوع الحساب.',

             'phone.required' => 'يرجى إدخال رقم الهاتف.',

    'phone.regex'    => 'يجب إدخال الأرقام باللغة الإنجليزية فقط.',
    // 'phone.min'      => 'رقم الهاتف يجب أن لا يقل عن 8 أرقام.',
    'phone.max'      => 'رقم الهاتف يجب أن لا يتجاوز 15 رقم.',
            //  'phone.regex' => 'صيغة رقم الهاتف غير صحيحة. يرجى إدخال رقم مع رمز الدولة مثل دولة الكويت تبدأ ب ‎+965',


        'phone.integer' => 'الرجاء ادخال رقم الهاتف',
]);


$countryData = json_decode($request->input('country_data'), true);

    $dialCode = $countryData['dial'] ?? null;
    $countryCode = $countryData['code'] ?? null;
    $flag = $countryData['flag'] ?? null;

    $cName = $countryData['name'] ?? null;


        // $filename = "";

        $path = 'upload/user_images/'.$old_img;




        if ($request->file('photo')) {


            $file = $request->file('photo');
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);


            if (file_exists($path) && $old_img != "" ) {
                unlink($path);
             }


             $user->photo = $filename;








        }



        if($request->password != "")
        {

            $user->password = Hash::make($request->password);

        }



        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->country_code = $dialCode;
                $user->country_flag = $flag;
                $user->country_name = $cName;


                $user->role = $request->role;




        // $user->is_game_free = $request->is_game_free;


        $user->save();

        $notification = array(
            'message' => 'تم التعديل',
            'alert-type' => 'success'
        );


         if($request->role === 'user')
        {

                    return redirect()->route('all.users')->with($notification);

        }


        else if($request->role === 'owner')
        {

                    return redirect()->route('all.owners')->with($notification);

        }

        else
        {
                                return redirect()->route('all.admin')->with($notification);

        }

        // return redirect()->route('all.users')->with($notification);















    }
    public function userInactive($id){
        User::findOrFail($id)->update(['status' => 'inactive']);
        $notification = array(
            'message' => 'المستخدم غير مفعل',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method
      public function userActive($id){
        User::findOrFail($id)->update(['status' => 'active']);
        $notification = array(
            'message' => 'المستخدم مفعل',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method



    public function deleteUser($id){
        $user = User::findOrFail($id);
        $img = $user->photo;

        // unlink($img );

      //  return $user->photo;

        $path = 'upload/user_images/'.$user->photo;

        if ($user->photo && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
        User::findOrFail($id)->delete();
        $notification = array(
            'message' => 'تم حذف المستخدم',
            'alert-type' => 'success'
        );
        return redirect()->route('all.users')->with($notification);

        // return redirect()->back()->with($notification);
    }// End Method


    /// API ///




        public function socialLoginApi(Request $request) {
        $token = $request->firebase_token ?? $request->id_token ?? $request->token ?? $request->access_token;
        $provider = $request->provider ?? 'google';

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق مطلوب.'
            ], 422);
        }

        $googleUser = null;
        $appleUser = null;

        if ($provider === 'google') {
            $googleUser = $this->verifyGoogleToken($token);
            if (!$googleUser || empty($googleUser['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق من جوجل غير صالح أو منتهي الصلاحية.'
                ], 401);
            }
            $email = $googleUser['email'];
        } elseif ($provider === 'apple') {
            $appleUser = $this->verifyAppleToken($token);
            if (!$appleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق من آبل غير صالح أو منتهي الصلاحية.'
                ], 401);
            }
            $email = $appleUser['email'] ?? $request->email;
        } else {
            $email = $request->email;
        }

        // البحث عن المستخدم في قاعدة البيانات عبر البريد أو معرف آبل
        $user = null;
        if (!empty($email)) {
            $user = User::where('email', $email)->first();
        }

        if (!$user && $appleUser && !empty($appleUser['id'])) {
            $user = User::where('firebase_token', $appleUser['id'])->first();
        }

        if ($user) {
            // 🟢 المستخدم مسجل مسبقاً: تسجيل الدخول مباشرة كمالك
            if ($user->role !== 'owner') {
                $user->role = 'owner';
                $user->save();
            }
            if (empty($user->photo) && !empty($googleUser['picture'])) {
                $user->photo = $googleUser['picture'];
                $user->save();
            }
            if ($appleUser && empty($user->firebase_token) && !empty($appleUser['id'])) {
                $user->firebase_token = $appleUser['id'];
                $user->save();
            }

            $tokenPlain = $user->createToken('ourapptoken')->plainTextToken;

            return response()->json([
                'success'     => true,
                'is_new_user' => false,
                'message'     => 'Login successful',
                'user'        => $user,
                'token'       => $tokenPlain
            ], 200);

        } else {
            // 🟡 المستخدم غير مسجل: إنشاء حساب مالك جديد + اشتراك في الباقة الافتراضية تلقائياً
            $fullName = $request->fname ? ($request->fname . ' ' . $request->lname) : ($googleUser['name'] ?? '');
            $nameParts = explode(' ', trim($fullName), 2);
            $fname = $request->fname ?? ($googleUser['given_name'] ?? ($nameParts[0] ?? 'مالك'));
            $lname = $request->lname ?? ($googleUser['family_name'] ?? ($nameParts[1] ?? 'جديد'));
            $photo = $request->photo ?? ($googleUser['picture'] ?? null);

            $newUser = User::create([
                'fname'            => $fname,
                'lname'            => $lname,
                'email'            => $email,
                'phone'            => $request->phone ?? null,
                'country_code'     => $request->country_code ?? null,
                'country_flag'     => $request->country_flag ?? null,
                'photo'            => $photo,
                'password'         => Hash::make(Str::random(24)),
                'role'             => 'owner',
                'is_game_free'     => 'paid',
                'provider'         => $provider,
                'firebase_token'   => $appleUser['id'] ?? null,
                'status'           => 'active',
                'otp_verification' => 1,
                'set_password'     => 0,
            ]);

            // الاشتراك التلقائي في الباقة الافتراضية
            $defaultPlan = \App\Models\SubscriptionPlan::where('is_default', true)
                ->where('status', 'active')
                ->first();

            $userSubscription = null;
            if ($defaultPlan) {
                $startDate = now();
                $duration = (int) $defaultPlan->plan_duration;
                $interval = $defaultPlan->plan_interval;

                if ($duration === 0) {
                    $endDate = null;
                } else {
                    $endDate = clone $startDate;
                    if ($interval === 'day') {
                        $endDate->addDays($duration);
                    } elseif ($interval === 'week') {
                        $endDate->addWeeks($duration);
                    } elseif ($interval === 'month') {
                        $endDate->addMonths($duration);
                    } elseif ($interval === 'year') {
                        $endDate->addYears($duration);
                    } else {
                        $endDate->addMonths($duration);
                    }
                }

                do {
                    $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
                } while (\App\Models\UserSubscription::where('transaction_id', $transactionId)->exists());

                $userSubscription = \App\Models\UserSubscription::create([
                    'owner_id'             => $newUser->id,
                    'subscription_plan_id' => $defaultPlan->id,
                    'start_date'           => $startDate,
                    'end_date'             => $endDate,
                    'amount_paid'          => $defaultPlan->price ?? '0.00',
                    'transaction_id'       => $transactionId,
                    'status'               => 'active',
                ]);
            }

            $tokenPlain = $newUser->createToken('ourapptoken')->plainTextToken;

            return response()->json([
                'success'      => true,
                'is_new_user'  => true,
                'message'      => 'تم إنشاء حساب المالك والاشتراك في الباقة الافتراضية بنجاح',
                'user'         => $newUser,
                'token'        => $tokenPlain,
                'default_plan' => $defaultPlan ? [
                    'id'                          => (int) $defaultPlan->id,
                    'name'                        => (string) $defaultPlan->name,
                    'description'                 => $defaultPlan->description ? (string) $defaultPlan->description : null,
                    'price'                       => (string) $defaultPlan->price,
                    'plan_duration'               => (int) $defaultPlan->plan_duration,
                    'plan_interval'               => (string) $defaultPlan->plan_interval,
                    'number_of_sub_users'         => !is_null($defaultPlan->number_of_sub_users) ? (int) $defaultPlan->number_of_sub_users : null,
                    'number_of_training_sessions' => !is_null($defaultPlan->number_of_training_sessions) ? (int) $defaultPlan->number_of_training_sessions : null,
                    'is_trial'                    => (int) ($defaultPlan->is_trial ?? 0),
                    'is_default'                  => 1,
                    'status'                      => (string) $defaultPlan->status,
                ] : null
            ], 200);
        }
    }

public function loginApi(Request $request) {
        $incomingFields = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (auth()->attempt($incomingFields)) {
            $user = auth()->user(); // Get authenticated user
            $token = $user->createToken('ourapptoken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user, // Return all user data
                'token' => $token
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }


    public function registerApi(Request $request) {
        // Check if email already exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists'
            ], 409); // 409 Conflict status code
        }

        // Create user
        $userCreated = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'photo' => $request->photo,
            'is_game_free' => 'paid',


        ]);

        if ($userCreated) {
            $token = $userCreated->createToken('ourapptoken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => $userCreated,
                'token' => $token
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Registration failed'
        ], 500);
    }


 public function checkPhoneNumberExist(Request $request) {


        // Check if email already exists

        if (User::where('phone', $request->phone)->where('country_code', $request->country_code)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'phone already exists'
            ], 200); // 409 Conflict status code
        }
        else
        {
  return response()->json([
                'success' => true,
                'message' => 'phone Not exists Go register'
            ], 200);

        }

    }



    //Important
    //  public function registerApiV2(Request $request) {




    //     // Check if email already exists

    //     if (User::where('phone', $request->phone)->where('country_code', $request->country_code)->exists()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'phone already exists'
    //         ], 409); // 409 Conflict status code
    //     }

    //     // Create user

    //         if (isset($request->otp_verification)) {

    //          $userCreated = User::create([
    //         'fname' => $request->fname,
    //         'lname' => $request->lname,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'country_code' => $request->country_code,
    //                     'country_flag' => $request->country_flag,

    //         'password' => Hash::make($request->password),
    //         'photo' => $request->photo,
    //         'is_game_free' => 'paid',
    //         'otp_verification' => $request->otp_verification,
    //                     'provider' => $request->provider,
    //                                             'firebase_token' => $request->firebase_token,


    //         'set_password' => $request->set_password,



    //     ]);

    //         }

    //         else
    //             {
    //     $userCreated = User::create([
    //         'fname' => $request->fname,
    //         'lname' => $request->lname,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'country_code' => $request->country_code,
    //                     'country_flag' => $request->country_flag,

    //         'password' => Hash::make($request->password),
    //         'photo' => $request->photo,
    //         'is_game_free' => 'paid',


    //     ]);
    //             }

    //     if ($userCreated) {
    //         $token = $userCreated->createToken('ourapptoken')->plainTextToken;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Registration successful',
    //             'user' => $userCreated,
    //             'token' => $token
    //         ], 201);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Registration failed'
    //     ], 500);
    // }

    //End Important





public function registerApiV2(Request $request) {

    // 1. فحص رقم الهاتف
    if (User::where('phone', $request->phone)->where('country_code', $request->country_code)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Phone already exists'
        ], 409);
    }

    // فحص الإيميل لتجنب انهيار قاعدة البيانات (مهم جداً!)
    if ($request->filled('email')) {
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists'
            ], 409);
        }
    }

    $passwordToSave = '';

    // 2. التحقق الأمني لتسجيل الدخول الاجتماعي
    if ($request->filled('provider') && ($request->filled('firebase_token') || $request->filled('token') || $request->filled('id_token'))) {
        $token = $request->firebase_token ?? $request->token ?? $request->id_token;
        if ($request->provider === 'google') {
            $googleUser = $this->verifyGoogleToken($token);
            if (!$googleUser || empty($googleUser['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق من جوجل غير صالح أو منتهي الصلاحية'
                ], 401);
            }
            if ($request->filled('email') && strtolower($googleUser['email']) !== strtolower($request->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات البريد غير متطابقة مع حساب جوجل.'
                ], 403);
            }
        } elseif ($request->provider === 'apple') {
            $appleUser = $this->verifyAppleToken($token);
            if (!$appleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق من آبل غير صالح أو منتهي الصلاحية'
                ], 401);
            }
        }
        $passwordToSave = Hash::make(Str::random(24));
    } else {
        // التسجيل العادي
        $passwordToSave = Hash::make($request->password);
    }

    // 3. تجهيز بيانات المستخدم
    $userData = [
        'fname'        => $request->fname,
        'lname'        => $request->lname,
        // 🟢 التعديل الثاني: إذا كان الإيميل فارغاً، احفظه كـ Null وليس ""
        'email'        => $request->filled('email') ? $request->email : null,
        'phone'        => $request->phone,
        'country_code' => $request->country_code,
        'country_flag' => $request->country_flag,
        'photo'        => $request->photo,
        'password'     => $passwordToSave,
        'is_game_free' => 'paid',
    ];

    if ($request->has('otp_verification')) {
        $userData['otp_verification'] = $request->otp_verification;
        $userData['provider']         = $request->provider;
        $userData['firebase_token']   = $request->firebase_token;
        $userData['set_password']     = $request->set_password;
    }

    // 4. إنشاء المستخدم
    try {
        $userCreated = User::create($userData);

        if ($userCreated) {
            $token = $userCreated->createToken('ourapptoken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user'    => $userCreated,
                'token'   => $token
            ], 201);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء الحفظ في قاعدة البيانات',
            'error_details' => $e->getMessage()
        ], 500);
    }

    return response()->json([
        'success' => false,
        'message' => 'Registration failed for unknown reason'
    ], 500);
}

public function registerOwnerApi(Request $request) {

    // 1. فحص رقم الهاتف
    if (User::where('phone', $request->phone)->where('country_code', $request->country_code)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Phone already exists'
        ], 409);
    }

    // لا تفحص الإيميل إلا إذا كان موجوداً وغير فارغ
    if ($request->filled('email')) {
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists'
            ], 409);
        }
    }

    $passwordToSave = '';

    // 2. التحقق الأمني لتسجيل الدخول الاجتماعي
    if ($request->filled('provider') && ($request->filled('firebase_token') || $request->filled('token') || $request->filled('id_token'))) {
        $token = $request->firebase_token ?? $request->token ?? $request->id_token;
        if ($request->provider === 'google') {
            $googleUser = $this->verifyGoogleToken($token);
            if (!$googleUser || empty($googleUser['email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق من جوجل غير صالح أو منتهي الصلاحية'
                ], 401);
            }
            if ($request->filled('email') && strtolower($googleUser['email']) !== strtolower($request->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات البريد غير متطابقة مع حساب جوجل.'
                ], 403);
            }
        } elseif ($request->provider === 'apple') {
            $appleUser = $this->verifyAppleToken($token);
            if (!$appleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق من آبل غير صالح أو منتهي الصلاحية'
                ], 401);
            }
        }
        $passwordToSave = Hash::make(Str::random(24));
    } else {
        // التسجيل العادي
        $passwordToSave = Hash::make($request->password);
    }

    // 3. تجهيز بيانات المستخدم مع تعيين الدور كـ owner
    $userData = [
        'fname'        => $request->fname,
        'lname'        => $request->lname,
        'email'        => $request->filled('email') ? $request->email : null,
        'phone'        => $request->phone,
        'country_code' => $request->country_code,
        'country_flag' => $request->country_flag,
        'photo'        => $request->photo,
        'password'     => $passwordToSave,
        'role'         => 'owner', // تعيين نوع الحساب كـ مالك
        'is_game_free' => 'paid',
    ];

    if ($request->has('otp_verification')) {
        $userData['otp_verification'] = $request->otp_verification;
        $userData['provider']         = $request->provider;
        $userData['firebase_token']   = $request->firebase_token;
        $userData['set_password']     = $request->set_password;
    }

    // 4. إنشاء المستخدم
    try {
        $userCreated = User::create($userData);

        if ($userCreated) {
            $token = $userCreated->createToken('ourapptoken')->plainTextToken;

            // Auto subscribe to default plan if available
            $defaultPlan = \App\Models\SubscriptionPlan::where('is_default', true)
                ->where('status', 'active')
                ->first();
                
            $userSubscription = null;
            if ($defaultPlan) {
                $startDate = now();
                $duration = (int) $defaultPlan->plan_duration;
                $interval = $defaultPlan->plan_interval;

                if ($duration === 0) {
                    $endDate = null;
                } else {
                    $endDate = clone $startDate;
                    if ($interval === 'day') {
                        $endDate->addDays($duration);
                    } elseif ($interval === 'month') {
                        $endDate->addMonths($duration);
                    } elseif ($interval === 'year') {
                        $endDate->addYears($duration);
                    }
                }

                do {
                    $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
                } while (\App\Models\UserSubscription::where('transaction_id', $transactionId)->exists());

                $userSubscription = \App\Models\UserSubscription::create(['owner_id' => $userCreated->id, 'subscription_plan_id' => $defaultPlan->id, 'start_date' => $startDate, 'end_date' => $endDate, 'amount_paid' => $defaultPlan->price ?? 0.00, 'transaction_id' => $transactionId, 'status' => 'active']);
            }

            return response()->json(['success' => true, 'message' => 'Owner registration successful', 'user' => $userCreated, 'token' => $token, 'default_plan' => $defaultPlan, 'subscription' => $userSubscription], 201);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء الحفظ في قاعدة البيانات',
            'error_details' => $e->getMessage()
        ], 500);
    }

    return response()->json([
        'success' => false,
        'message' => 'Owner registration failed for unknown reason'
    ], 500);
}

public function loginOwnerApi(Request $request)
{
    // Validation
    $incomingFields = $request->validate([
        'phone' => 'required|string',
        'country_code' => 'required|string',
        'password' => 'nullable|string|min:6',
    ]);

    // Find user by phone, country_code, and role 'owner'
    $user = \App\Models\User::where('phone', $incomingFields['phone'])
        ->where('country_code', $incomingFields['country_code'])
        ->where('role', 'owner')
        ->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Owner user not found'
        ], 404);
    }

    // If password is provided, verify it
    if (!empty($incomingFields['password'])) {
        if (!Hash::check($incomingFields['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }
    }

    // Create Sanctum token
    $token = $user->createToken('ourapptoken')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Owner login successful',
        'user' => $user,
        'token' => $token
    ], 200);
}

public function socialLoginOwnerApi(Request $request) {
    return $this->socialLoginApi($request);
}

//     public function loginApiV2(Request $request)
// {


//     if (!empty($request->password)) {

//       $incomingFields = $request->validate([
//              'phone' => 'required|string',
//             'country_code' => 'required|string',
//             'password' => 'required|min:6'
//         ]);

//     }


//     else
//         {

//     $incomingFields = $request->validate([
//         'phone' => 'required|string',
//         'country_code' => 'required|string',
//     ]);

//         }
//     // Find user instead of using auth()->attempt()
//     $user = \App\Models\User::where('phone', $incomingFields['phone'])
//                 ->where('country_code', $incomingFields['country_code'])
//                 ->first();

//     if ($user) {
//         // Create Sanctum token
//         $token = $user->createToken('ourapptoken')->plainTextToken;

//         return response()->json([
//             'success' => true,
//             'message' => 'Login successful',
//             'user' => $user,
//             'token' => $token
//         ], 200);
//     }

//     return response()->json([
//         'success' => false,
//         'message' => 'Invalid credentials'
//     ], 401);
// }

public function loginApiV2(Request $request)
{
    // Validation
    $incomingFields = $request->validate([
        'phone' => 'required|string',
        'country_code' => 'required|string',
        'password' => 'nullable|string|min:6',
    ]);

    // Find user
    $user = \App\Models\User::where('phone', $incomingFields['phone'])
        ->where('country_code', $incomingFields['country_code'])
        ->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    // If password is provided, verify it
    if (!empty($incomingFields['password'])) {
        if (!Hash::check($incomingFields['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }
    }

    // Create Sanctum token
    $token = $user->createToken('ourapptoken')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token
    ], 200);
}


    public function uploadUpadteImageApi(Request $request,$user_id)
    {


        $user = User::find($user_id);

        if ($request->file('photo')) {
            $file = $request->file('photo');
            @unlink(public_path('upload/user_images/'.$user->photo));
            $filename = 'app-'.date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/user_images'),$filename);


            return response()->json(['link' => $filename], 200);

        }

      else {
            return response()->json(['error' => 'Image not provided'], 400);
        }




    }

    public function uploadImageApi(Request $request)
    {



        if ($request->file('photo')) {
            $file = $request->file('photo');
            // @unlink(public_path('upload/user_images/'.$user->photo));
            $filename = 'app-'.date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/user_images'),$filename);


            return response()->json(['link' => $filename], 200);

        }

      else {
            return response()->json(['error' => 'Image not provided'], 400);
        }




    }




      public function uploadImageForGroupApi(Request $request)
    {


        if ($request->file('photo')) {
            $file = $request->file('photo');
            // @unlink(public_path('upload/user_images/'.$user->photo));
            $filename = 'app-'.date('YmdHi').$file->getClientOriginalName();
            $file->move(public_path('upload/group'),$filename);


            return response()->json(['link' => $filename], 200);

        }

      else {
            return response()->json(['error' => 'Image not provided'], 400);
        }




    }


/// editUserApi

    // public function editUserApi(Request $request)
    // {



    //     $user_id = $request->id;

    //     $user = User::findOrFail($user_id);





    //     if($request->password != "")
    //     {

    //         $user->password = Hash::make($request->password);

    //     }


    //     $user->fname = $request->fname;
    //     $user->lname = $request->lname;
    //     $user->phone = $request->phone;
    //     $user->photo = $request->photo;
    //     // $user->address = $request->address;
    //     $user->save();





    //     $token = "Non";

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'updated user successful',
    //         'user' => $user, // Return all user data
    //         'token' => $token
    //     ], 200);







    // }



    public function editUserApi(Request $request)
{
    $request->validate([
        'fname' => ['required', 'regex:/^[\pL\pN\s]+$/u'],
        'lname' => ['required', 'regex:/^[\pL\pN\s]+$/u'],
        'phone' => ['nullable'],
        'password' => ['nullable', 'min:6'],
    ], [
        'fname.regex' => 'الاسم الأول يجب أن يحتوي على حروف وأرقام فقط',
        'lname.regex' => 'الاسم الأخير يجب أن يحتوي على حروف وأرقام فقط',
    ]);

    $user = User::findOrFail($request->id);

    if (!empty($request->password)) {
        $user->password = Hash::make($request->password);
    }

    $user->fname = $request->fname;
    $user->lname = $request->lname;
    $user->phone = $request->phone;
    $user->photo = $request->photo;

    if (!empty($request->set_password))
        {
    $user->set_password = $request->set_password;

        }


    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'updated user successful',
        'user' => $user,
        'token' => 'Non'
    ], 200);
}


    public function getUserByEmail($email)
    {
        $user = User::where('email', $email)->first(); // Returns true or false

        if ($user) {

            $token = $user->createToken('ourapptoken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Email exists',
                'token' => $token,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email not found',
                'token' => 'non',

            ], 404);
        }
    }





    public function editUserPasswordApi(Request $request)
    {

  // Retrieve the token from the request header
  $token = $request->bearerToken();

  if (!$token) {

    return response()->json([
        'success' => false,
        'message' => 'Token not provided',
        'token' => 'non',


    ], 401);
  }

  // Find the token in the database
  $accessToken = PersonalAccessToken::findToken($token);

  if (!$accessToken) {

      return response()->json([
        'success' => false,
        'message' => 'Invalid token',
        'token' => 'non',


    ], 401);
  }



        $country_code = $request->country_code;
                $phone = $request->phone;


        $user = User::where('phone', $phone )->where('country_code', $country_code )->first(); // Returns true or false


        // $user_id = $request->id;


        // $user = User::findOrFail($user_id);







        if($request->password != "")
        {

            $user->password = Hash::make($request->password);

        }



        $user->save();





        $token = "Non";

        return response()->json([
            'success' => true,
            'message' => 'updated password successful',
            'token' => 'non',


        ], 200);







    }



    public function updateUserGamesNumber(Request $request)
    {
        $checkForIncrementGameOrDecrement = $request->checkForIncrementGameOrDecrement;
        $user_id = $request->user_id;
        $numberRequestGame = $request->numberRequestGame;

        $user = User::findOrFail($user_id);
        $numberOfGames = $user->number_of_games;

        if ($checkForIncrementGameOrDecrement == 'increment') {
            $numberOfGames += $numberRequestGame;
            $user->number_of_games = $numberOfGames;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'increment games successful',
                'numberOfGames'=>$user->number_of_games,

            ], 200);
        } elseif ($checkForIncrementGameOrDecrement == 'decrement') {
            $numberOfGames -= $numberRequestGame;
            $user->number_of_games = $numberOfGames;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'decrement games successful',
                'numberOfGames'=>$user->number_of_games,

            ], 200);
        }

        // Fallback response if the value doesn't match 'increment' or 'decrement'
        return response()->json([
            'success' => false,
            'message' => 'Invalid action specified',
        ], 400);
    }


 public function deleteUserApi(Request $request){

    $id = $request->delet_user_id;


        $user = User::findOrFail($id);
        $img = $user->photo;

        // unlink($img );

      //  return $user->photo;

        $path = 'upload/user_images/'.$user->photo;

        if ($user->photo && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
        User::findOrFail($id)->delete();

    return response()->json([
                'success' => true,
                'message' => 'user deleted successful',


            ], 200);

        // return redirect()->back()->with($notification);
    }// End Method


}
