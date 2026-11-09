<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VaccineController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        return view('admin.vaccines', ['vaccines' => Vaccine::with('doses')->orderBy('animal_type')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'animal_type' => ['required', Rule::in(['dog','cat','bird','rabbit','other'])],
            'pricing_method' => ['required', Rule::in(['fixed','per_dose'])],
            'fixed_price' => ['nullable','numeric','min:0'],
            'dose_prices' => ['nullable','string','max:1000'],
        ]);
        $base = Str::slug($data['name'], '_');
        $key = $data['animal_type'].'_'.($base ?: 'vaccine');
        $original = $key; $i = 1;
        while (Vaccine::where('vaccine_key', $key)->exists()) $key = $original.'_'.$i++;
        $vaccine = Vaccine::create([
            'vaccine_key' => $key, 'name' => $data['name'], 'animal_type' => $data['animal_type'],
            'pricing_method' => $data['pricing_method'], 'fixed_price' => $data['pricing_method'] === 'fixed' ? ($data['fixed_price'] ?? 0) : 0,
        ]);
        $this->saveDosePrices($vaccine, $data['dose_prices'] ?? '');
        return back()->with('status', 'Vaccine added successfully.');
    }

    public function update(Request $request, Vaccine $vaccine)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'animal_type' => ['required', Rule::in(['dog','cat','bird','rabbit','other'])],
            'pricing_method' => ['required', Rule::in(['fixed','per_dose'])],
            'fixed_price' => ['nullable','numeric','min:0'],
            'dose_prices' => ['nullable','string','max:1000'],
        ]);
        $vaccine->update([
            'name' => $data['name'], 'animal_type' => $data['animal_type'],
            'pricing_method' => $data['pricing_method'],
            'fixed_price' => $data['pricing_method'] === 'fixed' ? ($data['fixed_price'] ?? 0) : 0,
        ]);
        $vaccine->doses()->delete();
        $this->saveDosePrices($vaccine, $data['dose_prices'] ?? '');
        return back()->with('status', 'Vaccine updated successfully.');
    }

    public function toggle(Vaccine $vaccine)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $vaccine->update(['is_active' => ! $vaccine->is_active]);
        return back()->with('status', 'Vaccine availability updated.');
    }

    private function saveDosePrices(Vaccine $vaccine, string $lines): void
    {
        if ($vaccine->pricing_method !== 'per_dose') return;
        foreach (preg_split('/\r\n|\r|\n/', $lines) as $line) {
            if (! preg_match('/^\s*([1-9][0-9]?)\s*=\s*([0-9]+(?:\.[0-9]{1,2})?)\s*$/', $line, $m)) continue;
            $vaccine->doses()->create(['dose_number' => (int) $m[1], 'price' => $m[2]]);
        }
    }
}
