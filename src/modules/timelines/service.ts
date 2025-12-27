import { db } from '@/core/db'
import { taskTimelines } from '@/core/db/schema'
import { eq, sql } from 'drizzle-orm'
import { NotFoundError } from '@/core/utils/errors'
import type { TimelineModel } from './model'

export abstract class TimelineService {
    static async getAll(limit: number, offset: number) {
        return await db.select().from(taskTimelines).limit(limit).offset(offset).orderBy(taskTimelines.createdAt)
    }

    static async countAll(): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(taskTimelines)
        return Number(result[0].count)
    }

    static async getByTaskId(taskId: number, limit: number, offset: number) {
        return await db.select().from(taskTimelines).where(eq(taskTimelines.taskId, taskId)).limit(limit).offset(offset).orderBy(taskTimelines.createdAt)
    }

    static async countByTaskId(taskId: number): Promise<number> {
        const result = await db.select({ count: sql<number>`count(*)` }).from(taskTimelines).where(eq(taskTimelines.taskId, taskId))
        return Number(result[0].count)
    }

    static async getById(id: number) {
        const result = await db.select().from(taskTimelines).where(eq(taskTimelines.timelineId, id)).limit(1)
        if (!result.length) throw new NotFoundError('Timeline not found')
        return result[0]
    }

    static async create(data: TimelineModel.Create, userId: number) {
        const [timeline] = await db.insert(taskTimelines).values({ ...data, userId }).returning()
        return timeline
    }

    static async delete(id: number) {
        await this.getById(id)
        await db.delete(taskTimelines).where(eq(taskTimelines.timelineId, id))
    }
}
