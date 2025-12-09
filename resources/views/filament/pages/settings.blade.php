<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 20px">
            <x-filament::button type="submit" color="primary" wire:loading.attr="disabled">
                <span wire:loading.remove>Save Settings</span>
                <x-spinner wire:loading wire:target="save" />
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
