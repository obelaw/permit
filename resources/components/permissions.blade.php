<x-dynamic-component :component="$getFieldWrapperView()">
    <div 
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            search: '',
            collapsedGroups: {},

            init() {
                if (!Array.isArray(this.state)) {
                    this.state = {{ json_encode($getRecord()?->resource_permissions ?? []) }};
                }
            },

            isSelected(id) {
                return Array.isArray(this.state) && this.state.includes(id);
            },

            // Group Selection Logic
            isGroupAllSelected(childKeys) {
                if (!Array.isArray(this.state) || childKeys.length === 0) return false;
                return childKeys.every(key => this.state.includes(key));
            },

            isGroupSomeSelected(childKeys) {
                if (!Array.isArray(this.state) || childKeys.length === 0) return false;
                const selectedCount = childKeys.filter(key => this.state.includes(key)).length;
                return selectedCount > 0 && selectedCount < childKeys.length;
            },

            getSelectedCount(childKeys) {
                if (!Array.isArray(this.state)) return 0;
                return childKeys.filter(key => this.state.includes(key)).length;
            },

            toggleGroup(parentId, childKeys) {
                if (!Array.isArray(this.state)) this.state = [];
                const allSelected = this.isGroupAllSelected(childKeys);
                const keysToToggle = parentId ? [parentId, ...childKeys] : childKeys;

                if (allSelected) {
                    this.state = this.state.filter(id => !keysToToggle.includes(id));
                } else {
                    this.state = [...new Set([...this.state, ...keysToToggle])];
                }
            },

            // Global Actions
            selectAll(allKeys) {
                if (!Array.isArray(this.state)) this.state = [];
                this.state = [...new Set([...this.state, ...allKeys])];
            },

            deselectAll(allKeys) {
                if (!Array.isArray(this.state)) return;
                this.state = this.state.filter(id => !allKeys.includes(id));
            },

            // Collapse state toggle
            toggleCollapse(groupId) {
                this.collapsedGroups[groupId] = !this.collapsedGroups[groupId];
            },

            isCollapsed(groupId) {
                return !!this.collapsedGroups[groupId];
            },

            // Search filter matching helper
            matchesSearch(text, childValues = []) {
                if (!this.search.trim()) return true;
                const q = this.search.toLowerCase();
                if (text.toLowerCase().includes(q)) return true;
                return childValues.some(val => String(val).toLowerCase().includes(q));
            }
        }"
        class="space-y-4"
    >
        {{-- Resources View --}}
        @if ($getComponent() === 'resources')
            @php
                $allResourceKeys = [];
                foreach ($getPermissions() as $res) {
                    if (!empty($res['id']))
                        $allResourceKeys[] = $res['id'];
                    foreach (array_keys($res['permissions'] ?? []) as $key) {
                        $allResourceKeys[] = $key;
                    }
                }
            @endphp

            {{-- Top Toolbar: Search & Global Actions --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 bg-gray-50/80 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Search permissions..." 
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-primary-500 focus:ring-primary-500 pl-8 pr-3 py-1.5 dark:text-white"
                    >
                    <svg class="w-4 h-4 absolute left-2.5 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3 text-xs font-medium">
                    <span class="text-gray-500 dark:text-gray-400">
                        <strong x-text="Array.isArray(state) ? state.length : 0"></strong> Active
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <button type="button" @click="selectAll({{ json_encode($allResourceKeys) }})" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        Select All
                    </button>
                    <button type="button" @click="deselectAll({{ json_encode($allResourceKeys) }})" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        Clear
                    </button>
                </div>
            </div>

            {{-- List Grid --}}
            <div class="grid grid-cols-1 gap-4">
                @foreach ($getPermissions() as $resource)
                    @if (!empty($resource))
                        @php
                            $childKeys = array_keys($resource['permissions'] ?? []);
                            $childLabels = array_values($resource['permissions'] ?? []);
                        @endphp
                        <div 
                            x-show="matchesSearch('{{ addslashes($resource['title']) }}', {{ json_encode($childLabels) }})"
                            class="fi-section rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden"
                        >
                            {{-- Header --}}
                            <div class="flex items-center justify-between gap-3 p-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox"
                                        id="resource-{{ $resource['id'] }}"
                                        :checked="isGroupAllSelected({{ json_encode($childKeys) }})"
                                        :indeterminate="isGroupSomeSelected({{ json_encode($childKeys) }})"
                                        @change="toggleGroup('{{ $resource['id'] }}', {{ json_encode($childKeys) }})"
                                        class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:focus:ring-primary-600"
                                    >
                                    <label for="resource-{{ $resource['id'] }}" class="cursor-pointer select-none">
                                        <p class="font-semibold text-sm text-gray-950 dark:text-white">{{ $resource['title'] }}</p>
                                        @if (!empty($resource['description']))
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $resource['description'] }}</p>
                                        @endif
                                    </label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="getSelectedCount({{ json_encode($childKeys) }}) > 0 
                                            ? 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/30' 
                                            : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20'"
                                    >
                                        <span x-text="getSelectedCount({{ json_encode($childKeys) }})"></span> / {{ count($childKeys) }}
                                    </span>

                                    <button 
                                        type="button" 
                                        @click="toggleCollapse('res-{{ $resource['id'] }}')" 
                                        class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                    >
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isCollapsed('res-{{ $resource['id'] }}') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Child Grid --}}
                            @if (!empty($resource['permissions']))
                                <div x-show="!isCollapsed('res-{{ $resource['id'] }}')" class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                    @foreach ($resource['permissions'] as $permissionKey => $permissionValue)
                                        <label 
                                            x-show="matchesSearch('{{ addslashes(__($permissionValue)) }}')"
                                            class="flex items-center gap-2.5 p-2.5 rounded-lg border cursor-pointer transition select-none"
                                            :class="isSelected('{{ $permissionKey }}') 
                                                ? 'bg-primary-50/50 border-primary-200 dark:bg-primary-500/10 dark:border-primary-500/30' 
                                                : 'bg-white border-gray-100 hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-white/5'"
                                        >
                                            <input 
                                                x-model="state" 
                                                type="checkbox"
                                                id="perm-{{ $permissionKey }}" 
                                                value="{{ $permissionKey }}"
                                                class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:focus:ring-primary-600"
                                            >
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                {{ __($permissionValue) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Pages View --}}
        @if ($getComponent() === 'pages')
            @php
                $allPageKeys = [];
                foreach ($getPagePermissions() as $group => $permissions) {
                    $allPageKeys = array_merge($allPageKeys, array_column($permissions, 'id'));
                }
            @endphp

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 bg-gray-50/80 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Search pages..." 
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-primary-500 focus:ring-primary-500 pl-8 pr-3 py-1.5 dark:text-white"
                    >
                    <svg class="w-4 h-4 absolute left-2.5 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3 text-xs font-medium">
                    <span class="text-gray-500 dark:text-gray-400">
                        <strong x-text="Array.isArray(state) ? state.length : 0"></strong> Active
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <button type="button" @click="selectAll({{ json_encode($allPageKeys) }})" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        Select All
                    </button>
                    <button type="button" @click="deselectAll({{ json_encode($allPageKeys) }})" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        Clear
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @foreach ($getPagePermissions() as $group => $permissions)
                    @php
                        $pageIds = array_column($permissions, 'id');
                        $pageTitles = array_column($permissions, 'title');
                    @endphp
                    <div 
                        x-show="matchesSearch('{{ addslashes($group) }}', {{ json_encode($pageTitles) }})"
                        class="fi-section rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden"
                    >
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <input 
                                    type="checkbox"
                                    id="group-page-{{ Str::slug($group) }}"
                                    :checked="isGroupAllSelected({{ json_encode($pageIds) }})"
                                    :indeterminate="isGroupSomeSelected({{ json_encode($pageIds) }})"
                                    @change="toggleGroup(null, {{ json_encode($pageIds) }})"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:focus:ring-primary-600"
                                >
                                <label for="group-page-{{ Str::slug($group) }}" class="font-semibold text-sm text-gray-950 dark:text-white cursor-pointer select-none">
                                    {{ $group }}
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="getSelectedCount({{ json_encode($pageIds) }}) > 0 
                                        ? 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/30' 
                                        : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20'"
                                >
                                    <span x-text="getSelectedCount({{ json_encode($pageIds) }})"></span> / {{ count($pageIds) }}
                                </span>

                                <button type="button" @click="toggleCollapse('page-{{ Str::slug($group) }}')" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isCollapsed('page-{{ Str::slug($group) }}') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="!isCollapsed('page-{{ Str::slug($group) }}')" class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($permissions as $permission)
                                <label 
                                    x-show="matchesSearch('{{ addslashes(__($permission['title'])) }}')"
                                    class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition select-none"
                                    :class="isSelected('{{ $permission['id'] }}') 
                                        ? 'bg-primary-50/50 border-primary-200 dark:bg-primary-500/10 dark:border-primary-500/30' 
                                        : 'bg-white border-gray-100 hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-white/5'"
                                >
                                    <input 
                                        x-model="state" 
                                        type="checkbox"
                                        id="page-{{ $permission['id'] }}" 
                                        value="{{ $permission['id'] }}"
                                        class="fi-checkbox-input mt-0.5 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:focus:ring-primary-600"
                                    >
                                    <div>
                                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ __($permission['title']) }}</p>
                                        @if (!empty($permission['description']))
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $permission['description'] }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Widgets View --}}
        @if ($getComponent() === 'widgets')
            @php
                $allWidgetKeys = [];
                foreach ($getWidgetPermissions() as $group => $permissions) {
                    $allWidgetKeys = array_merge($allWidgetKeys, array_column($permissions, 'id'));
                }
            @endphp

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 bg-gray-50/80 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Search widgets..." 
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-primary-500 focus:ring-primary-500 pl-8 pr-3 py-1.5 dark:text-white"
                    >
                    <svg class="w-4 h-4 absolute left-2.5 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3 text-xs font-medium">
                    <span class="text-gray-500 dark:text-gray-400">
                        <strong x-text="Array.isArray(state) ? state.length : 0"></strong> Active
                    </span>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <button type="button" @click="selectAll({{ json_encode($allWidgetKeys) }})" class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        Select All
                    </button>
                    <button type="button" @click="deselectAll({{ json_encode($allWidgetKeys) }})" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        Clear
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @foreach ($getWidgetPermissions() as $group => $permissions)
                    @php
                        $widgetIds = array_column($permissions, 'id');
                        $widgetTitles = array_column($permissions, 'title');
                    @endphp
                    <div 
                        x-show="matchesSearch('{{ addslashes($group) }}', {{ json_encode($widgetTitles) }})"
                        class="fi-section rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden"
                    >
                        <div class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <input 
                                    type="checkbox"
                                    id="group-widget-{{ Str::slug($group) }}"
                                    :checked="isGroupAllSelected({{ json_encode($widgetIds) }})"
                                    :indeterminate="isGroupSomeSelected({{ json_encode($widgetIds) }})"
                                    @change="toggleGroup(null, {{ json_encode($widgetIds) }})"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:focus:ring-primary-600"
                                >
                                <label for="group-widget-{{ Str::slug($group) }}" class="font-semibold text-sm text-gray-950 dark:text-white cursor-pointer select-none">
                                    {{ $group }}
                                </label>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="getSelectedCount({{ json_encode($widgetIds) }}) > 0 
                                        ? 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/30' 
                                        : 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20'"
                                >
                                    <span x-text="getSelectedCount({{ json_encode($widgetIds) }})"></span> / {{ count($widgetIds) }}
                                </span>

                                <button type="button" @click="toggleCollapse('widget-{{ Str::slug($group) }}')" class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isCollapsed('widget-{{ Str::slug($group) }}') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="!isCollapsed('widget-{{ Str::slug($group) }}')" class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($permissions as $permission)
                                <label 
                                    x-show="matchesSearch('{{ addslashes(__($permission['title'])) }}')"
                                    class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition select-none"
                                    :class="isSelected('{{ $permission['id'] }}') 
                                        ? 'bg-primary-50/50 border-primary-200 dark:bg-primary-500/10 dark:border-primary-500/30' 
                                        : 'bg-white border-gray-100 hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-800 dark:hover:bg-white/5'"
                                >
                                    <input 
                                        x-model="state" 
                                        type="checkbox"
                                        id="widget-{{ $permission['id'] }}" 
                                        value="{{ $permission['id'] }}"
                                        class="fi-checkbox-input mt-0.5 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:focus:ring-primary-600"
                                    >
                                    <div>
                                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ __($permission['title']) }}</p>
                                        @if (!empty($permission['description']))
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $permission['description'] }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-dynamic-component>