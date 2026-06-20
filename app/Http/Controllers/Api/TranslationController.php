<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchTranslationRequest;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Http\Resources\TranslationCollection;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    public function __construct(
        private readonly TranslationService $translationService,
    ) {}

    public function index(SearchTranslationRequest $request): TranslationCollection
    {
        $filters = array_filter($request->validated());

        $translations = $this->translationService->list(
            $filters,
            $request->integer('per_page', 50),
        );

        return new TranslationCollection($translations);
    }

    public function store(StoreTranslationRequest $request): TranslationResource
    {
        $translation = $this->translationService->create($request->validated());

        return new TranslationResource($translation);
    }

    public function show(Translation $translation): TranslationResource
    {
        $translation->load('tags');

        return new TranslationResource($translation);
    }

    public function update(UpdateTranslationRequest $request, int $id): TranslationResource
    {
        $translation = $this->translationService->update($id, $request->validated());

        return new TranslationResource($translation);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->translationService->delete($id);

        return response()->json(null, 204);
    }
}
