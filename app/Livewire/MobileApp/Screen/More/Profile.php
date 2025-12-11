<?php

namespace App\Livewire\MobileApp\Screen\More;

use App\Traits\HasAlerts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

use function Symfony\Component\Clock\now;

class Profile extends Component
{
    use HasAlerts;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $country = '';
    public $zip_code = '';
    public $date_of_birth = '';

    #[Title('My Profile')]
    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone_number ?? '';
        $this->address = $user->address ?? '';
        $this->city = $user->city ?? '';
        $this->state = $user->state ?? '';
        $this->country = $user->country ?? '';
        $this->zip_code = $user->zip_code ?? '';
        $this->date_of_birth = $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '';
    }

    public function render()
    {
        return view('livewire.mobile-app.screen.more.profile');
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        try {
            $user->update([
                'name' => $this->name,
                'phone_number' => $this->phone,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'zip_code' => $this->zip_code,
                'date_of_birth' => Carbon::parse($this->date_of_birth)->format('Y-m-d'),
            ]);

            $this->successAlert('Profile updated successfully!');
        } catch (\Exception $e) {
            $this->errorAlert('Failed to update profile. Please try again.');
            dd($e->getMessage());
            // \Log::error('Profile update error: ' . $e->getMessage());
        }
    }
}
