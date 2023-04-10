<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/file/upload",
     *     tags={"File"},
     *     summary="Upload a file",
     *     security={{ "jwt": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="The file to upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     description="The file to upload",
     *                     type="file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the uploaded file details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="File uploaded successfully."),
     *             @OA\Property(property="file", type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="filename", type="string", example="example.pdf"),
     *                 @OA\Property(property="path", type="string", example="uploads/example.pdf"),
     *                 @OA\Property(property="size", type="integer", example=1024),
     *                 @OA\Property(property="mime_type", type="string", example="application/pdf")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="422", description="Invalid input"),
     *     @OA\Response(response="500", description="Error uploading file")
     * )
     */

    public function fileUpload(Request $request)
    {
        if($request->hasFile('file')) {
            $validFile = request()->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]);
        }
        // Generate a UUID for the file
        $fileUuid = Str::uuid();

        // Get the uploaded file details
        $file = $validFile['file'];
        $fileData = [
            'uuid' => $fileUuid,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $file->getClientMimeType()
        ];

        $directory = 'pet-shop';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
        // Save the file to the 'petShop' folder under the Laravel storage path
        $filePath = Storage::putFileAs('pet-shop', $file, $fileUuid . '.' . $file->getClientOriginalExtension());
        $fileData['path'] = $filePath;

        File::create($fileData);

        return response()->json(['message' => 'File uploaded successfully.', 'uuid' => $fileUuid], 201);
    }


    /**
     * @OA\Get(
     * path="/api/v1/file/{uuid}",
     * tags={"File"},
     * summary="Delete a file by UUID",
     * security={{ "jwt": {} }},
     * @OA\Parameter(
     * name="uuid",
     * in="path",
     * required=true,
     * description="UUID of the file to delete",
     * @OA\Schema(
     * type="string",
     * format="uuid",
     * example="44a7acca-1cee-3704-945c-e7df862cd88e"
     * )
     * ),
     * @OA\Response(
     * response="200",
     * description="File deleted successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="message", type="string", example="File deleted successfully.")
     * )
     * ),
     * @OA\Response(response="404", description="File not found"),
     * )
     */
    public function getFile($uuid)
    {
        File::where('uuid', $uuid)->delete();
        return response()->json(['message' => 'File deleted successfully.'], 200);
    }
}
