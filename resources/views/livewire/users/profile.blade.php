<div class="card">
    @include('partials.alerts')
    <div class="card-body">
        <div class="card">
            <div class="card-header bg-info">
                @if (Auth::id() === $user->id)
                    <h1>Mi Perfil</h1>
                @else
                    <h1>Perfil de {{ $name }}</h1>
                @endif
            </div>
            <div class="card-body">
                <div class="col mx-auto">
                    <form>
                        <div class="col mx-auto">
                            <div class="card-body p-0">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" value="{{ $name }}" placeholder="{{ $name }}">
                                    @error('name')
                                        <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        wire:model="email" value="{{ $email }}"
                                        placeholder="{{ $email }}">
                                    @error('email')
                                        <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        wire:model="password" placeholder="***************">
                                    @error('password')
                                        <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password-confirm" class="form-label">Confirmar Contraseña</label>
                                    <input id="password-confirm" type="password" class="form-control"
                                        wire:model="password_confirmation" placeholder="***************">
                                </div>
                                <p class="card-text"><small class="text-muted">Modificado por última vez:
                                        {{ $user->updated_at }}</small></p>
                            </div>
                            <button type="button" wire:click.prevent="update()"
                                class="btn btn-info float-end">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @can('gestionar roles y permisos')
            <div class="card mx-auto mt-3 p-3">
                <h3>Roles</h3>
                <form action="{{ route('users.updateRoles', encrypt($user->id)) }}" id="update_product_info"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <ul class="list-group text-start">
                        @foreach ($roles as $rol)
                            <li class="list-group-item list-group-item-action list-group-item-info">
                                <input @if ($user->hasrole($rol->name)) checked @endif name="{{ $rol->name }}"
                                    value="{{ $rol->name }}" class="form-check-input me-1" type="checkbox"
                                    id="rol{{ $rol->id }}">

                                <label class="form-check-label stretched-link"
                                    for="rol{{ $rol->id }}">{{ $rol->name }}</label>
                            </li>
                        @endforeach
                    </ul>
                    <button type="submit" class="btn btn-info mt-3 float-end">Guardar</button>
                </form>
            </div>
        @else
            <div class="card mx-auto mt-3 p-3">
                <h3>Roles</h3>
                <ul class="list-group text-start">
                    @foreach ($user->roles as $rol)
                        <li class="list-group-item list-group-item-action list-group-item-info">
                            <span class="badge bg-info">{{ $rol->name }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="text-muted mt-2">Solo los administradores pueden modificar roles.</p>
            </div>
        @endcan


    </div>
</div>
