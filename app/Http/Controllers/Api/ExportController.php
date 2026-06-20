<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {}

    public function export(): JsonResponse
    {
        $translations = $this->exportService->getExport();

        return response()->json($translations);
    }
}
