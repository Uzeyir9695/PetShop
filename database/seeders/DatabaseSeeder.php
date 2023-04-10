<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);
        User::factory(50)->create();
        Category::factory()->count(50)->create();
        Brand::factory()->count(50)->create();
        Product::factory()->count(50)->create();
        OrderStatus::factory()->count(50)->create();
        Payment::factory()->count(50)->create();
        Order::factory()->count(50)->create();
        Post::factory()->count(50)->create();
        Promotion::factory()->count(50)->create();
    }
}
