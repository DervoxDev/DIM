<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('messages.Business Analytics') }}
            </h2>
            <div class="mt-4 md:mt-0">
                <div id="dateRangeSelector" class="flex items-center">
                    <select id="timeframeSelect" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-sm shadow-sm">
                        <option value="daily">{{ __('messages.Daily') }}</option>
                        <option value="24hours">{{ __('messages.Last 24 Hours') }}</option>
                        <option value="weekly" selected>{{ __('messages.Weekly') }}</option>
                        <option value="monthly">{{ __('messages.Monthly') }}</option>
                        <option value="yearly">{{ __('messages.Yearly') }}</option>
                        <option value="all">{{ __('messages.All Time') }}</option>
                    </select>
                    <button id="applyDateRange" class="ml-3 rtl:mr-3 rtl:ml-0 inline-flex items-center bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 shadow-sm">
                        {{ __('messages.Apply') }}
                    </button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Business Overview Cards - Displayed in columns -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">
                        {{ __('messages.Business Overview') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
                        <!-- Total Sales Card -->
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/30 dark:to-indigo-800/20 rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ __('messages.Total Sales') }}</p>
                                    <h4 id="total-sales-value" class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">0</h4>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span id="sales-trend" class="flex items-center text-green-500">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    0%
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 ltr:ml-2 rtl:mr-2">{{ __('messages.vs all time') }}</span>
                            </div>
                        </div>
                        
                        <!-- Total Orders Card -->
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/20 rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ __('messages.Total Orders') }}</p>
                                    <h4 id="total-orders-value" class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">0</h4>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span id="orders-trend" class="flex items-center text-green-500">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    0%
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 ltr:ml-2 rtl:mr-2">{{ __('messages.vs all time') }}</span>
                            </div>
                        </div>
                        
                        <!-- Average Sale Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('messages.Average Sale') }}</p>
                                    <h4 id="average-sale-value" class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">0</h4>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span id="average-trend" class="flex items-center text-green-500">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    0%
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 ltr:ml-2 rtl:mr-2">{{ __('messages.vs all time') }}</span>
                            </div>
                        </div>
                        
                        <!-- Total Purchases Card -->
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/30 dark:to-amber-800/20 rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ __('messages.Total Purchases') }}</p>
                                    <h4 id="total-purchases-value" class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">0</h4>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span id="purchases-trend" class="flex items-center text-green-500">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    0%
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 ltr:ml-2 rtl:mr-2">{{ __('messages.vs all time') }}</span>
                            </div>
                        </div>
                        
                        <!-- Low Stock Items Card -->
                        <div class="bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/30 dark:to-rose-800/20 rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-rose-600 dark:text-rose-400">{{ __('messages.Low Stock Items') }}</p>
                                    <h4 id="low-stock-value" class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">0</h4>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                    <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span id="inventory-alert" class="text-rose-500 flex items-center">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    {{ __('messages.Need attention') }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Profit Margin Card -->
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/30 dark:to-emerald-800/20 rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ __('messages.Profit Margin') }}</p>
                                    <h4 id="profit-margin-value" class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">0%</h4>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-sm">
                                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center text-sm">
                                <span id="profit-margin-trend" class="flex items-center text-green-500">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    0%
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 ltr:ml-2 rtl:mr-2">{{ __('messages.vs all time') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
     <!-- Sales & Purchases Charts Side by Side -->
