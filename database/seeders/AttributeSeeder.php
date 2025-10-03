<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Color Attribute
        $colorAttribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'description' => 'Product color variations',
            'type' => 'color',
            'is_required' => false,
            'is_filterable' => true,
            'is_variation' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Color Values
        $colorValues = [
            ['value' => 'Red', 'color_code' => '#FF0000'],
            ['value' => 'Blue', 'color_code' => '#0000FF'],
            ['value' => 'Green', 'color_code' => '#00FF00'],
            ['value' => 'Yellow', 'color_code' => '#FFFF00'],
            ['value' => 'Black', 'color_code' => '#000000'],
            ['value' => 'White', 'color_code' => '#FFFFFF'],
            ['value' => 'Purple', 'color_code' => '#800080'],
            ['value' => 'Orange', 'color_code' => '#FFA500'],
        ];

        foreach ($colorValues as $index => $colorData) {
            AttributeValue::create([
                'attribute_id' => $colorAttribute->id,
                'value' => $colorData['value'],
                'slug' => strtolower($colorData['value']),
                'color_code' => $colorData['color_code'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Size Attribute
        $sizeAttribute = Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'description' => 'Product size variations',
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'is_variation' => true,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Size Values
        $sizeValues = [
            'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'
        ];

        foreach ($sizeValues as $index => $size) {
            AttributeValue::create([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size,
                'slug' => strtolower($size),
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Material Attribute
        $materialAttribute = Attribute::create([
            'name' => 'Material',
            'slug' => 'material',
            'description' => 'Product material type',
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'is_variation' => true,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Material Values
        $materialValues = [
            'Cotton',
            'Polyester',
            'Wool',
            'Silk',
            'Leather',
            'Denim',
            'Linen',
            'Cashmere',
        ];

        foreach ($materialValues as $index => $material) {
            AttributeValue::create([
                'attribute_id' => $materialAttribute->id,
                'value' => $material,
                'slug' => strtolower(str_replace(' ', '-', $material)),
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Brand Attribute (Non-variation)
        $brandAttribute = Attribute::create([
            'name' => 'Brand',
            'slug' => 'brand',
            'description' => 'Product brand',
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'is_variation' => false,
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // Brand Values
        $brandValues = [
            'Nike',
            'Adidas',
            'Puma',
            'Reebok',
            'Under Armour',
            'New Balance',
            'Converse',
            'Vans',
        ];

        foreach ($brandValues as $index => $brand) {
            AttributeValue::create([
                'attribute_id' => $brandAttribute->id,
                'value' => $brand,
                'slug' => strtolower(str_replace(' ', '-', $brand)),
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Storage Capacity Attribute
        $storageAttribute = Attribute::create([
            'name' => 'Storage Capacity',
            'slug' => 'storage-capacity',
            'description' => 'Storage capacity for electronic devices',
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'is_variation' => true,
            'sort_order' => 5,
            'is_active' => true,
        ]);

        // Storage Values
        $storageValues = [
            '32GB',
            '64GB',
            '128GB',
            '256GB',
            '512GB',
            '1TB',
            '2TB',
        ];

        foreach ($storageValues as $index => $storage) {
            AttributeValue::create([
                'attribute_id' => $storageAttribute->id,
                'value' => $storage,
                'slug' => strtolower($storage),
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Screen Size Attribute
        $screenAttribute = Attribute::create([
            'name' => 'Screen Size',
            'slug' => 'screen-size',
            'description' => 'Screen size for electronic devices',
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'is_variation' => true,
            'sort_order' => 6,
            'is_active' => true,
        ]);

        // Screen Size Values
        $screenValues = [
            '5.5"',
            '6.1"',
            '6.5"',
            '6.7"',
            '7"',
            '10"',
            '11"',
            '13"',
            '15"',
            '17"',
        ];

        foreach ($screenValues as $index => $screen) {
            AttributeValue::create([
                'attribute_id' => $screenAttribute->id,
                'value' => $screen,
                'slug' => strtolower(str_replace(['"', ' '], ['', '-'], $screen)),
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}