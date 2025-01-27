<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">


    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
        <!-- Team Card -->
        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6">
    <div class="flex items-start space-x-8">
        <div class="p-3 bg-indigo-100 rounded-lg">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Team Name</h3>
            <div class="flex items-center mt-2 space-x-4">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $team->name ?? 'No name'}}
                </p>
            </div>
        </div>
    </div>
</div>


        <!-- Subscription Type Card -->
  <!-- Subscription Type Card -->
<!-- Subscription Type Card -->
<div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6">
    <div class="flex items-start space-x-8">
        <div class="p-3 bg-indigo-100 rounded-lg">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <div class="flex-grow">
            <h3 class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Subscription</h3>
            <div class="flex items-center justify-between mt-2">
                <p class="text-lg font-semibold text-gray-900 dark:text-white mr-4">
                    {{ $subscription->subscription_type ?? 'N/A' }}
                </p>
                @if(($subscription->subscription_type ?? '') === 'Trial')
                <a href="#"
                   class="inline-flex items-center px-3 py-1.5 bg-white border border-indigo-600 text-indigo-600 text-sm font-medium rounded-md hover:bg-indigo-50 transition-all duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Upgrade
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

        <!-- Expiry Date Card -->
        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6">
    <div class="flex items-start space-x-8">
        <div class="p-3 bg-indigo-100 rounded-lg">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Expires On</h3>
            <div class="flex items-center mt-2 space-x-4">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $subscription->subscription_expiredDate ? date('M d, Y', strtotime($subscription->subscription_expiredDate)) : 'N/A' }}
                </p>
            </div>
        </div>
    </div>
</div>
    <!-- Team Workers Section -->
    <div class="px-6 pb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Team Workers</h3>
            <button class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 shadow-sm">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <!-- Increased mr-3 -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Worker
            </button>
        </div>

        @if($workers->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                @livewire('list-workers', ['team' => $team])
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-gray-500 dark:text-gray-400">No workers found in the team.</p>
            </div>
        @endif
    </div>
</div>
