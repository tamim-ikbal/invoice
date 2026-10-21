<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DashboardPeriodEnum;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard stats for the selected period.
     */
    public function index(Request $request, DashboardService $service): Response
    {
        $period = DashboardPeriodEnum::tryFrom((string) $request->query('period')) ?? DashboardPeriodEnum::ALL_TIME;

        return Inertia::render('admin/Dashboard', $service->index($period));
    }
}
