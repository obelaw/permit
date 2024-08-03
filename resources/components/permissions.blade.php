<x-dynamic-component :component="$getFieldWrapperView()">
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}'), states: {{ ($gates = $getRecord()?->permissions) ? json_encode($gates) : '[]' }} }">
        <div x-init="$watch('states', val => state = val)">

            @foreach ($getPermissions() as $resource)
                @if (!empty($resource))
                    <div class="flex items-center gap-4">
                        <input x-model="states" type="checkbox"
                            class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 transition duration-75 checked:ring-0 focus:ring-2 focus:ring-offset-0 disabled:pointer-events-none disabled:bg-gray-50 disabled:text-gray-50 disabled:checked:bg-current disabled:checked:text-gray-400 dark:bg-white/5 dark:disabled:bg-transparent dark:disabled:checked:bg-gray-600 text-primary-600 ring-gray-950/10 focus:ring-primary-600 checked:focus:ring-primary-500/50 dark:text-primary-500 dark:ring-white/20 dark:checked:bg-primary-500 dark:focus:ring-primary-500 dark:checked:focus:ring-primary-400/50 dark:disabled:ring-white/10"
                            id="{{ $resource['id'] }}" value="{{ $resource['id'] }}">
                        <label for="{{ $resource['id'] }}" class="grow cursor-pointer border-b dark:border-gray-700 py-4">
                            <p class="font-medium">{{ $resource['title'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $resource['description'] }}
                            </p>
                        </label>
                    </div>

                    @foreach ($resource['permissions'] as $permissionKay => $permissionValue)
                        <ul style="margin-left: 2rem;">
                            <li class="flex items-center gap-4">
                                <input x-model="states" type="checkbox"
                                    class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 transition duration-75 checked:ring-0 focus:ring-2 focus:ring-offset-0 disabled:pointer-events-none disabled:bg-gray-50 disabled:text-gray-50 disabled:checked:bg-current disabled:checked:text-gray-400 dark:bg-white/5 dark:disabled:bg-transparent dark:disabled:checked:bg-gray-600 text-primary-600 ring-gray-950/10 focus:ring-primary-600 checked:focus:ring-primary-500/50 dark:text-primary-500 dark:ring-white/20 dark:checked:bg-primary-500 dark:focus:ring-primary-500 dark:checked:focus:ring-primary-400/50 dark:disabled:ring-white/10"
                                    id="{{ $permissionKay }}" value="{{ $permissionKay }}">
                                <label for="{{ $permissionKay }}" class="grow cursor-pointer py-4">
                                    <p class="font-medium">{{ __($permissionValue) }}</p>
                                </label>
                            </li>
                        </ul>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
</x-dynamic-component>
