<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = TaxSetting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0|max:100',
            'active' => 'array',
        ]);

        foreach ($request->rates as $id => $rate) {
            $setting = TaxSetting::find($id);
            if ($setting) {
                $setting->update([
                    'rate' => $rate,
                    'is_active' => in_array($id, array_keys($request->active ?? [])),
                ]);
            }
        }

        return back()->with('success', 'Tax settings updated successfully.');
    }
}
