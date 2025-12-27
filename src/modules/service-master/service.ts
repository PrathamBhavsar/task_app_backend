import { db } from '@/core/db'
import { serviceMaster } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { ServiceMasterModel } from './model'

export abstract class ServiceMasterService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(serviceMaster).limit(limit).offset(offset).orderBy(serviceMaster.createdAt)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(serviceMaster)
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(serviceMaster).where(eq(serviceMaster.serviceMasterId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Service not found')
        return result[0]
    }

    static async create(data: ServiceMasterModel.Create) {
        const [service] = await db.insert(serviceMaster).values(data).returning()
        return service
    }

    static async update(id: number, data: ServiceMasterModel.Update) {
        await this.getById(id)
        const [service] = await db.update(serviceMaster).set(data).where(eq(serviceMaster.serviceMasterId, id)).returning()
        return service
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(serviceMaster).where(eq(serviceMaster.serviceMasterId, id))
    }
}
