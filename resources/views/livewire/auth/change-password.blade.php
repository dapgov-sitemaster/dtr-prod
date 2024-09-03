<div>
    <x-loading wire:loading wire:target="submit" />
    <div class="w-full bg-white p-8 rounded-xl mb-10">
        <div class="text-3xl font-bold text-dap-primary underline decoration-dap-secondary/50">
            Change Password
        </div>
        <div class="mt-10 text-center mx-auto">
            <div class="my-2">
                <img src="{{ asset('image/vertical-logo.png') }}" class="w-10 mx-auto" draggable="false">
                {{-- <div class="text-center text-xl font-semibold">Change Default Password</div> --}}
                <div class="text-center">Welcome,<br/> <span class=" font-semibold">{{ Auth::user()->email }}</span></div>
            </div>
            <div class=" my-4">
                <span class=" font-semibold">Create a strong password</span><br/>
                <span>Create a new, strong password that you don’t use for other websites</span>
            </div>
            <div class="mx-auto">
                <div class="mt-2 mb-4">
                    <label class="font-semibold">New Password:</label><br/>
                    <x-filament::input.wrapper :valid="! $errors->has('password')" x-data="{ showPassword: true }" class="mx-auto w-96 lg:w-full">
                        <x-filament::input
                            x-bind:type="showPassword ? 'password' : 'text'"
                            wire:model.live="password"
                            wire:keydown.enter='submit'
                            autocomplete="new-password"
                        />

                        <x-slot name="suffix">
                            <span type="button" @click="showPassword = ! showPassword">
                                <span x-show="!showPassword" class="text-dap-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </span>
                                <span x-show="showPassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </span>
                            </span>
                        </x-slot>
                    </x-filament::input.wrapper>
                    @error('password')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-2 mb-4">
                    <label class="font-semibold">Confirm New Password:</label><br/>
                    <x-filament::input.wrapper :valid="! $errors->has('password')" x-data="{ showConfirmPassword: true }" class="mx-auto w-96 lg:w-full">
                        <x-filament::input
                            x-bind:type="showConfirmPassword ? 'password' : 'text'"
                            wire:model.live="password_confirmation"
                            wire:keydown.enter='submit'
                            autocomplete="off"
                        />

                        <x-slot name="suffix">
                            <span type="button" @click="showConfirmPassword = ! showConfirmPassword">
                                <span x-show="!showConfirmPassword" class="text-dap-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </span>
                                <span x-show="showConfirmPassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </span>
                            </span>
                        </x-slot>
                    </x-filament::input.wrapper>
                </div>

                {{-- @if($status)
                <div class="w-full text-sm text-green-700 mb-4 p-2">
                    A reset link has been sent to your email address.
                </div>
                @endif --}}
                <div class="">
                    <x-button wire:click.prevent="submit" color="primary" class="p-1 mx-1">{{ __('Change Password') }}</x-button>
                </div>
            </div>
        </div>
    </div>
</div>
