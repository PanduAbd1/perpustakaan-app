<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $books = Book::with('author', 'categories')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('author', function ($a) use ($search) {
                            $a->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->query('category_id'), function ($query, $categoryId) {
                $query->whereHas('categories', function ($c) use ($categoryId) {
                    $c->where('categories.id', $categoryId);
                });
            })
            ->when($request->boolean('in_stock'), function ($query) {
                $query->where('stock', '>', 0);
            })
            ->latest()
            ->get();

        return response()->json($books);
    }

    public function lowStock(): JsonResponse
    {
        $books = Book::with('author', 'categories')
            ->where('stock', '<', 5)
            ->get();

        return response()->json($books);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'published_year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'author_id' => 'required|exists:authors,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book = Book::create(Arr::except($validated, ['category_ids']));
        $book->categories()->sync($validated['category_ids']);

        return response()->json($book->load('author', 'categories'), 201);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json($book->load('author', 'categories'));
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'isbn' => ['sometimes', 'string', Rule::unique('books', 'isbn')->ignore($book->id)],
            'published_year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'author_id' => 'sometimes|exists:authors,id',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book->update(Arr::except($validated, ['category_ids']));

        if (isset($validated['category_ids'])) {
            $book->categories()->sync($validated['category_ids']);
        }

        return response()->json($book->load('author', 'categories'));
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
