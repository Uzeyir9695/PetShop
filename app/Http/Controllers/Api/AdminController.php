<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserForm;
use App\Models\User;

class AdminController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/admin/user-listing",
     *     tags={"Admin"},
     *     summary="Get list of users",
     *     security={{ "jwt": {} }},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="users", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="first_name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                     @OA\Property(property="created_at", type="string", example="2022-04-01 12:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2022-04-02 12:00:00")
     *                 )
     *             )
     *         )
     *     ),
     * )
     */
    public function userList()
    {
        $users = User::whereNot('is_admin', '1')->get();
        return response()->json(['users' => $users], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/user-edit/{uuid}",
     *     tags={"Admin"},
     *     summary="Update a user",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the user to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data",
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John Doe"),
     *             @OA\Property(property="last_name", type="string", example="Does"),
     *             @OA\Property(property="address", type="string", example="London"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the updated user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User updated successfully."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="first_name", type="string", example="John Doe"),
     *                 @OA\Property(property="address", type="string", example="Georgia"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="403", description="Admin account can not be updated!"),
     *     @OA\Response(response="404", description="User not found."),
     * )
     */
    public function userEdit(UserForm $request, $uuid)
    {
        $data = $request->validated();
        // Check if user exists and is not an admin
        $user = User::where('uuid', $uuid)->first();

        // check if user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if($user->is_admin === 1) {
            return response()->json(['message' => 'Admin account can not be updated!'], 403);
        }
        // Fill the user instance with the new data
        $user->fill($data);
        // Save the changes to the database
        $user->save();

        return response()->json(['message' => 'User updated successfully.', 'user' => $data], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/user-delete/{uuid}",
     *     tags={"Admin"},
     *     summary="Delete a user by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the user to delete",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
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

    public function userDelete($uuid)
    {
        // Check if user exists and is not an admin
        $user = User::where('uuid', $uuid)->first();

        // check if user exists
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if($user->is_admin === 1) {
            return response()->json(['message' => 'Admin account can not be deleted!'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
