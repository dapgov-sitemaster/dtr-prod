<div>
    <div class="flex w-full h-screen bg-gray-200">
        <div class="m-auto flex h-auto">
            <div class="bg-white w-96 h-[28rem] p-6 border-4 border-dap-secondary lg:rounded-l-xl rounded-none">
                <div>
                    <img src="{{ asset('image/edtr-banner.png') }}" alt="" class=" w-64" draggable=false>
                </div>
                <div class="mt-5">
                    <div class="my-2 mt-10">
                        @error('credentials')
                            <p class="text-sm text-red-600 dark:text-red-400 m-1" >{{ $message }} </p>
                        @enderror
                        <div>
                            <x-label for="email" value="{{ __('Email') }}" />
                            <x-input wire:model='email' wire:keydown.enter='login' id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email')" required autofocus autocomplete="email" />
                            @error('email')
                                <x-input-error for="email" class="m-1 text-xs" >{{ $message }} </x-input-error>
                            @enderror
                        </div>

                        <div class="my-4">
                            <x-label for="password" value="{{ __('Password') }}" />
                            <x-input wire:model='password' wire:keydown.enter='login' id="password" class="block mt-1 w-full" type="password" name="password"
                                required autocomplete="current-password" />
                            @error('password')
                                <x-input-error for="password" class="m-1 text-xs" >{{ $message }} </x-input-error>
                            @enderror
                        </div>

                        <div class="block mt-4">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" wire:model='remember' />
                                <span
                                    class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <div class="mt-2 text-right">
                            <x-button wire:click.prevent='login' class="ml-2" color="primary">
                                {{ __('Log in') }}
                            </x-button>
                        </div>
                        {{-- <div class="text-right">
                            <a class="hover:underline text-sm text-gray-700 font-semibold dark:text-gray-400 rounded-md focus:outline-none"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="bg-dap-secondary w-96 h-[28rem] m-auto rounded-r-xl lg:block hidden">
                <div class="w-full flex justify-end">
                    <img src="{{ asset('image/applogo-white-edited.png') }}" alt="" class="h-80 opacity-40"
                        draggable=false>
                </div>
            </div>

        </div>
    </div>

</div>
