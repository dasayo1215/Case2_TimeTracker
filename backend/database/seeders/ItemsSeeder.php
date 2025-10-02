<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsSeeder extends Seeder
{
    public function run()
    {
        Item::create([
            'name' => 'テスト商品',
            'brand' => 'サンプルブランド',
            'price' => 1000,
            'description' => 'これはシンプルなシード用商品です',
        ]);

        Item::factory()->count(10)->create(); // ファクトリ使うなら
    }
}
