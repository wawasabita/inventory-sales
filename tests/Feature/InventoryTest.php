<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => 'staff']);
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

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('rahasia123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correct_login_opens_dashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('rahasia123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $existing = $this->product(['sku' => 'DUP-001']);

        $this->actingAs($this->admin())
            ->post('/products', [
                'sku' => 'DUP-001',
                'name' => 'Produk Kembar',
                'category_id' => $existing->category_id,
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'minimum_stock' => 1,
            ])
            ->assertSessionHasErrors('sku');

        $this->assertEquals(1, Product::count());
    }

    public function test_negative_price_is_rejected(): void
    {
        $category = Category::create(['name' => 'Minuman']);

        $this->actingAs($this->admin())
            ->post('/products', [
                'sku' => 'NEG-001',
                'name' => 'Harga Negatif',
                'category_id' => $category->id,
                'purchase_price' => -100,
                'selling_price' => 2000,
                'minimum_stock' => 1,
            ])
            ->assertSessionHasErrors('purchase_price');

        $this->assertDatabaseMissing('products', ['sku' => 'NEG-001']);
    }

    public function test_purchase_increases_stock(): void
    {
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->staff())
            ->post('/purchases', [
                'supplier_id' => $supplier->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000],
                ],
            ])
            ->assertRedirect();

        $this->assertEquals(15, $product->fresh()->stock);
        $this->assertEquals(5000, Purchase::first()->total);
    }

    public function test_sale_decreases_stock(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->staff())
            ->post('/sales', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect();

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertEquals(6000, Sale::first()->total);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $product = $this->product(['stock' => 2]);

        $this->actingAs($this->staff())
            ->post('/sales', [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertEquals(2, $product->fresh()->stock);
        $this->assertEquals(0, Sale::count());
    }

    public function test_failed_item_rolls_back_the_whole_sale(): void
    {
        $enough = $this->product(['stock' => 10]);
        $short = $this->product(['stock' => 1]);

        $this->actingAs($this->staff())
            ->post('/sales', [
                'items' => [
                    ['product_id' => $enough->id, 'quantity' => 3],
                    ['product_id' => $short->id, 'quantity' => 5],
                ],
            ])
            ->assertSessionHasErrors('items');

        $this->assertEquals(10, $enough->fresh()->stock);
        $this->assertEquals(1, $short->fresh()->stock);
        $this->assertEquals(0, Sale::count());
        $this->assertEquals(0, SaleItem::count());
    }

    public function test_staff_cannot_use_admin_functions(): void
    {
        $category = Category::create(['name' => 'Minuman']);
        $staff = $this->staff();

        $this->actingAs($staff)->get('/categories')->assertForbidden();
        $this->actingAs($staff)->get('/suppliers')->assertForbidden();

        $this->actingAs($staff)
            ->post('/products', [
                'sku' => 'STF-001',
                'name' => 'Dibuat Staff',
                'category_id' => $category->id,
                'purchase_price' => 1000,
                'selling_price' => 2000,
                'minimum_stock' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'STF-001']);
    }

    public function test_product_search_filters_by_name(): void
    {
        $this->product(['name' => 'Kopi Hitam']);
        $this->product(['name' => 'Teh Manis']);

        $this->actingAs($this->staff())
            ->get('/products?q=Kopi')
            ->assertSee('Kopi Hitam')
            ->assertDontSee('Teh Manis');
    }

    public function test_cancelling_a_sale_restores_stock(): void
    {
        $admin = $this->owner();
        $product = $this->product(['stock' => 10]);

        $this->actingAs($admin)->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

        $this->assertEquals(6, $product->fresh()->stock);

        $sale = Sale::first();
        $this->actingAs($admin)->post("/sales/{$sale->id}/cancel");

        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertEquals('cancelled', $sale->fresh()->status);
    }

    public function test_purchase_cancel_is_rejected_when_stock_already_used(): void
    {
        $admin = $this->owner();
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);
        $product = $this->product(['stock' => 0]);

        $this->actingAs($admin)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000],
            ],
        ]);

        $this->actingAs($admin)->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

        $purchase = Purchase::first();

        $this->actingAs($admin)
            ->post("/purchases/{$purchase->id}/cancel")
            ->assertSessionHas('error');

        $this->assertEquals('completed', $purchase->fresh()->status);
        $this->assertEquals(1, $product->fresh()->stock);
    }
}
