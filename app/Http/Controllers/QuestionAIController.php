<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuestionAIController extends Controller
{
    //
    public function allQuestionAi()
    {
        $category = Category::latest()->get();

        return view('admin.question_ai.add_question_ai',compact('category'));
    }




    /* stop this for now

    public function getdQuestionStoreAi(Request $request)
{

 $request->validate([
        'category_id' => 'required|exists:categories,id',
        'qu_number' => 'required|numeric|min:1|max:50',
    ], [
        'category_id.required' => 'حقل التصنيف مطلوب.',
        'category_id.exists' => 'الرجاء اختيار التصنيف.',
        'qu_number.required' => 'عدد الأسئلة مطلوب.',
        'qu_number.numeric' => 'عدد الأسئلة يجب أن يكون رقمًا.',
        'qu_number.min' => 'يجب أن يكون عدد الأسئلة على الأقل 1.',
        'qu_number.max' => 'يجب ألا يتجاوز عدد الأسئلة 50.',
    ]);


    $categoryId = $request->category_id;
    $info = $request->info;

    $category = Category::findOrFail($categoryId);
    $categoryName = $category->category_name;

    $quNumber =  $request->qu_number;



    // Construct prompt for ChatGPT
    // $prompt = "Generate 5 questions with answers about the topic: \"$categoryName\". " .
    //           ($info ? "Additional context: $info. " : "") .
    //           "Format the response as a JSON array of objects like this: " .
    //           "[{\"question\": \"...\", \"answer\": \"...\"}]";

    // $prompt = "قم بإنشاء 7 أسئلة مع إجاباتها حول الموضوع: \"$categoryName\". " .
    //       ($info ? "معلومات إضافية: $info. " : "") .
    //       "رجاءً أعد الإجابة بصيغة JSON على شكل مصفوفة كالتالي: " .
    //       "[{\"question\": \"السؤال...\", \"answer\": \"الإجابة...\"}]";




    // $prompt = "قم بإنشاء {$quNumber} سؤالاً مع إجاباتها حول الموضوع: \"$categoryName\". " .
    //   ($info ? "معلومات إضافية: $info. " : "") .
    //   "رجاءً أعد الإجابة بصيغة JSON على شكل مصفوفة كالتالي: " .
    //   "[{\"question\": \"السؤال...\", \"answer\": \"الإجابة...\"}]";


$prompt = "قم بإنشاء {$quNumber} سؤالاً قصيراً مع إجابات قصيرة حول الموضوع: \"$categoryName\". " .
    ($info ? "معلومات إضافية: $info. " : "") .
    "أعد الإجابة فقط بصيغة JSON، بدون أي شرح أو تعليقات، بالشكل التالي: " .
    "[{\"question\": \"...\", \"answer\": \"...\"}]";


    // Call OpenAI API
    // $response = Http::withHeaders([
    //     'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
    // ])->post('https://api.openai.com/v1/chat/completions', [
    //     'model' => 'gpt-4', // or 'gpt-3.5-turbo'
    //     'messages' => [
    //         ['role' => 'system', 'content' => 'You are an expert educational assistant.'],
    //         ['role' => 'user', 'content' => $prompt],
    //     ],
    //     'temperature' => 0.7,
    // ]);

    $response = Http::timeout(300)->withHeaders([
    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
])->post('https://api.openai.com/v1/chat/completions', [
    'model' => 'gpt-4', // switch to faster model
    'messages' => [
        ['role' => 'system', 'content' => 'أنت مساعد تعليمي ذكي.'],
        ['role' => 'user', 'content' => $prompt],
    ],
    'temperature' => 0.7,
]);

    if ($response->successful()) {
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'];

        // Try to parse as JSON
        try {
            $questions = json_decode($content, true);

            if (!is_array($questions)) {
                return back()->withErrors(['error' => 'The AI response was not formatted correctly.']);
            }

            // You can pass $questions to a view or store them


            // return view('admin.question_ai.show_generated_questions', compact('questions', 'categoryName'));

            return view('admin.question_ai.all_question_ai', compact('questions', 'categoryName','categoryId'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to decode AI response.']);
        }

    } else {
        return back()->withErrors(['error' => 'OpenAI API request failed.']);
    }
}

*/

public function getdQuestionStoreAi(Request $request)
{
    // 🛡️ Validate inputs
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'qu_number' => 'required|numeric|min:1|max:50',
    ], [
        'category_id.required' => 'حقل التصنيف مطلوب.',
        'category_id.exists' => 'الرجاء اختيار التصنيف.',
        'qu_number.required' => 'عدد الأسئلة مطلوب.',
        'qu_number.numeric' => 'عدد الأسئلة يجب أن يكون رقمًا.',
        'qu_number.min' => 'يجب أن يكون عدد الأسئلة على الأقل 1.',
        'qu_number.max' => 'يجب ألا يتجاوز عدد الأسئلة 50.',
    ]);

    $categoryId = $request->category_id;
    $info = $request->info;
    $quNumber = $request->qu_number;

    $category = Category::findOrFail($categoryId);
    $categoryName = $category->category_name;

    // ⚙️ Setup batching
    $batchSize = 5;
    $questions = [];

    for ($i = 0; $i < $quNumber; $i += $batchSize) {
        $currentBatchCount = min($batchSize, $quNumber - $i);

        $prompt = "قم بإنشاء {$currentBatchCount} سؤالاً قصيراً مع إجابات قصيرة حول الموضوع: \"$categoryName\". " .
            ($info ? "معلومات إضافية: $info. " : "") .
            "أعد الإجابة فقط بصيغة JSON، بدون أي شرح أو تعليقات، بالشكل التالي: " .
            "[{\"question\": \"...\", \"answer\": \"...\"}]";

        // 🔗 Call OpenAI API
        $response = Http::timeout(200)->withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'أنت مساعد تعليمي ذكي.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        if (!$response->successful()) {
            return back()->withErrors(['error' => 'فشل الاتصال بواجهة OpenAI.']);
        }

        $content = $response->json()['choices'][0]['message']['content'];

        // 🔍 Try to decode JSON
        try {
            $batchQuestions = json_decode($content, true);

            if (!is_array($batchQuestions)) {
                return back()->withErrors(['error' => 'الاستجابة من الذكاء الاصطناعي غير صالحة أو غير منظمة.']);
            }

            $questions = array_merge($questions, $batchQuestions);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'فشل في تحليل استجابة الذكاء الاصطناعي.']);
        }
    }

    // ✅ Return the combined questions to the view
    return view('admin.question_ai.all_question_ai', compact('questions', 'categoryName', 'categoryId'));
}



