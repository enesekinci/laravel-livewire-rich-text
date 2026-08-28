<?php

namespace EnesEkinci\RichText\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $maxKb = (int) config('rich-text.max_kb', 5120);
        $mimes = implode(',', config('rich-text.mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp']));

        try {
            $validated = $request->validate([
                'image' => ['required', 'file', "max:{$maxKb}", "mimes:{$mimes}"],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first('image') ?: 'Geçersiz dosya.',
            ], 422);
        }

        $disk = (string) config('rich-text.disk', 'public');
        $directory = trim((string) config('rich-text.directory', 'rich-text'), '/');

        // storePublicly so R2/S3 objects are readable via the public CDN URL.
        $path = $validated['image']->storePublicly($directory, $disk);
        $url = Storage::disk($disk)->url($path);

        return response()->json([
            'url' => $url,
        ]);
    }
}
