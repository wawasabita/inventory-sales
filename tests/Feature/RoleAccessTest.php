<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function product(array $overrides = []): Product
    {
        $category = Category::create(['name' => 'Kategori '.uniqid()]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-'.uniqid(),
            'name' => 'Produk Uji',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $overrides));
    }

    /** Pelanggan membuat satu pesanan berisi dua produk, lalu mengembalikannya. */
    private function customerOrder(User $customer, Product $a, Product $b): Sale
    {
        $this->actingAs($customer)
            ->withSession(['cart' => [$a->id => 2, $b->id => 3]])
            ->post('/shop/checkout')
            ->assertRedirect();

        return Sale::firstOrFail();
    }

    public function test_customer_cannot_open_store_pages(): void
    {
        $customer = $this->userWithRole('customer');

        foreach (['/dashboard', '/products', '/sales', '/purchases', '/categories', '/suppliers'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }
    }

    public function test_store_staff_cannot_open_the_shop(): void
    {
        $this->actingAs($this->userWithRole('staff'))->get('/shop')->assertForbidden();
    }

    public function test_forbidden_page_shows_a_friendly_message(): void
    {
        $this->actingAs($this->userWithRole('staff'))
            ->get('/categories')
            ->assertForbidden()
            ->assertSee('tidak punya izin');
    }

    public function test_customer_lands_in_the_shop_after_login(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'password' => Hash::make('rahasia123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('shop.index'));
    }

    public function test_owner_admin_and_staff_can_open_the_dashboard(): void
    {
        foreach (['owner', 'admin', 'staff'] as $role) {
            $this->actingAs($this->userWithRole($role))->get('/dashboard')->assertOk();
        }
    }

    public function test_register_always_creates_a_customer(): void
    {
        $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'owner',
        ])->assertRedirect(route('shop.index'));

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role' => 'customer']);
    }

    public function test_register_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertEquals(1, User::where('email', 'budi@example.com')->count());
    }

    public function test_customer_can_add_to_cart(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->userWithRole('customer'))
            ->post("/shop/cart/add/{$product->id}", ['quantity' => 2])
            ->assertSessionHas('cart', [$product->id => 2]);
    }

    public function test_cart_rejects_more_than_the_available_stock(): void
    {
        $product = $this->product(['stock' => 2]);

        $this->actingAs($this->userWithRole('customer'))
            ->post("/shop/cart/add/{$product->id}", ['quantity' => 5])
            ->assertSessionHas('error')
            ->assertSessionMissing('cart');
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $product = $this->product(['stock' => 0]);

        $this->actingAs($this->userWithRole('customer'))
            ->post("/shop/cart/add/{$product->id}", ['quantity' => 1])
            ->assertSessionHas('error')
            ->assertSessionMissing('cart');
    }

    public function test_customer_can_buy_several_products_at_once(): void
    {
        $customer = $this->userWithRole('customer');
        $a = $this->product(['stock' => 10]);
        $b = $this->product(['stock' => 5]);

        $sale = $this->customerOrder($customer, $a, $b);

        $this->assertEquals(8, $a->fresh()->stock);
        $this->assertEquals(2, $b->fresh()->stock);
        $this->assertEquals(2, $sale->items()->count());
        $this->assertEquals($customer->id, $sale->user_id);
        $this->assertEquals(10000, $sale->total);
    }

    public function test_checkout_is_rejected_when_stock_changed_after_adding_to_cart(): void
    {
        $product = $this->product(['stock' => 1]);

        $this->actingAs($this->userWithRole('customer'))
            ->withSession(['cart' => [$product->id => 3]])
            ->post('/shop/checkout')
            ->assertRedirect(route('shop.cart'))
            ->assertSessionHas('error');

        $this->assertEquals(1, $product->fresh()->stock);
        $this->assertEquals(0, Sale::count());
    }

    public function test_only_the_owner_can_cancel_and_stock_returns(): void
    {
        $customer = $this->userWithRole('customer');
        $a = $this->product(['stock' => 10]);
        $b = $this->product(['stock' => 5]);
        $sale = $this->customerOrder($customer, $a, $b);

        foreach (['customer', 'staff', 'admin'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->post("/sales/{$sale->id}/cancel")
                ->assertForbidden();
        }

        $this->assertEquals('completed', $sale->fresh()->status);
        $this->assertEquals(8, $a->fresh()->stock);

        $this->actingAs($this->userWithRole('owner'))
            ->post("/sales/{$sale->id}/cancel")
            ->assertRedirect();

        $this->assertEquals('cancelled', $sale->fresh()->status);
        $this->assertEquals(10, $a->fresh()->stock);
        $this->assertEquals(5, $b->fresh()->stock);
    }

    public function test_customer_cannot_see_another_customers_order(): void
    {
        $sale = $this->customerOrder(
            $this->userWithRole('customer'),
            $this->product(['stock' => 10]),
            $this->product(['stock' => 10]),
        );

        $this->actingAs($this->userWithRole('customer'))
            ->get("/shop/orders/{$sale->id}")
            ->assertForbidden();
    }
}
