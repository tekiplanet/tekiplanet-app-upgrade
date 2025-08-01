<div id="notification" 
     class="fixed right-4 top-4 flex items-center p-4 mb-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full"
     role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
    </div>
    <div class="ml-3 text-sm font-normal" id="notificationMessage"></div>
    <button type="button" 
            class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8"
            onclick="hideNotification()">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>

<script>
function showNotification(title, message, type = 'success') {
    const notification = document.getElementById('notification');
    const messageElement = document.getElementById('notificationMessage');
    
    // Set colors based on type
    if (type === 'success') {
        notification.className = 'fixed right-4 top-4 flex items-center p-4 mb-4 text-green-800 bg-green-50 rounded-lg shadow-lg z-50 transition-all duration-300 transform';
    } else {
        notification.className = 'fixed right-4 top-4 flex items-center p-4 mb-4 text-red-800 bg-red-50 rounded-lg shadow-lg z-50 transition-all duration-300 transform';
    }
    
    messageElement.textContent = message;
    
    // Show notification
    notification.style.transform = 'translateX(0)';
    
    // Hide after 5 seconds
    setTimeout(hideNotification, 5000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.style.transform = 'translateX(110%)';
}
</script> 