<div class="bottom-nav">
    <x-link :href="route('dashboard')" class="nav-item" :active="request()->routeIs('dashboard')" icon="house-door-fill">
        <span>Home</span>
    </x-link>

    @if ($configuration->allow_transfers)
        <x-link :href="route('transfer')" class="nav-item" :active="request()->routeIs('transfer')" icon="send-arrow-up">
            <span>Transfer</span>
        </x-link>
    @endif

    @if ($loan_feature_enabled)
        <x-link :href="route('loans')" class="nav-item" :active="request()->routeIs('loans')" icon="piggy-bank">
            <span>Loan</span>
        </x-link>
    @endif

    <x-link :href="route('payments')" class="nav-item" :active="request()->routeIs('payments')" icon="arrow-left-right">
        <span>Payments</span>
    </x-link>
    <x-link :href="route('more')" class="nav-item" :active="request()->routeIs('more')" icon="grid-3x3-gap">
        <span>More</span>
    </x-link>
</div>
