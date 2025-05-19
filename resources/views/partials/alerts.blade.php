<div class="mx-auto fixed-top mt-3" style="width:90%;max-width:700px;opacity:0.9;">
    @if (session()->has('success'))
        <div wire:poll.3s class="alert alert-success" style="margin-top:0px; margin-bottom:0px;">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div wire:poll.4s class="alert alert-warning" style="margin-top:0px; margin-bottom:0px;">
            {{ session('warning') }}
        </div>
    @endif

    @if (session()->has('danger'))
        <div wire:poll.7s  class="alert alert-danger" style="margin-top:0px; margin-bottom:0px;">
            {{ session('danger') }}
        </div>
    @endif
</div>
