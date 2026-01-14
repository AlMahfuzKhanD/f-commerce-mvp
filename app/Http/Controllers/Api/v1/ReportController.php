<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * GET /api/v1/analytics/summary
     */
    public function dashboard(Request $request)
    {
        return response()->json([
            'data' => $this->reportService->getDashboardKPIs()
        ]);
    }

    /**
     * GET /api/v1/reports/sales
     */
    public function sales(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return response()->json([
            'data' => $this->reportService->getSalesSummary(
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)->endOfDay()
            )
        ]);
    }

    /**
     * GET /api/v1/reports/profit
     * (Owners Only - Check Permission Middleware)
     */
    public function profit(Request $request)
    {
        // Permission check is handled via Middleware in routes

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return response()->json([
            'data' => $this->reportService->getProfitAnalysis(
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)->endOfDay()
            )
        ]);
    }

    /**
     * GET /api/v1/reports/products
     */
    public function topProducts(Request $request)
    {
        return response()->json([
            'data' => $this->reportService->getTopProducts($request->input('limit', 10))
        ]);
    }

    /**
     * GET /api/v1/reports/customers
     */
    public function topCustomers(Request $request)
    {
        return response()->json([
            'data' => $this->reportService->getTopCustomers($request->input('limit', 10))
        ]);
    }
}
