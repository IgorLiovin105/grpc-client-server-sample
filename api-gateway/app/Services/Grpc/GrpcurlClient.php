<?php

namespace App\Services\Grpc;

use Symfony\Component\Process\Process;

final class GrpcurlClient
{
    public function __construct(
        private readonly string $grpcurlPath,
        private readonly string $host
    ) {}

    public function call(
        string $protoDir,
        string $protoFile,
        string $method,
        array $payload,
        int $timeoutSeconds = 10
    ): array {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('JSON encode failed: ' . json_last_error_msg());
        }

        $process = new Process([
            $this->grpcurlPath,
            '-plaintext',
            '-import-path',
            $protoDir,
            '-proto',
            $protoFile,
            '-d',
            '@',
            $this->host,
            $method,
        ]);

        $process->setInput($json);
        $process->setTimeout($timeoutSeconds);
        $process->setEnv($this->windowsEnv());
        $process->run();

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($stderr !== '' ? $stderr : $stdout);
        }

        $decoded = json_decode($stdout, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'grpcurl returned non-JSON: ' . $stdout
            );
        }

        if ($decoded === null) {
            throw new \RuntimeException('grpcurl returned null: ' . $stdout);
        }
        return $decoded;
    }

    private function windowsEnv(): array
    {
        $grpcDir = dirname(str_replace('/', '\\', $this->grpcurlPath));

        return [
            'SystemRoot' => 'C:\Windows',
            'WINDIR'     => 'C:\Windows',
            'ComSpec'    => 'C:\Windows\System32\cmd.exe',
            'PATH'       => implode(';', [
                'C:\Windows\System32',
                'C:\Windows',
                'C:\Windows\System32\Wbem',
                'C:\Windows\System32\WindowsPowerShell\v1.0',
                $grpcDir,
            ]),
        ];
    }
}
