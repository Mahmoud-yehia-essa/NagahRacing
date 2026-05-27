<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\GamesCategories;
use App\Models\QuestionsRegister;
use Carbon\Carbon;


class GameController extends Controller
{
    public function allGames()
    {
        $games = Game::latest()->get();

        return view('admin.game.all_game',compact('games'));
    }

    public function detailsGames($id)
    {
        $game = Game::findOrFail($id);

        $teams = Team::where('game_id',$id)->get();;

        $questionsRegister = QuestionsRegister::where('game_id',$id)->get();;

        return view('admin.game.details_game',compact('game','teams','questionsRegister'));
    }


    public function deleteGame($id)
    {
        $game = Game::findOrFail($id);


        $game->delete();
        $notification = array(
            'message' => 'تم الحذف ',
            'alert-type' => 'success'
        );
        return redirect()->route('all.games')->with($notification);



    }



    /// API

    // Save Game
    public function saveGameApi(Request $request)
    {

       $game =  Game::create([
            'game_name' => $request->game_name,
            'user_id_created' => $request->user_id_created,
            'created_at' => Carbon::now(),]);

            $gameId = $game->id;

            // Get game id
            return response()->json(['game_id' => $gameId], 200);




    }


    public function saveTeamGameApi(Request $request)
    {

       $team =  Team::create([
            'team_name' => $request->team_name,
            'number_members' => $request->number_members,
            'result' => $request->result,


            'created_at' => Carbon::now(),
            'game_id' => $request->game_id,


        ]);

            $teamId = $team->id;

            // Get game id
            return response()->json(['team_id' => $teamId], 200);




    }




    public function saveGameCatesApi(Request $request)
    {

       $gamesCategories =  GamesCategories::create([
            'category_id' => $request->category_id,
            'game_id' => $request->game_id,
            'created_at' =>Carbon::now(),]);

            $categoryId = $gamesCategories->id;

            // Get game id
            return response()->json(['category_id' => $categoryId], 200);




    }


    public function saveGameQuetionApi(Request $request)
    {
        $questionsRegister =  QuestionsRegister::create([
            'game_id' => $request->game_id,
            'team_id' => $request->team_id,
            'question_id' => $request->question_id,
            'answer' => $request->answer,

            'created_at' => Carbon::now(),]);

            $questionsRegisterId = $questionsRegister->id;

            // Get questionsRegisterId
            return response()->json(['questionsRegister_id' => $questionsRegisterId], 200);

    }





    // public function getGamesByUserId($id)
    // {
    //     $games = Game::with(['gamesCategories', 'team']) // Eager load gamesCategories and team relationships
    //                  ->where('user_id_created', $id)
    //                  ->get();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Games fetched successfully',
    //         'games' => $games, // Return all user games with categories and team info
    //     ], 200);
    // }

    public function getGamesByUserId($id)
    {
        $games = Game::with([ 'team', 'categories']) // Eager load categories relationship
                     ->where('user_id_created', $id)
                     ->get();

        return response()->json([
            'success' => true,
            'message' => 'Games fetched successfully',
            'games' => $games, // Return all user games with categories and team info
        ], 200);
    }



}
