<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Models\Camal;
use App\Models\Round;
use App\Models\Category;
use App\Models\Festival;
use App\Models\Question;
use App\Models\Nomination;
use Illuminate\Http\Request;
use App\Models\CamelRoundParticipation;
use App\Models\CamelWorker;
use App\Models\TrainingSession;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        $festival_count = Festival::count();
        $rounds_count = Round::count();
        $camal_count = CamelRoundParticipation::count();
        $nomination_count = Nomination::count();
        $category_count = Category::count();
        $games_count = Game::count();
        $questions_count = Question::count();
        $users_count = User::where('role', '!=', 'admin')->count();

        // New dashboard statistics
        $owners_count = User::where('role', 'owner')->count();
        $workers_count = CamelWorker::count();
        $sessions_count = TrainingSession::count();
        $plans_count = SubscriptionPlan::count();
        $subscriptions_count = UserSubscription::count();
        $total_revenue = UserSubscription::sum('amount_paid');

        // Get only the latest 10 owners for the table on dashboard with their camel workers count
        $owners = User::where('role', 'owner')->withCount('camelWorkers')->latest()->take(10)->get();

        return view('admin.index', compact(
            'owners',
            'users_count',
            'category_count',
            'games_count',
            'questions_count',
            'festival_count',
            'rounds_count',
            'camal_count',
            'nomination_count',
            'owners_count',
            'workers_count',
            'sessions_count',
            'plans_count',
            'subscriptions_count',
            'total_revenue'
        ));
    }
}
