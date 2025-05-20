<?php

namespace App\Http\Livewire\Rubricas;

use App\Models\Rubrica;
use Livewire\Component;

class View extends Component
{
    public $rubricas;
    public $name;
    protected function rules()
    {
        return [
            'name' => 'required|min:6',
        ];
    }
    protected $messages = [
        'name.required' => 'El campo de Nombre no puede estar vacío.',
        'name.min' => 'El nombre debe tener al menos 6 caracteres.',
    ];
    public function render()
    {
        return view('livewire.rubricas.view');
    }

    public function mount()
    {
        $this->rubricas = Rubrica::all();
    }

    public function store()
    {
        $this->validate();
        Rubrica::create([
            'name' => $this->name,
        ]);
        $this->reset('name');
        $this->rubricas = Rubrica::all();
        session()->flash('message', 'Rubrica creada con éxito.');
    }
}
