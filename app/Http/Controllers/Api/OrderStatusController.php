<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusForm;
use App\Models\OrderStatus;
use Illuminate\Support\Str;

class OrderStatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/order-statuses",
     *     summary="List all order statuses",
     *     tags={"Order Statuses"},
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
     *         description="Returns a list of order statuses",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="order_statuses", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="a725cfe3-ec05-4233-a012-1f71cc637af8"),
     *                 @OA\Property(property="title", type="string", example="Processing")
     *             ))
     *         )
     *     )
     * )
     */

    public function index(\Illuminate\Support\Facades\Request $request)
    {
        // Set default values for filters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sort_by', 'id');
        $desc = $request->query('desc', false);

        // Build the product query with the filters
        $query = OrderStatus::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $orderStatuses = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['orderStatuses' => $orderStatuses], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/order-status",
     *     tags={"Order Statuses"},
     *     summary="Create a new order status",
     *     security={{ "jwt":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="uuid", type="string", example="Order Status UUID"),
     *             @OA\Property(property="title", type="string", example="Processing"),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Order status created successfully.", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Order status created successfully."))),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function store(OrderStatusForm $request)
    {
        $data = $request->validated();
        $data['uuid'] = Str::uuid();
        if (!in_array($data['title'], ['open', 'pending', 'payment', 'paid', 'shipped', 'cancelled'])) {
            return response()->json(['message' => 'Unsupported order status.'], 200);
        }

        OrderStatus::create($data);

        return response()->json(['message' => 'OrderStatus created successfully.'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/order-status/{uuid}",
     *     tags={"Order Statuses"},
     *     summary="Get an order status by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order status to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the requested order status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="order_status", type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="title", type="string", example="Shipped")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", description="Order status not found"),
     * )
     */

    public function show(string $uuid)
    {
        $orderStatus = OrderStatus::where('uuid', $uuid)->get();
        return response()->json(['orderStatus' => $orderStatus], 200);

    }

    /**
     * @OA\Put(
     *     path="/api/v1/order-status/{uuid}",
     *     tags={"Order Statuses"},
     *     summary="Update an order status by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order status to update",
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
     *             @OA\Property(property="title", type="string", example="Delivered")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order status updated successfully."),
     *             @OA\Property(property="order_status", type="object", example={"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "title": "Delivered"})
     *         )
     *     ),
     *     @OA\Response(response="404", description="Order status not found"),
     * )
     */

    public function update(OrderStatusForm $request, string $uuid)
    {
        $data = $request->validated();
        if (!in_array($data['title'], ['open', 'pending', 'payment', 'paid', 'shipped', 'cancelled'])) {
            return response()->json(['message' => 'Unsupported order status.'], 200);
        }
        $orderStatus = OrderStatus::where('uuid', $uuid)->first();
        // Fill the user instance with the new data
        $orderStatus->fill($data);
        // Save the changes to the database
        $orderStatus->save();

        return response()->json(['message' => 'OrderStatus updated successfully.', 'orderStatus' => $orderStatus], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/order-status/{uuid}",
     *     tags={"Order Statuses"},
     *     summary="Delete an order status by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order status to delete",
     *         @OA\Schema(
     *             type="string",
     *             example="1a2194a7-9eb2-322b-aa81-e13177f5714b"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Order status deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order status deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Order status not found"),
     * )
     */

    public function destroy($uuid)
    {
        $orderStatus = OrderStatus::where('uuid', $uuid)->first();
        $orderStatus->delete();
        return response()->json(['message' => 'OrderStatus deleted successfully.'], 200);
    }
}
