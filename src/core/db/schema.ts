import { pgTable, serial, varchar, timestamp, numeric, integer, boolean, text, uuid, pgEnum, date, inet, jsonb } from 'drizzle-orm/pg-core'
import { relations } from 'drizzle-orm'

export const userRoleEnum = pgEnum('user_role_enum', ['admin', 'salesperson', 'agent'])
export const taskStatusEnum = pgEnum('task_status_enum', ['Created', 'Measurement: Done', 'Quote: Done', 'Approved', 'In Progress', 'Completed', 'Cancelled'])
export const taskPriorityEnum = pgEnum('task_priority_enum', ['Low', 'Medium', 'High', 'Urgent'])
export const billStatusEnum = pgEnum('bill_status_enum', ['Pending', 'Paid', 'Partial', 'Overdue'])

export const users = pgTable('users', {
    userId: serial('user_id').primaryKey(),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    name: varchar('name', { length: 255 }).notNull(),
    email: varchar('email', { length: 255 }).unique(),
    password: varchar('password', { length: 255 }),
    contactNo: varchar('contact_no', { length: 50 }),
    address: text('address'),
    role: userRoleEnum('role').notNull().default('agent'),
    profileBgColor: varchar('profile_bg_color', { length: 20 }).default('#FF5733'),
    isActive: boolean('is_active').default(true).notNull(),
    lastLoginAt: timestamp('last_login_at', { withTimezone: true }),
    emailVerified: boolean('email_verified').default(false).notNull(),
    phoneVerified: boolean('phone_verified').default(false).notNull(),
})

export const userTokens = pgTable('user_tokens', {
    tokenId: uuid('token_id').primaryKey().defaultRandom(),
    userId: integer('user_id').notNull().references(() => users.userId, { onDelete: 'cascade' }),
    accessToken: text('access_token').notNull(),
    refreshToken: text('refresh_token').notNull().unique(),
    accessTokenExpiresAt: timestamp('access_token_expires_at', { withTimezone: true }).notNull(),
    refreshTokenExpiresAt: timestamp('refresh_token_expires_at', { withTimezone: true }).notNull(),
    isRevoked: boolean('is_revoked').default(false).notNull(),
    ipAddress: inet('ip_address'),
    userAgent: text('user_agent'),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    updatedAt: timestamp('updated_at', { withTimezone: true }).defaultNow().notNull(),
})

export const userSessions = pgTable('user_sessions', {
    sessionId: uuid('session_id').primaryKey().defaultRandom(),
    userId: integer('user_id').notNull().references(() => users.userId, { onDelete: 'cascade' }),
    tokenId: uuid('token_id').references(() => userTokens.tokenId, { onDelete: 'set null' }),
    loginAt: timestamp('login_at', { withTimezone: true }).defaultNow().notNull(),
    logoutAt: timestamp('logout_at', { withTimezone: true }),
    ipAddress: inet('ip_address'),
    userAgent: text('user_agent'),
    deviceInfo: jsonb('device_info'),
    isActive: boolean('is_active').default(true).notNull(),
})

export const clients = pgTable('clients', {
    clientId: serial('client_id').primaryKey(),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    name: varchar('name', { length: 255 }).notNull(),
    contactNo: varchar('contact_no', { length: 50 }),
    email: varchar('email', { length: 255 }),
    address: text('address'),
})

export const designers = pgTable('designers', {
    designerId: serial('designer_id').primaryKey(),
    name: varchar('name', { length: 255 }).notNull(),
    firmName: varchar('firm_name', { length: 255 }),
    contactNo: varchar('contact_no', { length: 50 }),
    address: text('address'),
    profileBgColor: varchar('profile_bg_color', { length: 20 }).default('#FF5733'),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
})

export const serviceMaster = pgTable('service_master', {
    serviceMasterId: serial('service_master_id').primaryKey(),
    name: varchar('name', { length: 255 }).notNull(),
    description: text('description'),
    defaultUnitPrice: numeric('default_unit_price', { precision: 10, scale: 2 }).notNull().default('0.00'),
    unit: varchar('unit', { length: 50 }).default('unit'),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
})

export const tasks = pgTable('tasks', {
    taskId: serial('task_id').primaryKey(),
    dealNo: varchar('deal_no', { length: 100 }).unique(),
    name: varchar('name', { length: 255 }).notNull(),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    startDate: date('start_date'),
    dueDate: date('due_date'),
    priority: taskPriorityEnum('priority').default('Medium'),
    remarks: text('remarks'),
    status: taskStatusEnum('status').default('Created'),
    createdBy: integer('created_by').references(() => users.userId, { onDelete: 'set null' }),
    clientId: integer('client_id').references(() => clients.clientId, { onDelete: 'set null' }),
    designerId: integer('designer_id').references(() => designers.designerId, { onDelete: 'set null' }),
    agencyId: integer('agency_id').references(() => users.userId, { onDelete: 'set null' }),
})

