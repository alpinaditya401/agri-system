<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommodityPrice;
use App\Models\FertilizerQuota;
use App\Models\FertilizerStock;
use App\Models\FertilizerTransaction;
use App\Models\FertilizerType;
use App\Models\Order;
use App\Services\FertilizerQuotaService;
use App\Support\DashboardRegion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
    }

    public function agricultureStatistics(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $regionFilters = DashboardRegion::fromRequest($request);
        $regionOptions = DashboardRegion::options($regionFilters);
        $statistics = $this->buildAgricultureStatistics($year, $regionFilters);

        return view('admin.reports.agriculture', compact('year', 'regionFilters', 'regionOptions', 'statistics'));
    }

    public function exportAgricultureStatistics(Request $request): StreamedResponse
    {
        $year = (int) $request->query('year', now()->year);
        $regionFilters = DashboardRegion::fromRequest($request);
        $statistics = $this->buildAgricultureStatistics($year, $regionFilters);

        $rows = collect([
            ['Ringkasan', 'Total petani', $statistics['farmers']['total']],
            ['Ringkasan', 'Petani terverifikasi', $statistics['farmers']['verified']],
            ['Ringkasan', 'Petani menunggu verifikasi', $statistics['farmers']['pending']],
            ['Ringkasan', 'Total luas lahan (ha)', $statistics['farmers']['land_area']],
            ['Ringkasan', 'Distributor subsidi aktif', $statistics['distributors']['active_subsidy']],
            ['Ringkasan', 'Jenis pupuk aktif', $statistics['fertilizers']['active_types']],
            ['Ringkasan', 'Stok pupuk tersedia (kg)', $statistics['fertilizers']['available_stock']],
            ['Ringkasan', 'Kuota dialokasikan (kg)', $statistics['fertilizers']['allocated_quota']],
            ['Ringkasan', 'Kuota terpakai (kg)', $statistics['fertilizers']['used_quota']],
            ['Ringkasan', 'Pupuk tersalurkan (kg)', $statistics['fertilizers']['dispensed_kg']],
        ]);

        foreach ($statistics['regions'] as $region) {
            $rows->push([
                'Wilayah',
                trim(($region['district'] ?: '-') . ', ' . ($region['province'] ?: '-')),
                "Petani: {$region['farmer_count']} | Lahan: {$region['land_area']} ha | Distributor aktif: {$region['active_distributors']}",
            ]);
        }

        return $this->streamCsv("statistik-pertanian-pupuk-{$year}.csv", [
            'Bagian',
            'Indikator',
            'Nilai',
        ], $rows);
    }

    /**
     * Fertilizer distribution report.
     */
    public function fertilizerDistribution(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $report = $this->quotaService->getStockMovementReport($year);

        return view('admin.reports.fertilizer', compact('report', 'year'));
    }

    public function exportFertilizerDistribution(Request $request): StreamedResponse
    {
        $year = (int) $request->query('year', now()->year);
        $report = $this->quotaService->getStockMovementReport($year);

        return $this->streamCsv("laporan-distribusi-pupuk-{$year}.csv", [
            'Distributor',
            'Jenis Pupuk',
            'Bulan',
            'Jumlah Transaksi',
            'Total Disalurkan (kg)',
            'Total Nilai',
        ], $report->map(fn($row) => [
            $row->distributor_name,
            $row->fertilizer_name,
            Carbon::createFromDate($year, $row->month, 1)->translatedFormat('F'),
            $row->transaction_count,
            $row->total_kg_dispensed,
            $row->total_value,
        ]));
    }

    /**
     * E-commerce transactions report.
     */
    public function transactions(Request $request): View
    {
        $orders = $this->transactionReportQuery($request)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'total_orders'    => Order::count(),
            'total_revenue'   => Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders'  => Order::where('order_status', 'pending')->count(),
            'completed_orders'=> Order::where('order_status', 'completed')->count(),
        ];

        return view('admin.reports.transactions', compact('orders', 'summary'));
    }

    public function exportTransactions(Request $request): StreamedResponse
    {
        $orders = $this->transactionReportQuery($request)
            ->latest()
            ->get();

        return $this->streamCsv('laporan-transaksi-' . now()->format('Ymd-His') . '.csv', [
            'Nomor Order',
            'Pembeli',
            'Petani',
            'Subtotal',
            'Ongkos Kirim',
            'Pajak',
            'Total',
            'Status Pesanan',
            'Status Pembayaran',
            'Metode Pembayaran',
            'Tanggal',
        ], $orders->map(fn(Order $order) => [
            $order->order_number,
            $order->buyer?->name ?? '-',
            $order->farmer?->name ?? '-',
            $order->subtotal,
            $order->shipping_cost,
            $order->tax_amount,
            $order->total_amount,
            $order->order_status,
            $order->payment_status,
            $order->payment_method ?? '-',
            $order->created_at?->format('Y-m-d H:i:s'),
        ]));
    }

    /**
     * Commodity prices report.
     */
    public function commodityPrices(Request $request): View
    {
        $prices = $this->commodityPriceReportQuery($request)
            ->latest('price_date')
            ->paginate(25)
            ->withQueryString();

        $categories = CommodityPrice::select('category')->distinct()->pluck('category');

        return view('admin.reports.prices', compact('prices', 'categories'));
    }

    public function exportCommodityPrices(Request $request): StreamedResponse
    {
        $prices = $this->commodityPriceReportQuery($request)
            ->latest('price_date')
            ->get();

        return $this->streamCsv('laporan-harga-komoditas-' . now()->format('Ymd-His') . '.csv', [
            'Komoditas',
            'Kategori',
            'Harga',
            'Satuan',
            'Wilayah',
            'Tanggal',
        ], $prices->map(fn(CommodityPrice $price) => [
            $price->commodity_name,
            $price->category,
            $price->price,
            $price->unit,
            $price->region ?? 'Nasional',
            Carbon::parse($price->price_date)->format('Y-m-d'),
        ]));
    }

    private function transactionReportQuery(Request $request)
    {
        return Order::with(['buyer', 'farmer'])
            ->when($request->query('status'), fn($q, $status) => $q->where('order_status', $status))
            ->when($request->query('from'), fn($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn($q, $to) => $q->whereDate('created_at', '<=', $to));
    }

    private function commodityPriceReportQuery(Request $request)
    {
        return CommodityPrice::query()
            ->when($request->query('category'), fn($q, $cat) => $q->where('category', $cat));
    }

    private function buildAgricultureStatistics(int $year, array $regionFilters): array
    {
        $farmers = DashboardRegion::applyUser(
            \App\Models\User::with('farmerProfile')
                ->whereHas('role', fn($query) => $query->where('name', 'farmer')),
            $regionFilters
        )->get();

        $distributors = DashboardRegion::applyUser(
            \App\Models\User::with(['distributorProfile', 'fertilizerStocks'])
                ->whereHas('role', fn($query) => $query->where('name', 'distributor')),
            $regionFilters
        )->get();

        $fertilizerStocks = FertilizerStock::with(['fertilizerType', 'distributor'])
            ->when(DashboardRegion::hasFilter($regionFilters), fn($query) => DashboardRegion::applyRelatedUser($query, 'distributor', $regionFilters))
            ->get();

        $fertilizerQuotas = FertilizerQuota::with(['fertilizerType', 'farmer'])
            ->where('year', $year)
            ->when(DashboardRegion::hasFilter($regionFilters), fn($query) => DashboardRegion::applyRelatedUser($query, 'farmer', $regionFilters))
            ->get();

        $transactions = FertilizerTransaction::with(['farmer', 'distributor', 'fertilizerType'])
            ->whereYear('created_at', $year)
            ->when(DashboardRegion::hasFilter($regionFilters), fn($query) => DashboardRegion::applyRelatedUser($query, 'farmer', $regionFilters))
            ->get();

        $verifiedFarmers = $farmers->filter(fn($user) => $user->farmerProfile?->verification_status === 'verified');
        $pendingFarmers = $farmers->filter(fn($user) => $user->farmerProfile?->verification_status === 'pending');
        $rejectedFarmers = $farmers->filter(fn($user) => $user->farmerProfile?->verification_status === 'rejected');
        $activeDistributors = $distributors->filter(fn($user) => $user->is_active && $user->distributorProfile?->verification_status === 'verified');

        $regions = $farmers
            ->groupBy(fn($user) => ($user->province ?: '-') . '|' . ($user->district ?: '-'))
            ->map(function ($users, string $key) use ($activeDistributors) {
                [$province, $district] = explode('|', $key);
                $activeDistributorCount = $activeDistributors
                    ->where('province', $province === '-' ? null : $province)
                    ->where('district', $district === '-' ? null : $district)
                    ->count();

                return [
                    'province' => $province === '-' ? null : $province,
                    'district' => $district === '-' ? null : $district,
                    'farmer_count' => $users->count(),
                    'land_area' => round((float) $users->sum(fn($user) => (float) ($user->farmerProfile?->land_area_hectares ?? 0)), 2),
                    'commodities' => $users->pluck('farmerProfile.main_commodity')->filter()->unique()->values(),
                    'active_distributors' => $activeDistributorCount,
                ];
            })
            ->sortByDesc('farmer_count')
            ->values();

        $stockByType = $fertilizerStocks
            ->groupBy('fertilizer_type_id')
            ->map(fn($stocks) => [
                'name' => $stocks->first()?->fertilizerType?->name ?? 'Pupuk',
                'stock_kg' => (int) $stocks->sum('stock_kg'),
                'reserved_kg' => (int) $stocks->sum('reserved_kg'),
                'available_kg' => max(0, (int) $stocks->sum('stock_kg') - (int) $stocks->sum('reserved_kg')),
            ])
            ->values();

        $transactionStatus = $transactions
            ->groupBy('status')
            ->map(fn($items, $status) => [
                'status' => $status,
                'count' => $items->count(),
                'approved_kg' => (int) $items->sum(fn($transaction) => $transaction->approved_kg ?? 0),
            ])
            ->values();

        return [
            'farmers' => [
                'total' => $farmers->count(),
                'verified' => $verifiedFarmers->count(),
                'pending' => $pendingFarmers->count(),
                'rejected' => $rejectedFarmers->count(),
                'land_area' => round((float) $farmers->sum(fn($user) => (float) ($user->farmerProfile?->land_area_hectares ?? 0)), 2),
                'commodities' => $farmers->pluck('farmerProfile.main_commodity')->filter()->unique()->values(),
            ],
            'distributors' => [
                'total' => $distributors->count(),
                'active_subsidy' => $activeDistributors->count(),
                'pending' => $distributors->filter(fn($user) => $user->distributorProfile?->verification_status === 'pending')->count(),
            ],
            'fertilizers' => [
                'active_types' => FertilizerType::where('is_active', true)->count(),
                'total_stock' => (int) $fertilizerStocks->sum('stock_kg'),
                'reserved_stock' => (int) $fertilizerStocks->sum('reserved_kg'),
                'available_stock' => max(0, (int) $fertilizerStocks->sum('stock_kg') - (int) $fertilizerStocks->sum('reserved_kg')),
                'allocated_quota' => (int) $fertilizerQuotas->sum('allocated_kg'),
                'used_quota' => (int) $fertilizerQuotas->sum('used_kg'),
                'remaining_quota' => max(0, (int) $fertilizerQuotas->sum('allocated_kg') - (int) $fertilizerQuotas->sum('used_kg')),
                'transaction_count' => $transactions->count(),
                'pending_transactions' => $transactions->where('status', 'pending')->count(),
                'dispensed_kg' => (int) $transactions->where('status', 'dispensed')->sum(fn($transaction) => $transaction->approved_kg ?? 0),
                'stock_by_type' => $stockByType,
                'transaction_status' => $transactionStatus,
            ],
            'regions' => $regions,
        ];
    }

    private function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
