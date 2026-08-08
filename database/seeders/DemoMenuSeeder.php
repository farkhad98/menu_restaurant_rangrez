<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoMenuSeeder extends Seeder
{
    public function run()
    {
        $groups = $this->menuGroups();
        $formats = ['jpg', 'png', 'webp', 'avif'];
        $formatCounts = array_fill_keys($formats, 0);
        $productNumber = 0;

        foreach ($groups as $group) {
            $category = Category::updateOrCreate(
                ['title_en' => $group['category']['title_en']],
                $group['category']
            );

            foreach ($group['products'] as $fields) {
                $format = $formats[$productNumber % count($formats)];
                $fields['category_id'] = $category->id;

                $product = Product::updateOrCreate(
                    ['title_en' => $fields['title_en']],
                    $fields
                );

                $imagePath = 'uploads/products/' . $product->id . '/' . $product->preview_image;
                $imageMissing = $product->preview_image == null || !Storage::exists($imagePath);

                if ($imageMissing) {
                    $sourcePath = $this->createSourceImage(
                        $format,
                        $productNumber,
                        $product->title_en
                    );

                    try {
                        $product->uploadImage(new UploadedFile(
                            $sourcePath,
                            basename($sourcePath),
                            $this->mimeType($format),
                            null,
                            true
                        ));
                    } finally {
                        if (is_file($sourcePath)) {
                            unlink($sourcePath);
                        }
                    }

                    $formatCounts[$format]++;
                }

                $productNumber++;
            }
        }

        $sourceDirectory = storage_path('app/seed-source-images');

        if (is_dir($sourceDirectory) && count(scandir($sourceDirectory)) === 2) {
            rmdir($sourceDirectory);
        }

        $this->command->info(
            'Демо-меню готово: ' . $productNumber . ' блюд. '
            . 'Загружено исходников: JPG ' . $formatCounts['jpg']
            . ', PNG ' . $formatCounts['png']
            . ', WebP ' . $formatCounts['webp']
            . ', AVIF ' . $formatCounts['avif'] . '.'
        );
    }

    private function createSourceImage(string $format, int $index, string $title): string
    {
        $directory = storage_path('app/seed-source-images');

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $sizes = [
            [1600, 1000],
            [2200, 1375],
            [2800, 1750],
            [3600, 2250],
        ];
        [$width, $height] = $sizes[$index % count($sizes)];
        $image = imagecreatetruecolor($width, $height);
        $palettes = [
            [[116, 38, 28], [235, 155, 74], [255, 226, 166]],
            [[29, 74, 67], [74, 153, 120], [219, 226, 178]],
            [[48, 43, 80], [123, 83, 150], [242, 183, 198]],
            [[82, 48, 26], [181, 115, 54], [249, 218, 157]],
            [[33, 70, 111], [66, 139, 166], [214, 232, 222]],
        ];
        $palette = $palettes[$index % count($palettes)];

        for ($y = 0; $y < $height; $y += 8) {
            $progress = $y / $height;
            $red = (int) ($palette[0][0] + (($palette[1][0] - $palette[0][0]) * $progress));
            $green = (int) ($palette[0][1] + (($palette[1][1] - $palette[0][1]) * $progress));
            $blue = (int) ($palette[0][2] + (($palette[1][2] - $palette[0][2]) * $progress));
            $color = imagecolorallocate($image, $red, $green, $blue);
            imagefilledrectangle($image, 0, $y, $width, min($height, $y + 8), $color);
        }

        $shadow = imagecolorallocate($image, 35, 29, 25);
        $plate = imagecolorallocate($image, 242, 235, 218);
        $food = imagecolorallocate($image, $palette[2][0], $palette[2][1], $palette[2][2]);
        $accent = imagecolorallocate($image, 197, 72, 52);
        $greenery = imagecolorallocate($image, 62, 125, 73);
        $cream = imagecolorallocate($image, 255, 246, 220);

        imagefilledellipse($image, (int) ($width * 0.51), (int) ($height * 0.50), (int) ($width * 0.72), (int) ($height * 0.72), $shadow);
        imagefilledellipse($image, (int) ($width * 0.50), (int) ($height * 0.47), (int) ($width * 0.72), (int) ($height * 0.72), $plate);
        imagefilledellipse($image, (int) ($width * 0.50), (int) ($height * 0.47), (int) ($width * 0.56), (int) ($height * 0.50), $food);

        for ($piece = 0; $piece < 18; $piece++) {
            $x = (int) ($width * (0.32 + ((($piece * 37) % 36) / 100)));
            $y = (int) ($height * (0.29 + ((($piece * 23) % 34) / 100)));
            $diameter = (int) ($width * (0.018 + (($piece % 4) / 250)));
            imagefilledellipse($image, $x, $y, $diameter, $diameter, $piece % 3 === 0 ? $accent : $greenery);
        }

        imagefilledrectangle($image, 0, (int) ($height * 0.84), $width, $height, $shadow);
        imagestring($image, 5, 32, (int) ($height * 0.88), substr($title, 0, 42), $cream);
        imagestring(
            $image,
            4,
            32,
            (int) ($height * 0.93),
            'SOURCE: ' . strtoupper($format) . ' | ' . $width . 'x' . $height,
            $cream
        );

        $path = $directory . '/menu-' . ($index + 1) . '.' . $format;
        $saved = match ($format) {
            'jpg' => imagejpeg($image, $path, 88),
            'png' => imagepng($image, $path, 6),
            'webp' => imagewebp($image, $path, 84),
            'avif' => function_exists('imageavif') ? imageavif($image, $path, 78) : false,
            default => false,
        };

        imagedestroy($image);

        if (!$saved) {
            throw new RuntimeException('Не удалось создать тестовое изображение формата ' . strtoupper($format) . '.');
        }

        return $path;
    }

    private function mimeType(string $format): string
    {
        return match ($format) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
        };
    }

    private function menuGroups(): array
    {
        return [
            [
                'category' => ['title_ru' => 'Основные блюда', 'title_en' => 'Main dishes'],
                'products' => [
                    ['title_ru' => 'Плов по-чайхански', 'title_en' => 'Traditional Uzbek Plov', 'description_ru' => 'Рис лазер, говядина, жёлтая морковь, нут и ароматные специи.', 'description_en' => 'Laser rice, beef, yellow carrots, chickpeas and aromatic spices.', 'price_uzs' => 65000, 'netto' => '400'],
                    ['title_ru' => 'Казан-кебаб', 'title_en' => 'Kazan Kebab', 'description_ru' => 'Нежная говядина, картофель, лук и свежая зелень.', 'description_en' => 'Tender beef, potatoes, onions and fresh herbs.', 'price_uzs' => 78000, 'netto' => '420'],
                    ['title_ru' => 'Курица терияки', 'title_en' => 'Teriyaki Chicken', 'description_ru' => 'Куриное филе в соусе терияки с рисом и овощами.', 'description_en' => 'Chicken fillet in teriyaki sauce with rice and vegetables.', 'price_uzs' => 59000, 'netto' => '360'],
                    ['title_ru' => 'Паста Альфредо', 'title_en' => 'Pasta Alfredo', 'description_ru' => 'Фетучини, курица, сливочный соус и пармезан.', 'description_en' => 'Fettuccine, chicken, creamy sauce and parmesan.', 'price_uzs' => 62000, 'netto' => '350'],
                    ['title_ru' => 'Лосось с овощами', 'title_en' => 'Salmon with Vegetables', 'description_ru' => 'Филе лосося на гриле с сезонными овощами.', 'description_en' => 'Grilled salmon fillet with seasonal vegetables.', 'price_uzs' => 118000, 'netto' => '330'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Супы', 'title_en' => 'Soups'],
                'products' => [
                    ['title_ru' => 'Мастава', 'title_en' => 'Mastava Soup', 'description_ru' => 'Наваристый суп с рисом, говядиной, овощами и сметаной.', 'description_en' => 'Rich soup with rice, beef, vegetables and sour cream.', 'price_uzs' => 39000, 'netto' => '350'],
                    ['title_ru' => 'Чучвара', 'title_en' => 'Chuchvara Soup', 'description_ru' => 'Домашние пельмени в ароматном бульоне со свежей зеленью.', 'description_en' => 'Homemade dumplings in aromatic broth with fresh herbs.', 'price_uzs' => 43000, 'netto' => '350'],
                    ['title_ru' => 'Тыквенный крем-суп', 'title_en' => 'Pumpkin Cream Soup', 'description_ru' => 'Запечённая тыква, сливки, семечки и пряное масло.', 'description_en' => 'Roasted pumpkin, cream, seeds and spiced oil.', 'price_uzs' => 36000, 'netto' => '300'],
                    ['title_ru' => 'Грибной крем-суп', 'title_en' => 'Mushroom Cream Soup', 'description_ru' => 'Шампиньоны, сливки, лук и хрустящие гренки.', 'description_en' => 'Mushrooms, cream, onions and crunchy croutons.', 'price_uzs' => 41000, 'netto' => '300'],
                    ['title_ru' => 'Куриный суп с лапшой', 'title_en' => 'Chicken Noodle Soup', 'description_ru' => 'Домашняя лапша, курица, морковь и лёгкий бульон.', 'description_en' => 'Homemade noodles, chicken, carrots and light broth.', 'price_uzs' => 35000, 'netto' => '350'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Шашлык и гриль', 'title_en' => 'Grill'],
                'products' => [
                    ['title_ru' => 'Шашлык из баранины', 'title_en' => 'Lamb Shashlik', 'description_ru' => 'Сочная баранина на углях с маринованным луком.', 'description_en' => 'Juicy charcoal-grilled lamb with pickled onions.', 'price_uzs' => 32000, 'netto' => '120'],
                    ['title_ru' => 'Шашлык из курицы', 'title_en' => 'Chicken Shashlik', 'description_ru' => 'Куриное филе в пряном маринаде, приготовленное на углях.', 'description_en' => 'Chicken fillet in a spiced marinade, cooked over charcoal.', 'price_uzs' => 28000, 'netto' => '130'],
                    ['title_ru' => 'Люля-кебаб', 'title_en' => 'Lula Kebab', 'description_ru' => 'Рубленая говядина и баранина с луком и специями.', 'description_en' => 'Minced beef and lamb with onions and spices.', 'price_uzs' => 34000, 'netto' => '140'],
                    ['title_ru' => 'Овощи на гриле', 'title_en' => 'Grilled Vegetables', 'description_ru' => 'Баклажан, цукини, сладкий перец, томаты и грибы.', 'description_en' => 'Eggplant, zucchini, sweet peppers, tomatoes and mushrooms.', 'price_uzs' => 42000, 'netto' => '300'],
                    ['title_ru' => 'Бараньи рёбрышки', 'title_en' => 'Grilled Lamb Ribs', 'description_ru' => 'Рёбрышки на гриле с соусом аджика и свежей зеленью.', 'description_en' => 'Grilled lamb ribs with adjika sauce and fresh herbs.', 'price_uzs' => 96000, 'netto' => '380'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Салаты', 'title_en' => 'Salads'],
                'products' => [
                    ['title_ru' => 'Салат Rangrez', 'title_en' => 'Rangrez Salad', 'description_ru' => 'Свежие овощи, зелень, сыр и фирменная заправка.', 'description_en' => 'Fresh vegetables, herbs, cheese and our signature dressing.', 'price_uzs' => 38000, 'netto' => '250'],
                    ['title_ru' => 'Цезарь с курицей', 'title_en' => 'Chicken Caesar Salad', 'description_ru' => 'Романо, куриное филе, пармезан, томаты и гренки.', 'description_en' => 'Romaine, chicken fillet, parmesan, tomatoes and croutons.', 'price_uzs' => 49000, 'netto' => '280'],
                    ['title_ru' => 'Греческий салат', 'title_en' => 'Greek Salad', 'description_ru' => 'Томаты, огурцы, маслины, сладкий перец и сыр фета.', 'description_en' => 'Tomatoes, cucumbers, olives, sweet peppers and feta cheese.', 'price_uzs' => 43000, 'netto' => '280'],
                    ['title_ru' => 'Ачичук', 'title_en' => 'Achichuk Salad', 'description_ru' => 'Тонко нарезанные томаты, лук, базилик и острый перец.', 'description_en' => 'Thinly sliced tomatoes, onions, basil and hot pepper.', 'price_uzs' => 26000, 'netto' => '220'],
                    ['title_ru' => 'Салат с киноа', 'title_en' => 'Quinoa Salad', 'description_ru' => 'Киноа, авокадо, овощи, руккола и цитрусовая заправка.', 'description_en' => 'Quinoa, avocado, vegetables, arugula and citrus dressing.', 'price_uzs' => 52000, 'netto' => '270'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Выпечка', 'title_en' => 'Bakery'],
                'products' => [
                    ['title_ru' => 'Самса с говядиной', 'title_en' => 'Beef Samsa', 'description_ru' => 'Слоёное тесто, рубленая говядина, лук и зира.', 'description_en' => 'Flaky pastry, chopped beef, onions and cumin.', 'price_uzs' => 18000, 'netto' => '150'],
                    ['title_ru' => 'Самса с курицей', 'title_en' => 'Chicken Samsa', 'description_ru' => 'Хрустящее тесто с сочной курицей и ароматными специями.', 'description_en' => 'Crispy pastry with juicy chicken and aromatic spices.', 'price_uzs' => 16000, 'netto' => '150'],
                    ['title_ru' => 'Хачапури по-аджарски', 'title_en' => 'Adjarian Khachapuri', 'description_ru' => 'Лодочка из теста с сыром сулугуни, яйцом и маслом.', 'description_en' => 'Boat-shaped bread with sulguni cheese, egg and butter.', 'price_uzs' => 59000, 'netto' => '380'],
                    ['title_ru' => 'Фокачча с розмарином', 'title_en' => 'Rosemary Focaccia', 'description_ru' => 'Итальянская лепёшка с розмарином, солью и оливковым маслом.', 'description_en' => 'Italian flatbread with rosemary, salt and olive oil.', 'price_uzs' => 26000, 'netto' => '220'],
                    ['title_ru' => 'Хлебная корзина', 'title_en' => 'Bread Basket', 'description_ru' => 'Ассорти свежего домашнего хлеба и лепёшек.', 'description_en' => 'Assorted fresh homemade bread and flatbreads.', 'price_uzs' => 22000, 'netto' => '250'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Завтраки', 'title_en' => 'Breakfast'],
                'products' => [
                    ['title_ru' => 'Омлет с овощами', 'title_en' => 'Vegetable Omelette', 'description_ru' => 'Три яйца, томаты, сладкий перец, шпинат и сыр.', 'description_en' => 'Three eggs, tomatoes, sweet peppers, spinach and cheese.', 'price_uzs' => 39000, 'netto' => '280'],
                    ['title_ru' => 'Сырники со сметаной', 'title_en' => 'Syrniki with Sour Cream', 'description_ru' => 'Творожные сырники с ягодным соусом и сметаной.', 'description_en' => 'Cottage cheese pancakes with berry sauce and sour cream.', 'price_uzs' => 42000, 'netto' => '260'],
                    ['title_ru' => 'Овсяная каша', 'title_en' => 'Oatmeal with Berries', 'description_ru' => 'Овсяные хлопья на молоке с ягодами, мёдом и орехами.', 'description_en' => 'Milk oatmeal with berries, honey and nuts.', 'price_uzs' => 32000, 'netto' => '300'],
                    ['title_ru' => 'Тост с авокадо', 'title_en' => 'Avocado Toast', 'description_ru' => 'Зерновой хлеб, авокадо, яйцо пашот и микрозелень.', 'description_en' => 'Wholegrain bread, avocado, poached egg and microgreens.', 'price_uzs' => 48000, 'netto' => '240'],
                    ['title_ru' => 'Шакшука', 'title_en' => 'Shakshuka', 'description_ru' => 'Яйца в пряном томатном соусе со сладким перцем.', 'description_en' => 'Eggs in a spiced tomato sauce with sweet peppers.', 'price_uzs' => 44000, 'netto' => '320'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Десерты', 'title_en' => 'Desserts'],
                'products' => [
                    ['title_ru' => 'Шоколадный фондан', 'title_en' => 'Chocolate Fondant', 'description_ru' => 'Тёплый шоколадный десерт с жидкой начинкой и мороженым.', 'description_en' => 'Warm chocolate dessert with a molten centre and ice cream.', 'price_uzs' => 42000, 'netto' => '180'],
                    ['title_ru' => 'Классический чизкейк', 'title_en' => 'Classic Cheesecake', 'description_ru' => 'Нежный сливочный чизкейк с ягодным соусом.', 'description_en' => 'Delicate creamy cheesecake with berry sauce.', 'price_uzs' => 39000, 'netto' => '170'],
                    ['title_ru' => 'Медовик', 'title_en' => 'Honey Cake', 'description_ru' => 'Тонкие медовые коржи со сметанным кремом.', 'description_en' => 'Thin honey layers with sour cream frosting.', 'price_uzs' => 36000, 'netto' => '180'],
                    ['title_ru' => 'Тирамису', 'title_en' => 'Tiramisu', 'description_ru' => 'Савоярди, маскарпоне, кофе эспрессо и какао.', 'description_en' => 'Savoiardi, mascarpone, espresso coffee and cocoa.', 'price_uzs' => 43000, 'netto' => '170'],
                    ['title_ru' => 'Фруктовая тарелка', 'title_en' => 'Fresh Fruit Plate', 'description_ru' => 'Ассорти сезонных фруктов и свежих ягод.', 'description_en' => 'Assorted seasonal fruits and fresh berries.', 'price_uzs' => 52000, 'netto' => '450'],
                ],
            ],
            [
                'category' => ['title_ru' => 'Напитки', 'title_en' => 'Drinks'],
                'products' => [
                    ['title_ru' => 'Домашний лимонад', 'title_en' => 'Homemade Lemonade', 'description_ru' => 'Лимон, апельсин, мята, газированная вода и лёд.', 'description_en' => 'Lemon, orange, mint, sparkling water and ice.', 'price_uzs' => 28000, 'netto' => '500'],
                    ['title_ru' => 'Ягодный морс', 'title_en' => 'Berry Mors', 'description_ru' => 'Освежающий напиток из клюквы, смородины и малины.', 'description_en' => 'Refreshing cranberry, blackcurrant and raspberry drink.', 'price_uzs' => 24000, 'netto' => '400'],
                    ['title_ru' => 'Холодный чай', 'title_en' => 'Iced Tea', 'description_ru' => 'Чёрный чай, лимон, персик, мята и лёд.', 'description_en' => 'Black tea, lemon, peach, mint and ice.', 'price_uzs' => 26000, 'netto' => '450'],
                    ['title_ru' => 'Капучино', 'title_en' => 'Cappuccino', 'description_ru' => 'Двойной эспрессо и воздушная молочная пена.', 'description_en' => 'Double espresso and light milk foam.', 'price_uzs' => 26000, 'netto' => '250'],
                    ['title_ru' => 'Манговый смузи', 'title_en' => 'Mango Smoothie', 'description_ru' => 'Манго, банан, натуральный йогурт и апельсиновый сок.', 'description_en' => 'Mango, banana, natural yogurt and orange juice.', 'price_uzs' => 34000, 'netto' => '400'],
                ],
            ],
        ];
    }
}
