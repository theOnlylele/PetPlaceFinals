<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\VaccinationRecord;
use App\Models\Vet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const PALETTE = ['#16834b', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#ec4899'];

    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);

        $today = Carbon::today();

        // ── Top stat cards ───────────────────────────────────
        $stats = [
            'owners'       => User::where('role', 'owner')->count(),
            'vets'         => Vet::where('staff_type', 'veterinarian')->where('approval_status', 'approved')->count(),
            'groomers'     => Vet::where('staff_type', 'groomer')->where('approval_status', 'approved')->count(),
            'pets'         => Pet::count(),
            'today'        => Appointment::whereDate('appointment_at', $today->toDateString())
                                ->whereIn('status', ['pending', 'confirmed', 'completed'])->count(),
            'pending'      => Appointment::where('status', 'pending')->count(),
            'vaccinesDue'  => VaccinationRecord::whereBetween('next_due_date', [
                                $today->toDateString(), $today->copy()->addDays(30)->toDateString(),
                              ])->count(),
            'applications' => Vet::where('approval_status', 'pending')->count(),
        ];

        // ── Chart 1: appointments per month (last 6 months) ──
        $start = $today->copy()->subMonths(5)->startOfMonth();
        $byMonth = Appointment::where('appointment_at', '>=', $start)
            ->pluck('appointment_at')
            ->groupBy(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->map->count();

        $monthly = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $monthly[] = ['label' => $month->format('M'), 'value' => (int) $byMonth->get($month->format('Y-m'), 0)];
        }

        // ── Chart 2: appointments by status ──────────────────
        $statusCounts = Appointment::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $statusStyle = [
            'pending'   => ['Pending',   '#f59e0b'],
            'confirmed' => ['Confirmed', '#16834b'],
            'completed' => ['Completed', '#3b82f6'],
            'cancelled' => ['Cancelled', '#ef4444'],
        ];
        $statusItems = [];
        foreach ($statusStyle as $key => [$label, $color]) {
            $statusItems[] = ['label' => $label, 'value' => (int) $statusCounts->get($key, 0), 'color' => $color];
        }

        // ── Chart 3: most requested services ─────────────────
        $services = Appointment::select('service_type', DB::raw('COUNT(*) as total'))
            ->groupBy('service_type')->orderByDesc('total')->limit(6)->get()
            ->map(fn ($row) => ['label' => $row->service_type ?: 'Other', 'value' => (int) $row->total])
            ->all();

        // ── Chart 4: pets by type ────────────────────────────
        $petItems = [];
        $n = 0;
        foreach (Pet::select('pet_type', DB::raw('COUNT(*) as total'))->groupBy('pet_type')->orderByDesc('total')->get() as $row) {
            $petItems[] = [
                'label' => ucfirst($row->pet_type ?: 'unspecified'),
                'value' => (int) $row->total,
                'color' => self::PALETTE[$n++ % count(self::PALETTE)],
            ];
        }

        return view('admin.dashboard', [
            'stats'    => $stats,
            'monthly'  => $monthly,
            'status'   => $this->donut($statusItems),
            'services' => $services,
            'petTypes' => $this->donut($petItems),
        ]);
    }

    /** Builds the CSS gradient for a donut chart. */
    private function donut(array $items): array
    {
        $total = array_sum(array_column($items, 'value'));

        if ($total === 0) {
            return ['total' => 0, 'gradient' => 'conic-gradient(#e5e7eb 0% 100%)', 'items' => $items];
        }

        $stops = [];
        $at = 0.0;

        foreach ($items as $index => $item) {
            $percent = $item['value'] / $total * 100;
            if ($item['value'] > 0) {
                $stops[] = $item['color'] . ' ' . round($at, 2) . '% ' . round($at + $percent, 2) . '%';
            }
            $items[$index]['percent'] = (int) round($percent);
            $at += $percent;
        }

        return ['total' => $total, 'gradient' => 'conic-gradient(' . implode(', ', $stops) . ')', 'items' => $items];
    }
}
