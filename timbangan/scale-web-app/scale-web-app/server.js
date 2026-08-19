import express from 'express';
import sqlite3 from 'sqlite3';
import cors from 'cors';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

// Initialize SQLite Database
const dbPath = join(__dirname, 'database.sqlite');
const db = new sqlite3.Database(dbPath, (err) => {
    if (err) {
        console.error("Error connecting to database:", err);
    } else {
        console.log("Connected to SQLite database.");
        initDb();
    }
});

function initDb() {
    db.serialize(() => {
        // Operators Table
        db.run(`CREATE TABLE IF NOT EXISTS operators (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL
        )`);

        // Products Table
        db.run(`CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL
        )`);

        // Records Table
        db.run(`CREATE TABLE IF NOT EXISTS records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp TEXT NOT NULL,
            date_str TEXT NOT NULL,
            operator TEXT NOT NULL,
            product TEXT NOT NULL,
            weight TEXT NOT NULL,
            status TEXT NOT NULL
        )`);
    });
}

// --- API Endpoints ---

// Get all operators
app.get('/api/operators', (req, res) => {
    db.all("SELECT * FROM operators ORDER BY name ASC", [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

// Add operator
app.post('/api/operators', (req, res) => {
    const { name } = req.body;
    if (!name) return res.status(400).json({ error: "Name is required" });
    db.run("INSERT INTO operators (name) VALUES (?)", [name], function(err) {
        if (err) return res.status(400).json({ error: err.message });
        res.json({ id: this.lastID, name });
    });
});

// Delete operator
app.delete('/api/operators/:id', (req, res) => {
    db.run("DELETE FROM operators WHERE id = ?", req.params.id, function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ deleted: this.changes });
    });
});

// Get all products
app.get('/api/products', (req, res) => {
    db.all("SELECT * FROM products ORDER BY name ASC", [], (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

// Add product
app.post('/api/products', (req, res) => {
    const { name } = req.body;
    if (!name) return res.status(400).json({ error: "Name is required" });
    db.run("INSERT INTO products (name) VALUES (?)", [name], function(err) {
        if (err) return res.status(400).json({ error: err.message });
        res.json({ id: this.lastID, name });
    });
});

// Delete product
app.delete('/api/products/:id', (req, res) => {
    db.run("DELETE FROM products WHERE id = ?", req.params.id, function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ deleted: this.changes });
    });
});

// Get records (with optional date filter YYYY-MM-DD)
app.get('/api/records', (req, res) => {
    const { date } = req.query;
    let query = "SELECT * FROM records";
    let params = [];
    
    if (date) {
        query += " WHERE date_str = ?";
        params.push(date);
    }
    
    query += " ORDER BY id DESC"; // Newest first
    
    db.all(query, params, (err, rows) => {
        if (err) return res.status(500).json({ error: err.message });
        res.json(rows);
    });
});

// Add record
app.post('/api/records', (req, res) => {
    const { timestamp, operator, product, weight, status } = req.body;
    
    // date_str formatted as YYYY-MM-DD for easy querying
    const dateObj = new Date();
    // Using local time string for the date part to match Indonesian timezone typically used
    const yyyy = dateObj.getFullYear();
    const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
    const dd = String(dateObj.getDate()).padStart(2, '0');
    const date_str = `${yyyy}-${mm}-${dd}`;

    db.run(
        "INSERT INTO records (timestamp, date_str, operator, product, weight, status) VALUES (?, ?, ?, ?, ?, ?)",
        [timestamp, date_str, operator || '-', product || '-', weight, status],
        function(err) {
            if (err) return res.status(500).json({ error: err.message });
            res.json({ id: this.lastID });
        }
    );
});

// Delete record
app.delete('/api/records/:id', (req, res) => {
    db.run("DELETE FROM records WHERE id = ?", req.params.id, function(err) {
        if (err) return res.status(500).json({ error: err.message });
        res.json({ deleted: this.changes });
    });
});

app.listen(port, () => {
    console.log(`Backend server running at http://localhost:${port}`);
});
