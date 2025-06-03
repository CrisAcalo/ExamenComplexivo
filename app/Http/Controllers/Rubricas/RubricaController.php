<?php

namespace App\Http\Controllers\Rubricas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RubricaController extends Controller
{
    public function create(){
        return view('livewire.rubricas.create.index');
    }

    public function edit($id){
        return view('livewire.rubricas.create.index', compact('id'));
    }
}
