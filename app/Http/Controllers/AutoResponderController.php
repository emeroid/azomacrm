<?php

namespace App\Http\Controllers;

use App\Models\AutoResponder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AutoResponderController extends Controller
{
    public function index()
    {
        return Inertia::render('AutoResponders/Index', [
            'responders' => auth()->user()->autoResponders()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255|unique:auto_responders,keyword,NULL,id,user_id,'.auth()->id(),
            'response_message' => 'required|string',
        ]);

        auth()->user()->autoResponders()->create($validated);

        return redirect()->back()->with('success', 'Auto-responder created!');
    }

    public function destroy(AutoResponder $autoResponder)
    {
        // Add authorization check
        // $this->authorize('delete', $autoResponder);
        $autoResponder->delete();
        return redirect()->back()->with('success', 'Auto-responder deleted!');
    }
}
