<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;

class EmbedController extends Controller
{
    public function show($slug)
    {
        $template = FormTemplate::where('slug', $slug)->firstOrFail();
        $template->load('fields');
        
        return response()
            ->view('form-builder.embed', [
                'template' => $template
            ])
            ->header('Content-Type', 'application/javascript');
    }
}