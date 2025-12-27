import { db } from '@/core/db'
import { taskServices } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { TaskServiceModel } from './model'

export abstract class TaskServiceService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(taskServices).limit(limit).offset(offset)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(taskServices)
        return Number(result[0].count)
    }

    static async getByTaskId(taskId: number, limit: number, offset: number) {
        return await db.select().from(taskServices).where(eq(taskServices.taskId, taskId)).limit(limit).offset(offset)
    }

    static async countByTaskId(taskId: number): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(taskServices).where(eq(taskServices.taskId, taskId))
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(taskServices).where(eq(taskServices.taskServiceId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Task service not found')
        return result[0]
    }

    static async create(data: TaskServiceModel.Create) {
        const [service] = await db.insert(taskServices).values(data).returning()
        return service
    }

    static async update(id: number, data: TaskServiceModel.Update) {
        await this.getById(id)
        const [service] = await db.update(taskServices).set(data).where(eq(taskServices.taskServiceId, id)).returning()
        return service
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(taskServices).where(eq(taskServices.taskServiceId, id))
    }
}
