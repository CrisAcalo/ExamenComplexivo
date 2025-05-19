<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class Permissions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $selected_id, $keyWord, $name, $guard_name;
    public $rolEncontrado;

    public function mount()
    {
        $this->guard_name = 'web';
    }

    public function render()
    {
        $keyWord = '%' . $this->keyWord . '%';
        return view('livewire.permissions.view', [
            'permissions' => Permission::latest()
                ->orWhere('name', 'LIKE', $keyWord)
                ->orWhere('guard_name', 'LIKE', $keyWord)
                ->paginate(10),
        ]);
    }

    public function cancel()
    {
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->name = null;
        $this->guard_name = null;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
        ]);

        Permission::create([
            'name' => $this->name,
        ]);

        $this->resetInput();
        $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'createDataModal']);
        session()->flash('success', 'Permiso creado exitosamente.');
    }

    public function edit($id)
    {
        $record = Permission::findById($id);
        $this->selected_id = $id;
        $this->name = $record->name;
        $this->guard_name = $record->guard_name;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required'
        ]);

        if ($this->selected_id) {
            $record = Permission::findById($this->selected_id);
            $record->update([
                'name' => $this->name
            ]);

            $this->resetInput();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'updateDataModal']);

            session()->flash('success', 'Permiso actualizado  exitosamente.');
        }
    }

    public function eliminar($id)
    {
        $this->rolEncontrado = Permission::findById($id);
    }

    public function destroy($id)
    {
        if ($id) {
            Permission::findById($id)->delete();
            $this->dispatchBrowserEvent('closeModalByName', ['modalName' => 'deleteDataModal']);
        }
        $this->rolEncontrado = null;
        session()->flash('success', 'Permso eliminado exitosamente.');
    }
}
