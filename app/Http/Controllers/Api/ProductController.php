<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductForm;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="Get a list of products",
     *     @OA\Response(response="200", description="A list of products")
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
        $query = Product::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $products = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['products' => $products], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/product",
     *     tags={"Products"},
     *     summary="Create a new product",
     *     security={{ "jwt":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="category_uuid", type="string", example="Category UUID"),
     *             @OA\Property(property="uuid", type="string", example="Product UUID"),
     *             @OA\Property(property="title", type="string", example="Product Title"),
     *             @OA\Property(property="price", type="number", format="float", example=9.99),
     *             @OA\Property(property="description", type="string", example="Product Description"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Product created successfully.", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Product created successfully."))),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function store(ProductForm $request)
    {
        $data = $request->validated();
        $data['category_uuid'] = $request->category_uuid;
        $data['uuid'] = Str::uuid();
        Product::create($data);

        return response()->json(['message' => 'Product created successfully.'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Get a product by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the product to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the requested product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="product", type="object", example={"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "title": "Product Title", "price": 9.99, "description": "Product Description", "metadata": {"brand": "fc3b1c7d-66eb-3034-8282-0153c25a887e", "image": "fb0c8521-e767-3fda-94ae-f79bef48331a"}})
     *         )
     *     ),
     *     @OA\Response(response="404", description="Product not found"),
     * )
     */

    public function show(string $uuid)
    {
        $product = Product::where('uuid', $uuid)->get();
        return response()->json(['product' => $product], 200);

    }

    /**
     * @OA\Put(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Update a product by UUID",
     *     security={{ "jwt":{} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the product to update",
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
     *             @OA\Property(property="category_uuid", type="string", example="Category UUID"),
     *             @OA\Property(property="uuid", type="string", example="Product UUID"),
     *             @OA\Property(property="title", type="string", example="Product Title"),
     *             @OA\Property(property="price", type="number", format="float", example=9.99),
     *             @OA\Property(property="description", type="string", example="Product Description"),
     *             @OA\Property(property="metadata", type="object", example={"key": "value"}),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the updated product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product updated successfully."),
     *             @OA\Property(
     *                 property="product",
     *                 type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="category_uuid", type="string", example="Category UUID"),
     *                 @OA\Property(property="title", type="string", example="Product Title"),
     *                 @OA\Property(property="price", type="number", format="float", example=9.99),
     *                 @OA\Property(property="description", type="string", example="Product Description"),
     *                 @OA\Property(property="metadata", type="object", example={"key": "value"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */
    public function update(ProductForm $request, string $uuid)
    {
        $data = $request->validated();
        $product = Product::where('uuid', $uuid)->first();
        // Fill the user instance with the new data
        $product->fill($data);
        // Save the changes to the database
        $product->save();

        return response()->json(['message' => 'Product updated successfully.', 'product' => $product], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Delete a product",
     *     security={{ "jwt":{} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="The UUID of the product to be deleted",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Product deleted successfully.", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Product deleted successfully."))),
     *     @OA\Response(response="404", description="Product not found.")
     * )
     */

    public function destroy(string $uuid)
    {
        $product = Product::where('uuid', $uuid)->first();
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.'], 200);
    }
}
