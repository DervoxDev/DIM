<div class="overflow-hidden shadow rounded-lg border">
          <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium">
          Team Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm">
                    Details about the team.
                </p>
            </div>
            <div class="border-t px-4 py-5 sm:p-0">
                <dl class="sm:divide-y">
                    <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium">
                            Team Name
                        </dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
{{ $team->name ?? 'No name'}}
     </dd>
         </div>
         <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium">
          Subscription Type
          </dt>
          <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
            {{ $subscription->subscription_type ?? 'N/A' }}
          </dd>
    </div>
    <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium">
          Subscription Expiry
                        </dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            {{ $subscription->subscription_expiredDate ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>
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
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium mt-6">
                    Edit Team Information
                </h3>
                <div class="mt-4">
                    @if($team)
                        @livewire('team-edit-form', ['team' => $team])
                    @endif
                </div>
            </div>
        </div>
