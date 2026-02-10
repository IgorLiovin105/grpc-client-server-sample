<?php

namespace App\Services\Grpc;

class HelloRpc
{
    private const PROTO_DIR  = 'D:/grpc/first/proto';
    private const PROTO_FILE = 'hello.proto';

    public function __construct(
        private readonly GrpcurlClient $client
    ) {}

    public function sendHello(string $content): string
    {
        $res = $this->client->call(
            protoDir: self::PROTO_DIR,
            protoFile: self::PROTO_FILE,
            method: 'hello.HelloService/SendHello',
            payload: ['content' => $content],
        );

        return (string) ($res['content'] ?? '');
    }
}
