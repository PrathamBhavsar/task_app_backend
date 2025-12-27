import { db } from '@/core/db'
import { config } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError, DuplicateError } from '@/core/utils/errors'
import type { ConfigModel } from './model'

export abstract class ConfigService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(config).limit(limit).offset(offset)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(config)
        return Number(result[0].count)
    }

    static async getByKey(key: string) {
        const result = await db.select().from(config).where(eq(config.key, key)).limit(1)
        if (!result.length) throw new NotFoundError('Config not found')
        return result[0]
    }

    static async create(data: ConfigModel.Create) {
        const existing = await db.select().from(config).where(eq(config.key, data.key)).limit(1)
        if (existing.length) throw new DuplicateError('Config key already exists')
        const [cfg] = await db.insert(config).values(data).returning()
        return cfg
    }

    static async update(key: string, data: ConfigModel.Update) {
        await this.getByKey(key)
        const [cfg] = await db.update(config).set(data).where(eq(config.key, key)).returning()
        return cfg
    }

    static async delete(key: string) {
        await this.getByKey(key)
        await db.delete(config).where(eq(config.key, key))
    }
}
