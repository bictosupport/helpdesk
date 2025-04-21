<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Role;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class DashboardController extends Controller {
   public function index() {
        $user = Auth()->user();
        $roleId = $user['role']['id'];//added
        if(empty($user['role'])){
            $customerRole = $this->getCustomerRole();
            User::where('id', $user['id'])->update(['role_id' => $customerRole->id]);
            return Auth::guard('web')->logout()->with('error', 'You need to login again!');
        }
        $byUser = null;
        $byAssign = null;
        $avgWhere = [];
        $opened_status = Status::where('slug', 'like', '%closed%')->first();

        $monthYear = request()->input('monthYear');

        // If a month-year is selected, filter the data accordingly
        if ($monthYear) {
            $date = Carbon::parse($monthYear);
            $startDate = $date->startOfMonth()->toDateString();
            $endDate = $date->endOfMonth()->toDateString();
        } else {
            // No filter applied: fetch overall data
            $startDate = null;
            $endDate = null;
        }
        
        $newTicketQuery = Ticket::where('status_id', '!=', $opened_status->id);
        $newTicketQuery->whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()]);
        if (!empty($opened_status)) {
            $avgWhere[] = ['status_id', '!=', $opened_status->id];
            $openedTickets = Ticket::byUser($byUser)->byAssign($byAssign)
                ->where('status_id', '!=', $opened_status->id)
                ->count();
        }

        // Admin-specific logic
        if (in_array($user['role']['slug'], ['customer'])) {
            $byUser = $user['id'];
            $avgWhere[] = ['user_id', '=', $byUser];
            $newTicketQuery->where('user_id', '=', $byUser);

            // Include the check for opened tickets for customers
            $openedTickets = Ticket::byUser($byUser)
                ->where('status_id', '!=', $opened_status->id) // Ensure status isn't 'closed'
                ->count();
        } elseif (in_array($user['role']['slug'], ['manager'])) {
            $byAssign = $user['id'];
            $avgWhere[] = ['assigned_to', '=', $byAssign];
            $newTicketQuery->where('assigned_to', '=', $byAssign);
        }


        if(in_array($user['role']['slug'], ['customer'])){
            $byUser = $user['id'];
            $avgWhere[] = ['user_id', '=', $byUser];
            $newTicketQuery->where('user_id', '=', $byUser);
        }elseif(in_array($user['role']['slug'], ['manager'])){
            $byAssign = $user['id'];
            $avgWhere[] = ['assigned_to', '=', $byAssign];
            $newTicketQuery->where('assigned_to', '=', $byAssign);
        }


        $top_clients_query = Ticket::selectRaw("user_id, count(id) as total");
        if ($startDate && $endDate) {
            $top_clients_query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $top_clients = $top_clients_query
            ->groupBy('user_id')
            ->orderBy('total', 'DESC')
            ->limit(3)
            ->get();
        $top_creators = [];
        $top_creator_tickets = 0;
        foreach ($top_clients as $client) {
            $top_creator_tickets += $client->total;
            $top_creators[] = [
                'name' => $client->user ? $client->user->first_name . ' ' . $client->user->last_name : '',
                'count' => $client->total
            ];
        }
        $top_creators = $this->generateColorCount($top_creators, $top_creator_tickets);

        // Ticket By Departments
        $top_tickets_by_department_query = Ticket::selectRaw("department_id, count(id) as total");
        if ($startDate && $endDate) {
            $top_tickets_by_department_query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $top_tickets_by_department = $top_tickets_by_department_query
            ->groupBy('department_id')
            ->orderBy('total', 'DESC')
            ->get();

        $top_departments = [];
        $count_tickets_by_department = 0;
        foreach ($top_tickets_by_department as $tt) {
            $count_tickets_by_department += $tt->total;
            $top_departments[] = [
                'name' => $tt->department ? $tt->department->name : '',
                'count' => $tt->total
            ];
        }
        $top_departments = $this->generateColorCount($top_departments, $count_tickets_by_department);
    
        // Ticket By Departments

        // Ticket By Types
        $top_tickets_by_type_query = Ticket::selectRaw("type_id, count(id) as total");
        if ($startDate && $endDate) {
            $top_tickets_by_type_query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $top_tickets_by_type = $top_tickets_by_type_query
            ->groupBy('type_id')
            ->orderBy('total', 'DESC')
            ->get();
        
        $top_types = [];
        $count_tickets_by_type = 0;
        foreach ($top_tickets_by_type as $tt) {
            $count_tickets_by_type += $tt->total;
            $top_types[] = [
                'name' => $tt->ticketType ? $tt->ticketType->name : '',
                'count' => $tt->total
            ];
        }
        $top_types = $this->generateColorCount($top_types, $count_tickets_by_type);
        // Ticket By Departments


        // Main Query for Tickets, Solved Tickets, and Ratings
        $ticketsByUser = Ticket::select(
            'users.first_name',
            'users.last_name',
            'users.photo_path',
            DB::raw('COUNT(tickets.id) AS total_tickets'),
            DB::raw('SUM(CASE WHEN tickets.status_id = 1 THEN 1 ELSE 0 END) AS solved_tickets'),
            DB::raw('AVG(reviews.rating) AS average_rating')
        )
        ->whereBetween('tickets.created_at', [$startDate, $endDate])
        ->join('users', 'users.id', '=', 'tickets.assigned_to')
        ->leftJoin('reviews', 'reviews.ticket_id', '=', 'tickets.id')
        ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.photo_path')
        ->orderByDesc('solved_tickets')
        ->orderByDesc('average_rating')
        ->get();
        

        $getAverageMinTime = DB::table('tickets')
            ->select(DB::raw("MIN(TIME_TO_SEC(TIMEDIFF(response, created_at))) AS timediff"))
            ->where($avgWhere)
            ->get();

        $getAverageMaxTime = DB::table('tickets')
            ->select(DB::raw("MAX(TIME_TO_SEC(TIMEDIFF(response, created_at))) AS timediff"))
            ->where($avgWhere)
            ->get();

        $fr = 0;
        $lr = 0;

        if(!empty($getAverageMinTime[0]->timediff)){
            $fr = CarbonInterval::seconds((int)$getAverageMinTime[0]->timediff)->cascade()->forHumans();
        }

        if(!empty($getAverageMaxTime[0]->timediff)){
            $lr = CarbonInterval::seconds((int)$getAverageMaxTime[0]->timediff)->cascade()->forHumans();
        }

        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $startThisMonth = Carbon::now()->startOfMonth()->toDateString();

        $lastMonthTotal = Ticket::whereBetween('created_at',[$fromDate,$tillDate])->count();
        $thisMonthTotal = Ticket::whereBetween('created_at', [$startThisMonth, now()])->count();

        $beforeMonths = Carbon::now()->startOfMonth()->subMonths(12);

        $previousMonths = [];
        for ($i = 0; $i <= 11; $i++) {
            $month = Carbon::today()->startOfMonth()->subMonth($i);
            $previousMonths[] = $month->shortMonthName;
        }

//        $thisMountTickets = Ticket::whereBetween('created_at', [$startThisMonth, now()])
        $previousMountTickets = Ticket::whereBetween('created_at', [$beforeMonths, now()])
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            });

        $m_total = 0;
        $months = [];
        foreach ($previousMountTickets as $tkey => $tValue){
            $total = $tValue->count();
            $m_total+= $total;
            $months[$tkey] = $total;
        }

        $unAssignedTicketQuery = Ticket::byUser($byUser)->byAssign($byAssign);

        $closedTickets = Ticket::byUser($byUser)->byAssign($byAssign)->filter(['search' => 'close'])->count();
        $totalTicketsforAdmin = Ticket::byUser($byUser)->byAssign($byAssign)->count();
//         $totalTicketsperUser = Ticket::byUser($byUser)->byAssign($byAssign)->count();
        

        $customer_role = Role::where('slug', 'customer')->first();
        if(!empty($customer_role)){
            $totalCustomers = User::where('role_id', $customer_role->id)->count();
        }

        $totalContacts = Contact::count();
        $user_department_id = $user->department_id;
        $user_id = $user['id'];
        $departments = Department::orderBy('name')->get();

        $ticketsByUser = Ticket::select(
            'users.first_name',
            'users.last_name',
            DB::raw('COUNT(tickets.id) AS total_tickets'),
            DB::raw('SUM(CASE WHEN tickets.status_id = 1 THEN 1 ELSE 0 END) AS solved_tickets'),
            DB::raw('AVG(reviews.rating) AS average_rating')
        )
        ->join('users', 'users.id', '=', 'tickets.assigned_to') // Join with users
        ->leftJoin('reviews', 'reviews.ticket_id', '=', 'tickets.id') // Left join with reviews
        ->whereNotNull('tickets.assigned_to') // Where assigned_to is not null
        ->groupBy('users.id', 'users.first_name', 'users.last_name') // Group by user fields
        ->get();
        return Inertia::render('Dashboard/Index', [
            'title' => 'Dashboard',
            'role_id' => $roleId,
            'departments' => $departments,
            'user_department_id' => $user_department_id,
            'user_id' => $user_id,
            'entries' => [],
            'opened_tickets' => $openedTickets ?? 0, 
            'total_tickets' => $totalTicketsforAdmin,
            'closed_tickets' => $closedTickets,
            'new_tickets' => $newTicketQuery
                ->whereRaw('Date(created_at) = CURDATE()')->count(),
            'un_assigned_tickets' => $unAssignedTicketQuery->where('assigned_to', '=', null)->count(),
            'first_response' => !empty($fr) ? explode(" ", $fr) : [],
            'last_response' => !empty($fr) ? explode(" ", $lr) : [],
            'top_creators' => $top_creators,
            'top_types' => $top_types,
            'top_departments' => $top_departments,
            'monthYear' => $monthYear, 
            'total_customer' => $totalCustomers ?? 0,
            'total_contacts' => $totalContacts,
            'ticketsByUser' => $ticketsByUser,
            'chart_line' => [
                'months' => $months,
                'previousMonths' => $previousMonths,
                'total' => $m_total,
                'last_month' => $lastMonthTotal,
                'this_month' => $thisMonthTotal,
            ]
        ]);
    }

    public function setLocale($language){
        $rtlCodes = ['sa'];
        $user = Auth()->user();
        Session()->put('locale', $language);
        Session()->put('dir', in_array($language, $rtlCodes) ? 'rtl' : 'ltr');
        if(!empty($user)){
            User::where('id', $user['id'])->update(['locale' => $language]);
        }
        return redirect()->back();
    }

    private function generateColorCount($items, $maxCount){
        $colors = ['#c25ef1','#7562ca','#2980b9','#c0392b','#43c0dc','#7366ff','#800081','#2c3e50','#f39c12','#16a085','#27ae60'];
        foreach ($items as $itemKey => &$itemValue){
            $itemValue['value'] = ($itemValue['count'] * 100)/$maxCount;
            $itemValue['label'] = $itemValue['name'].' '.round($itemValue['value'], 2).'% ('.$itemValue['count'].')';
            if($itemKey >= count($colors)){
                $itemValue['color'] = $colors[$itemKey % count($colors)];
            }else{
                $itemValue['color'] = $colors[$itemKey];
            }
        }
        return $items;
    }

    public function editProfile() {
        $user_id = Auth::id();
        $user = User::with('role') // Eager load the role
            ->where('id', $user_id)
            ->first();
        
        // Fetch the department name using the department_id
        $departmentName = $user->department_id ? Department::find($user->department_id)->name : null;
        
        return Inertia::render('Users/EditProfile', [
            'title' => $user->name,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->name, // Pass the role name instead of role_id
                'city' => $user->city,
                'address' => $user->address,
                'country_id' => $user->country_id,
                'photo' => $user->photo_path ?? null,
                'photo_path' => $user->photo_path ?? null,
                'department_name' => $departmentName,
            ],
            'countries' => Country::orderBy('name')->get()->map->only('id', 'name'),
            'cities' => City::orderBy('name')->get()->map->only('id', 'name')
        ]);
    }

    private function getCustomerRole(){
        $role = Role::where('slug', 'customer')->first();
        if(empty($role)){
            $itemAccess = [];
            $access = ['read' => false,'create' => false,'delete' => false,'update' => false];
            $items = ['faq','blog','chat','smtp','type','user','global','pusher','status','ticket','contact','category','customer','language','priority','department','organization','email_template','knowledge_base'];
            foreach ($items as $item){ $itemAccess[$item] = $access; }
            $role = Role::create(['name' => 'Customer', 'slug' => 'customer', 'access' => json_encode($itemAccess)]);
        }
        return $role;
    }
}
