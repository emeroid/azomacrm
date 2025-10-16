<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends EditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                            
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                            
                        TextInput::make('mobile')
                            ->tel()
                            ->maxLength(20),

                    ])->columns(2),
                    
                    
                Section::make('Change Password')
                    ->schema([
                        TextInput::make('current_password')
                            ->password()
                            ->requiredWith('new_password')
                            ->currentPassword()
                            ->label('Current Password'),
                            
                        TextInput::make('new_password')
                            ->password()
                            ->rule(Password::default())
                            ->different('current_password')
                            ->label('New Password'),
                            
                        TextInput::make('new_password_confirmation')
                            ->password()
                            ->same('new_password')
                            ->label('Confirm New Password'),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
        }
        
        unset($data['current_password']);
        unset($data['new_password']);
        unset($data['new_password_confirmation']);
        
        return $data;
    }

    protected function getLayoutData(): array
    {
        return [
            ...parent::getLayoutData(),
            'maxWidth' => '4xl', // or '6xl', '7xl', etc.
        ];
    }
    
}

// namespace App\Filament\Pages;

// use App\Models\User;
// use Filament\Forms\Components\TextInput;
// use Filament\Forms\Form;
// use Filament\Pages\Page;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Validation\Rules\Password;
// use Filament\Notifications\Notification;
// use Filament\Actions\Action;

// class Profile extends Page
// {
//     protected static ?string $navigationIcon = 'heroicon-o-user';

//     protected static string $view = 'filament.pages.profile';

//     protected static ?string $title = 'My Profile';

//     public ?array $data = [];

//     public function mount(): void
//     {
//         $this->form->fill(auth()->user()->withoutRelations()->toArray());
//     }

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 TextInput::make('first_name')
//                     ->required()
//                     ->maxLength(255),
//                 TextInput::make('last_name')
//                     ->required()
//                     ->maxLength(255),
//                 TextInput::make('mobile')
//                     ->tel()
//                     ->maxLength(255),
//                 // Email is displayed but not editable
//                 TextInput::make('email')
//                     ->email()
//                     ->disabled(),
//                 TextInput::make('password')
//                     ->password()
//                     ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
//                     ->dehydrated(fn (?string $state): bool => filled($state))
//                     ->required(fn (string $operation): bool => $operation === 'create') // Only required for creation, not update
//                     ->rule(Password::default())
//                     ->confirmed()
//                     ->maxLength(255),
//                 TextInput::make('password_confirmation')
//                     ->password()
//                     ->required(fn (string $operation): bool => $operation === 'create' || filled(request()->input('data.password')))
//                     ->maxLength(255),
//             ])
//             ->model(auth()->user())
//             ->statePath('data');
//     }

//     public function getFormActions(): array
//     {
//         return [
//             Action::make('save')
//                 ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
//                 ->submit('save'),
//         ];
//     }

//     public function save(): void
//     {
//         try {
//             $data = $this->form->getState();

//             // Remove password fields if they are empty (user didn't change password)
//             if (empty($data['password'])) {
//                 unset($data['password']);
//                 unset($data['password_confirmation']);
//             }

//             auth()->user()->update($data);

//             Notification::make()
//                 ->success()
//                 ->title('Profile updated successfully!')
//                 ->send();
//         } catch (\Exception $e) {
//             Notification::make()
//                 ->danger()
//                 ->title('Error updating profile.')
//                 ->body($e->getMessage())
//                 ->send();
//         }
//     }
// }