<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Category;

class Product extends Model
{
    use HasFactory;

    public const SUPPORTED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ];

    public const MAX_IMAGE_FILE_SIZE_KB = 25600;
    public const MAX_SOURCE_IMAGE_SIDE = 12000;
    public const MAX_SOURCE_IMAGE_PIXELS = 60000000;
    public const MAX_SAVED_IMAGE_SIDE = 2400;

    protected $fillable = [
        'title_ru',
        'title_en',
        'description_ru',
        'description_en',
        'price_uzs',
        'netto',
        'category_id',

    ];

    protected $hidden = [
        'preview_image',
    ];

    protected $appends = [
        'image',
        'date'
    ];

    protected $casts = [
        'price_uzs' => 'decimal:2',
        'category_id' => 'integer',
    ];

    public function category()
    {
	 	return $this->belongsTo(Category::class);
    }

    public function getImageAttribute() {
        return $this->getImage();
    }

    public function getDateAttribute() {
        return $this->created_at ? $this->created_at->format('d-m-Y') : null;
    }

    public function getTitle(string $lang): string
    {
        if (in_array($lang, config('app.available_locales'))) {
            $title = 'title_'.$lang;
        }else{
            $title = 'title_en';
        }
        return $this->$title;
    }

    public function getContent(string $lang): string
    {
        if (in_array($lang, config('app.available_locales'))) {
            $content = 'description_'.$lang;
        }else{
            $content = 'description_en';
        }
        return $this->$content;
    }

    public static function add($fields){
        $product = new self;

        $product->fill($fields);
        $product->save();

        return $product;
    }

    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    public function remove()
    {
        $this->removeImage();
        $this->delete();
    }

    public function removeImage()
    {
        if ($this->preview_image != null) {
            Storage::delete('uploads/products/'. $this->id . '/' . $this->preview_image);
        }
    }

    public function uploadImage($image)
    {
        if ($image == null) {
            return;
        }

        $imageInfo = @getimagesize($image->getRealPath());
        $sourceImage = $imageInfo
            ? $this->createImageResource($image->getRealPath(), $imageInfo['mime'] ?? '')
            : false;

        if ($sourceImage === false) {
            throw ValidationException::withMessages([
                'preview_image' => 'Не удалось обработать изображение. Загрузите JPG, PNG, WebP или AVIF.',
            ]);
        }

        $sourceImage = $this->fixJpegOrientation($sourceImage, $image->getRealPath(), $imageInfo['mime'] ?? '');
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $scale = min(
            1,
            self::MAX_SAVED_IMAGE_SIDE / $sourceWidth,
            self::MAX_SAVED_IMAGE_SIDE / $sourceHeight
        );
        $savedWidth = max(1, (int) round($sourceWidth * $scale));
        $savedHeight = max(1, (int) round($sourceHeight * $scale));
        $savedImage = imagecreatetruecolor($savedWidth, $savedHeight);

        imagealphablending($savedImage, false);
        imagesavealpha($savedImage, true);
        $transparent = imagecolorallocatealpha($savedImage, 0, 0, 0, 127);
        imagefilledrectangle($savedImage, 0, 0, $savedWidth, $savedHeight, $transparent);
        imagecopyresampled(
            $savedImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $savedWidth,
            $savedHeight,
            $sourceWidth,
            $sourceHeight
        );

        ob_start();
        $imageSaved = imagewebp($savedImage, null, 82);
        $imageContents = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($savedImage);

        if (!$imageSaved || $imageContents === false || $imageContents === '') {
            throw ValidationException::withMessages([
                'preview_image' => 'Не удалось сохранить изображение. Попробуйте другой файл.',
            ]);
        }

        $oldFilename = $this->preview_image;
        $filename = Str::random(20) . '.webp';
        $path = 'uploads/products/' . $this->id . '/' . $filename;

        if (!Storage::put($path, $imageContents, 'public')) {
            throw ValidationException::withMessages([
                'preview_image' => 'Не удалось сохранить изображение. Проверьте доступ к папке uploads.',
            ]);
        }

        try {
            $this->preview_image = $filename;
            $this->save();
        } catch (\Throwable $exception) {
            Storage::delete($path);
            throw $exception;
        }

        if ($oldFilename != null) {
            Storage::delete('uploads/products/'. $this->id . '/' . $oldFilename);
        }
    }

    private function createImageResource(string $path, string $mimeType)
    {
        try {
            return match ($mimeType) {
                'image/jpeg' => @imagecreatefromjpeg($path),
                'image/png' => @imagecreatefrompng($path),
                'image/webp' => @imagecreatefromwebp($path),
                'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
                default => false,
            };
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function fixJpegOrientation($image, string $path, string $mimeType)
    {
        if ($mimeType !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotatedImage = @imagerotate($image, $angle, 0);

        if ($rotatedImage === false) {
            return $image;
        }

        imagedestroy($image);
        return $rotatedImage;
    }

    public function getImage()
    {
        if ($this->preview_image == null) {
            return null;
        }
        return '/uploads/products/' . $this->id . '/' . $this->preview_image;
    }

}
