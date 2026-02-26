<?php

namespace App\Livewire\MobileApp\Screen;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class Payments extends Component
{
    public $filterType = 'all'; // all, credit, debit, transfer, withdrawal, deposit
    public $filterDate = 'all'; // all, today, week, month, year
    public $sortBy = 'latest'; // latest, oldest, highest, lowest
    public $searchQuery = '';
    public $showFilters = false;
    public $tempFilterType = 'all';
    public $tempFilterDate = 'all';
    public $tempSortBy = 'latest';
    public $stats = [];

    protected $queryString = [
        'filterType' => ['except' => 'all'],
        'filterDate' => ['except' => 'all'],
        'sortBy' => ['except' => 'latest'],
    ];

    public function mount()
    {
        $this->stats = $this->getTransactionStats();
    }

    #[Title('Transactions')]
    public function render()
    {
        $transactions = $this->getTransactions();
        // dd($transactions);
        return view('livewire.mobile-app.screen.payments', [
            'transactions' => $transactions,
        ]);
    }

    public function getTransactions()
    {
        return \App\Models\Transaction::query()
            ->whereIn('user_account_id', Auth::user()->accounts()->pluck('id'))
            ->whereIn('status', ['completed', 'pending_verification'])

            ->when(
                $this->filterType !== 'all',
                fn($q) =>
                $q->where('transaction_type', $this->filterType)
            )

            ->when($this->filterDate !== 'all', function ($q) {
                match ($this->filterDate) {
                    'today' => $q->whereDate('created_at', today()),
                    'week'  => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year),
                    'year'  => $q->whereYear('created_at', now()->year),
                };
            })

            ->when(
                $this->searchQuery,
                fn($q) =>
                $q->where('description', 'like', "%{$this->searchQuery}%")
            )

            ->orderBy(
                match ($this->sortBy) {
                    'oldest'  => 'created_at',
                    'highest' => 'amount',
                    'lowest'  => 'amount',
                    default   => 'created_at',
                },
                in_array($this->sortBy, ['oldest', 'lowest']) ? 'asc' : 'desc'
            )

            ->limit(50)
            ->get();
    }

    public function getTransactionStats()
    {
        $allTransactions = Auth::user()->accounts()
            ->with(['transactions' => function ($q) {
                $q->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }])
            ->get()
            ->pluck('transactions')
            ->flatten();

        $credits = $allTransactions->whereIn('transaction_type', ['credit', 'deposit'])->sum('amount');
        $debits = $allTransactions->whereIn('transaction_type', ['debit', 'withdrawal'])->sum('amount');

        return [
            'total' => $allTransactions->count(),
            'credits' => $credits,
            'debits' => $debits,
            'net' => $credits - $debits,
        ];
    }

    public function setDefault(): void
    {
        // $this->filterType = 'all';
        // dd($this->filterType, $this->filterDate, $this->sortBy);
    }

    public function clearFilters()
    {
        $this->filterType = 'all';
        $this->filterDate = 'all';
        $this->sortBy = 'latest';
        $this->tempSortBy = 'latest';
        $this->tempFilterDate = 'all';
    }

    public function applyFilters(): void
    {
        $this->filterType = $this->tempFilterType;
        $this->filterDate = $this->tempFilterDate;
        $this->sortBy = $this->tempSortBy;
        $this->dispatch('close-bottom-sheet', id: 'showFilters');
    }
}
