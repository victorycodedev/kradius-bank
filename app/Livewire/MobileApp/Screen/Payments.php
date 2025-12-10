<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Payments extends Component
{
    use WithPagination;

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
        $query = UserAccount::where('user_id', Auth::user()->id)
            ->with(['transactions' => function ($q) {

                $q->where('status', 'completed');

                // Apply type filter
                if ($this->filterType !== 'all') {
                    $q->where('transaction_type', $this->filterType);
                }

                // // Apply date filter
                // if ($this->filterDate !== 'all') {
                //     match ($this->filterDate) {
                //         'today' => $q->whereDate('created_at', today()),
                //         'week' => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                //         'month' => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                //         'year' => $q->whereYear('created_at', now()->year),
                //         default => null
                //     };
                // }

                // // Apply search
                // if ($this->searchQuery) {
                //     $q->where('description', 'like', '%' . $this->searchQuery . '%');
                // }

                // // Apply sorting - moved inside the query
                // match ($this->sortBy) {
                //     'latest' => $q->latest(),
                //     'oldest' => $q->oldest(),
                //     'highest' => $q->orderBy('amount', 'desc'),
                //     'lowest' => $q->orderBy('amount', 'asc'),
                //     default => $q->latest()
                // };
            }])
            ->get()
            // dd($query)
            ->pluck('transactions')
            ->flatten();

        // Apply collection-level sorting based on sortBy
        $sorted = match ($this->sortBy) {
            'latest' => $query->sortByDesc('created_at'),
            'oldest' => $query->sortBy('created_at'),
            'highest' => $query->sortByDesc('amount'),
            'lowest' => $query->sortBy('amount'),
            default => $query->sortByDesc('created_at')
        };

        return $sorted->take(50); // Limit to 50 for performance
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
        $this->resetPage();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->filterType = $this->tempFilterType;
        $this->filterDate = $this->tempFilterDate;
        $this->sortBy = $this->tempSortBy;
        $this->resetPage();
        $this->dispatch('close-bottom-sheet', id: 'showFilters');
    }
}
