<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class QuestionController extends Controller
{

    public function addQuestion()
    {

        $category = Category::latest()->get();

        return view('admin.question.add_question',compact('category'));
    }

    public function editQuestion($id)
    {
        $question = Question::findOrFail($id);
        $category = Category::latest()->get();


        return view('admin.question.edit_question',compact('question','category'));
    }


    /*
    public function addQuestionStore(Request $request)
    {
        // Validate the inputs conditionally
        $request->validate([
            'qu_title' => 'required|string|max:255',
            'qu_points' => 'required|integer',
            'questions_type' => 'required|string',
            'questionsـfile' => [
                'nullable', // If no file is uploaded, it's fine
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->questions_type == 'صورة') {
                        // If the question type is image, validate it as an image file
                        if (!$request->hasFile('questionsـfile') || !$request->file('questionsـfile')->isValid() || !in_array($request->questionsـfile->extension(), ['jpg', 'jpeg', 'png'])) {
                            return $fail('Please upload a valid image file for the question.');
                        }
                    } elseif ($request->questions_type == 'ملف صوتي') {
                        // If the question type is audio, validate it as an audio file
                        if (!$request->hasFile('questionsـfile') || !$request->file('questionsـfile')->isValid() || !in_array($request->questionsـfile->extension(), ['mp3', 'wav'])) {
                            return $fail('Please upload a valid audio file for the question.');
                        }
                    }
                },
            ],
            'answer_title' => 'required|string|max:255',
            'answer_type' => 'required|string',
            'answerـfile' => [
                'nullable', // If no file is uploaded, it's fine
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->answer_type == 'صورة') {
                        // If the answer type is image, validate it as an image file
                        if (!$request->hasFile('answerـfile') || !$request->file('answerـfile')->isValid() || !in_array($request->answerـfile->extension(), ['jpg', 'jpeg', 'png'])) {
                            return $fail('Please upload a valid image file for the answer.');
                        }
                    } elseif ($request->answer_type == 'ملف صوتي') {
                        // If the answer type is audio, validate it as an audio file
                        if (!$request->hasFile('answerـfile') || !$request->file('answerـfile')->isValid() || !in_array($request->answerـfile->extension(), ['mp3', 'wav'])) {
                            return $fail('Please upload a valid audio file for the answer.');
                        }
                    }
                },
            ],
        ]);

        // Handle file upload for the question
        $questionFilePath = null;
        if ($request->hasFile('questionsـfile')) {
            $questionFile = $request->file('questionsـfile');

            if ($request->questions_type == 'image') {
                // If it's an image, store it in the 'questions/images' folder
                $questionFilePath = $questionFile->storeAs('questions/images', $questionFile->getClientOriginalName(), 'public');
            } elseif ($request->questions_type == 'sound') {
                // If it's a sound file, store it in the 'questions/sounds' folder
                $questionFilePath = $questionFile->storeAs('questions/sounds', $questionFile->getClientOriginalName(), 'public');
            }
        }

        // Store the question data with the correct file path
        $question = Question::create([
            'qu_title' => $request->qu_title,
            'qu_points' => $request->qu_points,
            'questions_type' => $request->questions_type,
            'qu_image' => $request->questions_type == 'image' ? $questionFilePath : null,
            'qu_sound' => $request->questions_type == 'sound' ? $questionFilePath : null,
        ]);

        // Handle file upload for the answer
        $answerFilePath = null;
        if ($request->hasFile('answerـfile')) {
            $answerFile = $request->file('answerـfile');

            if ($request->answer_type == 'image') {
                // If it's an image, store it in the 'answers/images' folder
                $answerFilePath = $answerFile->storeAs('answers/images', $answerFile->getClientOriginalName(), 'public');
            } elseif ($request->answer_type == 'sound') {
                // If it's a sound file, store it in the 'answers/sounds' folder
                $answerFilePath = $answerFile->storeAs('answers/sounds', $answerFile->getClientOriginalName(), 'public');
            }
        }

        // Create the answer and link it to the question using 'question_id'
        Answer::create([
            'question_id' => $question->id, // Linking the answer to the question
            'answer_title' => $request->answer_title,
            'answer_type' => $request->answer_type,
            'answer_image' => $request->answer_type == 'image' ? $answerFilePath : null,
            'answer_sound' => $request->answer_type == 'sound' ? $answerFilePath : null,
        ]);

        // Redirect or return response
        return redirect()->route('all');
    }
        */


        /*
        public function addQuestionStore(Request $request)
        {
            // Validate inputs with custom Arabic messages
            $request->validate([
                'qu_title' => 'required|string|max:255',
                'qu_points' => 'required|integer',
                'questions_type' => 'required|string|in:text,image,sound',
                'questionsـfile' => 'nullable|file|max:30048', // 2MB max file size
                'answer_title' => 'required|string|max:255',
                'answer_type' => 'required|string|in:text,image,sound',
                'answerـfile' => 'nullable|file|max:30048',
                'category_id' => 'required|not_in:non', // التحقق من اختيار فئة صالحة

            ], [
                'qu_title.required' => 'يرجى إدخال عنوان السؤال.',
                'qu_title.string' => 'عنوان السؤال يجب أن يكون نصًا.',
                'qu_title.max' => 'عنوان السؤال يجب أن لا يتجاوز 255 حرفًا.',

                'qu_points.required' => 'يرجى إدخال نقاط السؤال.',
                'qu_points.integer' => 'نقاط السؤال يجب أن تكون عددًا صحيحًا.',

                'questions_type.required' => 'يرجى اختيار نوع السؤال.',
                'questions_type.in' => 'نوع السؤال يجب أن يكون نصي، صورة أو ملف صوتي.',

                'questionsـfile.file' => 'يرجى رفع ملف صالح.',
                'questionsـfile.max' => 'حجم الملف يجب أن لا يتجاوز 2 ميجابايت.',

                'answer_title.required' => 'يرجى إدخال عنوان الإجابة.',
                'answer_title.string' => 'عنوان الإجابة يجب أن يكون نصًا.',
                'answer_title.max' => 'عنوان الإجابة يجب أن لا يتجاوز 255 حرفًا.',

                'answer_type.required' => 'يرجى اختيار نوع الإجابة.',
                'answer_type.in' => 'نوع الإجابة يجب أن يكون نصي، صورة أو ملف صوتي.',

                'answerـfile.file' => 'يرجى رفع ملف صالح للإجابة.',
                'answerـfile.max' => 'حجم الملف يجب أن لا يتجاوز 2 ميجابايت.',

    'category_id.required' => 'الرجاء اختيار الفئة',
    'category_id.not_in' => 'الرجاء اختيار الفئة',
            ]);

            // Initialize variables for files
            $questionImage = null;
            $questionSound = null;

            // Handle file upload for question
            if ($request->questions_type !== 'text' && $request->hasFile('questionsـfile')) {
                $questionFile = $request->file('questionsـfile');
                $extension = strtolower($questionFile->getClientOriginalExtension());
                $filename = date('YmdHi') . '_' . uniqid() . '.' . $extension;

                // Validate file type based on selected question type
                if ($request->questions_type == 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $questionFile->move(public_path('upload/questions/images'), $filename);
                    $questionImage = $filename;
                } elseif ($request->questions_type == 'sound' && in_array($extension, ['mp3', 'wav'])) {
                    $questionFile->move(public_path('upload/questions/sounds'), $filename);
                    $questionSound = $filename;
                } else {

                    $notification = array(
                        'message' => 'نوع الملف غير صالح لنوع السؤال المحدد.',
                        'alert-type' => 'error'
                    );


                    return back()->with($notification);
                }
            }

            // Store the question in the database
            $question = Question::create([
                'qu_title' => $request->qu_title,
                'category_id'=>$request->category_id,
                'qu_points' => $request->qu_points,
                'questions_type' => $request->questions_type,
                'qu_image' => $questionImage,
                'qu_sound' => $questionSound,
            ]);

            // Initialize variables for files
            $answerImage = null;
            $answerSound = null;

            // Handle file upload for answer
            if ($request->answer_type !== 'text' && $request->hasFile('answerـfile')) {
                $answerFile = $request->file('answerـfile');
                $extension = strtolower($answerFile->getClientOriginalExtension());
                $filename = date('YmdHi') . '_' . uniqid() . '.' . $extension;

                // Validate file type based on selected answer type
                if ($request->answer_type == 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $answerFile->move(public_path('upload/answers/images'), $filename);
                    $answerImage = $filename;
                } elseif ($request->answer_type == 'sound' && in_array($extension, ['mp3', 'wav'])) {
                    $answerFile->move(public_path('upload/answers/sounds'), $filename);
                    $answerSound = $filename;
                } else {

                    $notification = array(
                        'message' => 'نوع الملف غير صالح لنوع الإجابة المحدد.',
                        'alert-type' => 'error'
                    );


                    return back()->with($notification);
                }
            }

            // Store the answer in the database
            Answer::create([
                'question_id' => $question->id,
                'answer_title' => $request->answer_title,
                'answer_type' => $request->answer_type,
                'answer_image' => $answerImage,
                'answer_sound' => $answerSound,
            ]);

            return redirect()->route('all.question')->with('success', 'تمت إضافة السؤال والإجابة بنجاح.');
        }
*/

public function addQuestionStore(Request $request)
{
    $request->validate([
        'qu_title' => 'required|string|max:255',
        'qu_points' => 'required|integer',
        'questions_type' => 'required|string|in:text,image,sound,video',
        'time_counter' => 'nullable|integer',
        'questionsـfile' => 'nullable|file|max:30720', // Increased size for videos
        'answer_title' => 'required|string|max:255',
        'answer_type' => 'required|string|in:text,image,sound,video',
        'answerـfile' => 'nullable|file|max:30720',
        'category_id' => 'required|not_in:non',
        'time_counter' => 'nullable|integer',

    ], [
        'qu_title.required' => 'يرجى إدخال عنوان السؤال.',
        'qu_title.string' => 'عنوان السؤال يجب أن يكون نصًا.',
        'qu_title.max' => 'عنوان السؤال يجب أن لا يتجاوز 255 حرفًا.',

        'qu_points.required' => 'يرجى إدخال نقاط السؤال.',
        'qu_points.integer' => 'نقاط السؤال يجب أن تكون عددًا صحيحًا.',
        'time_counter.integer' => 'الرجاء التأكد ان القيمة عدد صحيح',

        'questions_type.required' => 'يرجى اختيار نوع السؤال.',
        'questions_type.in' => 'نوع السؤال يجب أن يكون نصي، صورة، صوتي أو فيديو.',

        'questionsـfile.file' => 'يرجى رفع ملف صالح.',
        'questionsـfile.max' => 'حجم الملف يجب أن لا يتجاوز 30 ميجابايت.',

        'answer_title.required' => 'يرجى إدخال عنوان الإجابة.',
        'answer_title.string' => 'عنوان الإجابة يجب أن يكون نصًا.',
        'answer_title.max' => 'عنوان الإجابة يجب أن لا يتجاوز 255 حرفًا.',

        'answer_type.required' => 'يرجى اختيار نوع الإجابة.',
        'answer_type.in' => 'نوع الإجابة يجب أن يكون نصي، صورة، صوتي أو فيديو.',

        'answerـfile.file' => 'يرجى رفع ملف صالح للإجابة.',
        'answerـfile.max' => 'حجم الملف يجب أن لا يتجاوز 30 ميجابايت.',

        'category_id.required' => 'الرجاء اختيار الفئة.',
        'category_id.not_in' => 'الرجاء اختيار فئة صالحة.',
    ]);

    DB::beginTransaction(); // Start transaction

    try {
        // Initialize variables for question files
        $questionImage = $questionSound = $questionVideo = null;

        if ($request->questions_type !== 'text' && $request->hasFile('questionsـfile')) {
            $questionFile = $request->file('questionsـfile');
            $extension = strtolower($questionFile->getClientOriginalExtension());
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $extension;

            if ($request->questions_type == 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $questionFile->move(public_path('upload/questions/images'), $filename);
                $questionImage = $filename;
            } elseif ($request->questions_type == 'sound' && in_array($extension, ['mp3', 'wav'])) {
                $questionFile->move(public_path('upload/questions/sounds'), $filename);
                $questionSound = $filename;
            } elseif ($request->questions_type == 'video' && in_array($extension, ['mp4', 'avi', 'mov'])) {
                $questionFile->move(public_path('upload/questions/videos'), $filename);
                $questionVideo = $filename;
            } else {
                $notification = array(
                    'message' => 'نوع الملف غير صالح لنوع السؤال المحدد.',
                    'alert-type' => 'error'
                );


                return back()->with($notification);
            }
        }

        // Create question
        $question = Question::create([
            'qu_title' => $request->qu_title,
            'category_id' => $request->category_id,
            'qu_points' => $request->qu_points,
            'questions_type' => $request->questions_type,
            'time_counter' => $request->time_counter,
            'qu_image' => $questionImage,
            'qu_sound' => $questionSound,
            'qu_video' => $questionVideo,
        ]);

        if (!$question) {
            throw new \Exception('فشل في إنشاء السؤال.');
        }

        // Initialize variables for answer files
        $answerImage = $answerSound = $answerVideo = null;

        if ($request->answer_type !== 'text' && $request->hasFile('answerـfile')) {
            $answerFile = $request->file('answerـfile');
            $extension = strtolower($answerFile->getClientOriginalExtension());
            $filename = date('YmdHi') . '_' . uniqid() . '.' . $extension;

            if ($request->answer_type == 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $answerFile->move(public_path('upload/answers/images'), $filename);
                $answerImage = $filename;
            } elseif ($request->answer_type == 'sound' && in_array($extension, ['mp3', 'wav'])) {
                $answerFile->move(public_path('upload/answers/sounds'), $filename);
                $answerSound = $filename;
            } elseif ($request->answer_type == 'video' && in_array($extension, ['mp4', 'avi', 'mov'])) {
                $answerFile->move(public_path('upload/answers/videos'), $filename);
                $answerVideo = $filename;
            } else {
                throw new \Exception('نوع الملف غير صالح لنوع الإجابة المحدد.');
            }
        }

        // Create answer
        $answer = Answer::create([
            'question_id' => $question->id,
            'answer_title' => $request->answer_title,
            'answer_type' => $request->answer_type,
            'answer_image' => $answerImage,
            'answer_sound' => $answerSound,
            'answer_video' => $answerVideo,
        ]);

        if (!$answer) {
            throw new \Exception('فشل في إنشاء الإجابة.');
        }

        DB::commit(); // Commit transaction
        $notification = array(
            'message' =>  'تمت إضافة السؤال والإجابة بنجاح.',
            'alert-type' => 'success'
        );

        return redirect()->route('all.question')->with($notification);

    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction if an error occurs

        return back()->with('error', $e->getMessage());
    }
}



        public function allQuestion()
        {
            $questions = Question::latest()->get();


            return view('admin.question.all_question',compact('questions'));

        }



        public function editQuestionStore(Request $request)
        {


             // Validate inputs with custom Arabic messages
             $request->validate([
                'qu_title' => 'required|string|max:255',
                'qu_points' => 'required|integer',
                'questions_type' => 'required|string|in:text,image,sound,video',
                'questionsـfile' => 'nullable|file|max:30720', // 2MB max file size
                'answer_title' => 'required|string|max:255',
                'answer_type' => 'required|string|in:text,image,sound,video',
                'answerـfile' => 'nullable|file|max:30720',
                'category_id' => 'required|not_in:non',
                'time_counter' => 'nullable|integer',
                // التحقق من اختيار فئة صالحة

            ], [
                'qu_title.required' => 'يرجى إدخال عنوان السؤال.',
                'qu_title.string' => 'عنوان السؤال يجب أن يكون نصًا.',
                'qu_title.max' => 'عنوان السؤال يجب أن لا يتجاوز 255 حرفًا.',

                'qu_points.required' => 'يرجى إدخال نقاط السؤال.',
                'qu_points.integer' => 'نقاط السؤال يجب أن تكون عددًا صحيحًا.',

                'questions_type.required' => 'يرجى اختيار نوع السؤال.',
                'questions_type.in' => 'نوع السؤال يجب أن يكون نصي، صورة أو ملف صوتي.',

                'questionsـfile.file' => 'يرجى رفع ملف صالح.',
                'questionsـfile.max' => 'حجم الملف يجب أن لا يتجاوز 30 ميجابايت.',

                'answer_title.required' => 'يرجى إدخال عنوان الإجابة.',
                'answer_title.string' => 'عنوان الإجابة يجب أن يكون نصًا.',
                'answer_title.max' => 'عنوان الإجابة يجب أن لا يتجاوز 255 حرفًا.',

                'answer_type.required' => 'يرجى اختيار نوع الإجابة.',
                'answer_type.in' => 'نوع الإجابة يجب أن يكون نصي، صورة أو ملف صوتي.',

                'answerـfile.file' => 'يرجى رفع ملف صالح للإجابة.',
                'answerـfile.max' => 'حجم الملف يجب أن لا يتجاوز 30 ميجابايت.',

    'category_id.required' => 'الرجاء اختيار الفئة',
    'category_id.not_in' => 'الرجاء اختيار الفئة',
    'time_counter.integer' => 'الرجاء التأكد ان القيمة عدد صحيح',

            ]);




            // return "done";
            $question_id = $request->question_id;
            $answer_id = $request->answer_id;


            // if is exsite get old qustion image
            $old_question_image = $request->old_question_image;


           // if is exsite get old answer image
            $old_answer_image = $request->old_answer_image;



                // if is exsite get old qustion sound
                $old_question_sound = $request->old_question_sound;
                // if is exsite get old answer sound
                $old_answer_sound = $request->old_answer_sound;





                  // if is exsite get old qustion video
                $old_question_video = $request->old_question_video;
                // if is exsite get old answer sound
                $old_answer_video = $request->old_answer_video;










            $question = Question::findOrFail($question_id);


             $question->qu_title = $request->qu_title;
             $question->category_id = $request->category_id;



             $question->time_counter = $request->time_counter;



             if($old_question_video == "")
             {
                 $questionVideo = null;

             }
             else
             {
                 $questionVideo = $old_question_video;


             }


                // Initialize variables for files

                if($old_question_image == "")
                {
                    $questionImage = null;

                }
                else
                {
                    $questionImage = $old_question_image;


                }



                if($old_question_sound == "")
                {
                    $questionSound = null;

                }
                else
                {
                    $questionSound = $old_question_sound;


                }

                // Handle file upload for question
                if ($request->questions_type !== 'text' && $request->hasFile('questionsـfile')) {


                    $questionFile = $request->file('questionsـfile');
                    $extension = strtolower($questionFile->getClientOriginalExtension());
                    $filename = date('YmdHi') . '_' . uniqid() . '.' . $extension;

                    // Validate file type based on selected question type
                    if ($request->questions_type == 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        $questionFile->move(public_path('upload/questions/images'), $filename);
                        $questionImage = $filename;


                        $path = 'upload/questions/images/'.$old_question_image;
                        $pathSound = 'upload/questions/sounds/'.$old_question_sound;
                        $pathVideo = 'upload/questions/videos/'.$old_question_video;


                            if (file_exists($path) && $old_question_image != "" ) {
                                            unlink($path);
                                }

                                if (file_exists($pathSound) && $old_question_sound != "" ) {
                                    unlink($pathSound);
                        }

                        if (file_exists($pathVideo) && $old_question_video != "" ) {
                            unlink($pathVideo);
                }


                                $question->qu_image = $questionImage;


                    }


                    else if ($request->questions_type == 'video' && in_array($extension, ['mp4', 'mov'])) {
                        $questionFile->move(public_path('upload/questions/videos'), $filename);
                        $questionVideo = $filename;


                        $path = 'upload/questions/images/'.$old_question_image;
                        $pathSound = 'upload/questions/sounds/'.$old_question_sound;
                        $pathVideo = 'upload/questions/videos/'.$old_question_video;


                            if (file_exists($path) && $old_question_image != "" ) {
                                            unlink($path);
                                }

                                if (file_exists($pathSound) && $old_question_sound != "" ) {
                                    unlink($pathSound);
                        }

                        if (file_exists($pathVideo) && $old_question_video != "" ) {
                            unlink($pathVideo);
                }


                                $question->qu_video = $questionVideo;


                    }

                    else if ($request->questions_type == 'sound' && in_array($extension, ['mp3', 'wav'])) {




                        $path = 'upload/questions/images/'.$old_question_image;
                        $pathSound = 'upload/questions/sounds/'.$old_question_sound;
                        $pathVideo = 'upload/questions/videos/'.$old_question_video;


                            if (file_exists($path) && $old_question_image != "" ) {
                                            unlink($path);
                                }

                                if (file_exists($pathSound) && $old_question_sound != "" ) {
                                    unlink($pathSound);
                        }

                        if (file_exists($pathVideo) && $old_question_video != "" ) {
                            unlink($pathVideo);
                }

                        $questionFile->move(public_path('upload/questions/sounds'), $filename);
                        $questionSound = $filename;

                        $question->qu_sound = $questionSound;

                    } else {

                        $notification = array(
                            'message' => 'نوع الملف غير صالح لنوع السؤال المحدد.',
                            'alert-type' => 'error'
                        );


                        return back()->with($notification);
                    }
                }

                if($request->questions_type == 'text')
                {



                    $path = 'upload/questions/images/'.$old_question_image;
                    $pathSound = 'upload/questions/sounds/'.$old_question_sound;
                    $pathVideo = 'upload/questions/videos/'.$old_question_video;


                        if (file_exists($path) && $old_question_image != "" ) {
                                        unlink($path);
                            }

                            if (file_exists($pathSound) && $old_question_sound != "" ) {
                                unlink($pathSound);
                    }
                    if (file_exists($pathVideo) && $old_question_video != "" ) {
                        unlink($pathVideo);
            }

                }



                $question->qu_points = $request->qu_points;

                $question->questions_type = $request->questions_type;

                $question->save();





                //// this for answer
                $answer = Answer::findOrFail($answer_id);


                $answer->answer_title = $request->answer_title;




      // Initialize variables for files
      $answerImage = null;
      $answerSound = null;
      $answerVideo = null;



      if($old_answer_video == "")
      {
          $answerVideo = null;

      }
      else
      {
          $answerVideo = $old_answer_video;


      }

      if($old_answer_image == "")
      {
          $answerImage = null;

      }
      else
      {
          $answerImage = $old_answer_image;


      }



      if($old_answer_sound == "")
      {
          $answerSound = null;

      }
      else
      {
          $answerSound = $old_answer_sound;


      }


                /////


                    // Handle file upload for answer
            if ($request->answer_type !== 'text' && $request->hasFile('answerـfile')) {
                $answerFile = $request->file('answerـfile');
                $extension = strtolower($answerFile->getClientOriginalExtension());
                $filename = date('YmdHi') . '_' . uniqid() . '.' . $extension;

                // Validate file type based on selected answer type
                if ($request->answer_type == 'image' && in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $answerFile->move(public_path('upload/answers/images'), $filename);
                    $answerImage = $filename;


                    $path = 'upload/answers/images/'.$old_answer_image;
                    $pathSound = 'upload/answers/sounds/'.$old_answer_sound;
                    $pathVideo = 'upload/answers/videos/'.$old_answer_video;


                        if (file_exists($path) && $old_answer_image != "" ) {
                                        unlink($path);
                            }

                            if (file_exists($pathSound) && $old_answer_sound != "" ) {
                                unlink($pathSound);
                    }
                    if (file_exists($pathVideo) && $old_answer_video != "" ) {
                        unlink($pathVideo);
                                 }

                            $answer->answer_image = $answerImage;



                }

               else if ($request->answer_type == 'video' && in_array($extension, ['mp4', 'mov'])) {
                    $answerFile->move(public_path('upload/answers/videos'), $filename);
                    $answerVideo = $filename;


                    $path = 'upload/answers/images/'.$old_answer_image;
                    $pathSound = 'upload/answers/sounds/'.$old_answer_sound;
                    $pathVideo = 'upload/answers/videos/'.$old_answer_video;


                        if (file_exists($path) && $old_answer_image != "" ) {
                                        unlink($path);
                            }

                            if (file_exists($pathSound) && $old_answer_sound != "" ) {
                                unlink($pathSound);
                    }
                    if (file_exists($pathVideo) && $old_answer_video != "" ) {
                        unlink($pathVideo);
                                 }

                            $answer->answer_video = $answerVideo;



                }



                else if ($request->answer_type == 'sound' && in_array($extension, ['mp3', 'wav'])) {
                    $answerFile->move(public_path('upload/answers/sounds'), $filename);
                    $answerSound = $filename;


                    $path = 'upload/answers/images/'.$old_answer_image;
                    $pathSound = 'upload/answers/sounds/'.$old_answer_sound;
                    $pathVideo = 'upload/answers/videos/'.$old_answer_video;


                        if (file_exists($path) && $old_answer_image != "" ) {
                                        unlink($path);
                            }

                            if (file_exists($pathSound) && $old_answer_sound != "" ) {
                                unlink($pathSound);
                    }
                    if (file_exists($pathVideo) && $old_answer_video != "" ) {
                        unlink($pathVideo);
                                 }

                            $answer->answer_sound = $answerSound;



                } else {

                    $notification = array(
                        'message' => 'نوع الملف غير صالح لنوع الإجابة المحدد.',
                        'alert-type' => 'error'
                    );


                    return back()->with($notification);
                }
            }


            if($request->answer_type == 'text')
            {



                $path = 'upload/answers/images/'.$old_answer_image;
                $pathSound = 'upload/answers/sounds/'.$old_answer_sound;
                $pathVideo = 'upload/answers/videos/'.$old_answer_video;


                    if (file_exists($path) && $old_answer_image != "" ) {
                            unlink($path);
                        }

                        if (file_exists($pathSound) && $old_answer_sound != "" ) {
                            unlink($pathSound);
                }   if (file_exists($pathVideo) && $old_answer_video != "" ) {
                            unlink($pathVideo);
                             }

            }


                /////

                $answer->answer_type = $request->answer_type;

                $answer->save();



                $notification = array(
                    'message' => 'تم تعديل السؤال',
                    'alert-type' => 'success'
                );


                return redirect()->route('all.question')->with($notification);;

                // return redirect()->back()->with($notification);;


















        }




        public function deleteQuestion($id)
        {

            $question = Question::findOrFail($id);

            $old_question_image = $question->qu_image ?? "";
            $old_question_sound = $question->qu_sound ?? "";



            $old_answer_image = $question->answers->first()->answer_image ?? "";

            $old_answer_sound = $question->answers->first()->answer_sound ?? "";

            $path = 'upload/answers/images/'.$old_answer_image;
            $pathSound = 'upload/answers/sounds/'.$old_answer_sound;

            if (file_exists($path) && $old_answer_image != "" ) {
                unlink($path);
                    }

    if (file_exists($pathSound) && $old_answer_sound != "" ) {
        unlink($pathSound);
}




$pathQ = 'upload/questions/images/'.$old_question_image;
$pathSoundQ = 'upload/questions/sounds/'.$old_question_sound;


    if (file_exists($pathQ) && $old_question_image != "" ) {
                    unlink($pathQ);
        }

        if (file_exists($pathSoundQ) && $old_question_sound != "" ) {
            unlink($pathSoundQ);
}


            $question->delete();
            $notification = array(
                'message' => 'تم حذف السؤال',
                'alert-type' => 'success'
            );
            return redirect()->route('all.question')->with($notification);
        }


        /// Api



        /* Good Function to get Qustions
        public function getQuestionApi($id)
        {




            $qu = Question::where('category_id', $id)
            ->inRandomOrder() // Get random order
            ->take(6) // Limit to 6 questions
            ->get() // Fetch results
            ->shuffle() // Extra shuffle for randomness
            ->map(function ($question) {
                $question->is_user_answer = false;
                $question->who_answer = 0; // Add field and set to false
                // Add field and set to false
                return $question;
            });

        return response()->json($qu);
        }
        */



        /*
        public function getQuestionApi($id)
{
    // Fetch questions for each qu_points category
    $questions_200 = Question::where('category_id', $id)
        ->where('qu_points', 200)
        ->inRandomOrder()
        ->take(2)
        ->get();

    $questions_400 = Question::where('category_id', $id)
        ->where('qu_points', 400)
        ->inRandomOrder()
        ->take(2)
        ->get();

    $questions_600 = Question::where('category_id', $id)
        ->where('qu_points', 600)
        ->inRandomOrder()
        ->take(2)
        ->get();

    // Merge all question collections
    $qu = $questions_200->merge($questions_400)->merge($questions_600);

    // If less than 6 questions, fill from other available questions
    if ($qu->count() < 6) {
        $extra_questions = Question::where('category_id', $id)
            ->whereNotIn('id', $qu->pluck('id')) // Exclude already selected
            ->inRandomOrder()
            ->take(6 - $qu->count())
            ->get();

        $qu = $qu->merge($extra_questions);
    }

    // Shuffle again for extra randomness
    $qu = $qu->shuffle()->map(function ($question) {
        $question->is_user_answer = false;
        $question->who_answer = 0;
        return $question;
    });

    return response()->json($qu);
}
    */



    /*
    public function getQuestionApi($id)
{
    $questions_200 = Question::where('category_id', $id)
        ->where('qu_points', 200)
        ->inRandomOrder()
        ->take(2)
        ->get();

    $questions_400 = Question::where('category_id', $id)
        ->where('qu_points', 400)
        ->inRandomOrder()
        ->take(2)
        ->get();

    $questions_600 = Question::where('category_id', $id)
        ->where('qu_points', 600)
        ->inRandomOrder()
        ->take(2)
        ->get();

    // Merge all question collections
    $qu = $questions_200->merge($questions_400)->merge($questions_600);

    // Shuffle again for extra randomness
    $qu = $qu->shuffle()->map(function ($question) {
        $question->is_user_answer = false;
        $question->who_answer = 0;
        return $question;
    });

    return response()->json($qu);
}
*/


public function getQuestionApi($id)
{
    // Fetch 2 random questions for each qu_points category
    $questions_200 = Question::where('category_id', $id)
        ->where('qu_points', 200)
        ->inRandomOrder()
        ->take(2)
        ->get();

    $questions_400 = Question::where('category_id', $id)
        ->where('qu_points', 400)
        ->inRandomOrder()
        ->take(2)
        ->get();

    $questions_600 = Question::where('category_id', $id)
        ->where('qu_points', 600)
        ->inRandomOrder()
        ->take(2)
        ->get();

    // Merge in the required order: 200, 200, 400, 400, 600, 600
    $qu = $questions_200->merge($questions_400)->merge($questions_600);



    // Map and return in the correct order
    $qu = $qu->map(function ($question) {
        $question->is_user_answer = false;
        $question->who_answer = 0;
        return $question;
    });

    return response()->json($qu->values()); // Ensure proper indexing
}



// public function createQuestionSessionApi($id)
// {
//     // Fetch 2 random questions for each qu_points category
//     $questions_200 = Question::where('category_id', $id)
//         ->where('qu_points', 200)
//         ->inRandomOrder()
//         ->take(2)
//         ->get();

//     $questions_400 = Question::where('category_id', $id)
//         ->where('qu_points', 400)
//         ->inRandomOrder()
//         ->take(2)
//         ->get();

//     $questions_600 = Question::where('category_id', $id)
//         ->where('qu_points', 600)
//         ->inRandomOrder()
//         ->take(2)
//         ->get();

//     // Merge in the required order: 200, 200, 400, 400, 600, 600
//     $qu = $questions_200->merge($questions_400)->merge($questions_600);



//     // Map and return in the correct order
//     $qu = $qu->map(function ($question) {
//         $question->is_user_answer = false;
//         $question->who_answer = 0;
//         return $question;
//     });

//     return response()->json($qu->values()); // Ensure proper indexing
// }



public function createQuestionSessionApi(Request $request)
{
    $sessionId = $request->input('session_id');
        $categoryId = $request->input('category_id');


    if (!$sessionId) {
        return response()->json(['error' => 'session_id is required'], 422);
    }

    // هل الأسئلة محفوظة مسبقًا لهذه الجلسة؟
    $existingQuestions = \DB::table('game_session_questions')
        ->where('session_id', $sessionId)
        ->orderBy('index_order')
        ->pluck('question_id');

    if ($existingQuestions->isNotEmpty()) {
        // جلب نفس الأسئلة
        $questions = Question::whereIn('id', $existingQuestions)
            ->get()
            ->sortBy(function ($q) use ($existingQuestions) {
                return $existingQuestions->search($q->id);
            })
            ->values();
    } else {
        // توليد أسئلة جديدة لأول مرة
        $questions_200 = Question::where('category_id', $categoryId)
            ->where('qu_points', 200)
            ->inRandomOrder()
            ->take(2)
            ->get();

        $questions_400 = Question::where('category_id', $categoryId)
            ->where('qu_points', 400)
            ->inRandomOrder()
            ->take(2)
            ->get();

        $questions_600 = Question::where('category_id', $categoryId)
            ->where('qu_points', 600)
            ->inRandomOrder()
            ->take(2)
            ->get();

        $questions = $questions_200
            ->merge($questions_400)
            ->merge($questions_600)
            ->values();

        // حفظ الأسئلة للجلسة
        foreach ($questions as $index => $question) {
            \DB::table('game_session_questions')->insert([
                'session_id' => $sessionId,
                'question_id' => $question->id,
                'index_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // إضافة القيم الافتراضية
    $questions = $questions->map(function ($question) {
        $question->is_user_answer = false;
        $question->who_answer = 0;
        return $question;
    });

    return response()->json($questions);
}








        public function getQuestionAnswerApi($id)
        {

            $answer = Answer::where('question_id', $id)->get()->first();

        return response()->json($answer);
        }



}
