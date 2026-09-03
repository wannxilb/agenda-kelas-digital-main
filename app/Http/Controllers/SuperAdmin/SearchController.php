<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {
            return redirect()->back();
        }

        $like = '%' . mb_strtolower($query) . '%';

        $institutions = Institution::whereRaw('LOWER(name) LIKE ?', [$like])
            ->orWhereRaw('LOWER(email) LIKE ?', [$like])
            ->orWhereRaw('LOWER(address) LIKE ?', [$like])
            ->take(20)
            ->get();

        $users = User::where(function ($q) use ($like) {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
            })
            ->with('institution:id,name')
            ->take(30)
            ->get()
            ->groupBy(function ($user) {
                return $user->getRoleNames()->first() ?? 'unknown';
            });

        return view('super_admin.search_results.index', [
            'query' => $query,
            'institutions' => $institutions,
            'users' => $users,
        ]);
    }
}
