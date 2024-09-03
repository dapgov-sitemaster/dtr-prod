<div>
    
    <div class="flex w-full min-h-screen">
        <div class="w-96 lg:min-h-96 rounded-xl border-4 border-dap-secondary m-auto p-6">
            {{-- <div class="my-2">
                <img src="{{ asset('image/logo-w-label.png') }}" class="w-48 mx-auto">
                <div class="text-center text-xl font-semibold">Daily Time Record</div>
            </div>
            <div class="my-10 text-center px-14">
                @livewire('sign-in.index')
            </div> --}}
            
            <div class="mb-2">
                <img src="{{ asset('image/logo-w-label.png') }}" class="w-48 mx-auto" draggable="false">
                <div class="text-center text-xl font-semibold">Daily Time Record</div>
            </div>
            <div class="mb-2 text-gray-600 dark:text-gray-400">
                {{ __('You can now reset your password. Please provide a strong password.') }}
            </div>
            <div class="mb-4 text-gray-600 dark:text-gray-400">
                {{ __('Minimum of 6 characters.') }}
            </div>
            <form>
                <div class="mt-2 mb-4">
                    <label class="font-semibold">New Password:</label><br/>
                    <x-filament::input.wrapper :valid="! $errors->has('password')" x-data="{ showPassword: true }">
                        <x-filament::input
                            x-bind:type="showPassword ? 'password' : 'text'"
                            wire:model.live="password"
                            wire:keydown.enter='submit'
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
                    <x-filament::input.wrapper :valid="! $errors->has('password')" x-data="{ showConfirmPassword: true }">
                        <x-filament::input
                            x-bind:type="showConfirmPassword ? 'password' : 'text'"
                            wire:model.live="password_confirmation"
                            wire:keydown.enter='submit'
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
                <div class="text-right">
                    <x-button wire:click.prevent="submit" color="primary" class="p-1 mx-1">{{ __('Reset Password') }}</x-button>
                </div>
            </form>

            {{-- @dd($token) --}}
        </div>
    </div>
</div>

