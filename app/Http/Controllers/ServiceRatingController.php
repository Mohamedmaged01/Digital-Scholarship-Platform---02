<?php

namespace App\Http\Controllers;

use App\Models\ServiceRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** تقييم أداء الخدمة من المستفيد — الملاحظات إلزامية عند «غير راضٍ ويحتاج إلى تطوير». */
class ServiceRatingController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['required', Rule::in(ServiceRating::RATINGS)],
            'notes' => ['nullable', 'required_if:rating,needs_improvement,unsatisfied', 'string', 'max:2000'],
            'source' => ['nullable', Rule::in(ServiceRating::SOURCES)],
        ]);

        ServiceRating::create([
            ...$data,
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            'source' => $data['source'] ?? 'widget',
            'locale' => app()->getLocale(),
        ]);

        return response()->json(['message' => __('شكرًا لك، تم استلام تقييمك.')]);
    }
}
