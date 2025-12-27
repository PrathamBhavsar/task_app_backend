import { app } from './app'

const port = process.env.PORT || 3000
const host = process.env.HOST || '0.0.0.0'

app.listen({ port: Number(port), hostname: host })

console.log(`🦊 Interior Design API running at http://${host}:${port}`)
console.log(`📚 API endpoints available at http://${host}:${port}/api/v1`)