export const taskUsers = pgTable('task_users', {
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    userId: integer('user_id').notNull().references(() => users.userId, { onDelete: 'cascade' }),
    roleInTask: varchar('role_in_task', { length: 50 }),
})

export const measurements = pgTable('measurements', {
    measurementId: serial('measurement_id').primaryKey(),
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    location: varchar('location', { length: 255 }),
    width: numeric('width', { precision: 10, scale: 2 }),
    height: numeric('height', { precision: 10, scale: 2 }),
    area: numeric('area', { precision: 10, scale: 2 }).notNull().default('0.00'),
    unit: varchar('unit', { length: 10 }).notNull().default('m'),
    quantity: integer('quantity').notNull().default(1),
    unitPrice: numeric('unit_price', { precision: 10, scale: 2 }).notNull().default('0.00'),
    discount: numeric('discount', { precision: 10, scale: 2 }).notNull().default('0.00'),
    totalPrice: numeric('total_price', { precision: 10, scale: 2 }).notNull().default('0.00'),
    notes: text('notes'),
})

export const taskServices = pgTable('task_services', {
    taskServiceId: serial('task_service_id').primaryKey(),
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    serviceMasterId: integer('service_master_id').notNull().references(() => serviceMaster.serviceMasterId, { onDelete: 'restrict' }),
    quantity: integer('quantity').notNull(),
    unitPrice: numeric('unit_price', { precision: 10, scale: 2 }).notNull(),
    totalAmount: numeric('total_amount', { precision: 10, scale: 2 }).notNull(),
})

export const taskMessages = pgTable('task_messages', {
    messageId: serial('message_id').primaryKey(),
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    message: text('message').notNull(),
    userId: integer('user_id').notNull().references(() => users.userId, { onDelete: 'cascade' }),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
})

export const taskTimelines = pgTable('task_timelines', {
    timelineId: serial('timeline_id').primaryKey(),
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    status: varchar('status', { length: 50 }),
    userId: integer('user_id').notNull().references(() => users.userId, { onDelete: 'restrict' }),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
})

export const taskAttachments = pgTable('task_attachments', {
    attachmentId: serial('attachment_id').primaryKey(),
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    name: varchar('name', { length: 255 }).notNull(),
    attachmentUrl: text('attachment_url').notNull(),
    uploadedBy: integer('uploaded_by').notNull().references(() => users.userId, { onDelete: 'restrict' }),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
})

export const quotes = pgTable('quotes', {
    quoteId: serial('quote_id').primaryKey(),
    taskId: integer('task_id').notNull().references(() => tasks.taskId, { onDelete: 'cascade' }),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    subtotal: numeric('subtotal', { precision: 10, scale: 2 }).notNull(),
    tax: numeric('tax', { precision: 10, scale: 2 }).notNull(),
    total: numeric('total', { precision: 10, scale: 2 }).notNull(),
    notes: text('notes'),
})

export const bills = pgTable('bills', {
    billId: serial('bill_id').primaryKey(),
    taskId: integer('task_id').notNull().unique().references(() => tasks.taskId, { onDelete: 'cascade' }),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    dueDate: date('due_date'),
    subtotal: numeric('subtotal', { precision: 10, scale: 2 }).notNull(),
    tax: numeric('tax', { precision: 10, scale: 2 }).notNull(),
    total: numeric('total', { precision: 10, scale: 2 }).notNull(),
    additionalNotes: text('additional_notes'),
    status: billStatusEnum('status').notNull().default('Pending'),
})

export const config = pgTable('config', {
    key: varchar('key', { length: 100 }).primaryKey(),
    value: varchar('value', { length: 255 }).notNull(),
})

export const jobQueue = pgTable('job_queue', {
    jobId: serial('job_id').primaryKey(),
    jobType: varchar('job_type', { length: 100 }).notNull(),
    payload: jsonb('payload'),
    status: varchar('status', { length: 50 }).default('pending'),
    createdAt: timestamp('created_at', { withTimezone: true }).defaultNow().notNull(),
    processedAt: timestamp('processed_at', { withTimezone: true }),
    errorMessage: text('error_message'),
})
