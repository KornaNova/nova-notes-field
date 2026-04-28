<?php

namespace Outl1ne\NovaNotesField\Models;

use Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Outl1ne\NovaNotesField\NotesFieldServiceProvider;

class Note extends Model
{
    protected $table = 'nova_notes';
    protected $casts = [
        'system' => 'bool',
        'pinned_at' => 'datetime',
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];
    protected $fillable = [
        'model_id', 'model_type', 'text', 'created_by', 'system', 'notable_type', 'notable_id',
        'pinned_at', 'due_date', 'assigned_to', 'completed_at',
    ];
    protected $hidden = ['createdBy', 'assignee', 'notable_type', 'notable_id'];
    protected $appends = [
        'created_by_avatar_url', 'created_by_name',
        'can_delete', 'can_edit', 'can_pin', 'can_complete',
        'assignee_name',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(NotesFieldServiceProvider::getTableName());
    }

    public function notable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(static::resolveUserClass(), 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(static::resolveUserClass(), 'assigned_to');
    }

    public static function resolveUserClass()
    {
        $provider = 'users';
        if (config('nova.guard')) $provider = config('auth.guards.' . config('nova.guard') . '.provider');
        return config('auth.providers.' . $provider . '.model');
    }

    public function getAssigneeNameAttribute()
    {
        $user = $this->assignee;
        if (empty($user)) return null;

        if (!empty($user->name)) return $user->name;
        if (!empty($user->first_name)) return $user->first_name . (!empty($user->last_name) ? " {$user->last_name}" : '');
        if (!empty($user->email)) return $user->email;
        return null;
    }

    public function getCreatedByNameAttribute()
    {
        $user = $this->createdBy;

        // Try different combinations
        if (!empty($user->name)) return $user->name;
        if (!empty($user->first_name)) return $user->first_name . (!empty($user->last_name) ? " {$user->last_name}" : '');
        if (!empty($user->email)) return $user->email;
        return __('User');
    }

    public function getCreatedByAvatarUrlAttribute()
    {
        $createdBy = $this->createdBy;
        if (empty($createdBy)) return null;

        $avatarCallableOrFnName = config('nova-notes-field.get_avatar_url', null);
        if ($avatarCallableOrFnName) {
            if (is_callable($avatarCallableOrFnName)) return call_user_func($avatarCallableOrFnName, $createdBy);
            return $createdBy->$avatarCallableOrFnName ?? null;
        }

        return !empty($createdBy->email) ? 'https://www.gravatar.com/avatar/' . md5(strtolower($createdBy->email)) . '?s=300' : null;
    }

    public function getCanDeleteAttribute()
    {
        if (Gate::has('delete-nova-note')) return Gate::check('delete-nova-note', $this);

        if (config()->get('nova.guard')) {
            $user = Auth::guard(config('nova.guard'))->user();
        } else {
            $user = Auth::user();
        }

        if (empty($user)) return false;

        $createdBy = $this->createdBy;
        if (empty($createdBy)) return false;

        return $user->id === $createdBy->id;
    }

    public function getCanEditAttribute()
    {
        if (Gate::has('edit-nova-note')) return Gate::check('edit-nova-note', $this);

        if (config()->get('nova.guard')) {
            $user = Auth::guard(config('nova.guard'))->user();
        } else {
            $user = Auth::user();
        }

        if (empty($user)) return false;

        $createdBy = $this->createdBy;
        if (empty($createdBy)) return false;

        return $user->id === $createdBy->id;
    }

    public function getCanPinAttribute()
    {
        if (Gate::has('pin-nova-note')) return Gate::check('pin-nova-note', $this);

        if (config()->get('nova.guard')) {
            $user = Auth::guard(config('nova.guard'))->user();
        } else {
            $user = Auth::user();
        }

        if (empty($user)) return false;

        $createdBy = $this->createdBy;
        if (empty($createdBy)) return false;

        return $user->id === $createdBy->id;
    }

    public function getCanCompleteAttribute()
    {
        if (Gate::has('complete-nova-note')) return Gate::check('complete-nova-note', $this);

        if (config()->get('nova.guard')) {
            $user = Auth::guard(config('nova.guard'))->user();
        } else {
            $user = Auth::user();
        }

        if (empty($user)) return false;

        if ($this->assigned_to && $user->id === (int) $this->assigned_to) return true;

        $createdBy = $this->createdBy;
        if (empty($createdBy)) return false;

        return $user->id === $createdBy->id;
    }
}
