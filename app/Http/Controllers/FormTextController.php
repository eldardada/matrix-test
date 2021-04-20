<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormTextRequest;
use App\Http\Services\FormTextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormTextController extends Controller
{

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $text = $request->input('text');
//        $text = "Some1 Some2 Some3 {Some1 Some2 Some3 Some4}";
//        $text = "0уровень 0уровень {1ур} {1ур!овень! [2ур{3ур}] }}";
        $text = "{" . $text . "}";
        $formTextService = new FormTextService($text);
        $error = $formTextService->validate();

        if($error !== null) {
            return response()->json([
                'data' => [
                    'error' => true,
                    'message' => $error
                ]
            ]);
        }

        $data = $formTextService->addNewWords();

        return response()->json([
                'error' => false,
                'data' => $data
        ]);
    }

}
