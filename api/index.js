require('dotenv').config();
const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const app = express();

app.use(cors());
app.use(express.json());

// Create MySQL connection
const db = mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
});

// Connect to MySQL
db.connect((err) => {
    if (err) {
        console.error('Database connection failed:', err);
        return;
    }
    console.log('Connected to MySQL database');
});

// Default Route
app.get('/', (req, res) => {
    res.send('Hello, World! This is running on Vercel 🚀');
});

// API Route to Check MySQL Connection
app.get('/api/check-db', (req, res) => {
    db.query('SELECT NOW() AS time', (err, result) => {
        if (err) {
            return res.status(500).json({ error: 'Database query error', details: err });
        }
        res.json({ message: 'Database connected successfully', time: result[0].time });
    });
});

// Export as Vercel Serverless Function
module.exports = app;
