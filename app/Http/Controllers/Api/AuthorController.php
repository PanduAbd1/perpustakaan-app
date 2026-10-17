<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Author::withCount('books')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authors,email',
            'bio' => 'nullable|string',
        ]);

        return response()->json(Author::create($validated), 201);
    }

    public function show(Author $author): JsonResponse
    {
        return response()->json($author->load('books'));
    }

    public function update(Request $request, Author $author): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('authors', 'email')->ignore($author->id)],
            'bio' => 'nullable|string',
        ]);

        $author->update($validated);

        return response()->json($author);
    }

    public function destroy(Author $author): JsonResponse
    {
        $deletedBooks = $author->books()->count();
        $author->delete();

        return response()->json([
            'message' => 'Penulis berhasil dihapus beserta ' . $deletedBooks . ' buku terkait',
        ]);
    }
}
