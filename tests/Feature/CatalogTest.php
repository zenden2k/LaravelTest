<?php

namespace Tests\Feature;

 use App\Models\Category;
 use App\Models\Product;
 use App\Models\User;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Testing\Fluent\AssertableJson;
 use PHPUnit\Framework\Attributes\Test;
 use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_catalog(): void
    {
        User::factory(10)->create();

        Category::factory(10)->create();
        Product::factory(10)->create();

        $response = $this->get('/catalog?per_page=20');
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            return $this->checkJsonStructure($json);
        });

        $response->assertJsonCount(10, 'data');
    }

    #[Test]
    public function test_catalog_search_by_name(): void
    {
        User::factory()->create();

        Category::factory()->create();

        Product::factory()->createMany([
            ['name' => 'Cool car', 'price' => 1000],
            ['name' => 'Nice car', 'price' => 2000],
            ['name' => 'Very nice tractor', 'price' => 500],
        ]);

        $response = $this->get('/catalog?per_page=20&name=nice');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJson(function (AssertableJson $json) {
            return $this->checkJsonStructure($json)
                ->where('data.0.name', 'Nice car')
                ->where('data.0.price', 2000)
                ->where('data.1.name', 'Very nice tractor')
                ->where('data.1.price', 500);
        });
    }

    #[Test]
    public function test_catalog_search_by_name_and_price(): void
    {
        User::factory()->create();

        Category::factory()->create();

        Product::factory()->createMany([
            ['name' => 'Cool car', 'price' => 1000],
            ['name' => 'Nice car', 'price' => 2000],
            ['name' => 'Very nice tractor', 'price' => 500],
        ]);

        $response = $this->get('/catalog?per_page=20&name=nice&min_price=500&max_price=1000');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson(function (AssertableJson $json) {
            return $this->checkJsonStructure($json)
                ->where('data.0.name', 'Very nice tractor')
                ->where('data.0.price', 500);
        });
    }

    #[Test]
    public function test_catalog_search_by_category(): void
    {
        User::factory()->create();

        [$category1, $category2, $category3]  = Category::factory(3)->createMany();

        Product::factory()->createMany([
            ['name' => 'Cool car', 'price' => 1000, 'category_id' => $category2->id],
            ['name' => 'Nice car', 'price' => 2000, 'category_id' => $category3->id],
            ['name' => 'Very nice tractor', 'price' => 500, 'category_id' => $category1->id],
        ]);

        $response = $this->get('/catalog?per_page=20&category_id='.$category3->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJson(function (AssertableJson $json) {
            return $this->checkJsonStructure($json)
                ->where('data.0.name', 'Nice car')
                ->where('data.0.price', 2000);
        });
    }

    private function assertValidDate(): \Closure
    {
        return fn($value) =>
            is_string($value) &&
            !empty($value) &&
            strtotime($value) !== false;
    }

    private function assertPrice(): \Closure
    {
        return fn($value) =>
            is_numeric($value) &&
            $value > 0;
    }

    private function checkJsonStructure(AssertableJson $json): AssertableJson
    {
        return $json->hasAll('data', 'links', 'meta')
            ->whereAllType([
                'data' => 'array',
                'links' => 'array',
                'meta' => 'array'
            ])
            ->has('data.0', fn (AssertableJson $json) =>
            $json
                ->hasAll('id', 'name', 'description', 'created_at', 'quantity', 'price', 'category')
                ->whereAllType([
                    'id' => 'integer',
                    'name' => 'string',
                    'description' => 'string',
                    'created_at' => 'string',
                    'quantity' => 'integer',
                    'category' => 'array',
                ])
                ->where('price', $this->assertPrice())
                ->where('created_at', $this->assertValidDate())
                ->etc())
            ->etc();
    }
}
