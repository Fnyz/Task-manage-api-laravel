<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // get the specified user's categories
        $categories = Category::where('user_id', $request->user()->id);
        $categories = $categories->withCount('tasks')->latest()->paginate($request->query('per_page', 5));
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        // create a new category for the authenticated user
        $category = Category::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
        ]);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);

        if(!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        };

        // return the specified category if it belongs to the authenticated user
        $this->authorize('view', $category);

        // return the category as a JSON response
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // update the specified category if it belongs to the authenticated user
        $this->authorize('update', $category);

        // update the category with the validated data
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        // delete the specified category if it belongs to the authenticated user
        $this->authorize('delete', $category);

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        // restore the specified category if it belongs to the authenticated user
        $category = Category::withTrashed()->findOrFail($id);
        $this->authorize('update', $category);

        // restore the category
        $category->restore();
        return response()->json(['message' => 'Category restored successfully', 'category' => new CategoryResource($category)]);
    }
}
