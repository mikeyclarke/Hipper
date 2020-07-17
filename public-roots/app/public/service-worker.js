self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('message', (event) => {
    if (event.data.name && event.data.name === 'asset_digest_update') {
        
    }
});
