<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserForm;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     tags={"Users"},
     *     summary="Get authenticated user's details",
     *     security={{ "jwt": {} }},
     *     @OA\Response(
     *         response="200",
     *         description="User details fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object", example={"id": 1, "name": "John Doe", "email": "johndoe@example.com", "created_at": "2022-04-07T08:22:36.000000Z", "updated_at": "2022-04-07T08:22:36.000000Z"})
     *         )
     *     )
     * )
     */
    public function get()
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/orders",
     *     tags={"Users"},
     *     summary="Retrieve a list of orders for the current authenticated user",
     *     security={{ "jwt": {} }},
     *     @OA\Response(
     *         response="200",
     *         description="List of orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="orders", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="product_id", type="integer", example=3),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-04-07 12:34:56"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-04-07 12:34:56")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated user"),
     * )
     */

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->get();
        return response()->json(['orders' => $orders], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user/edit",
     *     tags={"Users"},
     *     summary="Edit the currently authenticated user",
     *     security={{ "jwt": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body containing the user data to be updated",
     *         @OA\JsonContent(
     *             required={"uuid"},
     *             @OA\Property(property="uuid", type="string", format="uuid", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="avatar", type="string", example="http://example.com/image.jpg"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="phone_number", type="string", example="555-555-1212")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User updated successfully."),
     *         )
     *     ),
     *     @OA\Response(response="400", description="Invalid request data"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="User not found")
     * )
     */

    public function edit(UserForm $request)
    {
        $data = $request->validated();
        $user = authUser(request()->bearerToken());  // get auth user using helper function from token
        $user = User::where('uuid', $user->uuid)->first();
        // check if user is not an admin and is not other than current
        if (auth()->user()->is_admin !== 1 && auth()->user()->id !== $user->id) {
            return response()->json(['message' => 'You are not authorized to delete this user.'], 403);
        }
        $user->fill($data); // Fill the user instance with the new data

        $user->save(); // Save the changes to the database

        return response()->json(['message' => 'User updated successfully.', 'user' => $data], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/user/delete",
     *     tags={"Users"},
     *     summary="Delete a user by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Response(
     *         response="200",
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="403", description="Admin account can not be deleted"),
     * )
     */
    public function delete()
    {
        $user = authUser(request()->bearerToken());// get auth user using helper function from token
        $user = User::where('uuid', $user->uuid)->first();// Check if user exists and is not an admin
        // check if user is not an admin and is not other than current
        if (auth()->user()->is_admin !== 1 && auth()->user()->id !== $user->id) {
            return response()->json(['message' => 'You are not authorized to delete this user.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
