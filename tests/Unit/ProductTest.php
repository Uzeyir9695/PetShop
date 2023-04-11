<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    public function getAuthUser()
    {
        $user = User::latest()->first();
        return $user;
    }

    public function productData()
    {
        $jsonData = json_encode([
            'brad' => '67590282-a5ef-32ad-842b-1a3396f1cfb9',
            'image' => '5182400a-dfd5-3feb-947f-c216b0dbdcab',
        ]);

        $data = [
            'category_uuid' => '6da57501-1ccf-56ce-aa23-3f48a90f731c',
            'title' => 'New Product',
            'description' => 'This is a product',
            'price' => 10.34,
            'metadata' => $jsonData
        ];

        return $data;
    }
    // get product for update and delete
    public function getProduct()
    {
        $response = $this->actingAs($this->getAuthUser(), 'jwt')->json('GET', '/api/v1/products');
        $response->assertStatus(200);
        // get first product for example and change title
        $product = $response->json()['products']['data'][0];
        return $product;
    }


    public function testCreateProduct()
    {
        $response =  $this->actingAs($this->getAuthUser(), 'jwt')->json('get', '/api/v1/products', $this->productData());
        // Ensure that the response has a successful status code
        $response->assertStatus(200);
        $response->assertJson(['products' => $response->json()['products']]);
    }

    public function testUpdateProduct()
    {
        $data = $this->getProduct();
        $data['title'] = 'new bb one';
        $update = $this->json('PUT', '/api/v1/product/' . $this->getProduct()['uuid'], $data);
        $update->assertStatus(200);
        $update->assertJson(['message' => 'Product updated successfully.', 'product' => $update->json()['product']]);
    }

    public function testDeleteProduct()
    {
        $delete = $this->json('DELETE', '/api/v1/product/' . $this->getProduct()['uuid']);
        $delete->assertStatus(200);
        $delete->assertJson(['message' => 'Product deleted successfully.']);
    }
}
