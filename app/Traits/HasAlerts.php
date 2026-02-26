<?php

namespace App\Traits;

trait HasAlerts
{
    /**
     * Default alert options
     */
    protected function getDefaultAlertOptions(): array
    {
        return [
            'duration' => 3000,
            'dismissible' => true,
            'x' => 'center',
            'y' => 'top',
        ];
    }

    /**
     * Display a success alert
     */
    public function successAlert(string $message, array $options = []): void
    {
        $this->alert('success', $message, $options);
    }

    /**
     * Display an error alert
     */
    public function errorAlert(string $message, array $options = []): void
    {
        $this->alert('error', $message, $options);
    }

    /**
     * Display an info alert
     */
    public function infoAlert(string $message, array $options = []): void
    {
        $this->alert('info', $message, $options);
    }

    /**
     * Display a warning alert
     */
    public function warningAlert(string $message, array $options = []): void
    {
        $this->alert('warning', $message, $options);
    }

    /**
     * Core alert method
     */
    protected function alert(string $type, string $message, array $options = []): void
    {
        // Merge with default options
        $options = array_merge($this->getDefaultAlertOptions(), $options);

        // Build notyf instance with options
        $notyf = notyf()
            ->duration($options['duration'])
            ->dismissible($options['dismissible'])
            ->position('x', $options['x'])
            ->position('y', $options['y']);

        // Display alert based on type
        match ($type) {
            'success' => $notyf->success($message),
            'error' => $notyf->error($message),
            'info' => $notyf->info($message),
            'warning' => $notyf->warning($message),
            default => $notyf->info($message),
        };
    }
}
