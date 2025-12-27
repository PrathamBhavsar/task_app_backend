import { t } from 'elysia'

export namespace DashboardModel {
    export const overview = t.Object({
        totalTasks: t.Number(),
        completedTasks: t.Number(),
        inProgressTasks: t.Number(),
        tasksDue: t.Number(),
        tasksByStage: t.Object({
            customerSelection: t.Number(),
            quotation: t.Number(),
            measurement: t.Number(),
            advancePaymentSO: t.Number(),
            finalPaymentInvoice: t.Number()
        }),
        tasksByReference: t.Array(t.Object({
            type: t.String(),
            name: t.String(),
            count: t.Number()
        })),
        designerPerformance: t.Array(t.Object({
            designerId: t.Number(),
            name: t.String(),
            completedTasks: t.Number(),
            totalTasks: t.Number()
        })),
        salespersonWorkload: t.Array(t.Object({
            userId: t.Number(),
            name: t.String(),
            taskCount: t.Number()
        }))
    })
    export type Overview = typeof overview.static

    export const response = t.Object({
        status: t.Literal('success'),
        data: overview,
        message: t.String()
    })
    export type Response = typeof response.static
}