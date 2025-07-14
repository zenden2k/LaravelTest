<?php

use App\Enums\OrderStatus;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InsufficientQuantityException;
use App\Exceptions\InvalidOrderStatusException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected ConnectionInterface $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = DB::connection();
        $this->orderService = new OrderService($this->db);
    }

    #[Test]
    public function it_creates_order_successfully()
    {
        $user = User::factory()->create([
            'money' => 100000.0,
            'reserved_money' => 0.0
        ]);

        $category = Category::factory()->create();

        $product1 = Product::factory()->create([
            'price' => 15000.0,
            'quantity' => 10,
            'reserved_quantity' => 0
        ]);

        $product2 = Product::factory()->create([
            'price' => 30000.0,
            'quantity' => 5,
            'reserved_quantity' => 0
        ]);

        $items = [
            ['product_id' => $product1->id, 'quantity' => 2],
            ['product_id' => $product2->id, 'quantity' => 1]
        ];

        $order = $this->orderService->createOrder($user->id, $items);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(OrderStatus::NEW, $order->status);
        $this->assertEquals(60000.0, $order->total_amount);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(2, $product1->reserved_quantity);
        $this->assertEquals(1, $product2->reserved_quantity);

        $user->refresh();
        $this->assertEquals(60000.0, $user->reserved_money);

        $this->assertCount(2, $order->products);
        $this->assertEquals(2, $order->products()->where('product_id', $product1->id)->first()->pivot->quantity);
        $this->assertEquals(1, $order->products()->where('product_id', $product2->id)->first()->pivot->quantity);
    }

    #[Test]
    public function it_throws_exception_when_product_not_found()
    {
        $user = User::factory()->create([
            'money' => 123456.7,
            'reserved_money' => 0.0
        ]);

        $items = [
            ['product_id' => 999, 'quantity' => 1]
        ];

        $this->expectException(ProductNotFoundException::class);

        $this->orderService->createOrder($user->id, $items);
    }

    #[Test]
    public function it_throws_exception_when_insufficient_product_quantity()
    {
        $user = User::factory()->create([
            'money' => 100000.0,
            'reserved_money' => 0.0
        ]);

        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'price' => 15000.0,
            'quantity' => 5,
            'reserved_quantity' => 3
        ]);

        $items = [
            ['product_id' => $product->id, 'quantity' => 3]
        ];

        $this->expectException(InsufficientQuantityException::class);

        $this->orderService->createOrder($user->id, $items);
    }

    #[Test]
    public function it_throws_exception_when_insufficient_user_funds()
    {
        $user = User::factory()->create([
            'money' => 100.0,
            'reserved_money' => 50.0
        ]);

        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'price' => 100.0,
            'quantity' => 10,
            'reserved_quantity' => 0
        ]);

        $items = [
            ['product_id' => $product->id, 'quantity' => 1]
        ];

        $this->expectException(InsufficientFundsException::class);

        $this->orderService->createOrder($user->id, $items);
    }

    #[Test]
    public function it_approves_order_successfully()
    {
        $user = User::factory()->create([
            'money' => 1000.0,
            'reserved_money' => 400.0
        ]);

        $category = Category::factory()->create();

        $product1 = Product::factory()->create([
            'price' => 100.0,
            'quantity' => 10,
            'reserved_quantity' => 3
        ]);

        $product2 = Product::factory()->create([
            'price' => 50.0,
            'quantity' => 5,
            'reserved_quantity' => 3
        ]);

        $order = Order::factory()->withProducts([
            [
                'product_id' => $product1->id,
                'quantity' => 3
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 2
            ],
        ])->create([
            'user_id' => $user->id,
            'status' => OrderStatus::NEW
        ]);

        $result = $this->orderService->approveOrder($order->id);

        $this->assertEquals(OrderStatus::APPROVED, $result->status);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(7, $product1->quantity);
        $this->assertEquals(0, $product1->reserved_quantity);

        $this->assertEquals(3, $product2->quantity);
        $this->assertEquals(1, $product2->reserved_quantity);

        $user->refresh();
        $this->assertEquals(600.0, $user->money); // 1000 - 3 * 100 - 2 * 50
        $this->assertEquals(0.0, $user->reserved_money);
    }

    #[Test]
    public function it_throws_exception_when_approving_order_with_insufficient_quantity()
    {
        $user = User::factory()->create([
            'money' => 1000.0,
            'reserved_money' => 400.0
        ]);

        $category = Category::factory()->create();

        $product1 = Product::factory()->create([
            'price' => 100.0,
            'quantity' => 10,
            'reserved_quantity' => 1
        ]);

        $product2 = Product::factory()->create([
            'price' => 50.0,
            'quantity' => 5,
            'reserved_quantity' => 2
        ]);

        $order = Order::factory()->withProducts([
            [
                'product_id' => $product1->id,
                'quantity' => 3
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 2
            ],
        ])->create([
            'user_id' => $user->id,
            'status' => OrderStatus::NEW
        ]);

        $this->expectException(InsufficientQuantityException::class);

        $this->orderService->approveOrder($order->id);
    }

    #[Test]
    public function it_throws_exception_when_approving_non_existent_order()
    {
        $this->expectException(EntityNotFoundException::class);

        $this->orderService->approveOrder(3453453);
    }

    #[Test]
    public function it_throws_exception_when_invalid_status()
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::APPROVED
        ]);

        $this->expectException(InvalidOrderStatusException::class);

        $this->orderService->approveOrder($order->id);
    }

    #[Test]
    public function it_throws_exception_when_invalid_status_2()
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::CANCELLED
        ]);

        $this->expectException(InvalidOrderStatusException::class);

        $this->orderService->approveOrder($order->id);
    }

    #[Test]
    public function it_throws_exception_when_approving_insufficient_reserved_funds()
    {
        $user = User::factory()->create([
            'money' => 1000.0,
            'reserved_money' => 50.0
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::NEW,
            'total_amount' => 100.0
        ]);

        $this->expectException(InsufficientFundsException::class);

        $this->orderService->approveOrder($order->id);
    }

    #[Test]
    public function it_throws_exception_when_approving_insufficient_user_funds()
    {
        $user = User::factory()->create([
            'money' => 100.0,
            'reserved_money' => 500.0
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::NEW,
            'total_amount' => 200.0
        ]);

        $this->expectException(InsufficientFundsException::class);

        $this->orderService->approveOrder($order->id);
    }
}
