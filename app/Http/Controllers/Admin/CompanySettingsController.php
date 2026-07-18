<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::getAllGrouped();
        return view('admin.settings.company', compact('settings'));
    }

    public function update(Request $request)
    {
        // Update text fields
        foreach ($request->except(['_token', '_method', 'company_logo']) as $key => $value) {
            CompanySetting::set($key, $value);
        }

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $request->validate(['company_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
            
            // Delete old logo
            $oldLogo = CompanySetting::get('company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            $path = $request->file('company_logo')->store('company', 'public');
            CompanySetting::set('company_logo', $path);
        }

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function removeLogo()
    {
        $oldLogo = CompanySetting::get('company_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }
        CompanySetting::set('company_logo', null);
        return back()->with('success', 'Logo removed.');
    }
}
