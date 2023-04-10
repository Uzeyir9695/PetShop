<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/main/blog",
     *     summary="List main page data",
     *     tags={"MainPage"},
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
     *         description="Returns main page data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="main_page", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="a725cfe3-ec05-4233-a012-1f71cc637af8"),
     *                 @OA\Property(property="title", type="string", example="Welcome to our online store!"),
     *                 @OA\Property(property="slug", type="string", example="welcome"),
     *                 @OA\Property(property="content", type="string", example="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed tempus, magna in pharetra sagittis, dolor nisi placerat eros, et fringilla turpis mi a ipsum. Vivamus malesuada lacus ut felis porttitor, ac tincidunt felis tristique. Praesent mattis arcu velit, nec tincidunt dolor bibendum in. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed vulputate tellus in risus dignissim, eget pretium eros venenatis."),
     *                 @OA\Property(property="metadata", type="object", @OA\AdditionalProperties(
     *                     type="string",
     *                     example="Some metadata"
     *                 ))
     *             ))
     *         )
     *     )
     * )
     */

    public function blogList(Request $request)
    {
        // Set default values for filters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sort_by', 'id');
        $desc = $request->query('desc', false);

        // Build the product query with the filters
        $query = Post::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $blogs = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['blogs' => $blogs], 200);

    }

    /**
     * @OA\Get(
     *     path="/api/v1/main/blog/{uuid}",
     *     tags={"MainPage"},
     *     summary="Get the main page",
     *     security={{ "jwt": {} }},
     *     @OA\Response(
     *         response="200",
     *         description="Returns the main page",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="page", type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="title", type="string", example="Welcome to our online store!"),
     *                 @OA\Property(property="slug", type="string", example="welcome"),
     *                 @OA\Property(property="content", type="string", example="<p>Welcome to our online store! We offer a wide variety of products at great prices.</p>"),
     *                 @OA\Property(property="metadata", type="object", example={"keywords": "online store, shopping, ecommerce"})
     *             )
     *         )
     *     ),
     * )
     */

    public function blogShow($uuid)
    {
        $blog = Post::where('uuid', $uuid)->get();
        return response()->json(['blog' => $blog], 200);

    }

    /**
     * @OA\Get(
     *     path="/api/v1/main/promotions",
     *     summary="List all promotions",
     *     tags={"MainPage"},
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
     *         description="Returns a list of promotions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="promotions", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="a725cfe3-ec05-4233-a012-1f71cc637af8"),
     *                 @OA\Property(property="title", type="string", example="20% off all electronics"),
     *                 @OA\Property(property="content", type="string", example="Use the code ELECTRO20 at checkout to get 20% off all electronics."),
     *                 @OA\Property(property="metadata", type="object", example={"expires_at": "2023-05-01T00:00:00.000Z"})
     *             ))
     *         )
     *     )
     * )
     */

    public function promotions(Request $request)
    {
        // Set default values for filters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sort_by', 'id');
        $desc = $request->query('desc', false);

        // Build the product query with the filters
        $query = Post::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $promotions = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['promotions' => $promotions], 200);
    }
}
