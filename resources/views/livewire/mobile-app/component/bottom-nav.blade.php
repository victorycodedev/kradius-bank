<div class="bottom-nav">
    <x-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="house-door-fill">
        <span>Home</span>
    </x-link>
    <x-link :href="route('transfer')" :active="request()->routeIs('transfer')" icon="send-arrow-up">
        <span>Transfer</span>
    </x-link>
    <x-link :href="route('loans')" :active="request()->routeIs('loans')" icon="piggy-bank">
        <span>Loan</span>
    </x-link>
    <x-link :href="route('payments')" :active="request()->routeIs('payments')" icon="arrow-left-right">
        <span>Payments</span>
    </x-link>
    <x-link :href="route('more')" :active="request()->routeIs('more')" icon="grid-3x3-gap">
        <span>More</span>
    </x-link>
</div>
