<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentForm;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/payments",
     *     summary="List all payments",
     *     tags={"Payments"},
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
     *         description="Returns a list of payments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="payments", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="8d2e5836-81a6-4e13-8d6f-1a6995e5b723"),
     *                 @OA\Property(property="type", type="string", example="credit_card"),
     *                 @OA\Property(property="details", type="object", example={"card_number": "4111111111111111", "exp_date": "01/2024", "cvv": "123"}),
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
        $query = Payment::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $payments = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['payments' => $payments], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payment",
     *     tags={"Payments"},
     *     summary="Create a new payment",
     *     security={{ "jwt":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="uuid", type="string", example="Payment UUID"),
     *             @OA\Property(property="type", type="string", example="credit_card"),
     *             @OA\Property(property="details", type="object", example={"card_number": "4111111111111111"}),
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Payment created successfully.", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Payment created successfully."))),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function store(PaymentForm $request)
    {
        $data = $request->validated();
        $data['uuid'] = Str::uuid();
        $type = $data['type'];
        if (!in_array($type, ['credit_card', 'cash_on_delivery', 'bank_transfer'])) {
            return response()->json(['message' => 'This type of payment not supported.'], 200);
        }
        Payment::create($data);

        return response()->json(['message' => 'Payment created successfully.'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payment/{uuid}",
     *     tags={"Payments"},
     *     summary="Get a payment by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the payment to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the requested payment",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="payment", type="object", example={"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "type": "credit_card", "amount": 9.99, "currency": "USD"})
     *         )
     *     ),
     *     @OA\Response(response="404", description="Payment not found"),
     * )
     */

    public function show(string $uuid)
    {
        $payment = Payment::where('uuid', $uuid)->get();
        return response()->json(['payment' => $payment], 200);

    }

    /**
     * @OA\Put(
     *     path="/api/v1/payment/{uuid}",
     *     tags={"Payments"},
     *     summary="Update a payment by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the payment to update",
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
     *             @OA\Property(property="type", type="string", example="credit_card"),
     *             @OA\Property(property="amount", type="number", format="float", example=9.99),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Payment updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Payment updated successfully."),
     *             @OA\Property(property="payment", type="object", example={"uuid": "44a7acca-1cee-3704-945c-e7df862cd88e", "type": "credit_card", "amount": 9.99, "currency": "USD"})
     *         )
     *     ),
     *     @OA\Response(response="404", description="Payment not found"),
     * )
     */

    public function update(PaymentForm $request, string $uuid)
    {
        $data = $request->validated();
        $type = $data['type'];
        // check if payment type is valid
        if (!in_array($type, ['credit_card', 'cash_on_delivery', 'bank_transfer'])) {
            return response()->json(['message' => 'This type of payment not supported.'], 200);
        }

        $payment = Payment::where('uuid', $uuid)->first();
        // Fill the user instance with the new data
        $payment->fill($data);
        // Save the changes to the database
        $payment->save();

        return response()->json(['message' => 'Payment updated successfully.', 'payment' => $payment], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/payment/{uuid}",
     *     tags={"Payments"},
     *     summary="Delete a payment by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the payment to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Payment deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Payment not found"),
     * )
     */

    public function destroy(string $uuid)
    {
        $payment = Payment::where('uuid', $uuid)->first();
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully.'], 200);
    }
}
