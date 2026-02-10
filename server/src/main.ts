import { NestFactory } from '@nestjs/core'
import { AppModule } from './app.module'
import { MicroserviceOptions, Transport } from '@nestjs/microservices'
import { join } from 'path'
import { ServerCredentials } from '@grpc/grpc-js'
import * as express from 'express'

async function bootstrap() {
  const app = await NestFactory.create(AppModule);

  app.use('/images', express.static('D:/grpc/first/images'));

  app.connectMicroservice<MicroserviceOptions>({
    transport: Transport.GRPC,
    options: {
      package: ['hello', 'file'],
      protoPath: [
        join(__dirname, '../../proto/hello.proto'),
        join(__dirname, '../../proto/file.proto'),
      ],
      url: '127.0.0.1:50051',
      credentials: ServerCredentials.createInsecure()
    }
  })

  await app.startAllMicroservices()
  await app.listen(3001)
}
bootstrap()