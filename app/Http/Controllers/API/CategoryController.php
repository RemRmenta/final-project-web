<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255|unique:categories,name',
            'description'=>'nullable|string'
        ]);

        $category = Category::create($data);
        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('serviceRequests'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'=>'sometimes|string|max:255|unique:categories,name,'.$category->id,
            'description'=>'nullable|string'
        ]);

        $category->update($data);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message'=>'Category deleted successfully']);
    }
}
