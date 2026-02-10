<?php

namespace App\Services\Grpc;

use Illuminate\Http\UploadedFile;

final class FileRpc
{
    private const PROTO_DIR  = 'D:/grpc/first/proto';
    private const PROTO_FILE = 'file.proto';

    public function __construct(
        private readonly GrpcurlClient $client
    ) {}

    public function sendFile(UploadedFile $file, int $timeoutSeconds = 60): array
    {
        if (!$file->isValid()) {
            throw new \RuntimeException('Uploaded file is not valid: ' . $file->getErrorMessage());
        }

        try {
            $bin = $file->get();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Cannot read uploaded file contents: ' . $e->getMessage(), 0, $e);
        }

        if ($bin === '' || $bin === null) {
            throw new \RuntimeException('Cannot read uploaded file contents (empty)');
        }

        $payload = [
            'filename' => $file->getClientOriginalName() ?: $file->getFilename(),
            'mime'     => $file->getClientMimeType() ?: 'application/octet-stream',
            'data'     => base64_encode($bin),
        ];

        return $this->client->call(
            protoDir: self::PROTO_DIR,
            protoFile: self::PROTO_FILE,
            method: 'file.FileService/SendFile',
            payload: $payload,
            timeoutSeconds: $timeoutSeconds
        );
    }
}
