<?php

namespace App\Http\Controllers;

use App\Models\InvAdjustment;
use App\Models\InvPhysicalCount;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'adjustmentCount' => InvAdjustment::whereIn('status', ['draft', 'diajukan'])->count(),
            'physicalCountCount' => InvPhysicalCount::whereIn('status', ['draft', 'diajukan'])->count(),
        ]);
    }
}
