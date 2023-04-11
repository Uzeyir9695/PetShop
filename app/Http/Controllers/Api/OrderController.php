<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderForm;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="List all orders",
     *     tags={"Orders"},
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
     *         description="Returns a list of orders",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="orders", type="array", @OA\Items(
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="order_status_id", type="integer", example=1),
     *                 @OA\Property(property="payment_id", type="integer", example=1),
     *                 @OA\Property(property="uuid", type="string", example="066bc390-cb0f-11eb-b8bc-0242ac130003"),
     *                 @OA\Property(property="products", type="array", @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="price", type="number", format="float", example=20.50)
     *                 )),
     *                 @OA\Property(property="address", type="object",
     *                     @OA\Property(property="street", type="string", example="123 Main St"),
     *                     @OA\Property(property="city", type="string", example="New York"),
     *                     @OA\Property(property="state", type="string", example="NY"),
     *                     @OA\Property(property="zip_code", type="string", example="10001")
     *                 ),
     *                 @OA\Property(property="delivery_fee", type="number", format="float", example=5.50),
     *                 @OA\Property(property="amount", type="number", format="float", example=46.0),
     *                 @OA\Property(property="shipped_at", type="string", format="date-time", example="2022-04-01T10:00:00.000Z")
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
        $query = Order::query();
        $query->orderBy($sortBy, $desc ? 'desc' : 'asc');

        // Get the paginated results
        $orders = $query->paginate($limit, ['*'], 'page', $page);
        return response()->json(['orders' => $orders], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/order",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     security={{ "jwt":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="order_status_id", type="integer", example="1"),
     *             @OA\Property(property="payment_id", type="integer", example="1"),
     *             @OA\Property(property="uuid", type="string", example="Order UUID"),
     *             @OA\Property(property="products", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="Product UUID"),
     *                 @OA\Property(property="quantity", type="integer", example="2")
     *             )),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="delivery_fee", type="number", example="5.99"),
     *             @OA\Property(property="amount", type="number", example="29.99"),
     *             @OA\Property(property="shipped_at", type="string", format="date-time", example="2023-04-07T10:00:00Z")
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Order created successfully.", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Order created successfully."))),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */

    public function store(OrderForm $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;
        $data['order_status_id'] = $request->order_status_id;
        $data['payment_id'] = $request->payment_id;
        $data['uuid'] = Str::uuid();
        $orderAmount = $data['amount'];
        $data['delivery_fee'] = $orderAmount > 500 ? 0 : $orderAmount * 0.15;

        Order::create($data);

        return response()->json(['message' => 'Order created successfully.'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/order/{uuid}",
     *     tags={"Orders"},
     *     summary="Get an order by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns the requested order",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="user_id", type="integer", example="1"),
     *                 @OA\Property(property="order_status_id", type="integer", example="2"),
     *                 @OA\Property(property="payment_id", type="integer", example="3"),
     *                 @OA\Property(property="products", type="array", @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example="1"),
     *                     @OA\Property(property="quantity", type="integer", example="2"),
     *                 )),
     *                 @OA\Property(property="address", type="string", example="123 Main St."),
     *                 @OA\Property(property="delivery_fee", type="number", example="5.00"),
     *                 @OA\Property(property="amount", type="number", example="29.99"),
     *                 @OA\Property(property="shipped_at", type="string", format="date-time", example="2023-04-07T12:34:56Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", description="Order not found"),
     * )
     */
    public function show(string $uuid)
    {
        $order = Order::where('uuid', $uuid)->get();
        return response()->json(['order' => $order], 200);

    }


    /**
     * @OA\Put(
     *     path="/api/v1/order/{uuid}",
     *     tags={"Orders"},
     *     summary="Update an order by UUID",
     *     security={{ "jwt":{} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order to update",
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
     *             @OA\Property(property="order_status_id", type="integer", example=1),
     *             @OA\Property(property="payment_id", type="integer", example=1),
     *             @OA\Property(property="uuid", type="string", example="Order UUID"),
     *             @OA\Property(property="products", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", example="Product UUID"),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             )),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="delivery_fee", type="number", example=5.99),
     *             @OA\Property(property="amount", type="number", example=29.99),
     *             @OA\Property(property="shipped_at", type="string", format="date-time", example="2023-04-07T10:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response="422", description="Validation error.")
     * )
     */
    public function update(OrderForm $request, string $uuid)
    {
        $data = $request->validated();
        $order = Order::where('uuid', $uuid)->first();
        // Fill the user instance with the new data
        $order->fill($data);
        // Save the changes to the database
        $order->save();

        return response()->json(['message' => 'Order updated successfully.', 'order' => $order], 200);
    }

    /**
     * @OA\Delete(
     * path="/api/v1/order/{uuid}",
     * tags={"Orders"},
     * summary="Delete an order by UUID",
     * security={{ "jwt": {} }},
     * @OA\Parameter(
     * name="uuid",
     * in="path",
     * required=true,
     * description="UUID of the order to delete",
     * @OA\Schema(
     * type="string",
     * format="uuid",
     * example="44a7acca-1cee-3704-945c-e7df862cd88e"
     * )
     * ),
     * @OA\Response(
     * response="200",
     * description="Order deleted successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="message", type="string", example="Order deleted successfully.")
     * )
     * ),
     * @OA\Response(response="404", description="Order not found"),
     * )
     */
    public function destroy($uuid)
    {
        $order = Order::where('uuid', $uuid)->first();
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully.'], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/orders/dashboard",
     *     tags={"Orders"},
     *     summary="Retrieve orders for a customer within a date range",
     *     security={{ "jwt": {} }},
     *      @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of records per page",
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         description="Field to sort the records by",
     *         @OA\Schema(
     *             type="string",
     *             example="order_date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         required=false,
     *         description="Sort records in descending order",
     *         @OA\Schema(
     *             type="boolean",
     *             example=true
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="customerUuid",
     *         in="query",
     *         required=true,
     *         description="UUID of the customer",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dateRange",
     *         in="query",
     *         required=true,
     *         description="Date range for the orders in ISO 8601 format",
     *         @OA\Schema(
     *             type="string",
     *             format="date-range",
     *             example="2023-04-01T00:00:00Z/2023-04-30T23:59:59Z"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderUuid",
     *         in="query",
     *         required=true,
     *         description="UUID of the order",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Orders for the customer within the given date range",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="order_uuid", type="string", example="44a7acca-1cee-3704-945c-e7df862cd88e"),
     *                 @OA\Property(property="order_date", type="string", format="date-time", example="2023-04-05T13:23:45Z"),
     *                 @OA\Property(property="total_amount", type="number", format="float", example="125.55"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="404", description="No orders found for the customer within the given date range"),
     * )
     */

    public function orderDashboard(Request $request)
    {
        $orders = Order::with(['user' => function ($query) {
            $query->select('id', 'first_name', 'last_name');
        }, 'orderStatus' => function ($query) {
            $query->select('id', 'title');
        }]);

        $this->filter($orders);

        $orders = $orders->select('user_id', 'order_status_id', 'uuid', 'amount', 'products');

        if ($orders->count() === 0) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        // Set default values for filters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sort_by', 'id');
        $desc = $request->query('desc', false);
        $orders = $orders->orderBy($sortBy, $desc ? 'desc' : 'asc');
//        // Get the paginated results
        $orders = $orders->paginate($limit, ['*'], 'page', $page);
        return response()->json(['orders' => $orders], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/orders/shipment-locator",
     *     tags={"Orders"},
     *     summary="Retrieve shipment locator for a customer's order within a date range",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="customerUuid",
     *         in="query",
     *         required=true,
     *         description="UUID of the customer",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dateRange",
     *         in="query",
     *         required=true,
     *         description="Date range for the shipment in ISO 8601 format",
     *         @OA\Schema(
     *             type="string",
     *             format="date-range",
     *             example="2023-04-01T00:00:00Z/2023-04-30T23:59:59Z"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderUuid",
     *         in="query",
     *         required=true,
     *         description="UUID of the order",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Shipment locator for the customer's order within the given date range",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="shipment_locator", type="string", example="ABC123")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Order not found"),
     * )
     */

    public function shipmentLocator()
    {
        $orders = Order::with(['user' => function ($query) {
            $query->select('id', 'uuid');
        }]);

        if(request()->has('orderUuid')) {
            $orders->where('uuid', request('orderUuid'));
        }

        else if (request()->has('customerUuid')) {
            $orders->whereHas('user', function ($query) {
                $query->where('uuid', 'like', request('customerUuid'));
            });
        }

        $orders = $orders->select('id', 'user_id','uuid', 'amount', 'shipped_at')->paginate(5);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // get categories' name by extracting products column from orders table
        $orderProducts = DB::table('orders')
            ->select('id', DB::raw("json_extract(products, '$[*].product') as product_uuids"))
            ->get();

        $productUuids = [];
        foreach ($orderProducts as $orderProduct) {
            $productUuids = array_merge($productUuids, json_decode($orderProduct->product_uuids, true));
        }

        $products = Product::whereIn('uuid', $productUuids)->get();

        $orderProductMap = [];
        foreach ($orderProducts as $orderProduct) {
            $orderProductMap[$orderProduct->id] = [];
            $productUuids = json_decode($orderProduct->product_uuids, true);
            foreach ($productUuids as $productUuid) {
                $product = $products->firstWhere('uuid', $productUuid);
                if ($product) {
                    $orderProductMap[$orderProduct->id][] = $product;
                }
            }
        }

        foreach ($orders as $order) {
            $orderProducts = $orderProductMap[$order->id];
            $productCategories = [];
            foreach ($orderProducts as $product) {
                $productCategories[] = $product->category->title;
            }
            $order->categories = $productCategories;
        }

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/order/{uuid}/download",
     *     tags={"Orders"},
     *     summary="Download an order by UUID",
     *     security={{ "jwt": {} }},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order to download",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="44a7acca-1cee-3704-945c-e7df862cd88e"
     *         )
     *     ),
     *     @OA\Response(response="200", description="Order downloaded successfully"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Order not found"),
     *     @OA\Response(response="500", description="Something went wrong")
     * )
     */

    public function orderDownload(Request $request, $uuid)
    {
        // Get the order details from the database
        $order = Order::with(['user', 'payment'])->where('uuid', $uuid)->first();
        if(!$order) {
            return response()->json(['message' => 'Order with indicated uuid not found! Make sure you have correct uuid format.'], 200);
        }
        $created_at = $order->created_at;
        $invoice_number = 'INV-' . Carbon::now()->format('Ymd-His');
        $customer_details = [
            'first_name' => $order->user->first_name,
            'last_name' => $order->user->last_name,
            'email' => $order->user->email,
            'phone_number' => $order->user->phone_number,
            'address' => $order->user->address,
            'created_at' => $created_at,
        ];
        // Decode the address JSON string into a PHP array
        $address = json_decode($order->address, true);
        $pay_details = json_decode($order->payment->details, true);
        $payment_details = [
          'billing' => $address['billing'],
          'shipping' => $address['billing'],
          'details' => $pay_details,
        ];

        $products = json_decode($order->products, true);
        $totalQuantity = collect($products)->sum('quantity');

        $total = $order->amount;
        $delivery_fee = $order->delivery_fee;
        if($delivery_fee === 0) {
            $subtotal = $total;
        } else {
            $subtotal = $total - $delivery_fee;
        }

        // Create the PDF content
        $html = '<h1>Invoice #' . $invoice_number . '</h1>';
        $html .= '<h2>Customer Details:</h2>';
        $html .= '<p>First Name: ' . $customer_details['first_name'] . '</p>';
        $html .= '<p>Last Name: ' . $customer_details['last_name'] . '</p>';
        $html .= '<p>Email: ' . $customer_details['email'] . '</p>';
        $html .= '<p>Phone Number: ' . $customer_details['phone_number'] . '</p>';
        $html .= '<p>Address: ' . $customer_details['address'] . '</p>';
        $html .= '<h2>Payment Details:</h2>';
        $html .= '<p>Billing Address: ' . $payment_details['billing'] . '</p>';
        $html .= '<p>Shipping Address: ' . $payment_details['shipping'] . '</p>';
        $html .= '<h2>Order Details:</h2>';
        $html .= '<p>Total Quantity: ' . $totalQuantity . '</p>';
        $html .= '<p>Subtotal: $' . number_format($subtotal, 2) . '</p>';
        $html .= '<p>Delivery Fee: $' . number_format($delivery_fee, 2) . '</p>';
        $html .= '<p>Total: $' . number_format($total, 2) . '</p>';
        $pdf = PDF::loadHTML($html);

        // Set the PDF filename
        $filename = 'order_' . $order->uuid . '.pdf';

        // Download the PDF
        return $pdf->download($filename)->header('Content-Type', 'application/pdf');
    }

    public function filter($orders)
    {
        if(request()->has('dateRange')) {
            $dates = explode(' - ', request()->get('dateRange'));
            $startDate = Carbon::parse($dates[0]);
            $endDate = Carbon::parse($dates[1]);
            $orders->whereBetween('created_at', [$startDate, $endDate]);
        }
        else if(request()->has('today')) {
            $orders->whereDate('created_at', Carbon::today());
        }
        else if(request()->has('monthly')) {
            $orders->whereMonth('created_at', Carbon::now()->month);
        }
        else if(request()->has('yearly')) {
            $orders->whereYear('created_at', Carbon::now()->year);
        }
    }
}
