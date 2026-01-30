<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Http\Requests\Admin\UpdateTeamRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['captain', 'members']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('captain', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $teams = $query->orderBy('score', 'desc')->paginate(20);

        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:teams',
            'captain_email' => 'required|email|exists:users,email',
            'target_ip' => 'nullable|ip',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            // Создаем команду
            $team = Team::create([
                'name' => $validated['name'],
                'target_ip' => $validated['target_ip'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $team->generateInviteCode();

            // Назначаем капитана
            $captain = User::where('email', $validated['captain_email'])->first();
            
            if ($captain) {
                $captain->update([
                    'team_id' => $team->id,
                    'role' => 'captain',
                ]);
            }

            \App\Models\EventLog::log('team_created_admin', [
                'user_id' => auth()->id(),
                'team_id' => $team->id,
                'details' => ['name' => $team->name],
            ]);
        });

        return redirect()->route('admin.teams.index')
            ->with('success', 'Команда успешно создана.');
    }

    public function edit(Team $team)
    {
        return view('admin.teams.edit', compact('team'));
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team->update($request->validated());

        return redirect()->route('admin.teams.index')
            ->with('success', 'Команда успешно обновлена.');
    }

    public function destroy(Team $team)
    {
        if ($team->members()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Невозможно удалить команду с участниками.');
        }

        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Команда успешно удалена.');
    }

    public function toggleStatus(Team $team)
    {
        $team->update(['is_active' => !$team->is_active]);

        $status = $team->is_active ? 'активирована' : 'деактивирована';
        
        return redirect()->back()
            ->with('success', "Команда успешно {$status}.");
    }

    public function members(Team $team)
    {
        $members = $team->members()->paginate(20);
        
        return view('admin.teams.members', compact('team', 'members'));
    }

    public function removeMember(Team $team, User $user)
    {
        if ($user->team_id !== $team->id) {
            return redirect()->back()
                ->with('error', 'Пользователь не состоит в этой команде.');
        }

        if ($user->isCaptain()) {
            return redirect()->back()
                ->with('error', 'Невозможно удалить капитана команды.');
        }

        $user->update(['team_id' => null, 'role' => 'participant']);

        return redirect()->back()
            ->with('success', 'Участник успешно удален из команды.');
    }
}