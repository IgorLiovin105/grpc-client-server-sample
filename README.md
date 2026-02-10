# Пример реализации gRPC клиента и сервера #
Клиент написан на Laravel, сервер на Nest.js <br />

Используемый стек на проекте:
* PHP версии 8.2.0
* Laravel версии 12.0
* Node.js версии 22.14.0
* Nest.js фреймворк 11.0.12
* Программа grpcurl версии 1.9.3

## Создания .proto файла ##
Для начала необходимо создать свой файл с расширением .proto
```protobuf
syntax = "proto3";

package my;

service MyService {
   rpc MyMethod (MyRequest) returns (MyResponse);
}

message MyRequest {
   string content = 1;
}

message MyResponse {
   string content = 1;
}
```
## Настройки клиента ##
Для начала работы необходимо прописать команду для Nest.js приложения
```cmd
npm install
```
В превую очередь настроим main.ts
```ts
import { NestFactory } from '@nestjs/core'
import { AppModule } from './app.module'
import { MicroserviceOptions, Transport } from '@nestjs/microservices'
import { join } from 'path'
import { ServerCredentials } from '@grpc/grpc-js'

async function bootstrap() {
   const app = await NestFactory.create(AppModule);

   app.connectMicroservice<MicroserviceOptions>({
      transport: Transport.GRPC,
      options: {
         package: 'my', // Ваш proto пакет
         protoPath: join(__dirname, '/path/to/proto/my.proto'), // Путь к proto файлу
         url: '127.0.0.1:50051', // Ваш gRPC host
         credentials: ServerCredentials.createInsecure()
      }
   })

   await app.startAllMicroservices()
   await app.listen(3001)
}
bootstrap()
```
Также пропишем контроллер
```ts
import { Controller } from '@nestjs/common'
import { GrpcMethod } from '@nestjs/microservices'

@Controller()
export class AppController {
	@GrpcMethod('MyService', 'MyMethod')
	async myMethod(content: string): Promise<string> {
		console.log(content)
		return content
	}
}
```
## Настройки клиента ##
Для начала работы необходимо прописать команду для Laravel приложения
```cmd
composer update
```
В первую очередь настроим .env
```env
GRPCURL_PATH=/path/to/grpcurl.exe # здесь укажите свой путь к grpcurl.exe
GRPC_HOST=localhost # здесь укажите свой gRPC host
```
Затем настроим AppServiceProvider
```php
use App\Services\Grpc\GrpcurlClient;
use App\Services\Grpc\MyRpc; // Ваш gRPC сервис

class AppServiceProvider extends ServiceProvider
{
   /**
     * Register any application services.
     */
   public function register(): void
   {
      $this->app->singleton(GrpcurlClient::class, function () {
         return new GrpcurlClient(
            grpcurlPath: env('GRPCURL_PATH', '/path/to/grpcurl.exe'), //Ваш путь к grpcurl.exe
            host: env('GRPC_HOST', '127.0.0.1:50051') // host вашего gGPC сервера
         );
      });
      $this->app->singleton(MyRpc::class);
   }

   /**
     * Bootstrap any application services.
     */
   public function boot(): void
   {
      //
   }
}
```
В App\Services\Grpc можно создавать свои классы для запросов gRPC
```php
namespace App\Services\Grpc;

final class MyService {
   private const PROTO_DIR  = '/path/to/proto'; // Ваш путь к папке proto
   private const PROTO_FILE = 'my.proto'; // Ваш proto файл
   public function __construct(
      private readonly GrpcurlClient $client
   ) {}

   public function myMethod(): array
   {
      return $this->client->call(
         protoDir: self::PROTO_DIR,
         protoFile: self::PROTO_FILE,
         method: 'my.MyService/MyMessage', // Ваш gRPC запроc
         payload: $payload,
         timeoutSeconds: $timeoutSeconds
      );
   }
}
```
Вызов gRPC сервиса в контроллере
```php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Grpc\MyRpc;

class MyController extends Controller
{
   public function __construct(private readonly MyRpc $myRpc) {}

   public function myMethod(Request $request): JsonResponse
   {
      $content = (string)($request->input('content', 'Hello from Laravel'));

      $reply = $this-$myRpc->myMethod($content);

      return response()->json([
         'ok' => true,
         'content' => $reply,
      ]);
   }
}
```