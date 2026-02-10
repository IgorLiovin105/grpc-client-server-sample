import { Controller } from '@nestjs/common'
import { GrpcMethod } from '@nestjs/microservices'
import { promises as fs } from 'fs'
import * as path from 'path'
import { randomUUID } from 'crypto'

const IMAGES_DIR = 'D:/grpc/first/images'
const BASE_URL = 'http://127.0.0.1:3001'

@Controller()
export class AppController {


	@GrpcMethod('HelloService', 'SendHello')
	async sendHello(content: string): Promise<string> {
		console.log(content)
		return content
	}

	@GrpcMethod('FileService', 'SendFile')
	async sendFile(file: any): Promise<any> {
		await fs.mkdir(IMAGES_DIR, { recursive: true })

		const original = this.safeName(file?.filename ?? 'file')
		const ext = path.extname(original) || '';
		const nameWithoutExt = path.basename(original, ext);

		const storedName = `${nameWithoutExt}_${randomUUID()}${ext}`;
		const fullPath = path.join(IMAGES_DIR, storedName);

		const data: Buffer = Buffer.isBuffer(file?.data) ? file.data : Buffer.from(file?.data ?? []);
		if (!data.length) {
			return { ok: false, error: 'Empty file data' };
		}

		await fs.writeFile(fullPath, data);

		const url = `${BASE_URL}/files/${encodeURIComponent(storedName)}`;

		const resposne = {
			ok: true,
			filename: storedName,
			path: fullPath,
			url,
			size: data.length,
			mime: file?.mime ?? '',
			error: '',
		}

		console.log(resposne)

		return resposne
	}

	private safeName(name: string) {
		const base = path.basename(name || 'file');
		return base.replace(/[^a-zA-Z0-9._-]/g, '_');
	}
}
