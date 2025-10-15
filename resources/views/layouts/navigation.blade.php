<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                     <x-nav-link :href="route('services.index')" :active="request()->routeIs('services.index')">
                        {{ __('Hizmetler') }}
                    </x-nav-link>
                    <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.index')">
                        {{ __('Müşteriler') }}
                    </x-nav-link>
                    <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.index')">
                        {{ __('Randevular') }}
                    </x-nav-link>
                    <x-nav-link :href="route('whatsapp-messages.index')" :active="request()->routeIs('whatsapp-messages.*')">
                        <div class="flex items-center">
                            <span class="mr-1">{{ __('WhatsApp') }}</span>
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-green-500 rounded-full">
                                {{ \App\Models\WhatsAppMessage::count() }}
                            </span>
                        </div>
                    </x-nav-link>
                    <x-nav-link :href="route('campaigns.index')" :active="request()->routeIs('campaigns.index')">
                        {{ __('Kampanyalar') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Notifications Bell (Tailwind + Alpine, no jQuery) -->
                <div x-data="notificationBell()" x-init="init()" class="relative mr-4">
                    <button @click="open = !open" type="button" class="relative inline-flex items-center p-2 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none" aria-expanded="false" aria-haspopup="true">
                        <!-- Heroicon: Bell -->
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span x-show="count > 0" x-text="count" class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full"></span>
                    </button>

                    <!-- Dropdown panel -->
                    <div x-show="open" @click.away="open = false" x-cloak class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="py-2 max-h-96 overflow-auto">
                            <div class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="count > 0 ? (count + ' Yeni Bildirim') : 'Yeni bildiriminiz yok'"></div>
                            <template x-if="items.length === 0">
                                <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Yeni bildiriminiz yok</div>
                            </template>
                            <template x-for="item in items" :key="item.id">
                                <a :href="item.url" @click.prevent="markAsReadThenGo(item)" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div class="flex justify-between text-sm">
                                        <span class="font-medium text-gray-800 dark:text-gray-100" x-text="item.title"></span>
                                        <span class="text-xs text-gray-500" x-text="item.time"></span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300" x-text="item.message"></div>
                                </a>
                            </template>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center justify-between">
                            <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">Tüm Bildirimleri Gör</a>
                            <button @click="markAllAsRead()" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Tümünü okundu işaretle</button>
                        </div>
                    </div>

                    <script>
                        function notificationBell() {
                            return {
                                open: false,
                                count: 0,
                                items: [],
                                init() {
                                    this.refresh();
                                    // 45 saniyede bir güncelle
                                    setInterval(() => this.refresh(), 45000);
                                },
                                async refresh() {
                                    try {
                                        const [countRes, itemsRes] = await Promise.all([
                                            fetch('{{ route('notifications.unread-count') }}'),
                                            fetch('{{ route('notifications.fetch') }}')
                                        ]);
                                        const countJson = await countRes.json();
                                        const itemsJson = await itemsRes.json();
                                        this.count = countJson.count ?? 0;
                                        this.items = Array.isArray(itemsJson) ? itemsJson : [];
                                    } catch (e) {
                                        // Sessizce geç
                                    }
                                },
                                async markAsReadThenGo(item) {
                                    if (item && item.id && !String(item.id).startsWith('appt_')) {
                                        try { await fetch(`/notifications/mark-as-read/${item.id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content } }); } catch (e) {}
                                    }
                                    window.location.href = item.url || '#';
                                },
                                async markAllAsRead() {
                                    try {
                                        await fetch('{{ route('notifications.mark-all-read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content } });
                                        this.count = 0;
                                        // öğelerde read_at alanı varsa güncelleyebiliriz, sadeleştiriyoruz
                                    } catch (e) {}
                                }
                            }
                        }
                    </script>
                </div>
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
