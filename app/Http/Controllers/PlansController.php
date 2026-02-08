<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    /**
     * Display all available plans.
     */
    public function index()
    {
        $plans = Plan::active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('billing.plans.index', compact('plans'));
    }

    /**
     * Display a specific plan's details.
     */
    public function show(Plan $plan)
    {
        if (!$plan->is_active) {
            abort(404);
        }

        return view('billing.plans.show', compact('plan'));
    }
}
