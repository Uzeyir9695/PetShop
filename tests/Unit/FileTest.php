<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function getAuthUser()
    {
        $user = User::latest()->first();
        return $user;
    }

    public function testUploadImage()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->getAuthUser(), 'jwt')->post('/api/v1/file/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->getData());
    }
}
