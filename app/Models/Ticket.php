<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Route model binding: Resolves route parameter to model instance.
     */
    public function resolveRouteBinding($value, $field = null) {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    /**
     * Scope for ordering tickets by subject.
     */
    public function scopeOrderBySubject($query){
        $query->orderBy('subject');
    }

    /**
     * Relationship to the user who created the ticket.
     */
    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to the ticket priority.
     */
    public function priority(){
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    /**
     * Relationship to ticket review.
     */
    public function review(){
        return $this->belongsTo(Review::class, 'review_id');
    }

    /**
     * Relationship to ticket attachments.
     */
    public function attachments(){
        return $this->hasMany(Attachment::class);
    }

    /**
     * Relationship to ticket comments.
     */
    public function comments(){
        return $this->hasMany(Comment::class);
    }

    /**
     * Relationship to ticket status.
     */
    public function status(){
        return $this->belongsTo(Status::class, 'status_id');
    }

    /**
     * Relationship to department.
     */
    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relationship to ticket type.
     */
    public function ticketType(){
        return $this->belongsTo(Type::class, 'type_id');
    }

    /**
     * Relationship to contact associated with the ticket.
     */
    public function contact(){
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Relationship to the user who owns the ticket.
     */
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to ticket category.
     */
    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relationship to the user assigned to the ticket.
     */
    public function assignedTo(){
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Accessor for formatted due date.
     */
    public function getDueAttribute($date){
        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Scope for filtering tickets by customer (user_id).
     */
    public function scopeByCustomer($query, $id){
        if (!empty($id)) {
            $query->where('user_id', $id);
        }
    }

    /**
     * Scope for filtering tickets by user (user_id).
     */
    public function scopeByUser($query, $id){
        if (!empty($id)) {
            $query->where('user_id', $id);
        }
    }

    /**
     * Scope for filtering tickets by assigned user (assigned_to).
     */
    public function scopeByAssign($query, $id){
        if (!empty($id)) {
            $query->where('assigned_to', $id);
        }
    }

    /**
     * Scope for filtering tickets based on a set of filters.
     */
    public function scopeFilter($query, array $filters) {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $statusIds = Status::where('slug', 'like', '%'.$search.'%')->pluck('id');
            $priorityIds = Priority::where('name', 'like', '%'.$search.'%')->pluck('id');
            $assignedIds = User::where('first_name', 'like', '%'.$search.'%')
                ->orWhere('last_name', 'like', '%'.$search.'%')->pluck('id');
            
            $query->where(function($query) use ($search, $statusIds, $priorityIds, $assignedIds) {
                $query->where('subject', 'like', '%'.$search.'%')
                      ->orWhere('uid', 'like', '%'.$search.'%')
                      ->orWhereIn('status_id', $statusIds)
                      ->orWhereIn('priority_id', $priorityIds)
                      ->orWhereIn('assigned_to', $assignedIds)
                      ->orWhereIn('user_id', $assignedIds);
            });
        })->when($filters['priority_id'] ?? null, function ($query, $priority) {
            $query->where('priority_id', $priority);
        })->when($filters['status_id'] ?? null, function ($query, $status) {
            $query->where('status_id', $status);
        })->when($filters['type_id'] ?? null, function ($query, $type) {
            $query->where('type_id', $type);
        })->when($filters['category_id'] ?? null, function ($query, $category) {
            $query->where('category_id', $category);
        })->when($filters['department_id'] ?? null, function ($query, $department) {
            $query->where('department_id', $department);
        });

        // Add distinct to avoid duplicate results
        $query->distinct();
    }
}
