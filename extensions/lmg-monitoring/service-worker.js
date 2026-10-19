const SELECTED_TABS_KEY = 'selected-work-tabs';

async function selectedTabs() {
    return (await chrome.storage.session.get(SELECTED_TABS_KEY))[SELECTED_TABS_KEY] || [];
}

async function saveSelectedTabs(tabs) {
    await chrome.storage.session.set({ [SELECTED_TABS_KEY]: tabs });
}

async function deliverSources(monitoringTabId) {
    const selected = await selectedTabs();
    const sources = [];
    const unavailable = [];

    for (const selectedTab of selected) {
        try {
            const streamId = await chrome.tabCapture.getMediaStreamId({
                targetTabId: selectedTab.id,
                consumerTabId: monitoringTabId,
            });

            sources.push({ streamId, title: selectedTab.title || 'Tab kerja' });
        } catch (_) {
            unavailable.push(selectedTab.title || 'Tab kerja');
        }
    }

    if (!sources.length) {
        throw new Error('Tidak ada tab kerja yang dapat dibagikan. Buka setiap tab kerja lalu klik icon extension untuk menambahkannya.');
    }

    await chrome.scripting.executeScript({
        target: { tabId: monitoringTabId },
        world: 'MAIN',
        args: [{ sources }],
        func: (payload) => window.dispatchEvent(new CustomEvent('lmg-monitoring-extension-sources', { detail: payload })),
    });

    return { count: sources.length, unavailable };
}

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
    (async () => {
        const tab = message.tab || sender.tab;

        if (message.type === 'list') {
            sendResponse({ ok: true, tabs: await selectedTabs() });
            return;
        }

        if (message.type === 'add-current') {
            if (!tab?.id || !/^https?:/i.test(tab.url || '')) {
                throw new Error('Buka tab website kerja terlebih dahulu.');
            }

            const tabs = await selectedTabs();
            const item = { id: tab.id, title: tab.title || 'Tab kerja', url: tab.url };
            await saveSelectedTabs([...tabs.filter((entry) => entry.id !== tab.id), item]);
            sendResponse({ ok: true, tabs: await selectedTabs() });
            return;
        }

        if (message.type === 'remove') {
            await saveSelectedTabs((await selectedTabs()).filter((entry) => entry.id !== message.tabId));
            sendResponse({ ok: true, tabs: await selectedTabs() });
            return;
        }

        if (message.type === 'clear') {
            await saveSelectedTabs([]);
            sendResponse({ ok: true, tabs: [] });
            return;
        }

        if (message.type === 'share-to-monitoring') {
            if (!tab?.id || !/\/monitoring(?:[/?#]|$)/.test(tab.url || '')) {
                throw new Error('Buka halaman Monitoring ERP, lalu klik Bagikan tab terpilih.');
            }

            sendResponse({ ok: true, ...(await deliverSources(tab.id)) });
            return;
        }

        throw new Error('Permintaan extension tidak dikenal.');
    })().catch((error) => sendResponse({ ok: false, message: error.message || 'Extension tidak dapat memproses permintaan.' }));

    return true;
});

chrome.tabs.onRemoved.addListener(async (tabId) => {
    await saveSelectedTabs((await selectedTabs()).filter((entry) => entry.id !== tabId));
});