<div class="flex flex-col lg:flex-row gap-6">
    <!-- Sales Chart -->
    <div class="w-full lg:w-1/2 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('messages.Sales Performance') }}
            </h3>
            <div id="sales-chart-container" class="h-80 relative">
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading chart...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchases Chart -->
    <div class="w-full lg:w-1/2 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('messages.Purchase Expenses') }}
            </h3>
            <div id="purchase-chart-container" class="h-80 relative">
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-rose-500"></div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading chart...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Top Products and Customers Side by Side -->
<div class="flex flex-col lg:flex-row gap-6">
    <!-- Top Products -->
    <div class="w-full lg:w-1/2 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('messages.Top Selling Products') }}
            </h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800/30 dark:text-indigo-300">
                {{ __('messages.Top 5') }}
            </span>
        </div>
        <div class="overflow-auto max-h-[350px] scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th class="w-3/5 px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider rounded-tl-lg">
                            {{ __('messages.Product') }}
                        </th>
                        <th class="w-1/5 px-4 py-3 bg-gray-50 dark:bg-gray-700 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.Quantity') }}
                        </th>
                        <th class="w-1/5 px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider rounded-tr-lg">
                            {{ __('messages.Revenue') }}
                        </th>
                    </tr>
                </thead>
                <tbody id="top-products-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-center">
                            <div class="py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading products...') }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Top Customers -->
    <div class="w-full lg:w-1/2 bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.Top Customers') }}
                </h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800/30 dark:text-indigo-300">
                    {{ __('messages.Top 5') }}
                </span>
            </div>
            <div class="overflow-auto max-h-[350px] scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                <table class="w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="sticky top-0 z-10">
                        <tr>
                            <th class="w-3/5 px-4 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider rounded-tl-lg">
                                {{ __('messages.Customer') }}
                            </th>
                            <th class="w-1/5 px-4 py-3 bg-gray-50 dark:bg-gray-700 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('messages.Orders') }}
                            </th>
                            <th class="w-1/5 px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider rounded-tr-lg">
                                {{ __('messages.Total Spent') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody id="top-customers-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center">
                                <div class="py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading customers...') }}</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


            <!-- Low Stock Alert Items Grid -->
            <!-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.Low Stock Alert Items') }}
                        </h3>
                        <span id="low-stock-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
                            <span id="low-stock-count">0</span> {{ __('messages.items') }}
                        </span>
                    </div>
                    <div id="low-stock-alerts-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div class="col-span-full flex justify-center py-8">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-rose-500"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading inventory data...') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide the date inputs since we're only using the timeframe selector
            document.querySelectorAll('#startDate, #endDate').forEach(el => {
                el.style.display = 'none';
            });
            
            // Set default dates as hidden values for the API
            const today = new Date();
            const oneWeekAgo = new Date();
            oneWeekAgo.setDate(today.getDate() - 7);
            
            // Create hidden inputs for dates
            const startDateInput = document.createElement('input');
            startDateInput.type = 'hidden';
            startDateInput.id = 'startDate';
            startDateInput.value = oneWeekAgo.toISOString().split('T')[0];
            
            const endDateInput = document.createElement('input');
            endDateInput.type = 'hidden';
            endDateInput.id = 'endDate';
            endDateInput.value = today.toISOString().split('T')[0];
            
            document.getElementById('dateRangeSelector').appendChild(startDateInput);
            document.getElementById('dateRangeSelector').appendChild(endDateInput);
            
            // Initialize charts
            let salesChart = null;
            let purchaseChart = null;
            
            // Load data on initial page load
            loadAllData();
            
            // Event listeners for controls
            document.getElementById('applyDateRange').addEventListener('click', function() {
                updateDatesBasedOnTimeframe();
                loadAllData();
            });
            
            document.getElementById('timeframeSelect').addEventListener('change', function() {
                updateDatesBasedOnTimeframe();
            });
            // Function to update dates based on selected timeframe
            function updateDatesBasedOnTimeframe() {
                const timeframe = document.getElementById('timeframeSelect').value;
                const today = new Date();
                let startDate = new Date();
                
                switch(timeframe) {
                    case 'daily':
                        startDate = new Date(today);
                        startDate.setHours(0, 0, 0, 0);
                        break;
                    case '24hours':
                        startDate = new Date(today);
                        startDate.setHours(today.getHours() - 24);
                        break;
                    case 'weekly':
                        startDate = new Date(today);
                        startDate.setDate(today.getDate() - 7);
                        break;
                    case 'monthly':
                        startDate = new Date(today);
                        startDate.setMonth(today.getMonth() - 1);
                        break;
                    case 'yearly':
                        startDate = new Date(today);
                        startDate.setFullYear(today.getFullYear() - 1);
                        break;
                    case 'all':
                        startDate = new Date(2000, 0, 1); // Far in the past
                        break;
                }
                
                document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
                document.getElementById('endDate').value = today.toISOString().split('T')[0];
            }
            
            // Load all data from the API endpoints
            function loadAllData() {
                const timeframe = document.getElementById('timeframeSelect').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                // Show loading state in containers
                showLoadingState('sales-chart-container');
                showLoadingState('purchase-chart-container');
                showLoadingState('top-products-body', 'table');
                showLoadingState('top-customers-body', 'table');
                showLoadingState('low-stock-alerts-container', 'grid');
                
                loadSalesData(timeframe, startDate, endDate);
                loadPurchaseData(timeframe, startDate, endDate);
                loadInventoryData(timeframe, startDate, endDate);
                loadCustomerData(timeframe, startDate, endDate);
                loadOverallData(timeframe, startDate, endDate);
            }
            
            // Show loading animation
            function showLoadingState(containerId, type = 'chart') {
                const container = document.getElementById(containerId);
                if (!container) return;
                
                if (type === 'chart') {
                    container.innerHTML = `
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading chart...') }}</p>
                            </div>
                        </div>
                    `;
                } else if (type === 'table') {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td colspan="3" class="px-4 py-4 text-center">
                            <div class="py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading data...') }}</p>
                            </div>
                        </td>
                    `;
                    container.innerHTML = '';
                    container.appendChild(row);
                } else if (type === 'grid') {
                    container.innerHTML = `
                        <div class="col-span-full flex justify-center py-8">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-rose-500"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.Loading items...') }}</p>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Show error state in containers
            function showErrorState(containerId, message, type = 'chart') {
                const container = document.getElementById(containerId);
                if (!container) return;
                
                if (type === 'chart') {
                    container.innerHTML = `
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center max-w-md">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.Error Loading Data') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${message}</p>
                                <div class="mt-3">
                                    <button type="button" onclick="location.reload()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        {{ __('messages.Retry') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                } else if (type === 'table') {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td colspan="3" class="px-4 py-4 text-center">
                            <div class="py-4">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">${message}</p>
                            </div>
                        </td>
                    `;
                    container.innerHTML = '';
                    container.appendChild(row);
                } else if (type === 'grid') {
                    container.innerHTML = `
                        <div class="col-span-full flex justify-center py-8">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.Error Loading Data') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${message}</p>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Fetch sales data from API
            function loadSalesData(timeframe, startDate, endDate) {
                const params = new URLSearchParams({
                    timeframe: timeframe,
                    start_date: startDate,
                    end_date: endDate
                });
                
                fetch(`/api/v1/analytics/sales?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            initializeSalesChart(data.data);
                            updateTopProducts(data.data.top_products);
                            
                            // Update summary cards
                            document.getElementById('total-sales-value').textContent = formatCurrency(data.data.summary.total_revenue);
                            document.getElementById('total-orders-value').textContent = data.data.summary.total_orders;
                            document.getElementById('average-sale-value').textContent = formatCurrency(data.data.summary.average_sale);
                            
                            // Add profit margin calculation (if available in your data)
                            if (data.data.summary.profit_margin) {
                                document.getElementById('profit-margin-value').textContent = 
                                    (data.data.summary.profit_margin * 100).toFixed(1) + '%';
                                
                                // Calculate trend for profit margin
                                const profitMarginTrend = calculateTrend(
                                    data.data.summary.profit_margin, 
                                    data.data.all_time.profit_margin || 0.2
                                );
                                updateTrendIndicator('profit-margin-trend', profitMarginTrend);
                            }
                            
                            // Calculate and display trends
                            const salesTrend = calculateTrend(data.data.summary.total_revenue, data.data.all_time.total_revenue / 30);
                            const ordersTrend = calculateTrend(data.data.summary.total_orders, data.data.all_time.total_orders / 30);
                            const avgTrend = calculateTrend(data.data.summary.average_sale, data.data.all_time.average_sale);
                            
                            updateTrendIndicator('sales-trend', salesTrend);
                            updateTrendIndicator('orders-trend', ordersTrend);
                            updateTrendIndicator('average-trend', avgTrend);
                        } else {
                            showErrorState('sales-chart-container', data.message || '{{ __("messages.Failed to load sales data") }}');
                            showErrorState('top-products-body', data.message || '{{ __("messages.Failed to load product data") }}', 'table');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading sales data:', error);
                        showErrorState('sales-chart-container', '{{ __("messages.Could not connect to the server") }}');
                        showErrorState('top-products-body', '{{ __("messages.Could not connect to the server") }}', 'table');
                    });
            }
            
            // Fetch purchase data from API
            function loadPurchaseData(timeframe, startDate, endDate) {
                const params = new URLSearchParams({
                    timeframe: timeframe,
                    start_date: startDate,
                    end_date: endDate
                });
                
                fetch(`/api/v1/analytics/purchases?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            initializePurchaseChart(data.data);
                            
                            // Update purchases card
                            if (data.data.summary && data.data.all_time) {
                                document.getElementById('total-purchases-value').textContent = formatCurrency(data.data.summary.total_cost);
                                const purchasesTrend = calculateTrend(data.data.summary.total_cost, data.data.all_time.total_cost / 30);
                                updateTrendIndicator('purchases-trend', purchasesTrend);
                            }
                        } else {
                            showErrorState('purchase-chart-container', data.message || '{{ __("messages.Failed to load purchase data") }}');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading purchase data:', error);
                        showErrorState('purchase-chart-container', '{{ __("messages.Could not connect to the server") }}');
                    });
            }
            
            // Fetch inventory data from API
            function loadInventoryData(timeframe, startDate, endDate) {
                const params = new URLSearchParams({
                    timeframe: timeframe,
                    start_date: startDate,
                    end_date: endDate
                });
                
                fetch(`/api/v1/analytics/inventory?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            updateLowStockAlerts(data.data.low_stock_alerts);
                            
                            // Update count in badge and card
                            const lowStockCount = data.data.low_stock_alerts ? data.data.low_stock_alerts.length : 0;
                            document.getElementById('low-stock-value').textContent = lowStockCount;
                            document.getElementById('low-stock-count').textContent = lowStockCount;
                            
                            // Show/hide alert status
                            if (lowStockCount > 0) {
                                document.getElementById('low-stock-badge').classList.add('bg-rose-100', 'text-rose-800');
                                document.getElementById('low-stock-badge').classList.remove('bg-green-100', 'text-green-800');
                            } else {
                                document.getElementById('low-stock-badge').classList.remove('bg-rose-100', 'text-rose-800');
                                document.getElementById('low-stock-badge').classList.add('bg-green-100', 'text-green-800');
                            }
                        } else {
                            showErrorState('low-stock-alerts-container', data.message || '{{ __("messages.Failed to load inventory data") }}', 'grid');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading inventory data:', error);
                        showErrorState('low-stock-alerts-container', '{{ __("messages.Could not connect to the server") }}', 'grid');
                    });
            }
            
            // Fetch customer data from API
            function loadCustomerData(timeframe, startDate, endDate) {
                const params = new URLSearchParams({
                    timeframe: timeframe,
                    start_date: startDate,
                    end_date: endDate
                });
                
                fetch(`/api/v1/analytics/customers?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            updateTopCustomers(data.data.top_customers);
                        } else {
                            showErrorState('top-customers-body', data.message || '{{ __("messages.Failed to load customer data") }}', 'table');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading customer data:', error);
                        showErrorState('top-customers-body', '{{ __("messages.Could not connect to the server") }}', 'table');
                    });
            }
            
            // Fetch overall dashboard data from API
            function loadOverallData(timeframe, startDate, endDate) {
                const params = new URLSearchParams({
                    timeframe: timeframe,
                    start_date: startDate,
                    end_date: endDate
                });
                
                fetch(`/api/v1/analytics/dashboard?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // We can use this for additional data if needed
                        console.log('Overall data loaded successfully');
                    })
                    .catch(error => {
                        console.error('Error loading overall data:', error);
                    });
            }
            // Initialize and update the sales chart
            function initializeSalesChart(data) {
                const container = document.getElementById('sales-chart-container');
                container.innerHTML = '<canvas id="salesChart"></canvas>';
                
                const ctx = document.getElementById('salesChart').getContext('2d');
                const labels = generateLabels(data.period_info.timeframe, data.history.length);
                
                salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '{{ __("messages.Sales") }}',
                            data: data.history,
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    color: document.documentElement.classList.contains('dark') ? 'rgb(229, 231, 235)' : 'rgb(55, 65, 81)',
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: document.documentElement.classList.contains('dark') ? 'rgb(31, 41, 55)' : 'rgb(255, 255, 255)',
                                titleColor: document.documentElement.classList.contains('dark') ? 'rgb(229, 231, 235)' : 'rgb(31, 41, 55)',
                                bodyColor: document.documentElement.classList.contains('dark') ? 'rgb(209, 213, 219)' : 'rgb(75, 85, 99)',
                                borderColor: document.documentElement.classList.contains('dark') ? 'rgb(55, 65, 81)' : 'rgb(229, 231, 235)',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return formatCurrency(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgba(107, 114, 128, 0.1)' : 'rgba(107, 114, 128, 0.1)'
                                },
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgb(209, 213, 219)' : 'rgb(75, 85, 99)',
                                    callback: function(value) {
                                        return formatCurrency(value, false);
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgb(209, 213, 219)' : 'rgb(75, 85, 99)'
                                }
                            }
                        }
                    }
                });
            }
            
            // Initialize and update the purchase chart
            function initializePurchaseChart(data) {
                const container = document.getElementById('purchase-chart-container');
                container.innerHTML = '<canvas id="purchaseChart"></canvas>';
                
                const ctx = document.getElementById('purchaseChart').getContext('2d');
                const labels = generateLabels(data.period_info.timeframe, data.history.length);
                
                purchaseChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '{{ __("messages.Purchases") }}',
                            data: data.history,
                            borderColor: 'rgb(220, 38, 38)',
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            pointRadius: 0,
                            tension: 0.4
                            
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    color: document.documentElement.classList.contains('dark') ? 'rgb(229, 231, 235)' : 'rgb(55, 65, 81)',
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: document.documentElement.classList.contains('dark') ? 'rgb(31, 41, 55)' : 'rgb(255, 255, 255)',
                                titleColor: document.documentElement.classList.contains('dark') ? 'rgb(229, 231, 235)' : 'rgb(31, 41, 55)',
                                bodyColor: document.documentElement.classList.contains('dark') ? 'rgb(209, 213, 219)' : 'rgb(75, 85, 99)',
                                borderColor: document.documentElement.classList.contains('dark') ? 'rgb(55, 65, 81)' : 'rgb(229, 231, 235)',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return formatCurrency(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgba(107, 114, 128, 0.1)' : 'rgba(107, 114, 128, 0.1)'
                                },
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgb(209, 213, 219)' : 'rgb(75, 85, 99)',
                                    callback: function(value) {
                                        return formatCurrency(value, false);
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgb(209, 213, 219)' : 'rgb(75, 85, 99)'
                                }
                            }
                        }
                    }
                });
            }
            
          // Update the top products table
