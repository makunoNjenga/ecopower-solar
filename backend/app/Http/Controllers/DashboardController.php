<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        // Product statistics
        $totalProducts    = Product::count();
        $activeProducts   = Product::where('is_active', true)->count();
        $inactiveProducts = Product::where('is_active', false)->count();
        $featuredProducts = Product::where('is_featured', true)->count();
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->where('stock_quantity', '>', 0)
            ->count();
        $outOfStockProducts = Product::where('stock_quantity', 0)->count();

        // User statistics
        $totalUsers        = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Recent products
        $recentProducts = Product::with(['category', 'primaryImage'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Low stock alerts
        $lowStockAlerts = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        return response()->json([
            'products'         => [
                'total'        => $totalProducts,
                'active'       => $activeProducts,
                'inactive'     => $inactiveProducts,
                'featured'     => $featuredProducts,
                'low_stock'    => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts,
            ],
            'users'            => [
                'total'          => $totalUsers,
                'new_this_month' => $newUsersThisMonth,
            ],
            'recent_products'  => $recentProducts,
            'low_stock_alerts' => $lowStockAlerts,
        ]);
    }
}
