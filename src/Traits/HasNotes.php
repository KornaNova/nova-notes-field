<?php

namespace Outl1ne\NovaNotesField\Traits;

use Illuminate\Support\Facades\Auth;
use Outl1ne\NovaNotesField\NotesFieldServiceProvider;

trait HasNotes
{
    /**
     * Creates a new note and attaches it to the model.
     *
     * @param string $note The note text which can contain raw HTML.
     * @param bool $user Enables or disables the use of `Auth::guard(config('nova.guard'))->user()` to set as the creator.
     * @param bool $system Defines whether the note is system created and can be deleted or not.
     * @param array $extra Optional task fields: due_date, assigned_to, completed_at, pinned_at.
     * @return \Outl1ne\NovaNotesField\Models\Note
     **/
    public function addNote($note, $user = true, $system = true, array $extra = [])
    {
        $userId = $user ? Auth::guard(config('nova.guard'))->id() : null;

        $allowed = array_intersect_key($extra, array_flip(['due_date', 'assigned_to', 'completed_at', 'pinned_at']));

        return $this->notes()->create(array_merge([
            'text' => $note,
            'created_by' => $userId,
            'system' => $system,
        ], $allowed));
    }

    /**
     * Edit a note with the given ID and text.
     *
     * @param int|string $noteId The ID of the note to edit.
     * @param string $text The note text which can contain raw HTML.
     * @param array $extra Optional task fields: due_date, assigned_to, completed_at, pinned_at.
     * @return \Outl1ne\NovaNotesField\Models\Note
     **/
    public function editNote($noteId, $text, array $extra = [])
    {
        $note = $this->notes()->where('id', '=', $noteId)->firstOrFail();

        $allowed = array_intersect_key($extra, array_flip(['due_date', 'assigned_to', 'completed_at', 'pinned_at']));

        $note->update(array_merge(['text' => $text], $allowed));
        return $note;
    }

    /**
     * Mark a note attached to this model as complete or reopened.
     *
     * @param int|string $noteId
     * @param bool $completed
     * @return \Outl1ne\NovaNotesField\Models\Note
     **/
    public function completeNote($noteId, $completed = true)
    {
        $note = $this->notes()->where('id', '=', $noteId)->firstOrFail();
        $note->update([
            'completed_at' => $completed ? now() : null,
        ]);
        return $note;
    }

    /**
     * Pin or unpin a note attached to this model.
     *
     * @param int|string $noteId The ID of the note to pin or unpin.
     * @param bool $pinned True to pin, false to unpin.
     * @return \Outl1ne\NovaNotesField\Models\Note
     **/
    public function pinNote($noteId, $pinned = true)
    {
        $note = $this->notes()->where('id', '=', $noteId)->firstOrFail();
        $note->update([
            'pinned_at' => $pinned ? now() : null,
        ]);
        return $note;
    }

    /**
     * Deletes a note with given ID and dissociates it from the model.
     *
     * @param int|string $noteId The ID of the note to delete.
     * @return void
     **/
    public function deleteNote($noteId)
    {
        $this->notes()->where('id', '=', $noteId)->delete();
    }

    public function notes()
    {
        return $this->morphMany(NotesFieldServiceProvider::getNotesModel(), 'notable');
    }
}
