<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Page Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Conversation Management</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Manage and monitor user conversations across the platform.
                            </p>
                        </div>
                        <a href="{{ route('users.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back
                        </a>
                    </div>

                    <!-- User Info (if filtering by user) -->
                    @if($userId && $user)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-lg font-medium text-gray-700 dark:text-gray-300">
                                            {{ substr($user->name, 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $user->name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Member since {{ $user->created_at->format('M j, Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Conversations Table -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                        <livewire:tables.conversation-table :userId="$userId" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
