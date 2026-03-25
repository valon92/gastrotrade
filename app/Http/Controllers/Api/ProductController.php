<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProjectImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private ?bool $projectImagesTableExists = null;

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

        $products = $query->orderBy('name', 'asc')->get();

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
            ->orderBy('name', 'asc')
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
            'size' => ['nullable', 'string', 'max:100'],
            'liters' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sold_by_package' => ['nullable', 'boolean'],
            'pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:20480'],
            'image_path' => ['nullable', 'string', 'max:500'],
        ], [
            'image.file' => 'Skedari i zgjedhur nuk është valid.',
            'image.mimes' => 'Lejohen vetëm formatet: JPG, PNG, GIF, WEBP.',
            'image.max' => 'Foto nuk duhet të kalojë 20 MB.',
        ]);

        $data = $validated;
        $data['slug'] = $this->generateUniqueSlug($validated['name']);
        $data['is_featured'] = (bool)($validated['is_featured'] ?? false);
        $data['sold_by_package'] = (bool)($validated['sold_by_package'] ?? false);
        $data['description'] = $validated['description'] ?? null;
        $data['size'] = isset($validated['size']) && $validated['size'] !== '' ? $validated['size'] : null;
        $data['liters'] = isset($validated['liters']) && $validated['liters'] !== '' ? $validated['liters'] : null;
        $data['barcode'] = isset($validated['barcode']) && $validated['barcode'] !== '' ? $validated['barcode'] : null;
        $sortOrder = $validated['sort_order'] ?? null;
        $data['sort_order'] = (isset($sortOrder) && $sortOrder !== '' && $sortOrder !== null) ? (int) $sortOrder : 0;

        if (!$data['sold_by_package']) {
            $data['pieces_per_package'] = null;
        }

        if ($request->hasFile('image')) {
            try {
                $data['image_path'] = $this->storeProductImage($request->file('image'));
            } catch (\RuntimeException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        } elseif ($request->filled('image_path') && (Str::startsWith($request->input('image_path'), '/images/') || Str::startsWith($request->input('image_path'), '/uploads/'))) {
            $data['image_path'] = $request->input('image_path');
        }

        // Only pass fillable attributes (e.g. exclude uploaded file key)
        $data = array_intersect_key($data, array_fill_keys((new Product)->getFillable(), true));

        $product = DB::transaction(function () use ($data) {
            $product = Product::create($data);
            if (!empty($product->image_path)) {
                $this->syncProductFeaturedImage($product, $product->image_path);
                $this->syncProjectImageLibrary($product->image_path, $product->id);
            }
            return $product->load(['category', 'images']);
        });

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 201);
    }

    /**
     * Admin: update product
     */
    public function update(Request $request, Product $product)
    {
        // Normalize empty inputs so validation accepts them (FormData sends strings)
        if ($request->has('price') && $request->input('price') === '') {
            $request->merge(['price' => null]);
        }
        if ($request->has('sort_order') && $request->input('sort_order') === '') {
            $request->merge(['sort_order' => null]);
        }
        if ($request->has('pieces_per_package') && $request->input('pieces_per_package') === '') {
            $request->merge(['pieces_per_package' => null]);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'size' => ['nullable', 'string', 'max:100'],
            'liters' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sold_by_package' => ['nullable', 'boolean'],
            'pieces_per_package' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:20480'],
            'image_path' => ['nullable', 'string', 'max:500'],
        ], [
            'image.file' => 'Skedari i zgjedhur nuk është valid.',
            'image.mimes' => 'Lejohen vetëm formatet: JPG, PNG, GIF, WEBP.',
            'image.max' => 'Foto nuk duhet të kalojë 20 MB.',
        ]);

        $data = $validated;
        if (array_key_exists('size', $data) && $data['size'] === '') {
            $data['size'] = null;
        }
        if (array_key_exists('liters', $data) && $data['liters'] === '') {
            $data['liters'] = null;
        }
        if (array_key_exists('barcode', $data) && $data['barcode'] === '') {
            $data['barcode'] = null;
        }

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
            $this->deleteProductImage($product->image_path);
            try {
                $data['image_path'] = $this->storeProductImage($request->file('image'));
            } catch (\RuntimeException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        } elseif ($request->filled('image_path') && (Str::startsWith($request->input('image_path'), '/images/') || Str::startsWith($request->input('image_path'), '/uploads/'))) {
            $data['image_path'] = $request->input('image_path');
        }

        $data = array_intersect_key($data, array_fill_keys((new Product)->getFillable(), true));

        DB::transaction(function () use ($product, $data) {
            $product->update($data);
            if (array_key_exists('image_path', $data) && !empty($data['image_path'])) {
                $this->syncProductFeaturedImage($product, $data['image_path']);
                $this->syncProjectImageLibrary($data['image_path'], $product->id);
            }
        });

        return response()->json([
            'success' => true,
            'data' => $product->refresh()->load(['category', 'images']),
        ]);
    }

    /**
     * Admin: delete product
     */
    public function destroy(Product $product)
    {
        $this->deleteProductImage($product->image_path);
        $product->images()->delete();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Sync product_images: one featured record for the main image (created/updated automatically).
     */
    private function syncProductFeaturedImage(Product $product, string $imagePath): void
    {
        $fileName = basename($imagePath);
        $existing = $product->images()->where('is_featured', true)->first();
        if ($existing) {
            $existing->update(['file_name' => $fileName, 'file_path' => $imagePath]);
        } else {
            $product->images()->create([
                'file_name' => $fileName,
                'file_path' => $imagePath,
                'is_featured' => true,
                'sort_order' => 0,
            ]);
        }
    }

    /**
     * Store uploaded image in public/uploads/products (works without storage:link, e.g. cPanel).
     * Ensures directory exists and is writable; throws on failure with a clear message for the client.
     */
    private function storeProductImage($file): string
    {
        // Store inside public/images so it's always web-accessible on shared hosting/cPanel.
        // (Some hosts don't serve files written under public/uploads reliably depending on document root.)
        $subDir = 'images/Uploads/products/' . now()->format('Y/m');
        $dir = public_path($subDir);
        if (!File::isDirectory($dir)) {
            File::ensureDirectoryExists($dir, 0755, true);
        }
        if (!File::isWritable($dir)) {
            throw new \RuntimeException(
                'Drejtoria për foto nuk lejon shkrim. Kontrolloni të drejtat e folderit public/images/Uploads në server.'
            );
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }
        $name = Str::uuid() . '.' . $ext;
        try {
            $file->move($dir, $name);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Ngarkimi i fotos dështoi. Kontrolloni të drejtat e folderit public/images/Uploads në server.',
                0,
                $e
            );
        }
        return '/images/Uploads/products/' . now()->format('Y/m') . '/' . $name;
    }

    private function deleteProductImage(?string $path): void
    {
        if (!$path) {
            return;
        }
        if (Str::startsWith($path, '/uploads/')) {
            // Legacy path support
            $relativePath = Str::after($path, '/');
            $fullPath = public_path($relativePath);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
            return;
        }
        if (Str::startsWith($path, '/images/Uploads/')) {
            $relativePath = Str::after($path, '/');
            $fullPath = public_path($relativePath);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
            return;
        }
        if (Str::startsWith($path, '/storage/')) {
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

    /**
     * Admin: list image paths from public/images (foto nga projekti)
     */
    public function listProjectImages()
    {
        $basePath = public_path('images');
        $pathsWithMtime = [];
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!File::isDirectory($basePath)) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $baseReal = realpath($basePath);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $extensions, true)) {
                continue;
            }
            $full = $file->getRealPath();
            $relative = substr($full, strlen($baseReal) + 1);
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
            $path = '/images/' . $relative;
            $pathsWithMtime[$path] = $file->getMTime();
        }
        arsort($pathsWithMtime);

        $ordered = [];
        if ($this->hasProjectImagesTable()) {
            $dbFirst = ProjectImage::query()
                ->orderByDesc('updated_at')
                ->pluck('file_path')
                ->all();
            foreach ($dbFirst as $path) {
                if (isset($pathsWithMtime[$path]) && !in_array($path, $ordered, true)) {
                    $ordered[] = $path;
                }
            }
        }
        foreach (array_keys($pathsWithMtime) as $path) {
            if (!in_array($path, $ordered, true)) {
                $ordered[] = $path;
            }
        }

        return response()
            ->json(['success' => true, 'data' => $ordered])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Admin: safely serve a project image from filesystem.
     * Helps when static web server config doesn't expose public/images properly.
     */
    public function serveProjectImage(Request $request)
    {
        $path = (string) $request->query('path', '');
        if ($path === '' || !Str::startsWith($path, '/')) {
            abort(404);
        }

        $public = public_path();
        $full = realpath($public . DIRECTORY_SEPARATOR . ltrim($path, '/'));
        if ($full === false) {
            abort(404);
        }

        $imagesRoot = realpath(public_path('images'));
        $uploadsRoot = realpath(public_path('uploads'));

        $allowed = false;
        if ($imagesRoot && Str::startsWith($full, $imagesRoot . DIRECTORY_SEPARATOR)) {
            $allowed = true;
        }
        if ($uploadsRoot && Str::startsWith($full, $uploadsRoot . DIRECTORY_SEPARATOR)) {
            $allowed = true;
        }
        if (!$allowed || !is_file($full)) {
            abort(404);
        }

        return response()->file($full, [
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * Admin: upload image directly into public/images (project library).
     * Optionally links image to an existing product immediately.
     */
    public function uploadProjectImage(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:20480'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ], [
            'image.required' => 'Ju lutem zgjidhni një foto.',
            'image.mimes' => 'Lejohen vetëm formatet: JPG, PNG, GIF, WEBP.',
            'image.max' => 'Foto nuk duhet të kalojë 20 MB.',
        ]);

        $file = $request->file('image');
        $subDir = 'images/Uploads/' . now()->format('Y/m');
        $dir = public_path($subDir);
        if (!File::isDirectory($dir)) {
            File::ensureDirectoryExists($dir, 0755, true);
        }
        if (!File::isWritable($dir)) {
            return response()->json([
                'success' => false,
                'message' => 'Drejtoria public/images/Uploads nuk lejon shkrim në server.',
            ], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }
        $name = Str::uuid() . '.' . $ext;

        try {
            $file->move($dir, $name);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ngarkimi i fotos dështoi. Provoni përsëri.',
            ], 422);
        }

        $publicPath = '/images/Uploads/' . now()->format('Y/m') . '/' . $name;
        $updatedProduct = null;

        if (!empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                DB::transaction(function () use ($product, $publicPath, &$updatedProduct) {
                    $product->update(['image_path' => $publicPath]);
                    $this->syncProductFeaturedImage($product, $publicPath);
                    $this->syncProjectImageLibrary($publicPath, $product->id);
                    $updatedProduct = $product->refresh()->load(['category', 'images']);
                });
            }
        } else {
            $this->syncProjectImageLibrary($publicPath, null, [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'path' => $publicPath,
                'product_updated' => (bool)$updatedProduct,
                'product' => $updatedProduct,
            ],
        ], 201);
    }

    private function syncProjectImageLibrary(string $filePath, ?int $productId = null, ?array $meta = null): void
    {
        if (!$this->hasProjectImagesTable()) {
            return;
        }

        $full = public_path(ltrim($filePath, '/'));
        $defaults = [
            'linked_product_id' => $productId,
            'uploaded_by' => auth()->id(),
        ];
        if (is_array($meta)) {
            $defaults = array_merge($defaults, [
                'original_name' => $meta['original_name'] ?? null,
                'mime_type' => $meta['mime_type'] ?? null,
                'size_bytes' => $meta['size_bytes'] ?? null,
            ]);
        }
        if (File::exists($full)) {
            $defaults['checksum_sha1'] = @sha1_file($full) ?: null;
            $defaults['size_bytes'] = $defaults['size_bytes'] ?? @filesize($full) ?: null;
        }

        $record = ProjectImage::firstOrNew(['file_path' => $filePath]);
        $record->fill($defaults);
        $record->save();
    }

    private function hasProjectImagesTable(): bool
    {
        if ($this->projectImagesTableExists !== null) {
            return $this->projectImagesTableExists;
        }

        try {
            $this->projectImagesTableExists = Schema::hasTable('project_images');
        } catch (\Throwable $e) {
            $this->projectImagesTableExists = false;
        }

        return $this->projectImagesTableExists;
    }
}