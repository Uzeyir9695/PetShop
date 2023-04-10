<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandForm;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/brands",
     *     tags={"Brands"},
     *     summary="Get a list of brands",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number of the results",
     *         required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="The number of results per page",
     *         required=false,
     *         @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="The field to sort the results by",
     *         required=false,
     *         @OA\Schema(type="string", default="id")
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Whether to sort the results in descending order",
     *         required=false,
     *         @OA\Schema(type="boolean", default="false")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a paginated list of brands",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="brands", type="object", example={
     *                 "current_page": 1,
     *                 "data": {
     *                     {"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "title": "Brand 1", "slug": "brand-1"},
     *                     {"uuid": "55a7acca-2dee-4704-946c-e7df862cd88f", "title": "Brand 2", "slug": "brand-2"},
     *                     {"uuid": "66a7acca-3eee-5704-947c-e7df862cd88g", "title": "Brand 3", "slug": "brand-3"},
     *                     {"uuid": "77a7acca-4ffe-6704-948c-e7df862cd88h", "title": "Brand 4", "slug": "brand-4"},
     *                     {"uuid": "88a7acca-5aee-7704-949c-e7df862cd88i", "title": "Brand 5", "slug": "brand-5"}
     *                 },
     *                 "first_page_url": "http://example.com/api/v1/brands?page=1",
     *                 "from": 1,
     *                 "last_page": 1,
     *                 "last_page_url": "http://example.com/api/v1/brands?page=1",
     *                 "links": {
     *                     "previous": null,
     *                     "next": null
     *                 },
     *                 "next_page_url": null,
     *                 "path": "http://example.com/api/v1/brands",
     *                 "per_page": 10,
     *                 "prev_page_url": null,
     *                 "to": 5,
     *                 "total": 5
     *             })
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
        $query = Brand::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $brands = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['brands' => $brands], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/brand",
     *     tags={"Brands"},
     *     summary="Create a new brand",
     *     security={{ "jwt": {} }},
     *     @OA\RequestBody(
     *         description="Brand data",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Brand Title"),
     *             @OA\Property(property="slug", type="string", example="brand-slug")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Brand created successfully."),
     *     @OA\Response(response="422", description="Invalid data.")
     * )
     */
    public function store(BrandForm $request)
    {
        $data = $request->validated();
        $data['uuid'] = Str::uuid();
        Brand::create($data);

        return response()->json(['message' => 'Brand created successfully.'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/brand/{uuid}",
     *     tags={"Brands"},
     *     summary="Get a brand by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the brand to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the requested brand",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="brand", type="object", example={"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "title": "Brand Title", "slug": "brand-slug"})
     *         )
     *     ),
     *     @OA\Response(response="404", description="Brand not found"),
     * )
     */
    public function show(string $uuid)
    {
        $brand = Brand::where('uuid', $uuid)->get();
        return response()->json(['brand' => $brand], 200);

    }

    /**
     * @OA\Patch(
     *     path="/api/v1/brand/{uuid}",
     *     tags={"Brands"},
     *     summary="Update a brand by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the brand to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Brand data to update",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Brand Title"),
     *             @OA\Property(property="slug", type="string", example="brand-slug")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the updated brand",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand updated successfully."),
     *             @OA\Property(property="brand", type="object", example={"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "title": "Brand Title", "slug": "brand-slug"})
     *         )
     *     ),
     *     @OA\Response(response="404", description="Brand not found"),
     * )
     */
    public function update(BrandForm $request, string $uuid)
    {
        $data = $request->validated();
        $brand = Brand::where('uuid', $uuid)->first();
        // Fill the user instance with the new data
        $brand->fill($data);
        // Save the changes to the database
        $brand->save();

        return response()->json(['message' => 'Brand updated successfully.', 'brand' => $brand], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/brand/{uuid}",
     *     tags={"Brands"},
     *     summary="Delete a brand by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the brand to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Brand deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Brand not found"),
     * )
     */
    public function destroy(string $uuid)
    {
        $brand = Brand::where('uuid', $uuid)->first();
        $brand->delete();
        return response()->json(['message' => 'Brand deleted successfully.'], 200);
    }
}
