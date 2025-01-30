require('dotenv').config(); // Load environment variables from .env
const mysql = require('mysql');

// Create a connection to the database
const connection = mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
});

// Connect to the database
connection.connect((err) => {
    if (err) {
        console.error('Error connecting to the database: ' + err.stack);
        return;
    }
    console.log('Connected to the database as ID ' + connection.threadId);
});

// Query the database
connection.query('SELECT * FROM users', (error, results) => {
    if (error) {
        console.error('Error executing query: ' + error);
        return;
    }
    console.log('Query Results:', results);
});

// Close the connection
connection.end();
