<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Grpc\HelloRpc;

class HelloController extends Controller
{
    public function __construct(private readonly HelloRpc $helloRpc) {}

    public function sendHello(Request $request): JsonResponse
    {
        $content = (string)($request->input('content', 'Hello from Laravel'));

        $reply = $this->helloRpc->sendHello($content);

        return response()->json([
            'ok' => true,
            'content' => $reply,
        ]);
    }
}
