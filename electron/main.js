'use strict';

const { app, BrowserWindow, ipcMain, screen, shell } = require('electron');
const path = require('path');

// ─── Configuration ────────────────────────────────────────────────────────────
// The URL of the Laravel dev/production server.
// Change this to your production URL when building for distribution.
const APP_URL = process.env.ELECTRON_APP_URL || 'https://local.chillypos.io';

// ─── Window references (prevent GC) ───────────────────────────────────────────
let mainWindow = null;
let customerViewWindow = null;

// ─── Main window ──────────────────────────────────────────────────────────────
function createMainWindow() {
    const primaryDisplay = screen.getPrimaryDisplay();
    const { width, height } = primaryDisplay.workAreaSize;

    mainWindow = new BrowserWindow({
        x: primaryDisplay.bounds.x,
        y: primaryDisplay.bounds.y,
        width,
        height,
        show: false, // show after ready-to-show to avoid white flash
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            contextIsolation: true,
            nodeIntegration: false,
            sandbox: false,
        },
    });

    mainWindow.loadURL(`${APP_URL}/`);

    mainWindow.once('ready-to-show', () => {
        mainWindow.show();
        mainWindow.maximize();
    });

    mainWindow.on('closed', () => {
        if (customerViewWindow && !customerViewWindow.isDestroyed()) {
            customerViewWindow.close();
        }
        mainWindow = null;
    });

    // Open DevTools only in development
    if (process.env.NODE_ENV === 'development') {
        mainWindow.webContents.openDevTools();
    }
}

// ─── Customer view window ─────────────────────────────────────────────────────
function createCustomerViewWindow() {
    // Get all connected displays
    const allDisplays = screen.getAllDisplays();
    const primaryDisplay = screen.getPrimaryDisplay();

    // Prefer a non-primary display; fall back to the primary if only one screen
    const targetDisplay =
        allDisplays.find(d => d.id !== primaryDisplay.id) || primaryDisplay;

    // Close any existing customer view window first so we always reopen
    // at the correct position (a hidden window ignores position updates)
    if (customerViewWindow && !customerViewWindow.isDestroyed()) {
        customerViewWindow.close();
        customerViewWindow = null;
    }

    const { x, y, width, height } = targetDisplay.bounds;

    console.log(`[CustomerView] Opening on display id=${targetDisplay.id} isSecondary=${targetDisplay.id !== primaryDisplay.id} bounds=`, { x, y, width, height });

    customerViewWindow = new BrowserWindow({
        x,
        y,
        width,
        height,
        fullscreen: true,   // set in constructor alongside x/y so Electron places
        frame: false,       // it on the correct display before going fullscreen
        show: false,
        backgroundColor: '#f3f4f6',
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            contextIsolation: true,
            nodeIntegration: false,
            sandbox: false,
        },
    });

    customerViewWindow.loadURL(`${APP_URL}/admin/pos-customer-view`);

    // Capture local reference so the callbacks are not affected by the outer
    // variable being reassigned or set to null by a concurrent close event.
    const win = customerViewWindow;

    win.once('ready-to-show', () => {
        if (!win.isDestroyed()) win.show();
    });

    win.on('closed', () => {
        // Only clear the outer reference if it still points to this window.
        if (customerViewWindow === win) customerViewWindow = null;
    });

    return {
        display: targetDisplay.id,
        isSecondary: targetDisplay.id !== primaryDisplay.id,
        bounds: targetDisplay.bounds,
    };
}

// ─── IPC handlers ─────────────────────────────────────────────────────────────

// Renderer → Main: open the customer view on the second screen
ipcMain.handle('open-customer-view', () => {
    console.log('[IPC] open-customer-view received — creating customer view window');
    return createCustomerViewWindow();
});

// Renderer → Main: close the customer view
ipcMain.handle('close-customer-view', () => {
    if (customerViewWindow && !customerViewWindow.isDestroyed()) {
        customerViewWindow.close();
        customerViewWindow = null;
    }
});

// Renderer → Main: query whether customer view is currently open
ipcMain.handle('customer-view-status', () => {
    return {
        isOpen: !!customerViewWindow && !customerViewWindow.isDestroyed(),
    };
});

// ─── App lifecycle ────────────────────────────────────────────────────────────
app.whenReady().then(() => {
    createMainWindow();

    app.on('activate', () => {
        // macOS: re-create window when dock icon is clicked and no windows are open
        if (BrowserWindow.getAllWindows().length === 0) {
            createMainWindow();
        }
    });
});

app.on('window-all-closed', () => {
    // On macOS it is common to stay active until Cmd+Q
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
