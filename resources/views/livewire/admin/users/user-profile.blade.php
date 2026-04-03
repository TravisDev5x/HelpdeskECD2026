<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">Imagen de perfil</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <img src="{{ $user->avatar ? asset('uploads/avatars/'.$user->avatar) : asset('uploads/avatars/default.png') }}" style="width:150px; height:150px; float:left; border-radius:50%; margin-right:25px;" alt="">
                            <h2>{{ $user->name }}</h2>
                        </div>
                        <div class="col-md-12 pt-4">
                            <form wire:submit.prevent="saveAvatar">
                                @csrf
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="row">
                                                <label for="avatar" class="col-md-2 col-form-label">Imagen</label>
                                                <div class="custom-file col-md-10">
                                                    <input type="file" class="custom-file-input @error('avatar') is-invalid @enderror" id="avatar" wire:model="avatar" accept="image/*">
                                                    <label class="custom-file-label" for="avatar">Selecciona una foto para tu perfil</label>
                                                    @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 pt-2">
                                        <div wire:loading wire:target="avatar" class="small text-muted mb-2">Cargando archivo…</div>
                                        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="saveAvatar,avatar">Actualizar imagen</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">Datos personales</div>
                <div class="card-body">
                    <form wire:submit.prevent="saveProfile">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nombre:</label>
                            <input id="name" name="name" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input id="email" name="email" type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input id="password" type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Contraseña">
                            <span class="text-muted small">Dejar en blanco para no cambiar la contraseña</span>
                            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Repite la contraseña:</label>
                            <input id="password_confirmation" type="password" wire:model="password_confirmation" class="form-control" placeholder="Repite la contraseña">
                        </div>
                        <button class="btn btn-primary btn-block" type="submit" wire:loading.attr="disabled" wire:target="saveProfile">Actualizar información</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
