// API Base URL (adjust if hosted on different machine)
const API_URL = 'http://localhost:3000/api';

// DOM Elements - Tabs
const tabBtns = document.querySelectorAll('.tab-btn');
const tabPanes = document.querySelectorAll('.tab-pane');

// DOM Elements - Controls
const btnConnect = document.getElementById('btn-connect');
const btnDisconnect = document.getElementById('btn-disconnect');
const btnRecord = document.getElementById('btn-record');
const btnExport = document.getElementById('btn-export');
const baudRateSelect = document.getElementById('baud-rate-select');

// DOM Elements - Master Data Inputs
const inputOperator = document.getElementById('input-operator');
const inputProduk = document.getElementById('input-produk');

const newOperatorInput = document.getElementById('new-operator-input');
const btnAddOperator = document.getElementById('btn-add-operator');
const deleteOperatorSelect = document.getElementById('delete-operator-select');
const btnDeleteOperator = document.getElementById('btn-delete-operator');

const newProductInput = document.getElementById('new-product-input');
const btnAddProduct = document.getElementById('btn-add-product');
const deleteProductSelect = document.getElementById('delete-product-select');
const btnDeleteProduct = document.getElementById('btn-delete-product');

// DOM Elements - Settings
const inputMin = document.getElementById('setpoint-min');
const inputMax = document.getElementById('setpoint-max');
const interlockCheckbox = document.getElementById('interlock-checkbox');

// DOM Elements - Display
const weightValueEl = document.getElementById('weight-value');
const weightDisplayBlock = document.getElementById('weight-display');
const rawDataValueEl = document.getElementById('raw-data-value');
const statusIndicator = document.getElementById('status-indicator');

// DOM Elements - Records
const filterDateInput = document.getElementById('filter-date');
const recordsTbody = document.getElementById('records-tbody');

// State
let port = null;
let reader = null;
let readableStreamClosed = null;
let keepReading = true;
let currentWeightStr = "0";
let records = []; // Array of records fetched from DB

// --- Initialization & Event Listeners ---

// Initialize default date filter to today
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
filterDateInput.value = `${yyyy}-${mm}-${dd}`;

// Load data on startup
window.addEventListener('DOMContentLoaded', () => {
    loadOperators();
    loadProducts();
    loadRecords();
});

filterDateInput.addEventListener('change', loadRecords);

// Tab Switching
tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));
        
        btn.classList.add('active');
        const targetId = btn.getAttribute('data-target');
        document.getElementById(targetId).classList.add('active');
    });
});

btnConnect.addEventListener('click', connectSerial);
btnDisconnect.addEventListener('click', disconnectSerial);
btnRecord.addEventListener('click', recordWeight);
btnExport.addEventListener('click', exportToCSV);

inputMin.addEventListener('change', evaluateSetPoint);
inputMax.addEventListener('change', evaluateSetPoint);
interlockCheckbox.addEventListener('change', evaluateSetPoint);

if (!('serial' in navigator)) {
    alert("Web Serial API tidak didukung di browser ini. Mohon gunakan Google Chrome atau Microsoft Edge versi terbaru di PC/Laptop.");
    btnConnect.disabled = true;
}

// --- Master Data API Logic ---

async function loadOperators() {
    try {
        const res = await fetch(`${API_URL}/operators`);
        const data = await res.json();
        
        inputOperator.innerHTML = '<option value="">Pilih Operator...</option>';
        deleteOperatorSelect.innerHTML = '<option value="">Hapus Operator...</option>';
        
        data.forEach(op => {
            inputOperator.innerHTML += `<option value="${op.name}">${op.name}</option>`;
            deleteOperatorSelect.innerHTML += `<option value="${op.id}">${op.name}</option>`;
        });
    } catch (e) {
        console.error("Gagal memuat operator", e);
    }
}

async function loadProducts() {
    try {
        const res = await fetch(`${API_URL}/products`);
        const data = await res.json();
        
        inputProduk.innerHTML = '<option value="">Pilih Produk...</option>';
        deleteProductSelect.innerHTML = '<option value="">Hapus Produk...</option>';
        
        data.forEach(prod => {
            inputProduk.innerHTML += `<option value="${prod.name}">${prod.name}</option>`;
            deleteProductSelect.innerHTML += `<option value="${prod.id}">${prod.name}</option>`;
        });
    } catch (e) {
        console.error("Gagal memuat produk", e);
    }
}

