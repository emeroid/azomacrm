<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $fillable = ['template_id', 'name', 'label', 'type', 'is_required', 'properties', 'order'];
    
    protected $casts = [
        'properties' => 'array',
        'is_required' => 'boolean'
    ];

    public function template() {
        return $this->belongsTo(FormTemplate::class, 'template_id');
    }

    public function getHtmlAttributes()
    {
        $attrs = [
            'class' => 'form-control',
            'id' => 'field_' . $this->name,
            'name' => $this->name,
        ];

        if ($this->is_required) {
            $attrs['required'] = 'required';
        }

        // Add type-specific attributes
        switch ($this->type) {
            case 'text':
                $attrs['type'] = 'text';
                $attrs['placeholder'] = $this->properties['placeholder'] ?? '';
                break;
            case 'number':
                $attrs['type'] = 'number';
                $attrs['min'] = $this->properties['min'] ?? '';
                $attrs['max'] = $this->properties['max'] ?? '';
                $attrs['step'] = $this->properties['step'] ?? '1';
                break;
            case 'textarea':
                $attrs['rows'] = $this->properties['rows'] ?? '3';
                break;
            case 'select':
                // Options will be handled separately
                break;
            case 'radio':
            case 'checkbox':
                // Options will be handled separately
                break;
        }

        return $attrs;
    }

    public function renderField($value = null)
    {
        $attributes = $this->getHtmlAttributes();
        $html = '';

        switch ($this->type) {
            case 'text':
            case 'number':
            case 'email':
            case 'tel':
                $html = '<input ' . $this->buildAttributes($attributes, $value) . '>';
                break;
            case 'textarea':
                $html = '<textarea ' . $this->buildAttributes($attributes) . '>' . e($value) . '</textarea>';
                break;
            case 'select':
                $html = '<select ' . $this->buildAttributes($attributes) . '>';
                $html .= '<option value="">Select ' . e($this->label) . '</option>';
                foreach ($this->properties['options'] ?? [] as $option) {
                    $selected = $value == $option['value'] ? ' selected' : '';
                    $html .= '<option value="' . e($option['value']) . '"' . $selected . '>' . e($option['label']) . '</option>';
                }
                $html .= '</select>';
                break;
            case 'radio':
                foreach ($this->properties['options'] ?? [] as $option) {
                    $checked = $value == $option['value'] ? ' checked' : '';
                    $html .= '<div class="form-check">';
                    $html .= '<input class="form-check-input" type="radio" name="' . e($this->name) . '" id="' . e($this->name . '_' . $option['value']) . '" value="' . e($option['value']) . '"' . $checked . '>';
                    $html .= '<label class="form-check-label" for="' . e($this->name . '_' . $option['value']) . '">' . e($option['label']) . '</label>';
                    $html .= '</div>';
                }
                break;
            case 'checkbox':
                $html .= '<div class="form-check">';
                $html .= '<input class="form-check-input" type="checkbox" name="' . e($this->name) . '" id="' . e($this->name) . '" value="1"' . ($value ? ' checked' : '') . '>';
                $html .= '<label class="form-check-label" for="' . e($this->name) . '">' . e($this->label) . '</label>';
                $html .= '</div>';
                break;
        }

        return $html;
    }

    protected function buildAttributes($attributes, $value = null)
    {
        $html = '';
        foreach ($attributes as $key => $val) {
            if ($key === 'value' && $value !== null) {
                $val = $value;
            }
            $html .= ' ' . e($key) . '="' . e($val) . '"';
        }
        return $html;
    }

    const FIELD_TYPES = [
        'text' => [
            'name' => 'text',
            'label' => 'Text Input',
            'type' => 'text',
            'icon' => 'fas fa-font',
            'is_required' => false,
            'properties' => [
                'placeholder' => 'Enter text',
                'maxlength' => 255
            ],
            'order' => 0,
        ],
        'number' => [
            'name' => 'number',
            'label' => 'Number Input',
            'type' => 'number',
            'icon' => 'fas fa-hashtag',
            'is_required' => false,
            'properties' => [
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'placeholder' => 'Enter number'
            ],
            'order' => 1,
        ],
        'email' => [
            'name' => 'email',
            'label' => 'Email Input',
            'type' => 'email',
            'icon' => 'fas fa-at',
            'is_required' => false,
            'properties' => [
                'placeholder' => 'Enter email address'
            ],
            'order' => 2,
        ],
        'tel' => [
            'name' => 'tel',
            'label' => 'Phone Input',
            'type' => 'tel',
            'icon' => 'fas fa-phone',
            'is_required' => false,
            'properties' => [
                'placeholder' => 'Enter phone number'
            ],
            'order' => 3,
        ],
        'textarea' => [
            'name' => 'textarea',
            'label' => 'Text Area',
            'type' => 'textarea',
            'icon' => 'fas fa-align-left',
            'is_required' => false,
            'properties' => [
                'placeholder' => 'Enter detailed text',
                'rows' => 4
            ],
            'order' => 4,
        ],
        'select' => [
            'name' => 'select',
            'label' => 'Dropdown',
            'type' => 'select',
            'icon' => 'fas fa-caret-square-down',
            'is_required' => false,
            'properties' => [
                'options' => ['Option 1', 'Option 2']
            ],
            'order' => 5,
        ],
        'radio' => [
            'name' => 'radio',
            'label' => 'Radio Buttons',
            'type' => 'radio',
            'icon' => 'fas fa-dot-circle',
            'is_required' => false,
            'properties' => [
                'options' => ['Option A', 'Option B']
            ],
            'order' => 6,
        ],
        'checkbox' => [
            'name' => 'checkbox',
            'label' => 'Checkbox',
            'type' => 'checkbox',
            'icon' => 'fas fa-check-square',
            'is_required' => false,
            'properties' => [
                'text' => 'I agree'
            ],
            'order' => 7,
        ],
        'date' => [
            'name' => 'date',
            'label' => 'Date Picker',
            'type' => 'date',
            'icon' => 'fas fa-calendar-alt',
            'is_required' => false,
            'properties' => [
                'format' => 'YYYY-MM-DD'
            ],
            'order' => 8,
        ],
        'product_selector' => [
            'name' => 'product',
            'label' => 'Product Selection',
            'type' => 'product_selector',
            'icon' => 'fas fa-calendar-alt',
            'is_required' => true,
            'properties' => [
                'placeholder' => 'Enter detailed text',
                'number' => 'Price',
                'note' => 'Note'
            ],
            'order' => 9,
        ],
    ];
    
}