// Navigation helper functions
function showServerSelector(feature) {
    // For now, redirect to the feature management page
    // In the future, this could show a server selector modal
    let route = '';
    switch(feature) {
        case 'databases':
            route = '/databases';
            break;
        case 'queue-workers':
            route = '/queue-workers';
            break;
        default:
            route = '/servers';
    }
    
    window.location.href = route;
}

function showSiteSelector(feature) {
    // For now, redirect to the feature management page
    // In the future, this could show a site selector modal
    let route = '';
    switch(feature) {
        case 'ssl':
            route = '/ssl-certificates';
            break;
        default:
            route = '/sites';
    }
    
    window.location.href = route;
}

function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}

// Close modal on Esc key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && !document.getElementById('modal').classList.contains('hidden')) {
        closeModal();
    }
});