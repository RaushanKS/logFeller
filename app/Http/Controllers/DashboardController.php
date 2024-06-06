<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // $lastWeekUsers = User::whereBetween('users.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->join('user_role', 'user_role.user_id', '=', 'users.id')->count();
        // $lastWeekOrders = Orders::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        // $lastWeekUsers = User::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        // $totalSale = Orders::where('status', 5)->sum('pay_amount');

        $loggedInUser = Auth::user()->name;
        $totalRecords = Products::count();
        $activated = Products::where('status', 1)->count();
        $inactivated = Products::where('status', 0)->count();
        $twoDaysAgo = Carbon::now()->subDays(30);

        $recentProducts = Products::where('created_at', '>=', $twoDaysAgo)->count();

        return view('dashboard', compact(['loggedInUser', 'totalRecords', 'activated', 'inactivated', 'recentProducts']));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
