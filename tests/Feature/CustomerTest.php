<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    private function product(array $overrides = []): Product
    {
        $category = Category::create(['name' => 'Kategori '.uniqid()]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-'.uniqid(),
            'name' => 'Teh Hangat',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $overrides));
    }

    private function order(User $user, Product $product, int $quantity): void
    {
        $this->actingAs($user)->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => $quantity],
            ],
        ]);
    }

    public function test_visitor_can_register_as_customer(): void
    {
        $this->post('/register', [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('users', ['email' => 'pelanggan@example.com', 'role' => 'customer']);
        $this->assertAuthenticated();
    }

    public function test_registration_ignores_role_sent_by_visitor(): void
    {
        $this->post('/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'penyusup@example.com', 'role' => 'customer']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'sama@example.com']);

        $this->post('/register', [
            'name' => 'Pelanggan',
            'email' => 'sama@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertEquals(1, User::count());
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $this->post('/register', [
            'name' => 'Pelanggan',
            'email' => 'baru@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'berbeda123',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);
    }

    public function test_customer_login_goes_to_product_catalog(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('products.index'));
    }

    public function test_customer_cannot_use_store_operations(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get('/dashboard')->assertForbidden();
        $this->actingAs($customer)->get('/purchases')->assertForbidden();
        $this->actingAs($customer)->get('/purchases/create')->assertForbidden();
        $this->actingAs($customer)->post('/purchases', [])->assertForbidden();
        $this->actingAs($customer)->get('/categories')->assertForbidden();
        $this->actingAs($customer)->get('/suppliers')->assertForbidden();
    }

    public function test_customer_can_browse_products(): void
    {
        $this->product(['name' => 'Teh Hangat']);

        $this->actingAs($this->customer())
            ->get('/products')
            ->assertOk()
            ->assertSee('Teh Hangat');
    }

    public function test_customer_order_decreases_stock(): void
    {
        $customer = $this->customer();
        $product = $this->product(['stock' => 10]);

        $this->actingAs($customer)
            ->post('/sales', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect();

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertEquals($customer->id, Sale::first()->user_id);
        $this->assertEquals(6000, Sale::first()->total);
    }

    public function test_customer_order_with_insufficient_stock_is_rejected(): void
    {
        $product = $this->product(['stock' => 2]);

        $this->actingAs($this->customer())
            ->post('/sales', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertEquals(2, $product->fresh()->stock);
        $this->assertEquals(0, Sale::count());
    }

    public function test_customer_sees_only_own_orders(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        $product = $this->product(['stock' => 20]);

        $this->order($first, $product, 1);
        $this->order($second, $product, 1);

        $firstNumber = Sale::where('user_id', $first->id)->value('sale_number');
        $secondNumber = Sale::where('user_id', $second->id)->value('sale_number');

        $this->actingAs($first)
            ->get('/sales')
            ->assertSee($firstNumber)
            ->assertDontSee($secondNumber);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        $product = $this->product(['stock' => 20]);

        $this->order($second, $product, 1);
        $sale = Sale::where('user_id', $second->id)->first();

        $this->actingAs($first)->get("/sales/{$sale->id}")->assertForbidden();
    }

    public function test_customer_cannot_cancel_an_order(): void
    {
        $customer = $this->customer();
        $product = $this->product(['stock' => 10]);

        $this->order($customer, $product, 4);
        $sale = Sale::first();

        $this->actingAs($customer)->post("/sales/{$sale->id}/cancel")->assertForbidden();

        $this->assertEquals(6, $product->fresh()->stock);
        $this->assertEquals('completed', $sale->fresh()->status);
    }
}
