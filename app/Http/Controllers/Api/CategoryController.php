<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Admin: create a new category (e.g. when adding a product).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Emri i kategorisë është i detyrueshëm.',
        ]);

        $name = trim($validated['name']);
        $slug = Str::slug($name);
        $baseSlug = $slug ?: 'kategoria';
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . ($counter++);
        }

        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $category,
        ], 201);
    }
}
