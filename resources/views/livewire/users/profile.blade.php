<div>
    <!-- Banner Verde ESPE -->
    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #3d8e72ff 0%, #3da66aff 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if (file_exists(public_path('storage/logos/LOGO-ESPE_500.png')))
                        <img src="{{ asset('storage/logos/LOGO-ESPE_500.png') }}" alt="Logo ESPE"
                             style="width: 60px; height: 60px; object-fit: contain;" class="me-3">
                    @else
                        <div class="bg-white bg-opacity-25 rounded p-2 me-3">
                            <i class="bi bi-person-circle fs-3 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                            @if (Auth::id() === $user->id)
                                MI PERFIL
                            @else
                                PERFIL DE {{ strtoupper($name) }}
                            @endif
                        </h1>
                        <p class="mb-0 text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">
                            Gestión de información personal y configuración de cuenta
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #2d7a5f;">
            <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                <i class="bi bi-person-badge me-2"></i>Información Personal
            </h5>
        </div>
        <div class="card-body p-4">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" value="{{ $name }}" placeholder="{{ $name }}">
                            @error('name')
                                <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Correo</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        wire:model="email" value="{{ $email }}"
                                        placeholder="{{ $email }}">
                            @error('email')
                                <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        @can('gestionar usuarios')
                            <div class="col-12">
                                <label for="departamento_id" class="form-label fw-semibold">Departamento (Opcional)</label>
                                <select wire:model="departamento_id" class="form-select @error('departamento_id') is-invalid @enderror" id="departamento_id">
                                    <option value="">Sin departamento asignado</option>
                                    @foreach($departamentosDisponibles as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Asignar departamento solo para docentes</small>
                                @error('departamento_id')
                                    <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        @endcan

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        wire:model="password" placeholder="***************">
                            @error('password')
                                <span class="error invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password-confirm" class="form-label fw-semibold">Confirmar Contraseña</label>
                            <input id="password-confirm" type="password" class="form-control"
                                wire:model="password_confirmation" placeholder="***************">
                        </div>

                        <div class="col-12">
                            <p class="card-text mb-0"><small class="text-muted"><i class="bi bi-clock-history me-1"></i>Modificado por última vez: {{ $user->updated_at }}</small></p>
                        </div>

                        <div class="col-12">
                            <button type="button" wire:click.prevent="update()"
                                class="btn px-4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;">
                                <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @can('gestionar roles y permisos')
        <div class="card shadow-sm border-0">
            <div class="card-header py-3" style="background-color: #f8f9fa; border-bottom: 2px solid #2d7a5f;">
                <h5 class="mb-0 fw-bold" style="color: #2d7a5f;">
                    <i class="bi bi-shield-check me-2"></i>Gestión de Roles
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('users.updateRoles', encrypt($user->id)) }}" id="update_product_info"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <ul class="list-group text-start shadow-sm">
                        @php
                            $superAdminRole = $roles->where('name', 'Super Admin')->first();
                            $directorApoyo = $roles->whereIn('name', ['Director de Carrera', 'Docente de Apoyo']);
                            $docenteRole = $roles->where('name', 'Docente')->first();
                            $userHasDirectorOrApoyo = $user->hasRole('Director de Carrera') || $user->hasRole('Docente de Apoyo');
                        @endphp

                        {{-- 1. Super Admin (primero) --}}
                        @if($superAdminRole)
                            <li class="list-group-item list-group-item-action" style="border-left: 4px solid #3d8e72ff;">
                                <input @if ($user->hasrole('Super Admin')) checked @endif name="Super Admin"
                                    value="Super Admin" class="form-check-input me-2" type="checkbox"
                                    id="rol{{ $superAdminRole->id }}">

                                <label class="form-check-label stretched-link fw-semibold" for="rol{{ $superAdminRole->id }}">
                                    <i class="bi bi-star-fill text-warning me-1"></i>Super Admin
                                </label>
                            </li>
                        @endif

                        {{-- 2. Director de Carrera / Docente de Apoyo (segundo) --}}
                        @if($directorApoyo->count() > 0)
                            <li class="list-group-item list-group-item-action" style="border-left: 4px solid #3d8e72ff;">
                                @foreach($directorApoyo as $rol)
                                    <input @if ($user->hasrole($rol->name)) checked @endif name="{{ $rol->name }}"
                                        value="{{ $rol->name }}" class="form-check-input me-1" type="checkbox"
                                        id="rol{{ $rol->id }}" style="display: none;">
                                @endforeach

                                <input @if ($userHasDirectorOrApoyo) checked @endif
                                    name="Director de Carrera" value="Director de Carrera"
                                    class="form-check-input me-2" type="checkbox" id="rolDirectorApoyo"
                                    onchange="document.querySelectorAll('[name=\'Director de Carrera\'], [name=\'Docente de Apoyo\']').forEach(cb => cb.checked = this.checked)">

                                <label class="form-check-label stretched-link fw-semibold" for="rolDirectorApoyo">
                                    <i class="bi bi-briefcase-fill text-primary me-1"></i>Director de Carrera / Docente de Apoyo
                                </label>
                            </li>
                        @endif

                        {{-- 3. Docente (tercero) --}}
                        @if($docenteRole)
                            <li class="list-group-item list-group-item-action" style="border-left: 4px solid #3d8e72ff;">
                                <input @if ($user->hasrole('Docente')) checked @endif name="Docente"
                                    value="Docente" class="form-check-input me-2" type="checkbox"
                                    id="rol{{ $docenteRole->id }}">

                                <label class="form-check-label stretched-link fw-semibold" for="rol{{ $docenteRole->id }}">
                                    <i class="bi bi-person-video3 text-success me-1"></i>Docente
                                </label>
                            </li>
                        @endif
                    </ul>
                    <div class="mt-3">
                        <button type="submit" class="btn px-4" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none;">
                            <i class="bi bi-check-circle me-2"></i>Guardar Roles
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
    {{-- Los usuarios normales NO ven la sección de roles en su propio perfil --}}
</div>
