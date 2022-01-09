<?php

namespace App\Listeners;

use App\Events\ProductStore;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Support\Facades\DB;

class ProductStoreListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function cartesianProduct($products = [])
    {
        if (count($products) == 1) {
            return $products[0];
        } else if (count($products) == 2) {
            return collect($products[0])->crossJoin($products[1])->toArray();
        } else if (count($products) == 3) {
            return collect($products[0])->crossJoin($products[1], $products[2])->toArray();
        }
    }

    /**
     * Handle the event.
     *
     * @param ProductStore $event
     * @return void
     */
    public function handle(ProductStore $event)
    {
        DB::beginTransaction();

        // product store
        $product = new Product();
        $product->title = $event->product->title;
        $product->sku = $event->product->sku;
        $product->description = $event->product->description;
        $product->save();

        // product variants store
        $array = [];
        if (!empty($event->product->product_variant)) {
            foreach ($event->product->product_variant as $key => $variant) {

                if (!empty($variant['tags'])) {
                    foreach ($variant['tags'] as $tag) {
                        $product_variant = new ProductVariant();
                        $product_variant->variant = $tag;
                        $product_variant->variant_id = $variant['option'];
                        $product_variant->product_id = $product->id;
                        $product_variant->save();

                        $array[$key][] = $product_variant->id;
                    }
                }
            }

            $combinations = $this->cartesianProduct($array);

            // product variants prices
            $count = count($array);
            foreach ($combinations as $index => $combination) {
                $product_price = new ProductVariantPrice();
                $product_price->price = $event->product->product_variant_prices[$index]['price'];
                $product_price->stock = $event->product->product_variant_prices[$index]['stock'];
                $product_price->product_id = $product->id;

                if ($count == 1) {
                    $product_price->product_variant_one = $combination;
                } else if ($count == 2) {
                    $product_price->product_variant_one = $combination[0];
                    $product_price->product_variant_two = $combination[1];
                } else if ($count == 3) {
                    $product_price->product_variant_one = $combination[0];
                    $product_price->product_variant_two = $combination[1];
                    $product_price->product_variant_three = $combination[2];
                }
                $product_price->save();
            }
        }
        DB::commit();
    }
}
