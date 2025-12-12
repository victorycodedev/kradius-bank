<div class="screen faq-support-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>FAQs & Support</h1>
    </div>

    <!-- Support Contact Card -->
    <div class="support-contact-card">
        <div class="support-icon">
            <i class="bi bi-headset"></i>
        </div>
        <div class="support-info">
            <h3>Need Help?</h3>
            <p>Our support team is here to assist you</p>
        </div>
        <a href="mailto:{{ $supportEmail }}" class="contact-btn">
            <i class="bi bi-envelope"></i>
            Contact Us
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="search-filter-section">
        <!-- Search Bar -->
        <div class="search-bar">
            <i class="bi bi-search"></i>
            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search FAQs..."
                class="search-input">
            @if ($searchQuery)
                <button wire:click="clearSearch" class="clear-search">
                    <i class="bi bi-x"></i>
                </button>
            @endif
        </div>

        <!-- Category Filter -->
        @if ($categories->count() > 0)
            <div class="category-chips">
                <button wire:click="$set('selectedCategory', 'all')" @class(['category-chip', 'active' => $selectedCategory === 'all'])>
                    All
                </button>
                @foreach ($categories as $category)
                    <button wire:click="$set('selectedCategory', '{{ $category }}')" @class(['category-chip', 'active' => $selectedCategory === $category])>
                        {{ ucfirst($category) }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- FAQs List -->
    <div class="faqs-container">
        @forelse($faqs as $faq)
            <div class="faq-card" x-data="{ expanded: false, expandedFaqId: {{ $faq->id }} }" wire:key="faq-{{ $faq->id }}">
                <button @click="expanded = !expanded" class="faq-question" type="button">
                    <span>{{ $faq->question }}</span>
                    <i
                        x-bind:class="expanded && expandedFaqId === {{ $faq->id }} ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div class="faq-answer" x-show="expanded && expandedFaqId === {{ $faq->id }}" x-collapse>
                    <div class="answer-content">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h3>No FAQs Found</h3>
                <p>
                    @if ($searchQuery)
                        Try adjusting your search terms
                    @else
                        No frequently asked questions available at the moment
                    @endif
                </p>
                @if ($searchQuery)
                    <button wire:click="clearSearch" class="btn-secondary">
                        Clear Search
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Still Need Help Section -->
    {{-- <div class="help-section">
        <div class="help-card">
            <i class="bi bi-question-circle"></i>
            <h3>Still need help?</h3>
            <p>Can't find the answer you're looking for? Our support team is ready to help.</p>
            <a href="mailto:{{ $supportEmail }}" class="btn-primary">
                <i class="bi bi-envelope"></i>
                Email Support
            </a>
        </div>
    </div> --}}

    <livewire:mobile-app.component.bottom-nav />
</div>
