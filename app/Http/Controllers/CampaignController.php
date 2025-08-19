<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Service;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('services')
            ->latest()
            ->paginate(10);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $services = Service::all();
        return view('campaigns.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
        ]);

        \DB::transaction(function () use ($validated) {
            $campaign = Campaign::create($validated);
            $campaign->services()->sync($validated['services']);
        });

        return redirect()->route('campaigns.index')
            ->with('success', 'Kampanya başarıyla oluşturuldu.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('services');
        return view('campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign)
    {
        $services = Service::all();
        $campaign->load('services');
        return view('campaigns.edit', compact('campaign', 'services'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
        ]);

        \DB::transaction(function () use ($campaign, $validated) {
            $campaign->update($validated);
            $campaign->services()->sync($validated['services']);
        });

        return redirect()->route('campaigns.index')
            ->with('success', 'Kampanya başarıyla güncellendi.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('campaigns.index')
            ->with('success', 'Kampanya başarıyla silindi.');
    }
}
