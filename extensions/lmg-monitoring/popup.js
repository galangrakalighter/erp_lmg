const $ = (selector) => document.querySelector(selector);
let currentTab;

function status(message = '', error = false) {
    const node = $('#status');
    node.textContent = message;
    node.classList.toggle('error', error);
}

async function message(type, payload = {}) {
    return chrome.runtime.sendMessage({ type, ...payload, tab: currentTab });
}

function renderTabs(tabs) {
    const container = $('#selected-tabs');
    container.innerHTML = '';

    if (!tabs.length) {
        container.innerHTML = '<p class="empty">Belum ada tab yang dipilih.</p>';
        return;
    }

    tabs.forEach((tab) => {
        const row = document.createElement('div');
        row.className = 'tab';
        const name = document.createElement('b');
        name.textContent = tab.title;
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.title = 'Hapus tab';
        remove.textContent = '×';
        remove.addEventListener('click', async () => {
            const response = await message('remove', { tabId: tab.id });
            if (response.ok) renderTabs(response.tabs);
        });
        row.append(name, remove);
        container.append(row);
    });
}

async function refresh() {
    const response = await message('list');
    if (response.ok) renderTabs(response.tabs);
}

async function run(type) {
    status('Memproses…');
    const response = await message(type);
    status(response.ok ? (type === 'share-to-monitoring' ? `${response.count} tab dikirim ke Monitoring ERP.` : 'Tab kerja ditambahkan.') : response.message, !response.ok);
    if (response.ok) await refresh();
}

(async () => {
    [currentTab] = await chrome.tabs.query({ active: true, currentWindow: true });
    $('#current-tab').textContent = currentTab?.title || 'Tab tidak tersedia';
    await refresh();
    $('#add-current').addEventListener('click', () => run('add-current'));
    $('#share').addEventListener('click', () => run('share-to-monitoring'));
    $('#clear-tabs').addEventListener('click', async () => {
        const response = await message('clear');
        if (response.ok) renderTabs([]);
    });
})();
