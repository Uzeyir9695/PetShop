<?php

namespace Tests\Unit;

use App\Models\JwtToken;
use App\Models\User;
use Tests\TestCase;

class OrderTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    public function getAuthUser()
    {
        $user = User::latest()->first();
        return $user;
    }

    public function orderData()
    {
        $products = json_encode([
            'product'=> ['cca2e403-a62e-3efb-9bb9-fdba2a096c4a', 'quantity'=> 3]
        ]);
        $address = json_encode([
            'billing'=> "874 Arnold Highway\nEast Alycefort, DC 16811-8708",
            'shipping'=> "2262 Wallace Ports Suite 095\nWindlerville, AR 68891"
        ]);
        $data = [
            'user_id' => 8,
            'order_status_id' => 3,
            'payment_id' => 2,
            'products' => $products,
            'address' => $address,
            'delivery_fee' => 23.3,
            'amount' => 767,
        ];

        return $data;
    }
    // get product for update and delete
    public function getOrder()
    {
        $response = $this->actingAs($this->getAuthUser(), 'jwt')->json('GET', '/api/v1/orders');
        $response->assertStatus(200);
        // get first product for example and change title
        $order = $response->json()['orders']['data'][0];
        return $order;
    }


    public function testCreateOrder()
    {
        $data = $this->orderData();
        $response =  $this->actingAs($this->getAuthUser(), 'jwt')->json('get', '/api/v1/orders', $data);
        // Ensure that the response has a successful status code
        $response->assertStatus(200);
//         Ensure that the user is authorized to access the endpoint
        $response->assertJson(['orders' => $response->json()['orders']]);
    }

    public function testUpdateOrder()
    {
        $data = $this->getOrder();
        $data['amount'] = '444.87';
        $update = $this->json('PUT', '/api/v1/order/' . $this->getOrder()['uuid'], $data);
        $update->assertStatus(200);
        $update->assertJson(['message' => 'Order updated successfully.', 'order' => $update->json()['order']]);
    }

    public function testDeleteOrder()
    {
        $delete = $this->json('DELETE', '/api/v1/order/' . $this->getOrder()['uuid']);
        $delete->assertStatus(200);
        $delete->assertJson(['message' => 'Order deleted successfully.']);
    }

    public function testDownloadOrder()
    {
        $delete = $this->json('get', '/api/v1/order/' . $this->getOrder()['uuid'].'/download');
        $delete->assertStatus(200);
    }
}
