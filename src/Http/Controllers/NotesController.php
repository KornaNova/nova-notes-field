<?php

namespace Outl1ne\NovaNotesField\Http\Controllers;

use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Outl1ne\NovaNotesField\Models\Note;

class NotesController extends Controller
{
    // GET /notes
    public function getNotes(Request $request)
    {
        $validationResult = $this->validateRequest($request);
        if ($validationResult['has_errors'] === true) return response($validationResult['errors'], 400);

        $model = $validationResult['model'];
        $displayOrder = config('nova-notes-field.display_order', 'DESC');
        $notes = $model->notes()
            ->with('assignee')
            ->orderByRaw('CASE WHEN pinned_at IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('pinned_at', 'desc')
            ->orderBy('created_at', $displayOrder)
            ->orderBy('id', $displayOrder)
            ->get();

        return response()->json([
            'date_format' => config('nova-notes-field.date_format', 'dd MMM yyyy HH:mm'),
            'trix_enabled' => config('nova-notes-field.use_trix_input', false),
            'notes' => $notes,
        ], 200);
    }

    // POST /notes
    public function addNote(Request $request)
    {
        $validationResult = $this->validateRequest($request);
        if ($validationResult['has_errors'] === true) return response($validationResult['errors'], 400);

        $model = $validationResult['model'];
        $note = $request->input('note');

        if (empty($note)) return response(['errors' => ['note' => 'required']], 400);

        $model->addNote($note, true, false, [
            'due_date' => $this->normalizeDueDate($request->input('due_date')),
            'assigned_to' => $this->normalizeAssignedTo($request->input('assigned_to')),
        ]);

        return response('', 204);
    }

    // PATCH /notes/{note}
    public function editNote(Request $request, Note $note)
    {
        $noteText = $request->input('note');

        if (empty($noteText)) return response(['errors' => ['note' => 'required']], 400);

        if (!$note->can_edit) return response()->json(['error' => 'unauthorized'], 400);

        $note->update([
            'text' => $noteText,
            'due_date' => $this->normalizeDueDate($request->input('due_date')),
            'assigned_to' => $this->normalizeAssignedTo($request->input('assigned_to')),
        ]);

        return response('', 204);
    }

    // PATCH /notes/{note}/complete
    public function completeNote(Request $request, Note $note)
    {
        if (!$note->can_complete) return response()->json(['error' => 'unauthorized'], 400);

        $completed = (bool) $request->input('completed', true);

        $note->update([
            'completed_at' => $completed ? now() : null,
        ]);

        return response('', 204);
    }

    // GET /users
    public function getAssignableUsers(Request $request)
    {
        $callable = config('nova-notes-field.assignable_users');
        if (is_callable($callable)) {
            $users = call_user_func($callable, $request);
        } else {
            $userClass = Note::resolveUserClass();
            $users = $userClass ? $userClass::query()->get() : collect();
        }

        $users = collect($users)->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $this->resolveUserName($user),
            ];
        })->filter(fn ($u) => !empty($u['id']))->values();

        $currentUser = config('nova.guard')
            ? \Illuminate\Support\Facades\Auth::guard(config('nova.guard'))->user()
            : \Illuminate\Support\Facades\Auth::user();

        if ($currentUser) {
            $currentId = $currentUser->id;
            [$me, $rest] = $users->partition(fn ($u) => $u['id'] === $currentId);
            $users = $me->values()->concat($rest->values())->values();
        }

        return response()->json(['users' => $users], 200);
    }

    private function resolveUserName($user)
    {
        if (!empty($user->name)) return $user->name;
        if (!empty($user->first_name)) return $user->first_name . (!empty($user->last_name) ? " {$user->last_name}" : '');
        if (!empty($user->email)) return $user->email;
        return (string) $user->id;
    }

    private function normalizeDueDate($value)
    {
        if (empty($value)) return null;
        return $value;
    }

    private function normalizeAssignedTo($value)
    {
        if (empty($value)) return null;
        return (int) $value;
    }

    // PATCH /notes/{note}/pin
    public function pinNote(Request $request, Note $note)
    {
        if (!$note->can_pin) return response()->json(['error' => 'unauthorized'], 400);

        $pinned = (bool) $request->input('pinned', true);

        $note->update([
            'pinned_at' => $pinned ? now() : null,
        ]);

        return response('', 204);
    }

    // DELETE /notes
    public function deleteNote(Request $request)
    {
        $validationResult = $this->validateRequest($request);
        if ($validationResult['has_errors'] === true) return response()->json($validationResult['errors'], 400);

        $model = $validationResult['model'];
        $noteId = $request->input('noteId');

        if (empty($noteId)) return response()->json(['errors' => ['noteId' => 'required']], 400);

        $note = $model->notes()->where('id', $noteId)->first();
        if (empty($note)) return response('', 204);

        if (!$note->canDelete) return response()->json(['error' => 'unauthorized'], 400);

        $model->deleteNote($noteId);

        return response('', 204);
    }

    private function validateRequest(Request $request)
    {
        $resourceId = $request->get('resourceId');
        $resourceName = $request->get('resourceName');

        $errors = [];
        if (empty($resourceId)) $errors['resourceId'] = 'required';
        if (empty($resourceName)) $errors['resourceName'] = 'required';

        if (!empty($resourceName)) {
            $resourceClass = Nova::resourceForKey($resourceName);
            if (empty($resourceClass)) $errors['resourceName'] = 'invalid_name';
            else {
                $modelClass = $resourceClass::$model;

                if (method_exists($modelClass, 'trashed')) {
                    $model = $modelClass::withTrashed()->find($resourceId);
                } else {
                    $model = $modelClass::find($resourceId);
                }

                if (empty($model)) $errors['resourceId'] = 'not_found';
            }
        }

        return [
            'has_errors' => sizeof($errors) > 0,
            'errors' => $errors,
            'model' => isset($model) ? $model : null,
        ];
    }
}
