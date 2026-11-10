<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Vet extends Model
{
    /** Length of one appointment slot, used to detect double-booking. */
    public const SLOT_MINUTES = 30;

   protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'license_number',
    'clinic_name',
    'staff_type',
    'specialty',
    'approval_status',
    'working_days',
    'working_hours_start',
    'working_hours_end',
    'days_off',
    'phone',
    'profile_photo',
];
    protected function casts(): array
    {
        return [
            'working_days' => 'array',
            'days_off' => 'array',
        ];
    }

    /**
     * Is this staff member free at the given date and time?
     *
     * Returns ['status' => 'available'|'booked'|'unavailable', 'message' => string]
     */
    public function availabilityAt(Carbon $at): array
    {
        if (in_array($at->toDateString(), $this->days_off ?? [], true)) {
            return ['status' => 'unavailable', 'message' => 'Day off on ' . $at->format('M d')];
        }

        if (
            ! empty($this->working_days)
            && $this->working_hours_start
            && $this->working_hours_end
        ) {
            if (! in_array($at->format('l'), $this->working_days, true)) {
                return ['status' => 'unavailable', 'message' => 'Does not work on ' . $at->format('l') . 's'];
            }

            $time = $at->format('H:i:s');

            if ($time < $this->working_hours_start || $time >= $this->working_hours_end) {
                return [
                    'status' => 'unavailable',
                    'message' => 'Working hours are '
                        . Carbon::parse($this->working_hours_start)->format('g:i A')
                        . ' - '
                        . Carbon::parse($this->working_hours_end)->format('g:i A'),
                ];
            }
        }

        $column = $this->staff_type === 'groomer' ? 'groomer_id' : 'vet_id';

        $isBooked = Appointment::where($column, $this->id)
            ->where('status', 'confirmed')
            ->where('appointment_at', '>', $at->copy()->subMinutes(self::SLOT_MINUTES))
            ->where('appointment_at', '<', $at->copy()->addMinutes(self::SLOT_MINUTES))
            ->exists();

        if ($isBooked) {
            return ['status' => 'booked', 'message' => 'Already booked at this time'];
        }

        return ['status' => 'available', 'message' => 'Available'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function groomingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'groomer_id');
    }

    public function visitRecords(): HasMany
    {
        return $this->hasMany(VisitRecord::class);
    }

    public function vaccinationRecords(): HasMany
    {
        return $this->hasMany(VaccinationRecord::class);
    }
}
