<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        $articles = Article::with('author')->latest()->paginate(20);
        return view('admin.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('admin.articles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'content'     => ['required', 'string'],
            'category'    => ['nullable', 'string', 'max:50'],
            'tags'        => ['nullable', 'array'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'status'      => ['required', 'in:draft,published,archived'],
        ]);

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $count = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        Article::create([
            'author_id'    => auth()->id(),
            'title'        => $validated['title'],
            'slug'         => $slug,
            'excerpt'      => $validated['excerpt'],
            'content'      => $validated['content'],
            'category'     => $validated['category'],
            'tags'         => $validated['tags'],
            'cover_image'  => $validated['cover_image'],
            'status'       => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function show(Article $artikel): View
    {
        return view('admin.articles.show', compact('artikel'));
    }

    public function edit(Article $artikel): View
    {
        return view('admin.articles.edit', compact('artikel'));
    }

    public function update(Request $request, Article $artikel): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'excerpt'     => ['nullable', 'string', 'max:500'],
            'content'     => ['required', 'string'],
            'category'    => ['nullable', 'string', 'max:50'],
            'tags'        => ['nullable', 'array'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'status'      => ['required', 'in:draft,published,archived'],
        ]);

        $artikel->update([
            'title'        => $validated['title'],
            'excerpt'      => $validated['excerpt'],
            'content'      => $validated['content'],
            'category'     => $validated['category'],
            'tags'         => $validated['tags'],
            'cover_image'  => $validated['cover_image'],
            'status'       => $validated['status'],
            'published_at' => $validated['status'] === 'published' && !$artikel->published_at ? now() : $artikel->published_at,
        ]);

        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $artikel): RedirectResponse
    {
        $artikel->delete();
        return redirect()->route('admin.artikel.index')->with('success', 'Artikel berhasil dihapus.');
    }
}
