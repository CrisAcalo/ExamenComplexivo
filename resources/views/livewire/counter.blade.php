<div style="text-align: center">
    <input type="text" name="number" id="number" wire:model="number">
    <button wire:click="increment">+</button>
    <h1>{{ $count }}</h1>
    <h1>{{ $number }}</h1>
</div>