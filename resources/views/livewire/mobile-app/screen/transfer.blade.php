<div class="screen transfer-screen">
    <!-- Header -->
    <div class="investment-header">
        <button wire:click="backStep" class="btn-back"
            @if ($step === 1) onclick="window.history.back()" @endif>
            <i class="bi bi-arrow-left"></i>
        </button>
        <h1>Transfer Money</h1>
    </div>

    <!-- Progress Steps -->
    <div class="transfer-progress">
        <div class="progress-step {{ $step >= 1 ? 'active' : '' }} {{ $step > 1 ? 'completed' : '' }}">
            <div class="step-number">1</div>
            <span>Details</span>
        </div>
        <div class="progress-line {{ $step > 1 ? 'active' : '' }}"></div>
        <div class="progress-step {{ $step >= 2 ? 'active' : '' }} {{ $step > 2 ? 'completed' : '' }}">
            <div class="step-number">2</div>
            <span>PIN</span>
        </div>
        @if (count($requiredVerifications) > 0)
            <div class="progress-line {{ $step > 2 ? 'active' : '' }}"></div>
            <div class="progress-step {{ $step >= 3 ? 'active' : '' }} {{ $step > 3 ? 'completed' : '' }}">
                <div class="step-number">3</div>
                <span>Verify</span>
            </div>
        @endif
    </div>

    <livewire:mobile-app.component.bottom-nav />
</div>
