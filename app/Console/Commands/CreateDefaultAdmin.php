<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateDefaultAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-default-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the default system administrator user (Emeka Mbaegbu) if they do not already exist.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Define Default User Details
        $defaultEmail = 'admin@azomacrm.site';
        $defaultPassword = 'password'; 
        
        $userData = [
            'first_name' => 'Emeka',
            'last_name' => 'Mbaegbu',
            'mobile' => '08012345678', // Placeholder, update in DB after creation if needed
            'email' => $defaultEmail,
            'role' => Role::ADMIN->value, // Assuming Role enum is accessible and correct
            'is_blacklisted' => false,
            'password' => $defaultPassword,
        ];

        // 2. Check if user already exists
        $user = User::where('email', $defaultEmail)->first();

        if ($user) {
            $this->info("Default admin user ({$defaultEmail}) already exists. No action taken.");
            
            // Optional: Ensure the existing user has the correct admin role
            if ($user->role !== Role::ADMIN->value) {
                $user->role = Role::ADMIN->value;
                $user->save();
                $this->warn("Updated existing user's role to 'admin'.");
            }

            return Command::SUCCESS;
        }

        // 3. Create the user
        try {
            // Note: Since the User model has the 'password' cast to 'hashed', 
            // Laravel handles the hashing automatically.
            User::create($userData);

            $this->info("✅ Default admin user created successfully!");
            $this->info("-------------------------------------------------------");
            $this->info("Name: {$userData['first_name']} {$userData['last_name']}");
            $this->info("Email: {$defaultEmail}");
            $this->info("Role: ADMIN");
            $this->warn("Default Password: {$defaultPassword}");
            $this->info("-------------------------------------------------------");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Failed to create default admin user: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
