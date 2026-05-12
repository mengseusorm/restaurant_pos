'use strict';

/**
 * Electron Preload Script
 *
 * Exposes a safe, minimal API to the renderer (Vue) via contextBridge.
 * contextIsolation=true keeps Node/Electron APIs out of the page's JS scope.
 */

const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    /** Flag so Vue components can detect they are running inside Electron */
    isElectron: true,

    /**
     * Open the customer view window on the secondary screen.
     * Resolves with { display, isSecondary, bounds } from the main process.
     */
    openCustomerView: () => ipcRenderer.invoke('open-customer-view'),

    /** Close the customer view window. */
    closeCustomerView: () => ipcRenderer.invoke('close-customer-view'),

    /** Check whether the customer view is currently open. */
    customerViewStatus: () => ipcRenderer.invoke('customer-view-status'),
});
