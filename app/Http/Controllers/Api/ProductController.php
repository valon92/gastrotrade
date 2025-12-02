<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images']);

        // Filter by category if provided
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter featured products
        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        $products = $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'images'])
            ->where('slug', $slug)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Get all categories
     */
    public function categories()
    {
        $categories = Category::withCount('products')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        $products = Product::with(['category', 'images'])
            ->where('is_featured', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Admin: list products with optional filters
     */
    public function adminIndex(Request $request)
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Admin: store product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sold_by_package' => ['nullable', 'boolean'],
            'pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = $validated;
        $data['slug'] = $this->generateUniqueSlug($validated['name']);
        $data['is_featured'] = (bool)($validated['is_featured'] ?? false);
        $data['sold_by_package'] = (bool)($validated['sold_by_package'] ?? false);
        $data['description'] = $validated['description'] ?? null;

        if (!$data['sold_by_package']) {
            $data['pieces_per_package'] = null;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'data' => $product->load('category'),
        ], 201);
    }

    /**
     * Admin: update product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sold_by_package' => ['nullable', 'boolean'],
            'pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = $validated;

        if (array_key_exists('name', $data) && $data['name'] !== $product->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
        }

        if (array_key_exists('sold_by_package', $data)) {
            $data['sold_by_package'] = (bool)$data['sold_by_package'];
            if (!$data['sold_by_package']) {
                $data['pieces_per_package'] = null;
            }
        }

        if (array_key_exists('is_featured', $data)) {
            $data['is_featured'] = (bool)$data['is_featured'];
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image_path);
            $data['image_path'] = $this->storeImage($request->file('image'));
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'data' => $product->refresh()->load('category'),
        ]);
    }

    /**
     * Admin: delete product
     */
    public function destroy(Product $product)
    {
        $this->deleteImage($product->image_path);
        $product->images()->delete();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    private function storeImage($file): string
    {
        $path = $file->store('products', 'public');
        return Storage::url($path);
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, '/storage/')) {
            $relativePath = Str::after($path, '/storage/');
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . ($counter++);
        }

        return $slug;
    }
}