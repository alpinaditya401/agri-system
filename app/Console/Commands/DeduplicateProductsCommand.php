<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class DeduplicateProductsCommand extends Command
{
    protected $signature = 'products:deduplicate {--apply : Soft-delete duplicate products. Defaults to dry-run.}';

    protected $description = 'Detect duplicate products by name, farmer, category, and price; optionally soft-delete duplicates.';

    public function handle(): int
    {
        $groups = Product::query()
            ->selectRaw('name, farmer_id, category_id, price_per_unit, COUNT(*) as duplicate_count, MIN(id) as keep_id')
            ->groupBy('name', 'farmer_id', 'category_id', 'price_per_unit')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('duplicate_count')
            ->get();

        if ($groups->isEmpty()) {
            $this->info('Tidak ada produk duplikat aktif berdasarkan nama + petani + kategori + harga.');

            return self::SUCCESS;
        }

        $rows = [];
        $deleted = 0;

        foreach ($groups as $group) {
            $duplicates = Product::query()
                ->where('name', $group->name)
                ->where('farmer_id', $group->farmer_id)
                ->where('category_id', $group->category_id)
                ->where('price_per_unit', $group->price_per_unit)
                ->where('id', '!=', $group->keep_id)
                ->orderBy('id')
                ->get();

            $rows[] = [
                'nama' => $group->name,
                'farmer_id' => $group->farmer_id,
                'category_id' => $group->category_id,
                'harga' => $group->price_per_unit,
                'keep_id' => $group->keep_id,
                'duplicate_ids' => $duplicates->pluck('id')->implode(', '),
            ];

            if ($this->option('apply')) {
                foreach ($duplicates as $duplicate) {
                    $duplicate->delete();
                    $deleted++;
                }
            }
        }

        $this->table(['Nama', 'Farmer ID', 'Kategori ID', 'Harga', 'Keep ID', 'Duplicate IDs'], $rows);

        if ($this->option('apply')) {
            $this->warn("Selesai. {$deleted} produk duplikat di-soft-delete. Pastikan backup database tersedia sebelum menjalankan di production.");
        } else {
            $this->info('Dry-run selesai. Jalankan dengan --apply untuk soft-delete duplikat yang terdeteksi.');
        }

        return self::SUCCESS;
    }
}
