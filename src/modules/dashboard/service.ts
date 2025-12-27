import { db } from '@/core/db'
import { tasks, users, designers } from '@/core/db/schema'
import { count, eq, and, lt, sql, isNull } from 'drizzle-orm'
import { DashboardModel } from './model'

export class DashboardService {
    static async getOverview(): Promise<DashboardModel.Overview> {
        // Execute all queries in parallel for optimal performance
        const [
            taskStats,
            tasksByStage,
            tasksByReference,
            designerPerformance,
            salespersonWorkload
        ] = await Promise.all([
            this.getTaskStats(),
            this.getTasksByStage(),
            this.getTasksByReference(),
            this.getDesignerPerformance(),
            this.getSalespersonWorkload()
        ])

        return {
            totalTasks: taskStats.total,
            completedTasks: taskStats.completed,
            inProgressTasks: taskStats.inProgress,
            tasksDue: taskStats.due,
            tasksByStage,
            tasksByReference,
            designerPerformance,
            salespersonWorkload
        }
    }

    private static async getTaskStats() {
        const result = await db
            .select({
                total: count(),
                completed: count(sql`CASE WHEN ${tasks.status} = 'Completed' THEN 1 END`),
                inProgress: count(sql`CASE WHEN ${tasks.status} = 'In Progress' THEN 1 END`),
                due: count(sql`CASE WHEN ${tasks.dueDate} < CURRENT_DATE AND ${tasks.status} NOT IN ('Completed', 'Cancelled') THEN 1 END`)
            })
            .from(tasks)

        const row = result[0]
        return {
            total: row.total,
            completed: row.completed,
            inProgress: row.inProgress,
            due: row.due
        }
    }

    private static async getTasksByStage() {
        const result = await db
            .select({
                customerSelection: count(sql`CASE WHEN ${tasks.status} = 'Created' THEN 1 END`),
                quotation: count(sql`CASE WHEN ${tasks.status} = 'Quote: Done' THEN 1 END`),
                measurement: count(sql`CASE WHEN ${tasks.status} = 'Measurement: Done' THEN 1 END`),
                advancePaymentSO: count(sql`CASE WHEN ${tasks.status} = 'Approved' THEN 1 END`),
                finalPaymentInvoice: count(sql`CASE WHEN ${tasks.status} = 'Completed' THEN 1 END`)
            })
            .from(tasks)

        const row = result[0]
        return {
            customerSelection: row.customerSelection,
            quotation: row.quotation,
            measurement: row.measurement,
            advancePaymentSO: row.advancePaymentSO,
            finalPaymentInvoice: row.finalPaymentInvoice
        }
    }

    private static async getTasksByReference() {
        const [designerTasks, directTasks] = await Promise.all([
            db
                .select({
                    name: designers.name,
                    taskCount: count(tasks.taskId)
                })
                .from(designers)
                .leftJoin(tasks, eq(designers.designerId, tasks.designerId))
                .groupBy(designers.designerId, designers.name)
                .orderBy(sql`COUNT(${tasks.taskId}) DESC`, designers.name),
            db
                .select({
                    count: count()
                })
                .from(tasks)
                .where(isNull(tasks.designerId))
        ])

        const references = []

        // Add designer references
        for (const row of designerTasks) {
            references.push({
                type: 'designer',
                name: row.name,
                count: row.taskCount
            })
        }

        // Add direct customer reference
        const directCount = directTasks[0].count
        references.push({
            type: 'direct',
            name: 'Direct Customer',
            count: directCount
        })

        return references
    }

    private static async getDesignerPerformance() {
        const result = await db
            .select({
                designerId: designers.designerId,
                name: designers.name,
                totalTasks: count(tasks.taskId),
                completedTasks: count(sql`CASE WHEN ${tasks.status} = 'Completed' THEN 1 END`)
            })
            .from(designers)
            .leftJoin(tasks, eq(designers.designerId, tasks.designerId))
            .groupBy(designers.designerId, designers.name)
            .orderBy(
                sql`COUNT(CASE WHEN ${tasks.status} = 'Completed' THEN 1 END) DESC`,
                sql`COUNT(${tasks.taskId}) DESC`,
                designers.name
            )

        return result.map(row => ({
            designerId: row.designerId,
            name: row.name,
            completedTasks: row.completedTasks,
            totalTasks: row.totalTasks
        }))
    }

    private static async getSalespersonWorkload() {
        const result = await db
            .select({
                userId: users.userId,
                name: users.name,
                taskCount: count(tasks.taskId)
            })
            .from(users)
            .leftJoin(tasks, eq(users.userId, tasks.createdBy))
            .where(and(eq(users.role, 'salesperson'), eq(users.isActive, true)))
            .groupBy(users.userId, users.name)
            .orderBy(sql`COUNT(${tasks.taskId}) DESC`, users.name)

        return result.map(row => ({
            userId: row.userId,
            name: row.name,
            taskCount: row.taskCount
        }))
    }
}