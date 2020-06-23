<?php


namespace App\Traits;


trait ApiResponder {
    /**
     * Api success response
     * @param array $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse($data = [], $code = 200){
        $data['success'] = true;
        return response()->json($data, $code);
    }

    /**
     * Error response
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function errorResponse($message, $code = 422){
        $data = [
            'success' => false,
            'error' => $message,
            'code' => $code
        ];

        return response()->json($data, $code);
    }
}
