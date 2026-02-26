@props([
    'show' => false,
    'maxWidth' => 'full',
    'title' => '',
    'showClose' => true,
    'id' => null, // Livewire property name
])

<div x-data="{
    show: $wire.entangle('{{ $id }}'),
    startY: 0,
    currentY: 0,
    isDragging: false,

    init() {
        this.$watch('show', (value) => {
            document.body.style.overflow = value ? 'hidden' : '';
        });
    },

    close() {
        // Animate slide down
        this.$refs.sheet.style.transform = 'translateY(100%)';

        // After animation ends, hide and reset transform
        setTimeout(() => {
            this.show = false;
            this.$refs.sheet.style.transform = '';
        }, 300); // same as your sheet-leave transition
    },

    handleTouchStart(e) {
        this.startY = e.touches[0].clientY;
        this.isDragging = true;
    },

    handleTouchMove(e) {
        if (!this.isDragging) return;

        this.currentY = e.touches[0].clientY;
        const diff = this.currentY - this.startY;

        if (diff > 0) {
            this.$refs.sheet.style.transform = `translateY(${diff}px)`;
        }
    },

    handleTouchEnd() {
        if (!this.isDragging) return;

        const diff = this.currentY - this.startY;
        const threshold = 100;

        if (diff > threshold) {
            this.close();
        } else {
            this.$refs.sheet.style.transform = 'translateY(0)';
        }

        this.isDragging = false;
        this.startY = 0;
        this.currentY = 0;
    }
}" x-cloak @keydown.escape.window="close()" x-show="show"
    @close-bottom-sheet.window="if ($event.detail.id === '{{ $id }}') close()"
    @open-bottom-sheet.window="if ($event.detail.id === '{{ $id }}') show = true" style="display:none"
    class="bottom-sheet-wrapper">

    <!-- Backdrop -->
    <div x-show="show" x-transition:enter="backdrop-enter" x-transition:enter-start="backdrop-enter-start"
        x-transition:enter-end="backdrop-enter-end" x-transition:leave="backdrop-leave"
        x-transition:leave-start="backdrop-leave-start" x-transition:leave-end="backdrop-leave-end" @click="close()"
        class="bottom-sheet-backdrop"></div>

    <!-- Sheet -->
    <div x-show="show" x-ref="sheet" x-transition:enter="sheet-enter" x-transition:enter-start="sheet-enter-start"
        x-transition:enter-end="sheet-enter-end" x-transition:leave="sheet-leave"
        x-transition:leave-start="sheet-leave-start" x-transition:leave-end="sheet-leave-end"
        @touchstart="handleTouchStart($event)" @touchmove="handleTouchMove($event)" @touchend="handleTouchEnd()"
        class="bottom-sheet {{ $maxWidth === 'full' ? 'bottom-sheet-full' : 'bottom-sheet-contained' }}">

        <!-- Drag Handle -->
        <div class="bottom-sheet-handle">
            <div class="bottom-sheet-handle-bar"></div>
        </div>

        <!-- Header -->
        @if ($title || $showClose)
            <div class="bottom-sheet-header">
                @if ($title)
                    <h2 class="bottom-sheet-title">{{ $title }}</h2>
                @endif

                @if ($showClose)
                    <button @click="close()" class="bottom-sheet-close" type="button">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        <!-- Body -->
        <div class="bottom-sheet-body">
            {{ $slot }}
        </div>
    </div>
</div>
