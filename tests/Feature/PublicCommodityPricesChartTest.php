<?php

namespace Tests\Feature;

use App\Models\CommodityPrice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCommodityPricesChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_price_page_shows_charts_and_commodity_comparison(): void
    {
        Carbon::setTestNow('2026-07-01 08:00:00');

        try {
            $this->seedPriceSeries('bps-cabai-merah-susenas', 'Cabai Merah', 'Hortikultura', 36000);
            $this->seedPriceSeries('bps-beras-medium-penggilingan', 'Beras Medium', 'Pangan', 13200);
            $this->seedPriceSeries('demo-jagung-pipilan', 'Jagung Pipilan', 'Palawija', 6900);

            $this->get(route('public.prices', [
                'compare' => [
                    'bps-cabai-merah-susenas',
                    'bps-beras-medium-penggilingan',
                ],
            ]))
                ->assertOk()
                ->assertSee('Trend dan komparasi komoditas')
                ->assertSee('priceTrendChart')
                ->assertSee('categoryAverageChart')
                ->assertSee('Cabai Merah')
                ->assertSee('Beras Medium')
                ->assertSee('Bandingkan');
        } finally {
            Carbon::setTestNow();
        }
    }

    private function seedPriceSeries(string $code, string $name, string $category, int $basePrice): void
    {
        foreach (range(0, 2) as $monthOffset) {
            CommodityPrice::create([
                'commodity_name' => $name,
                'commodity_code' => $code,
                'category' => $category,
                'price' => $basePrice + ($monthOffset * 500),
                'unit' => 'kg',
                'region' => 'Nasional',
                'region_code' => '0000',
                'source' => 'Demo Test',
                'price_date' => now()->startOfMonth()->subMonths(2 - $monthOffset)->endOfMonth(),
                'raw_data' => [
                    'source_note' => 'Data test untuk chart harga komoditas.',
                ],
            ]);
        }
    }
}
