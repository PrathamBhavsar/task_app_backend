import { db } from '@/core/db'
import { measurements } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { MeasurementModel } from './model'

export abstract class MeasurementService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(measurements).limit(limit).offset(offset)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(measurements)
        return Number(result[0].count)
    }

    static async getByTaskId(taskId: number, limit: number, offset: number) {
        return await db.select().from(measurements).where(eq(measurements.taskId, taskId)).limit(limit).offset(offset)
    }

    static async countByTaskId(taskId: number): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(measurements).where(eq(measurements.taskId, taskId))
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(measurements).where(eq(measurements.measurementId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Measurement not found')
        return result[0]
    }

    static async create(data: MeasurementModel.Create) {
        const [measurement] = await db.insert(measurements).values(data).returning()
        return measurement
    }

    static async update(id: number, data: MeasurementModel.Update) {
        await this.getById(id)
        const [measurement] = await db.update(measurements).set(data).where(eq(measurements.measurementId, id)).returning()
        return measurement
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(measurements).where(eq(measurements.measurementId, id))
    }
}
