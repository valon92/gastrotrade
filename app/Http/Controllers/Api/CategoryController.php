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

    /**
     * Admin: update category details (name/description).
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Emri i kategorisë është i detyrueshëm.',
        ]);

        $name = trim($validated['name']);
        if ($name !== $category->name) {
            $baseSlug = Str::slug($name) ?: 'kategoria';
            $slug = $baseSlug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '-' . ($counter++);
            }
            $category->slug = $slug;
        }

        $category->name = $name;
        $category->description = $validated['description'] ?? null;
        $category->save();

        return response()->json([
            'success' => true,
            'data' => $category->fresh()->loadCount('products'),
        ]);
    }

    /**
     * Admin: delete category when no products depend on it.
     */
    public function destroy(Category $category)
    {
        $productsCount = $category->products()->count();
        if ($productsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Kjo kategori përdoret nga {$productsCount} produkte. Ndryshoni produktet para fshirjes.",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategoria u fshi me sukses.',
        ]);
    }
}