btnAddOperator.addEventListener('click', async () => {
    const name = newOperatorInput.value.trim();
    if (!name) return;
    try {
        await fetch(`${API_URL}/operators`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        });
        newOperatorInput.value = '';
        loadOperators();
    } catch (e) { alert("Gagal menambah operator"); }
});

btnDeleteOperator.addEventListener('click', async () => {
    const id = deleteOperatorSelect.value;
    if (!id) return;
    try {
        await fetch(`${API_URL}/operators/${id}`, { method: 'DELETE' });
        loadOperators();
    } catch (e) { alert("Gagal menghapus operator"); }
});

btnAddProduct.addEventListener('click', async () => {
    const name = newProductInput.value.trim();
    if (!name) return;
    try {
        await fetch(`${API_URL}/products`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        });
        newProductInput.value = '';
        loadProducts();
    } catch (e) { alert("Gagal menambah produk"); }
});

btnDeleteProduct.addEventListener('click', async () => {
    const id = deleteProductSelect.value;
    if (!id) return;
    try {
        await fetch(`${API_URL}/products/${id}`, { method: 'DELETE' });
        loadProducts();
    } catch (e) { alert("Gagal menghapus produk"); }
});


// --- Scale Logic ---

function parseWeightFromRawData(rawData) {
    const match = rawData.match(/-?\d+\.?\d*/);
    if (match) return match[0];
    return null;
}

function updateWeightDisplay(weightStr, rawData) {
    if (weightStr !== null) {
        currentWeightStr = weightStr;
        weightValueEl.textContent = weightStr;
        if (port) evaluateSetPoint();
    }
    if (rawData) rawDataValueEl.textContent = JSON.stringify(rawData);
}

function evaluateSetPoint() {
    if (!port) return;
    
    const weight = parseFloat(currentWeightStr);
    const minTarget = parseFloat(inputMin.value) || 0;
    const maxTarget = parseFloat(inputMax.value) || 0;
    
    weightDisplayBlock.className = 'weight-display state-connected';
    statusIndicator.className = 'status-indicator';
    
    if (weight < minTarget) {
        weightDisplayBlock.classList.add('state-under');
        statusIndicator.classList.add('under');
        statusIndicator.textContent = "UNDER (KURANG)";
    } else if (weight > maxTarget) {
        weightDisplayBlock.classList.add('state-over');
        statusIndicator.classList.add('over');
        statusIndicator.textContent = "OVER (LEBIH)";
    } else {
        weightDisplayBlock.classList.add('state-accept');
        statusIndicator.classList.add('accept');
        statusIndicator.textContent = "ACCEPT (SESUAI TARGET)";
    }

    if (interlockCheckbox.checked) {
        const isAccept = statusIndicator.classList.contains('accept');
        btnRecord.disabled = !isAccept;
    } else {
        btnRecord.disabled = false;
    }
}

function getStatusBadge(statusText) {
    if (statusText.includes("UNDER")) return `<span class="badge badge-under">UNDER</span>`;
    if (statusText.includes("OVER")) return `<span class="badge badge-over">OVER</span>`;
    if (statusText.includes("ACCEPT")) return `<span class="badge badge-accept">ACCEPT</span>`;
    return `<span class="badge">${statusText}</span>`;
}

async function connectSerial() {
    try {
        const baudRate = parseInt(baudRateSelect.value, 10);
        
        port = await navigator.serial.requestPort();
        await port.open({ baudRate: baudRate });
        
        btnConnect.disabled = true;
        btnDisconnect.disabled = false;
        baudRateSelect.disabled = true;
        
        weightDisplayBlock.classList.add('state-connected');
        statusIndicator.textContent = "MENGAMBIL DATA...";
        
        evaluateSetPoint();
        
        keepReading = true;
        readLoop();
        
    } catch (error) {
        console.error("Gagal terhubung ke port:", error);
        if (error.name !== 'NotFoundError') {
            alert(`Gagal terhubung: ${error.message}`);
        }
    }
}

async function readLoop() {
    const textDecoder = new TextDecoderStream();
    readableStreamClosed = port.readable.pipeTo(textDecoder.writable);
    reader = textDecoder.readable.getReader();

    let buffer = "";
    try {
        while (keepReading) {
            const { value, done } = await reader.read();
            if (done) break;
            
            if (value) {
                buffer += value;
                let eolIndex;
                while ((eolIndex = buffer.indexOf('\n')) >= 0) {
                    const line = buffer.slice(0, eolIndex).trim();
                    buffer = buffer.slice(eolIndex + 1);
                    if (line.length > 0) {
                        const parsedWeight = parseWeightFromRawData(line);
                        updateWeightDisplay(parsedWeight, line);
                    }
                }
            }
        }
    } catch (error) {
        console.error("Error saat membaca data:", error);
    } finally {
        reader.releaseLock();
    }
}

