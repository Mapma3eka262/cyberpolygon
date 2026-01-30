<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginRegister()
    {
        return view('auth.login-register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users|min:3|max:50',
            'password' => 'required|string|min:6|confirmed',
            'surname' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'patronymic' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'team_name' => 'required_if:role,captain|string|max:100',
            'role' => 'required|in:captain,participant',
            'invite_code' => 'required_if:role,participant|string|size:8|exists:teams,invite_code',
            'privacy_policy' => 'accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('activeTab', 'register');
        }

        $userData = [
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'surname' => $request->surname,
            'name' => $request->name,
            'patronymic' => $request->patronymic,
            'phone' => $request->phone,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->role === 'captain') {
            // Создать команду
            $team = Team::create([
                'name' => $request->team_name,
            ]);
            $team->generateInviteCode();
            
            $userData['team_id'] = $team->id;
        } else {
            // Присоединиться к существующей команде
            $team = Team::where('invite_code', $request->invite_code)->first();
            $userData['team_id'] = $team->id;
        }

        $user = User::create($userData);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'Неверные учетные данные.',
        ])->with('activeTab', 'login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}