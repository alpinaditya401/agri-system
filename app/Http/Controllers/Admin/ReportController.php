<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommodityPrice;
use App\Models\FertilizerTransaction;
use App\Models\Order;
use App\Services\FertilizerQuotaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly FertilizerQuotaService $quotaService)
    {
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
