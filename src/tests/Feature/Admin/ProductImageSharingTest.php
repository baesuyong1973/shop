<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageSharingTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    private Admin $admin;

    private Product $original;

    private Product $copy;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('products/shared.jpg', 'image');

        $this->shop = Shop::create(['name' => 'Shop', 'slug' => 'shop', 'is_active' => true]);

        $this->admin = new Admin([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => 'password',
        ]);
        $this->admin->role = Admin::ROLE_SUPER_ADMIN;
        $this->admin->save();

        $this->original = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Original',
            'image_path' => 'products/shared.jpg',
            'price' => 1000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.copy', $this->original), ['shop_id' => $this->shop->id]);

        $this->copy = Product::where('id', '!=', $this->original->id)->firstOrFail();
    }

    public function test_replacing_copied_products_image_keeps_original_image(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.shop.products.update', [$this->shop, $this->copy]), [
                '_method' => 'PATCH',
                'name' => 'Copy',
                'price' => 1000,
                'stock' => 10,
                'image' => UploadedFile::fake()->image('new.jpg'),
            ])
            ->assertRedirect();

        $this->assertNotSame('products/shared.jpg', $this->copy->fresh()->image_path);
        Storage::disk('public')->assertExists('products/shared.jpg');
    }

    public function test_deleting_copied_product_keeps_original_image(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.shop.products.destroy', [$this->shop, $this->copy]))
            ->assertRedirect();

        Storage::disk('public')->assertExists('products/shared.jpg');
    }

    public function test_deleting_last_product_using_image_removes_it(): void
    {
        $this->copy->delete();

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.shop.products.destroy', [$this->shop, $this->original]))
            ->assertRedirect();

        Storage::disk('public')->assertMissing('products/shared.jpg');
    }
}
