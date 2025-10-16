<?php

namespace Database\Seeders;

use App\Models\Outcome;
use App\Models\WhatsappTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultOutcomesAndTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default outcomes
        foreach (Outcome::getDefaultOutcomes() as $outcome) {
            Outcome::firstOrCreate(
                ['key' => $outcome['key']],
                array_merge($outcome, ['is_default' => true])
            );
        }

        // Seed default templates
        foreach (WhatsappTemplate::getDefaultTemplates() as $template) {
            WhatsAppTemplate::firstOrCreate(
                ['key' => $template['key']],
                array_merge($template, ['is_default' => true])
            );
        }

    }
}
