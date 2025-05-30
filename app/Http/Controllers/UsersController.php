<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RedirectIfNotParmitted;
use App\Models\Attachment;
use App\Models\Blog;
use App\Models\City;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Message;
use App\Models\Note;
use App\Models\Participant;
use App\Models\PendingEmail;
use App\Models\PendingUser;
use App\Models\Restriction;
use App\Models\Review;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class UsersController extends Controller{
    public function __construct(){
        $this->middleware(RedirectIfNotParmitted::class.':user');
    }
    public function index(){
        return Inertia::render('Users/Index', [
            'title' => 'Users',
            'filters' => Request::all(['search','role_id']),
            'roles' => Role::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'users' => User::orderByName()
                ->filter(Request::all(['search','role_id']))
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'country' => $user->country_id ? $user->country->name: null,
                    'city' => $user->city,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'photo' => $user->photo_path ?? null,
                ]),
        ]);
    }

    public function create(){
        return Inertia::render('Users/Create',[
            'title' => 'Create a new user',
            'roles' => Role::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'countries' => Country::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
            'cities' => City::orderBy('name')
                ->get()
                ->map
                ->only('id', 'name')
        ]);
    }

    public function store(){
        $userRequest = Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'phone' => ['nullable', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')],
            'password' => ['nullable'],
            'city' => ['nullable'],
            'address' => ['nullable'],
            'country_id' => ['nullable'],
            'role_id' => ['nullable'],
        ]);

        if(Request::file('photo')){
            $userRequest['photo_path'] = '/files/'.Request::file('photo')->store('users', ['disk' => 'file_uploads']);
        }

        $customerRole = Role::where('slug', 'customer')->first();
        if(empty($userRequest['role_id']) && !empty($customerRole)){
            $userRequest['role_id'] = $customerRole->id;
        }

        $user = User::create($userRequest);

        event(new UserCreated(['id' => $user->id, 'password' => $userRequest['password']]));

        return Redirect::route('users')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $a_user = auth()->user();

        $roles = Role::pluck('id', 'slug')->all();

        if ($a_user['role']['slug'] == 'customer') {
            if ($user->id != $a_user['id']) {
                return Redirect::back();
            }
        } elseif ($a_user['role']['slug'] == 'manager') {
            if ($user->id != $a_user['id'] && $user->role_id != $roles['customer'] ?? 0) {
                return Redirect::back();
            }
        }



        return Inertia::render('Users/Edit', [
            'title' => $user->name,
            'roles' => Role::orderBy('name')->get()->map->only('id', 'name'),
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'role_id' => $user->role_id,
                'city' => $user->city,
                'address' => $user->address,
                'country_id' => $user->country_id,
                'photo' => $user->photo_path ?? null,
                'photo_path' => $user->photo_path ?? null,
                'department_id' => $user->department_id,

                // 👇 Include restriction fields in form (optional)
                'restrict_until' => optional($user->restrictions->last())->up_to_date,
                'restriction_message' => optional($user->restrictions->last())->restriction_message,
                'remarks' => optional($user->restrictions->last())->remarks,

                // 👇 Send the full restriction trail
                'restrictions' => $user->restrictions->map(function ($restriction) {
                    return [
                        'restriction_id' => $restriction->restriction_id,
                        'up_to_date' => $restriction->up_to_date,
                        'restriction_message' => $restriction->restriction_message,
                        'remarks' => $restriction->remarks,
                        'created_at' => $restriction->created_at->toDateTimeString(),
                    ];
                }),
            ],
            'departments' => Department::orderBy('name')->get(),
            'countries' => Country::orderBy('name')->get()->map->only('id', 'name'),
            'cities' => City::orderBy('name')->get()->map->only('id', 'name'),
        ]);
    }

    public function update(User $user) {
        if (config('app.demo')) {
            return Redirect::back()->with('error', 'Updating user is not allowed for the live demo.');
        }
    
        // Validate incoming data
        Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'phone' => ['nullable', 'max:25'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable'],
            'city' => ['nullable'],
            'address' => ['nullable'],
            'country_id' => ['nullable'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'photo' => ['nullable', 'image'],
            
            // ✅ New validation rules
            'restrict_until' => ['nullable', 'date'],
            'restriction_message' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);
        
    
        // Update basic user info
        $user->update(Request::only(['first_name', 'last_name', 'phone', 'email', 'city', 'address', 'country_id']));
    
        // Update role if provided
        if (!empty(Request::get('role_id'))) {
            $user->update(['role_id' => Request::get('role_id')]);
        }
    
        // Update department if provided
        if (Request::get('department_id')) {
            $user->update(['department_id' => Request::get('department_id')]);
        }
    
        // Handle photo upload
        if (Request::file('photo')) {
            // Delete existing photo if it exists
            if (isset($user->photo_path) && !empty($user->photo_path) && File::exists(public_path($user->photo_path))) {
                File::delete(public_path($user->photo_path));
            }
            // Store the new photo and update the user record
            $user->update(['photo_path' => '/files/' . Request::file('photo')->store('users', ['disk' => 'file_uploads'])]);
        }
    
        // Update password if provided
        if (Request::get('password')) {
            $user->update(['password' => Request::get('password')]);
        }

        // Save or update restriction info
        if (Request::filled(['restrict_until', 'restriction_message', 'remarks'])) {
            $latestRestriction = Restriction::where('user_id', $user->id)
                ->latest('created_at')
                ->first();

            $now = Carbon::now()->toDateString();
            $formUpToDate = Request::get('restrict_until');

            if ($latestRestriction && $latestRestriction->up_to_date >= $now) {
                // 🔁 Update the existing active restriction
                $latestRestriction->update([
                    'up_to_date' => $formUpToDate,
                    'restriction_message' => Request::get('restriction_message'),
                    'remarks' => Request::get('remarks'),
                ]);
            } else {
                // ➕ Add new restriction history
                Restriction::create([
                    'user_id' => $user->id,
                    'up_to_date' => $formUpToDate,
                    'restriction_message' => Request::get('restriction_message'),
                    'remarks' => Request::get('remarks'),
                ]);
            }
        }
    
        return Redirect::back()->with('success', 'Profile updated.');
    }
    

    public function destroy(User $user) {

        if (config('app.demo')) {
            return Redirect::back()->with('error', 'Deleting user is not allowed for the live demo.');
        }

        $userId = $user->id;
        $user->delete();
        $this->removeUserFromRelatedTables($userId);

        return Redirect::route('users')->with('success', 'User deleted!');
    }
    public function restore(User $user){
        $user->restore();
        return Redirect::back()->with('success', 'User restored!');
    }

    private function removeUserFromRelatedTables($userId){
        Note::where('user_id', $userId)->update(['user_id' => null]);
        PendingEmail::where('user_id', $userId)->update(['user_id' => null]);
        Review::where('user_id', $userId)->update(['user_id' => null]);
        Comment::where('user_id', $userId)->update(['user_id' => null]);
        Attachment::where('user_id', $userId)->update(['user_id' => null]);
        Ticket::where('user_id', $userId)->update(['user_id' => null]);
        Participant::where('user_id', $userId)->update(['user_id' => null]);
        Message::where('user_id', $userId)->update(['user_id' => null]);
        PendingUser::where('user_id', $userId)->update(['user_id' => null]);
    }
}
