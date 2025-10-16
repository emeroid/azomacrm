<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'user_id', 'redirect_url', 'is_template'];
    
    public function fields()
    {
        return $this->hasMany(TemplateField::class, 'template_id')->orderBy('order');
    }

    protected $casts = [
        'is_template' => 'boolean',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for template forms
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }
    
    // Scope for user forms
    public function scopeUserForms($query, $user_id)
    {
        return $query->where('user_id', $user_id)->where('is_template', false);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id && !$this->is_template;
    }

    public function isTemplate(): bool
    {
        return $this->is_template;
    }
}
