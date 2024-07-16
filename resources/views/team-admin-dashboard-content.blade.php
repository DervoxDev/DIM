<div class="relative overflow-x-auto shadow rounded-lg border">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium">
            Team Information
        </h3>
        <p class="mt-1 max-w-2xl text-sm">
            Details about the team.
        </p>
    </div>
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Field
                </th>
                <th scope="col" class="px-6 py-3">
                    Value
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Team Name
                </th>
                <td class="px-6 py-4">
                    {{ $team->name ?? 'No name'}}
                </td>
            </tr>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Subscription Type
                </th>
                <td class="px-6 py-4">
                    {{ $subscription->subscription_type ?? 'N/A' }}
                </td>
            </tr>
            <tr class="bg-white dark:bg-gray-800">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    Subscription Expiry
                </th>
                <td class="px-6 py-4">
                    {{ $subscription->subscription_expiredDate ?? 'N/A' }}
                </td>
            </tr>
        </tbody>
    </table>
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium mt-6">
            Team Workers
        </h3>
        @if($workers->isNotEmpty())
            @livewire('list-workers', ['team' => $team])
        @else
            <p class="text-sm">No workers found.</p>
        @endif
    </div>

</div>
