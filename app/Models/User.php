<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\Role;
use App\Services\UpdateUsername;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'is_blacklisted',
        'role',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blacklisted' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [
            Role::ADMIN->value,
            Role::DELIVERY_AGENT->value,
            Role::MARKETER->value,
            Role::CALL_AGENT->value,
            Role::MANAGER->value
        ]);
    }


    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: 'N/A';
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
    
    public function getIsAdminAttribute() {
        return $this->role == "admin";
    }

    public function marketingOrders()
    {
        return $this->hasMany(Order::class, 'marketer_id');
    }

    public function formTemplates() {
        return $this->hasMany(FormTemplate::class);
    }

    public function deliveryOrders()
    {
        return $this->hasMany(Order::class, 'delivery_agent_id');
    }

    public function getFullNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            UpdateUsername::exec($user);
        });
    }

    public function fundRequests()
    {
        return $this->hasMany(FundRequest::class);
    }

    public function whatsAppDevices()
    {
        return $this->hasMany(WhatsappDevice::class);
    }

    public function autoResponders() {
        return $this->hasMany(AutoResponder::class);
    }

    public function scheduledMessages() {
        return $this->hasMany(ScheduledMessage::class);
    }

    public function campaigns() {
        return $this->hasMany(Campaign::class);
    }
    
    public function orders() {
        return $this->hasMany(Order::class);
    }
}
