<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|min:500',
            'category_id' => 'required|numeric|exists:categories,id',
        ]);

        if (Article::where('ip', request()->ip())->whereDay('created_at', now()->day)->count() >= 3) {
            return back()->with('error', 'Quota atteint');
        }

        $article = Article::create([
            'name' => $request->name,
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'ip' => request()->ip()
        ]);

        if (is_null($article->name)) {
            $article->update([
               'name' => config('press.article.nullable_name_prefix').$article->id
            ]);
        }

        return back()->with('success', 'Article crée avec succès');
    }
}
