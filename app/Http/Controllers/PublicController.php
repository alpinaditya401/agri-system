<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\CommodityPrice;
use App\Services\BpsApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicController extends Controller
{
    protected $bpsApiService;

    public function __construct(BpsApiService $bpsApiService)
    {
        $this->bpsApiService = $bpsApiService;
    }

    /**
     * Route: / (name: home)
     */
    public function landing(): View
    {
        $latestPrices = $this->bpsApiService->getCachedLandingPrices();
        $articles = Article::with('author')
            ->published()
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('landing', compact('latestPrices', 'articles'));
    }

    /**
     * Route: /harga-komoditas (name: public.prices)
     */
    public function commodityPrices(Request $request): View
    {
        $prices = CommodityPrice::query()
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('commodity_prices')
                    ->groupBy('commodity_code', 'region_code');
            })
            ->orderBy('price_date', 'desc')
            ->orderBy('commodity_name')
            ->limit(80)
            ->get();

        if ($prices->isEmpty()) {
            $prices = collect($this->bpsApiService->getLatestPrices(50));
        }

        $categories = collect($prices)->pluck('category')->filter()->unique()->values();
        $availableCommodities = collect($prices)
            ->filter(fn($price) => filled($price->commodity_code ?? null))
            ->unique('commodity_code')
            ->map(fn($price) => [
                'code' => $price->commodity_code,
                'name' => $price->commodity_name,
                'category' => $price->category,
            ])
            ->sortBy('name')
            ->values();

        $requestedCompare = collect((array) $request->query('compare', []))
            ->map(fn($code) => trim((string) $code))
            ->filter();

        $availableCodes = $availableCommodities->pluck('code');
        $compareCodes = $requestedCompare
            ->filter(fn($code) => $availableCodes->contains($code))
            ->take(5)
            ->values();

        if ($compareCodes->isEmpty()) {
            $compareCodes = $availableCodes->take(4)->values();
        }

        [$priceChart, $comparisonRows, $categorySummary] = $this->buildCommodityComparison($compareCodes, $prices);

        $bpsArticle = Article::published()
            ->where('slug', 'sumber-data-bps-agrilink')
            ->first();

        return view('public.prices', compact(
            'prices',
            'categories',
            'bpsArticle',
            'availableCommodities',
            'compareCodes',
            'priceChart',
            'comparisonRows',
            'categorySummary',
        ));
    }

    private function buildCommodityComparison(Collection $compareCodes, Collection $latestPrices): array
    {
        $trendRows = CommodityPrice::query()
            ->whereIn('commodity_code', $compareCodes)
            ->where('price_date', '>=', now()->subMonths(11)->startOfMonth())
            ->orderBy('price_date')
            ->get();

        $labels = $trendRows
            ->pluck('price_date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        $colors = ['#059669', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed'];
        $datasets = $compareCodes->map(function (string $code, int $index) use ($trendRows, $labels, $latestPrices, $colors) {
            $latest = $latestPrices->firstWhere('commodity_code', $code);
            $rowsByDate = $trendRows
                ->where('commodity_code', $code)
                ->groupBy(fn($row) => Carbon::parse($row->price_date)->format('Y-m-d'))
                ->map(fn($rows) => $rows->sortByDesc('id')->first());

            return [
                'label' => $latest->commodity_name ?? $code,
                'data' => $labels->map(fn($date) => $rowsByDate->has($date) ? round((float) $rowsByDate[$date]->price, 0) : null)->values(),
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => $colors[$index % count($colors)] . '22',
                'borderWidth' => 3,
                'tension' => 0.35,
                'spanGaps' => true,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
            ];
        })->values();

        $comparisonRows = $compareCodes->map(function (string $code) {
            $rows = CommodityPrice::query()
                ->where('commodity_code', $code)
                ->orderByDesc('price_date')
                ->orderByDesc('id')
                ->limit(2)
                ->get();

            $latest = $rows->first();
            $previous = $rows->get(1);
            $change = $latest && $previous ? (float) $latest->price - (float) $previous->price : null;
            $changePercent = $change !== null && (float) $previous->price > 0
                ? round(($change / (float) $previous->price) * 100, 2)
                : null;

            return [
                'code' => $code,
                'name' => $latest?->commodity_name ?? $code,
                'category' => $latest?->category ?? '-',
                'latest_price' => $latest ? (float) $latest->price : null,
                'previous_price' => $previous ? (float) $previous->price : null,
                'change' => $change,
                'change_percent' => $changePercent,
                'unit' => $latest?->unit ?? 'kg',
                'date' => $latest?->price_date,
            ];
        })->values();

        $categorySummary = $latestPrices
            ->groupBy(fn($price) => $price->category ?: 'Umum')
            ->map(fn($items, $category) => [
                'category' => $category,
                'count' => $items->count(),
                'average' => round((float) $items->avg(fn($item) => (float) $item->price), 0),
                'min' => round((float) $items->min(fn($item) => (float) $item->price), 0),
                'max' => round((float) $items->max(fn($item) => (float) $item->price), 0),
            ])
            ->sortBy('category')
            ->values();

        return [[
            'labels' => $labels->map(fn($date) => Carbon::parse($date)->translatedFormat('M Y'))->values(),
            'datasets' => $datasets,
            'categoryLabels' => $categorySummary->pluck('category')->values(),
            'categoryAverages' => $categorySummary->pluck('average')->values(),
        ], $comparisonRows, $categorySummary];
    }

    /**
     * Route: /artikel (name: public.articles)
     */
    public function articles(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category');

        $articles = Article::with('author')
            ->published()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($category, fn($query) => $query->where('category', $category))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Article::published()
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('public.articles', compact('articles', 'categories', 'search', 'category'));
    }

    /**
     * Route: /artikel/{slug} (name: public.articles.show)
     */
    public function articleShow(string $slug): View
    {
        $article = Article::with('author')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $article->increment('view_count');

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('public.article_show', compact('article', 'related'));
    }

    /**
     * Route: /peta (name: public.map)
     */
    public function map(): View
    {
        return view('public.map');
    }
}