async function disconnectSerial() {
    keepReading = false;
    
    if (reader) await reader.cancel();
    
    if (readableStreamClosed) {
        try { await readableStreamClosed; } catch (e) {}
    }
    
    if (port) await port.close();

    btnConnect.disabled = false;
    btnDisconnect.disabled = true;
    btnRecord.disabled = true;
    baudRateSelect.disabled = false;
    
    weightDisplayBlock.className = 'weight-display';
    statusIndicator.className = 'status-indicator';
    statusIndicator.textContent = "STANDBY";
    
    port = null;
    reader = null;
    
    updateWeightDisplay("0", "Terputus");
}

// --- Recording & History Logic ---

async function recordWeight() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID');
    const dateString = now.toLocaleDateString('id-ID');
    const fullDateTime = `${dateString} ${timeString}`;

    let currentStatus = "UNKNOWN";
    if (statusIndicator.classList.contains('under')) currentStatus = "UNDER";
    else if (statusIndicator.classList.contains('over')) currentStatus = "OVER";
    else if (statusIndicator.classList.contains('accept')) currentStatus = "ACCEPT";

    const operator = inputOperator.value || "-";
    const product = inputProduk.value || "-";

    const data = {
        timestamp: fullDateTime,
        operator: operator,
        product: product,
        weight: currentWeightStr,
        status: currentStatus
    };

    try {
        await fetch(`${API_URL}/records`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        // Visual feedback
        const originalText = btnRecord.innerHTML;
        btnRecord.innerHTML = "TERSIPAN ✓";
        btnRecord.classList.remove('btn-success');
        btnRecord.style.background = 'var(--primary-color)';
        setTimeout(() => {
            btnRecord.innerHTML = originalText;
            btnRecord.style.background = '';
            btnRecord.classList.add('btn-success');
        }, 1000);

        loadRecords();
    } catch (e) {
        alert("Gagal menyimpan data ke database");
    }
}

async function loadRecords() {
    const dateFilter = filterDateInput.value;
    try {
        const res = await fetch(`${API_URL}/records?date=${dateFilter}`);
        records = await res.json();
        renderRecordsTable();
    } catch (e) {
        console.error("Gagal memuat riwayat", e);
    }
}

async function deleteRecord(id) {
    if (!confirm("Hapus data penimbangan ini?")) return;
    try {
        await fetch(`${API_URL}/records/${id}`, { method: 'DELETE' });
        loadRecords();
    } catch (e) {
        alert("Gagal menghapus riwayat");
    }
}

function renderRecordsTable() {
    recordsTbody.innerHTML = '';
    
    if (records.length === 0) {
        recordsTbody.innerHTML = '<tr class="empty-row"><td colspan="7">Belum ada data terekam pada tanggal ini.</td></tr>';
        btnExport.disabled = true;
        return;
    }
    
    btnExport.disabled = false;
    
    records.forEach((record, idx) => {
        // Records are already ordered DESC from DB
        const displayNo = records.length - idx;
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${displayNo}</td>
            <td>${record.timestamp}</td>
            <td>${record.operator}</td>
            <td>${record.product}</td>
            <td><strong>${record.weight}</strong></td>
            <td>${getStatusBadge(record.status)}</td>
            <td>
                <button class="btn-delete" data-id="${record.id}">Hapus</button>
            </td>
        `;
        recordsTbody.appendChild(tr);
    });
    
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = parseInt(e.target.getAttribute('data-id'), 10);
            deleteRecord(id);
        });
    });
}

function exportToCSV() {
    if (records.length === 0) return;
    
    let csvContent = "No,Waktu,Operator,Produk,Berat (kg),Status\n";
    
    // Sort chronological for CSV if desired, or keep as is. Let's keep as is but reverse to oldest first
    const csvRecords = [...records].reverse();
    
    csvRecords.forEach((record, index) => {
        const safeOperator = `"${record.operator.replace(/"/g, '""')}"`;
        const safeProduct = `"${record.product.replace(/"/g, '""')}"`;
        csvContent += `${index + 1},${record.timestamp},${safeOperator},${safeProduct},${record.weight},${record.status}\n`;
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    
    const dateStr = filterDateInput.value;
    
    link.setAttribute("href", url);
    link.setAttribute("download", `Data_Timbangan_${dateStr}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
