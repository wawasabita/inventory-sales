<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun owner, admin, staf, dan pelanggan contoh.
        $this->call(RoleSeeder::class);

        $makanan = Category::create(['name' => 'Makanan']);
        $minuman = Category::create(['name' => 'Minuman']);
        $perawatanDiri = Category::create(['name' => 'Perawatan Diri']);
        $alatTulis = Category::create(['name' => 'Alat Tulis']);

        Supplier::create([
            'name' => 'CV Sumber Rejeki',
            'phone' => '081234567890',
            'email' => 'sumberrejeki@example.com',
        ]);

        Supplier::create([
            'name' => 'UD Berkah Jaya',
            'phone' => '082345678901',
        ]);

        Supplier::create([
            'name' => 'PT Maju Bersama',
            'phone' => '085612345678',
            'email' => 'majubersama@example.com',
        ]);

        Product::create([
            'category_id' => $makanan->id,
            'sku' => 'MKN-001',
            'name' => 'Indomie Mi Goreng 85 g',
            'purchase_price' => 2900,
            'selling_price' => 3500,
            'stock' => 120,
            'minimum_stock' => 20,
        ]);

        Product::create([
            'category_id' => $makanan->id,
            'sku' => 'MKN-002',
            'name' => 'Biskuit Roma Kelapa 300 g',
            'purchase_price' => 9500,
            'selling_price' => 11500,
            'stock' => 45,
            'minimum_stock' => 10,
        ]);

        Product::create([
            'category_id' => $makanan->id,
            'sku' => 'MKN-003',
            'name' => 'Chitato Sapi Panggang 68 g',
            'purchase_price' => 8200,
            'selling_price' => 10000,
            'stock' => 60,
            'minimum_stock' => 15,
        ]);

        Product::create([
            'category_id' => $makanan->id,
            'sku' => 'MKN-004',
            'name' => 'Beras Premium 5 kg',
            'purchase_price' => 68000,
            'selling_price' => 75000,
            'stock' => 30,
            'minimum_stock' => 8,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'sku' => 'MNM-001',
            'name' => 'Aqua Air Mineral 600 ml',
            'purchase_price' => 2900,
            'selling_price' => 3500,
            'stock' => 200,
            'minimum_stock' => 30,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'sku' => 'MNM-002',
            'name' => 'Teh Botol Sosro 450 ml',
            'purchase_price' => 4500,
            'selling_price' => 5500,
            'stock' => 80,
            'minimum_stock' => 20,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'sku' => 'MNM-003',
            'name' => 'Teh Pucuk Harum 350 ml',
            'purchase_price' => 3000,
            'selling_price' => 4000,
            'stock' => 12,
            'minimum_stock' => 24,
        ]);

        Product::create([
            'category_id' => $minuman->id,
            'sku' => 'MNM-004',
            'name' => 'Good Day Cappuccino 25 g',
            'purchase_price' => 1500,
            'selling_price' => 2000,
            'stock' => 150,
            'minimum_stock' => 30,
        ]);

        Product::create([
            'category_id' => $perawatanDiri->id,
            'sku' => 'PRW-001',
            'name' => "Pond's Bright Beauty Facial Foam 100 g",
            'purchase_price' => 24000,
            'selling_price' => 28000,
            'stock' => 40,
            'minimum_stock' => 10,
        ]);

        Product::create([
            'category_id' => $perawatanDiri->id,
            'sku' => 'PRW-002',
            'name' => 'Vaseline Healthy White Lotion 200 ml',
            'purchase_price' => 30000,
            'selling_price' => 35000,
            'stock' => 28,
            'minimum_stock' => 8,
        ]);

        Product::create([
            'category_id' => $perawatanDiri->id,
            'sku' => 'PRW-003',
            'name' => 'Nivea Lip Care Original 4,8 g',
            'purchase_price' => 22000,
            'selling_price' => 27000,
            'stock' => 9,
            'minimum_stock' => 15,
        ]);

        Product::create([
            'category_id' => $perawatanDiri->id,
            'sku' => 'PRW-004',
            'name' => 'Garnier Micellar Water 125 ml',
            'purchase_price' => 33000,
            'selling_price' => 39000,
            'stock' => 25,
            'minimum_stock' => 8,
        ]);

        Product::create([
            'category_id' => $alatTulis->id,
            'sku' => 'ATK-001',
            'name' => 'Pulpen Standard AE7 Hitam',
            'purchase_price' => 2000,
            'selling_price' => 3000,
            'stock' => 150,
            'minimum_stock' => 25,
        ]);

        Product::create([
            'category_id' => $alatTulis->id,
            'sku' => 'ATK-002',
            'name' => 'Buku Tulis Sidu 38 Lembar',
            'purchase_price' => 3500,
            'selling_price' => 5000,
            'stock' => 90,
            'minimum_stock' => 20,
        ]);
    }
}
