<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $team = $user->team;
        
        return view('dashboard', compact('user', 'team'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->route('dashboard')
                ->withErrors($validator)
                ->with('activeTab', 'profile');
        }

        $user->update($request->only(['email', 'phone']));

        return redirect()->route('dashboard')
            ->with('success', 'Профиль успешно обновлен.')
            ->with('activeTab', 'profile');
    }

    public function addTeamMember(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCaptain()) {
            return redirect()->route('dashboard')
                ->with('error', 'Только капитан может добавлять участников.');
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users|min:3|max:50',
            'password' => 'required|string|min:6',
            'surname' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            return redirect()->route('dashboard')
                ->withErrors($validator)
                ->with('activeTab', 'team');
        }

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'surname' => $request->surname,
            'name' => $request->name,
            'patronymic' => $request->patronymic,
            'phone' => $request->phone,
            'email' => $request->email,
            'role' => 'participant',
            'team_id' => $user->team_id,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Участник успешно добавлен.')
            ->with('activeTab', 'team');
    }
}