<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Livewire\Component;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Form;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Filament\Notifications\Notification;
class ListWorkers extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $team;

    public function mount($team)
    {
        $this->team = $team;
    }

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->team->users()->whereHas('roles', function ($q) {
                $q->where('name', 'worker');
            }))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                // Add any filters here if necessary
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add User')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->email()
                            ->unique('users', 'email'),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])
                    ->action(function (array $data): void {
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => bcrypt($data['password']),
                            'team_id' => $this->team->id
                        ]);
                        $user->assignRole('worker');
                        Notification::make()
                            ->title('notify')
                            ->body('Worker added successfully')
                            ->success()
                            ->send();
                    })
                // ->slideOver(),
            ]);
        // ->actions([
            //      Tables\Actions\EditAction::make()->slideOver(),
        //      Tables\Actions\DeleteAction::make(),
        // ]);
    }

    public function render(): View
    {
        return view('livewire.list-workers');
    }
}
