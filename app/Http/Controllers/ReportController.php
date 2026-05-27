<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Game;
use App\Models\User;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;


class ReportController extends Controller
{

    public function ReportView(){
        return view('admin.report.report_view');
    }




    public function SearchByDate(Request $request){


        $request->validate([
            'date' => 'required|date', // مطلوب ويجب أن يكون تاريخًا صالحًا
        ], [
            'date.required' => 'يجب إدخال التاريخ.', // رسالة خطأ عند ترك الحقل فارغًا
            'date.date' => 'يجب إدخال تاريخ صحيح.', // رسالة خطأ إذا لم يكن المدخل تاريخًا صالحًا
        ]);

        $date = new DateTime($request->date);
    $formatDate = $date->format('Y-m-d'); // Ensure format is YYYY-MM-DD

    $users = User::where('role', '!=', 'admin')->whereDate('created_at', $formatDate)->latest()->get();

    $category = Category::whereDate('created_at', $formatDate)->latest()->get();

    $games = Game::whereDate('created_at', $formatDate)->latest()->get();

    $questions = Question::whereDate('created_at', $formatDate)->latest()->get();


    // return $users;

         return view('admin.report.report_by_date',compact('users','formatDate','category','games','questions'));

    }// End Method


    // public function SearchByMonth(Request $request){

    //     // $month = $request->month;
    //     // $year = $request->year_name;

    //     // $orders = Order::where('order_month',$month)->where('order_year',$year)->latest()->get();
    //     // return view('backend.report.report_by_month',compact('orders','month','year'));

    // }// End Method


    public function SearchByMonth(Request $request)
{
    // التحقق من صحة الإدخال
    $request->validate([
        'year_name' => 'required|not_in:non', // السنة مطلوبة ويجب أن تكون من 2000 إلى السنة الحالية
        'month' => 'required|not_in:non', // الشهر مطلوب ويجب أن يكون بين 1 و 12
    ], [
        'month.required' => 'يجب اختيار الشهر.',
        'month.not_in' => 'يجب اختيار الشهر.',
        'year_name.not_in' => 'يجب اختيار السنة.',

        'month.min' => 'الشهر يجب أن يكون بين 1 و 12.',
        'month.max' => 'الشهر يجب أن يكون بين 1 و 12.',
        'year_name.required' => 'يجب اختيار السنة.',
        'year_name.numeric' => 'يجب أن تكون السنة رقمية.',
        'year_name.min' => 'السنة يجب أن تكون بعد 2000.',
        'year_name.max' => 'السنة لا يمكن أن تتجاوز السنة الحالية.',
    ]);

    // استخراج الشهر والسنة
    $month = date('m', strtotime($request->month));
    $year = $request->year_name;
    // dd($month, $year);



    // البحث باستخدام الشهر والسنة
    $users = User::where('role', '!=', 'admin')->whereYear('created_at', $year)
    ->whereMonth('created_at', $month)
    ->latest()
    ->get();
$formatDate = $year.'/'.$month; // Ensure format is YYYY-MM-DD

    $category = Category::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->latest()
                        ->get();

    $games = Game::whereYear('created_at', $year)
                 ->whereMonth('created_at', $month)
                 ->latest()
                 ->get();

    $questions = Question::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->latest()
                         ->get();

                         return view('admin.report.report_by_date',compact('users','formatDate','category','games','questions'));

    // عرض البيانات في الـ Blade
    // return view('admin.report.report_by_month', compact('users', 'month', 'year', 'category', 'games', 'questions'));
}


        public function SearchByYear(Request $request){


              // التحقق من صحة الإدخال
    $request->validate([
        'years' => 'required|not_in:non', // السنة مطلوبة ويجب أن تكون من 2000 إلى السنة الحالية
    ], [


        'years.not_in' => 'يجب اختيار السنة.',

        'years.required' => 'يجب اختيار السنة.',
        'years.numeric' => 'يجب أن تكون السنة رقمية.',
        'years.min' => 'السنة يجب أن تكون بعد 2000.',
        'years.max' => 'السنة لا يمكن أن تتجاوز السنة الحالية.',
    ]);


            $year = $request->years;
            // dd($month, $year);



            // البحث باستخدام الشهر والسنة
            $users = User::where('role', '!=', 'admin')->whereYear('created_at', $year)
            ->latest()
            ->get();
        $formatDate = $year; // Ensure format is YYYY-MM-DD

            $category = Category::whereYear('created_at', $year)
                                ->latest()
                                ->get();

            $games = Game::whereYear('created_at', $year)
                         ->latest()
                         ->get();

            $questions = Question::whereYear('created_at', $year)
                                 ->latest()
                                 ->get();

                                 return view('admin.report.report_by_date',compact('users','formatDate','category','games','questions'));


    }// End Method



    public function OrderByUser(){
        // $users = User::where('role','user')->latest()->get();
        // return view('backend.report.report_by_user',compact('users'));

    }// End Method

    public function SearchByUser(Request $request){

        // $user_id = $request->user;
        // $users = User::find($user_id);

        // $orders = Order::where('user_id',$user_id)->latest()->get();
        // return view('backend.report.report_by_user_show',compact('orders','users'));
    }// End Method


}