function updateTopProducts(products) {
    const tbody = document.getElementById('top-products-body');
    tbody.innerHTML = '';
    
    if (products && products.length) {
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-500 rounded-full">
                              <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        </div>
                        
                        <div class="ltr:ml-6 rtl:mr-6">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(product.name)}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(product.description)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300">
                        ${product.total_quantity}
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white font-medium">
                    ${product.total_revenue}
                </td>
            `;
            tbody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="3" class="px-4 py-5 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.No product data available') }}
            </td>
        `;
        tbody.appendChild(row);
    }
}

// Update the top customers table
function updateTopCustomers(customers) {
    const tbody = document.getElementById('top-customers-body');
    tbody.innerHTML = '';
    
    if (customers && customers.length) {
        customers.forEach(customer => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 dark:bg-blue-900/30 text-blue-500 rounded-full">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        </div>
                        <div class="ltr:ml-6 rtl:mr-6">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">${escapeHtml(customer.name)}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(customer.email || '')}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                        ${customer.total_purchases}
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white font-medium">
                    ${customer.total_spent}
                </td>
            `;
            tbody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="3" class="px-4 py-5 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.No customer data available') }}
            </td>
        `;
        tbody.appendChild(row);
    }
}

            
            // Update the low stock alerts
            function updateLowStockAlerts(alerts) {
                const container = document.getElementById('low-stock-alerts-container');
                container.innerHTML = '';
                
                if (alerts && alerts.length) {
                    alerts.forEach(item => {
                        const stockLevel = parseInt(item.quantity);
                        const minLevel = parseInt(item.min_stock_level);
                        const percentageLeft = Math.min(100, Math.round((stockLevel / minLevel) * 100));
                        
                        // Different colors based on stock level
                        let statusColor = '';
                        if (percentageLeft <= 30) {
                            statusColor = 'bg-rose-500';
                        } else if (percentageLeft <= 70) {
                            statusColor = 'bg-amber-500';
                        } else {
                            statusColor = 'bg-emerald-500';
                        }
                        
                        const card = document.createElement('div');
                        card.className = 'bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-4 relative';
                        card.innerHTML = `
                            <div class="flex items-center">
                                <div class="p-2 rounded-full ${statusColor === 'bg-rose-500' ? 'bg-rose-100 dark:bg-rose-900/20 text-rose-500' : 'bg-amber-100 dark:bg-amber-900/20 text-amber-500'}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div class="ltr:ml-4 rtl:mr-4 flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">${escapeHtml(item.name)}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${escapeHtml(item.description)}</p>
                                    <div class="mt-3">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                            <div class="h-1.5 rounded-full ${statusColor}" style="width: ${percentageLeft}%"></div>
                                        </div>
                                        <div class="mt-1 flex justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ __('messages.Current') }}: <span class="${statusColor === 'bg-rose-500' ? 'text-rose-500' : 'text-amber-500'}">${item.quantity}</span>
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ __('messages.Min') }}: ${item.min_stock_level}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    const message = document.createElement('div');
                    message.className = 'col-span-full text-center py-8';
                    message.innerHTML = `
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.No low stock items') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.All inventory items are at healthy levels') }}</p>
                    `;
                    container.appendChild(message);
                }
            }
            
            // Generate appropriate labels based on timeframe
            function generateLabels(timeframe, length) {
                const labels = [];
                
                switch (timeframe) {
                    case 'daily':
                    case '24hours':
                        for (let i = 0; i < length; i++) {
                            labels.push(`${i}:00`);
                        }
                        break;
                        
                    case 'weekly':
                        const days = ['{{ __("messages.Sun") }}', '{{ __("messages.Mon") }}', '{{ __("messages.Tue") }}', '{{ __("messages.Wed") }}', '{{ __("messages.Thu") }}', '{{ __("messages.Fri") }}', '{{ __("messages.Sat") }}'];
                        for (let i = 0; i < Math.min(length, days.length); i++) {
                            labels.push(days[i]);
                        }
                        break;
                        
                    case 'monthly':
                        for (let i = 1; i <= length; i++) {
                            labels.push(`${i}`);
                        }
                        break;
                        
                    case 'yearly':
                        const months = [
                            '{{ __("messages.Jan") }}', '{{ __("messages.Feb") }}', '{{ __("messages.Mar") }}', 
                            '{{ __("messages.Apr") }}', '{{ __("messages.May") }}', '{{ __("messages.Jun") }}',
                            '{{ __("messages.Jul") }}', '{{ __("messages.Aug") }}', '{{ __("messages.Sep") }}',
                            '{{ __("messages.Oct") }}', '{{ __("messages.Nov") }}', '{{ __("messages.Dec") }}'
                        ];
                        for (let i = 0; i < Math.min(length, months.length); i++) {
                            labels.push(months[i]);
                        }
                        break;
                        
                    case 'all':
                        for (let i = 0; i < length; i++) {
                            labels.push(`${i + 1}`);
                        }
                        break;
                }
                
                return labels;
            }
            
            // Calculate percentage trend between current and all-time values
            function calculateTrend(current, allTime) {
                if (!allTime || allTime === 0) return 0;
                
                return ((current - allTime) / allTime) * 100;
            }
            
            // Update trend indicator with appropriate styling
            function updateTrendIndicator(elementId, trendPercentage) {
                const element = document.getElementById(elementId);
                if (!element) return;
                
                // Limit to one decimal place and add % sign
                const formattedPercentage = Math.abs(trendPercentage).toFixed(1) + '%';
                let iconPath, className;
                
                if (trendPercentage > 0) {
                    iconPath = 'M5 10l7-7m0 0l7 7m-7-7v18';
                    className = 'text-green-500';
                } else if (trendPercentage < 0) {
                    iconPath = 'M19 14l-7 7m0 0l-7-7m7 7V3';
                    className = 'text-red-500';
                } else {
                    iconPath = 'M5 12h14';
                    className = 'text-gray-500';
                }
                
                element.innerHTML = `
                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}" />
                    </svg>
                    ${formattedPercentage}
                `;
                
                element.className = 'flex items-center ' + className;
            }
            
            // Format currency values
            function formatCurrency(value, includeSymbol = true) {
                if (value === null || value === undefined) return '-';
                
                const formatter = new Intl.NumberFormat('{{ app()->getLocale() }}', {
                    style: includeSymbol ? 'currency' : 'decimal',
                    currency: 'mad', // Change to your currency
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                return formatter.format(value);
            }
            
            // Escape HTML to prevent XSS
            function escapeHtml(unsafe) {
                if (unsafe === null || unsafe === undefined) return '';
                
                return unsafe
                    .toString()
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });
    </script>
    @endpush
</x-app-layout>
