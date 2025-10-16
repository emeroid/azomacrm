<?php

namespace App\Filament\Resources\FormSubmissionResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\FormSubmission;
use Illuminate\Support\HtmlString;

class SubmissionDetails extends Widget
{
    protected static string $view = 'filament.resources.form-submission-resource.widgets.submission-details';

    public FormSubmission $record;

    public function renderSubmissionData()
    {
        $data = $this->record->data ?? [];
        $html = '<div class="space-y-4">';
        
        foreach ($data as $key => $value) {
            $key = ucwords(str_replace(['_', '-'], ' ', $key));
            $value = is_array($value) ? json_encode($value) : $value;
            
            $html .= <<<HTML
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-lg shadow">
                <div class="font-medium text-gray-500">{$key}</div>
                <div class="md:col-span-2 break-words">{$value}</div>
            </div>
            HTML;
        }
        
        $html .= '</div>';
        
        return new HtmlString($html);
    }
}
