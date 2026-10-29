<?php

namespace App\Http\Controllers;

use App\Models\DeploymentNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeploymentNoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:255']]);
        $note = new DeploymentNote($data);
        $note->user_id = $request->user()->id;
        $note->save();

        return redirect('/deployment')->with('status', 'Catatan berhasil disimpan.');
    }

    public function destroy(Request $request, DeploymentNote $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 403);
        $note->delete();

        return redirect('/deployment')->with('status', 'Catatan berhasil dihapus.');
    }
}
