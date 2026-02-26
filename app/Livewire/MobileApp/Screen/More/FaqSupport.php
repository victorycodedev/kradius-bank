<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Models\Faq;
use Livewire\Attributes\Title;
use Livewire\Component;

class FaqSupport extends Component
{
    public $searchQuery = '';
    public $selectedCategory = 'all';
    public $expandedFaqId = null;

    #[Title('FAQs & Support')]
    public function render()
    {
        $faqs = Faq::where('is_active', true)
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('question', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('answer', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->selectedCategory !== 'all', function ($query) {
                $query->where('category', $this->selectedCategory);
            })
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Faq::where('is_active', true)
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter();

        return view('livewire.mobile-app.screen.more.faq-support', [
            'faqs' => $faqs,
            'categories' => $categories,
        ]);
    }

    public function toggleFaq($faqId)
    {
        if ($this->expandedFaqId === $faqId) {
            $this->expandedFaqId = null;
        } else {
            $this->expandedFaqId = $faqId;
        }
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->selectedCategory = 'all';
    }
}
