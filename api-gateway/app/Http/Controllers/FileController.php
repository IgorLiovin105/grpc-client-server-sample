<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Grpc\FileRpc;

class FileController extends Controller
{
    public function __construct(private readonly FileRpc $fileRpc) {}

    public function sendFile(Request $request): JsonResponse
    {
        $file = $request->file('file');

        $reply = $this->fileRpc->sendFile($file);

        return response()->json($reply);
    }
}
