<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Page Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">User Management</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Manage and monitor users across the platform.
                            </p>
                        </div>
                        <a href="{{ route('users.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add New User
                        </a>
                    </div>

                    <!-- Users Table -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                        <livewire:tables.user-table />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
