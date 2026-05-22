<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OcrController extends Controller
{
    protected OcrService $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Analyse un document via OCR et retourne les données structurées.
     */
    public function analyze(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier doit être une image (JPG, PNG) ou un PDF de moins de 5 Mo.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $this->ocrService->analyze($request->file('document'));

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du traitement OCR : ' . $e->getMessage()
            ], 500);
        }
    }
}
