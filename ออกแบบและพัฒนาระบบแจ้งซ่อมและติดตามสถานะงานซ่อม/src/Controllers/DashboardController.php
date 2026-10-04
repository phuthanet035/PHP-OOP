<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\DashboardService;

/**
 * Admin Analytics Dashboard Controller
 * Matching Sequence Diagram 6.3
 */
class DashboardController extends BaseController
{
    private DashboardService $dashboardService;

    public function __construct(?DashboardService $dashboardService = null)
    {
        $this->dashboardService = $dashboardService ?? new DashboardService();
    }

    public function index(Request $request): void
    {
        $stats = $this->dashboardService->getStatistics();
        $this->view('admin/dashboard', ['stats' => $stats]);
    }
}