public function addQuestionToGameAi(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'qu_title' => 'required|string',
        'answer_title' => 'required|string',
    ]);

     $quTitle = $request->qu_title;
     $answerTitle = $request->answer_title;
    $categoryId = $request->category_id;

            $category = Category::latest()->get();



    // احفظ السؤال
    // $question = new Question();
    // $question->category_id = $request->category_id;
    // $question->qu_title = $request->qu_title;
    // $question->qu_points = 1; // يمكنك تعديلها لاحقًا
    // $question->time_counter = null;
    // $question->questions_type = 'text';
    // $question->save();

    // // احفظ الإجابة
    // $answer = new Answer();
    // $answer->question_id = $question->id;
    // $answer->answer_title = $request->answer_title;
    // $answer->answer_type = 'text';
    // $answer->save();



    return view('admin.question_ai.add_question_to_game_ai',compact('quTitle','answerTitle','categoryId','category'));
}


    /*
     public function getdQuestionStoreAi(Request $request)
    {

        $categoryId = $request->category_id;

        $category = Category::find($categoryId);

       $categoryName =  $category->category_name;

        // i want to return qustions and answer based on  $categoryName using AI ChatG




    }
        */






    //  public function allQuestionAi()
    // {
    //     $category = Category::latest()->get();

    //     return view('admin.question_ai.all_question_ai',compact('category'));
    // }
}
