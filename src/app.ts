import { Elysia } from 'elysia'
import { cors } from '@elysiajs/cors'
import { swagger } from '@elysiajs/swagger'
import { errorHandler } from '@/core/middleware/errorHandler'
import {
    authController,
    userController,
    clientController,
    designerController,
    serviceMasterController,
    taskController,
    measurementController,
    taskServiceController,
    taskMessageController,
    timelineController,
    quoteController,
    billController,
    configController
} from '@/modules'

export const app = new Elysia()
    .use(cors())
    .use(swagger({
        path: '/swagger',
        documentation: {
            info: {
                title: 'Interior Design API',
                version: '1.0.0',
                description: 'API for Interior Design & Task Management System'
            },
            tags: [
                { name: 'Auth', description: 'Authentication endpoints' },
                { name: 'Users', description: 'User management' },
                { name: 'Clients', description: 'Client management' },
                { name: 'Designers', description: 'Designer management' },
                { name: 'Tasks', description: 'Task/Project management' },
                { name: 'Quotes', description: 'Quote management' },
                { name: 'Bills', description: 'Bill management' }
            ]
        }
    }))
    .use(errorHandler())
    .get('/', () => ({ status: 'success', message: 'Interior Design API v1.0', timestamp: new Date().toISOString() }))
    .get('/health', () => ({ status: 'healthy', timestamp: new Date().toISOString() }))
    .group('/api/v1', (app) =>
        app
            .get('/', () => ({
                status: 'success',
                message: 'Interior Design API v1',
                endpoints: ['/auth', '/users', '/clients', '/designers', '/service-master', '/tasks', '/measurements', '/task-services', '/task-messages', '/timelines', '/quotes', '/bills', '/config']
            }))
            .use(authController)
            .use(userController)
            .use(clientController)
            .use(designerController)
            .use(serviceMasterController)
            .use(taskController)
            .use(measurementController)
            .use(taskServiceController)
            .use(taskMessageController)
            .use(timelineController)
            .use(quoteController)
            .use(billController)
            .use(configController)
    )
