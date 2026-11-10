<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\PetOwner;
use App\Models\User;
use App\Models\Vet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeVet(): Vet
    {
        $user = User::factory()->create(['role' => 'vet']);

        return Vet::create([
            'user_id' => $user->id,
            'first_name' => 'Alex',
            'last_name' => 'Vet',
            'license_number' => 'VET-123',
            'staff_type' => 'veterinarian',
            'approval_status' => 'approved',
            'working_days' => ['Monday'],
            'working_hours_start' => '09:00:00',
            'working_hours_end' => '17:00:00',
        ]);
    }

    private function makeOwner(): array
    {
        $user = User::factory()->create(['role' => 'owner']);
        $owner = PetOwner::create(['user_id' => $user->id, 'first_name' => 'Sam', 'last_name' => 'Owner']);
        $pet = $owner->pets()->create(['pet_name' => 'Mochi', 'pet_type' => 'dog']);

        return [$user, $owner, $pet];
    }

    private function lookup(User $user, $when)
    {
        return $this->actingAs($user)->getJson(route('owner.staff-availability', [
            'appointment_at' => $when->format('Y-m-d\TH:i'),
            'service_type' => 'Check-up / Consultation',
        ]));
    }

    public function test_vet_is_available_during_working_hours(): void
    {
        $this->makeVet();
        [$user] = $this->makeOwner();

        $this->lookup($user, now()->next('Monday')->setTime(10, 0))
            ->assertOk()
            ->assertJsonPath('available_count', 1)
            ->assertJsonPath('staff.0.status', 'available');
    }

    public function test_vet_is_unavailable_outside_working_hours_and_days(): void
    {
        $this->makeVet();
        [$user] = $this->makeOwner();

        $this->lookup($user, now()->next('Monday')->setTime(20, 0))
            ->assertJsonPath('staff.0.status', 'unavailable');

        $this->lookup($user, now()->next('Tuesday')->setTime(10, 0))
            ->assertJsonPath('staff.0.status', 'unavailable');
    }

    public function test_vet_with_a_confirmed_appointment_shows_as_booked(): void
    {
        $vet = $this->makeVet();
        [$user, $owner, $pet] = $this->makeOwner();
        $slot = now()->next('Monday')->setTime(10, 0);

        Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'service_type' => 'Check-up / Consultation',
            'appointment_at' => $slot,
            'status' => 'confirmed',
        ]);

        $this->lookup($user, $slot->copy())->assertJsonPath('staff.0.status', 'booked');
        $this->lookup($user, $slot->copy()->addMinutes(15))->assertJsonPath('staff.0.status', 'booked');
        $this->lookup($user, $slot->copy()->addMinutes(30))->assertJsonPath('staff.0.status', 'available');
    }

    public function test_owner_cannot_request_a_booked_vet(): void
    {
        $vet = $this->makeVet();
        [$user, $owner, $pet] = $this->makeOwner();
        $slot = now()->next('Monday')->setTime(10, 0);

        Appointment::create([
            'pet_owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'vet_id' => $vet->id,
            'service_type' => 'Check-up / Consultation',
            'appointment_at' => $slot,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user)
            ->post(route('owner.appointments.store'), [
                'pet_id' => $pet->id,
                'service_type' => 'Check-up / Consultation',
                'vet_id' => $vet->id,
                'appointment_at' => $slot->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('appointment_at');
    }
}
