<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class TeamEditForm extends Component implements HasForms
{
    use InteractsWithForms;

    public Team $team;

    public $name;
    public $subscription_type;
    public $subscription_expiredDate;

    public function mount(Team $team)
    {
        $this->team = $team;
        $this->form->fill([
            'name' => $this->team->name,
            'subscription_type' => $this->team->subscription->subscription_type,
            'subscription_expiredDate' => $this->team->subscription->subscription_expiredDate,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
        ];
    }

    public function submit()
    {
        $this->team->update([
            'name' => $this->name,
        ]);

        session()->flash('message', 'Team updated successfully.');
    }
    public function render()
    {
        return view('livewire.team-edit-form');
    }
}
