<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryForm;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="List all categories",
     *     tags={"Categories"},
     *     security={{ "jwt":{} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort in descending order",
     *         required=false,
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of categories",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="categories", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426655440000"),
     *                 @OA\Property(property="title", type="string", example="Electronics"),
     *                 @OA\Property(property="slug", type="string", example="electronics")
     *             ))
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        // Set default values for filters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sort_by', 'id');
        $desc = $request->query('desc', false);

        // Build the product query with the filters
        $query = Category::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $categories = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['categories' => $categories], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/category",
     *     tags={"Categories"},
     *     summary="Create a new category",
     *     security={{ "jwt":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="uuid", type="string", example="Category UUID"),
     *             @OA\Property(property="title", type="string", example="Electronics"),
     *             @OA\Property(property="slug", type="string", example="electronics"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Category created successfully.", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Category created successfully."))),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function store(CategoryForm $request)
    {
        $data = $request->validated();
        $data['uuid'] = Str::uuid();
        Category::create($data);

        return response()->json(['message' => 'Category created successfully.'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/category/{uuid}",
     *     tags={"Categories"},
     *     summary="Get a category by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the category to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the requested category",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="title", type="string", example="Books"),
     *                 @OA\Property(property="slug", type="string", example="books")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", description="Category not found"),
     * )
     */

    public function show(string $uuid)
    {
        $category = Category::where('uuid', $uuid)->get();
        return response()->json(['category' => $category], 200);

    }

    /**
     * @OA\Put(
     *     path="/api/v1/category/{uuid}",
     *     tags={"Categories"},
     *     summary="Update a category by UUID",
     *     security={{ "jwt":{} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the category to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Category Title"),
     *             @OA\Property(property="slug", type="string", example="category-slug"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the updated category",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category updated successfully."),
     *             @OA\Property(
     *                 property="category",
     *                 type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="title", type="string", example="Category Title"),
     *                 @OA\Property(property="slug", type="string", example="category-slug"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function update(CategoryForm $request, string $uuid)
    {
        $data = $request->validated();
        $category = Category::where('uuid', $uuid)->first();
        // Fill the user instance with the new data
        $category->fill($data);
        // Save the changes to the database
        $category->save();

        return response()->json(['message' => 'Category updated successfully.', 'category' => $category], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/category/{uuid}",
     *     tags={"Categories"},
     *     summary="Delete a category by ID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="ID of the category to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="int64",
     *             example="c62f1e85-5329-3030-bf2a-173f5a800376"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Category not found"),
     * )
     */

    public function destroy(string $uuid)
    {
        $category = Category::where('uuid', $uuid)->first();
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.'], 200);
    }
}
