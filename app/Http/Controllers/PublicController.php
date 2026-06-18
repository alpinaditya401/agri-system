<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\BpsApiService;
use Illuminate\Http\Request;
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
    public function commodityPrices(): View
    {
        $prices = $this->bpsApiService->getLatestPrices(50);
        $categories = array_unique(array_column($prices, 'category'));

        return view('public.prices', compact('prices', 'categories'));
    }

    /**
     * Route: /artikel (name: public.articles)
     */
    public function articles(): View
    {
        $articles = Article::with('author')
            ->published()
            ->latest('published_at')
            ->paginate(12);

        return view('public.articles', compact('articles'));
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
