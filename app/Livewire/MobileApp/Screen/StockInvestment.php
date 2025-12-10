<?php

namespace App\Livewire\MobileApp\Screen;

use App\Models\Investment;
use App\Models\Stock;
use App\Models\UserAccount;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

class StockInvestment extends Component
{
    use HasAlerts;

    public $activeTab = 'stocks';
    public $selectedStockId = null;
    public $showInvestModal = false;
    public $amount = 0;
    public $accountId;

    #[Title('Stock Investment')]
    public function render()
    {
        $this->activeTab = request('tab', 'stocks');

        $stocks = Stock::where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->get();

        $myInvestments = Investment::where('user_id', Auth::id())
            ->with(['stock', 'userAccount'])
            ->latest()
            ->get();

        return view('livewire.mobile-app.screen.stock-investment', [
            'stocks' => $stocks,
            'myInvestments' => $myInvestments,
        ]);
    }

    public function selectStock($stockId)
    {
        $this->selectedStockId = $stockId;
        $this->showInvestModal = true;
    }

    public function invest()
    {
        // Validate the data
        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'accountId' => ['required', 'exists:user_accounts,id'],
            'selectedStockId' => ['required', 'exists:stocks,id'],
        ]);

        $stock = Stock::findOrFail($this->selectedStockId);

        $account = UserAccount::where('id', $validated['accountId'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Additional validations
        if ($validated['amount'] < $stock->minimum_investment) {
            $this->errorAlert(message: 'Minimum investment is $' . number_format($stock->minimum_investment, 2));
            return;
        }

        if ($stock->maximum_investment && $validated['amount'] > $stock->maximum_investment) {
            $this->errorAlert(message: 'Maximum investment is $' . number_format($stock->maximum_investment, 2));
            return;
        }

        if ($account->balance < $validated['amount']) {
            $this->errorAlert(message: 'You have insufficient balance.');
            return;
        }

        DB::beginTransaction();
        try {
            // Calculate shares
            $shares = $validated['amount'] / $stock->current_price;

            // Create investment
            Investment::create([
                'user_id' => Auth::user()->id,
                'stock_id' => $stock->id,
                'user_account_id' => $account->id,
                'amount' => $validated['amount'],
                'shares' => $shares,
                'purchase_price' => $stock->current_price,
                'current_value' => $validated['amount'],
                'status' => 'active',
            ]);

            // Deduct from account
            $account->decrement('balance', $validated['amount']);

            // Create transaction record
            $account->transactions()->create([
                'transaction_type' => 'debit',
                'amount' => $validated['amount'],
                'currency' => $account->currency,
                'balance_before' => $account->balance + $validated['amount'],
                'balance_after' => $account->balance,
                'reference_number' => 'INV' . strtoupper(uniqid()),
                'description' => "Investment in {$stock->symbol} - {$shares} shares",
                'status' => 'completed',
                'channel' => 'mobile_app',
                'completed_at' => now(),
            ]);

            // Update stock investment count
            $stock->increment('investment_count');

            DB::commit();

            $this->successAlert(message: 'Investment successful.');

            $this->dispatch('close-bottom-sheet', id: 'showInvestModal');
            $this->selectedStockId = null;
        } catch (\Exception $e) {
            $this->errorAlert(message: 'Investment failed. Please try again');
            DB::rollBack();
        }
    }
}
