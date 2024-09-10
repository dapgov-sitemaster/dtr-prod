<div>
    <x-loading wire:target="submit" />
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
                <div class="mb-4 text-gray-600 dark:text-gray-400">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                <form>
                    <div class="mt-2 mb-4">
                        <label class="font-semibold">DAP Email Address:</label><br/>
                        <x-filament::input.wrapper :valid="! $errors->has('email')">
                            <x-filament::input
                                type="text"
                                wire:model.live="email"
                            />
                        </x-filament::input.wrapper>
                        @error('email')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                        {{-- <label class="font-semibold">Email:</label><br/>

                        <input wire:model.live="email" type="email" class="w-full py-3 px-3 focus:outline-none rounded-lg @error('email') border-2 border-red-500 @enderror" autocomplete="off"> --}}

                    </div>
                    @if($status)
                    <div class="w-full text-sm text-green-700 mb-4 p-2">
                        A reset link has been sent to your email address.
                    </div>
                    @endif
                    @error('spam')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                    <div class="text-right">
                        <x-filament::button wire:click.prevent="submit">
                            {{ __('Email Password Reset Link') }}
                        </x-filament::button>
                        {{-- <x-button wire:click.prevent="submit" color="primary" class="p-1 mx-1">{{ __('Email Password Reset Link') }}</x-button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
