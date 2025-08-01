<div id="notification" 
     class="fixed top-4 right-4 flex items-center p-4 mb-4 rounded-lg shadow-md transition-opacity duration-300 opacity-0 pointer-events-none z-50"
     role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
        <svg id="notificationIcon" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
        </svg>
    </div>
    <div class="ml-3 text-sm font-medium flex-1 whitespace-normal break-words" id="notificationMessage"></div>
    <button type="button" 
            class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8 hover:bg-gray-100 dark:hover:bg-gray-700" 
            onclick="hideNotification()">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
        </svg>
    </button>
</div>

<script>
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const messageElement = document.getElementById('notificationMessage');
    
    // console.log('Message received:', message);
    
    messageElement.style.display = 'block';
    messageElement.style.visibility = 'visible';
    messageElement.textContent = message;
    
    // console.log('Message element content:', messageElement.textContent);
    // console.log('Message element HTML:', messageElement.innerHTML);
    // console.log('Message element style:', window.getComputedStyle(messageElement));
    
    if (type === 'success') {
        notification.className = 'fixed top-4 right-4 flex items-center p-4 mb-4 text-green-800 bg-green-50 dark:bg-gray-800 dark:text-green-400 rounded-lg shadow-md transition-opacity duration-300 z-50 min-w-[300px]';
        notification.querySelector('.inline-flex').className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200';
    } else {
        notification.className = 'fixed top-4 right-4 flex items-center p-4 mb-4 text-red-800 bg-red-50 dark:bg-gray-800 dark:text-red-400 rounded-lg shadow-md transition-opacity duration-300 z-50 min-w-[300px]';
        notification.querySelector('.inline-flex').className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200';
    }

    notification.style.opacity = '1';
    notification.style.pointerEvents = 'auto';

    setTimeout(hideNotification, 5000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.style.opacity = '0';
    notification.style.pointerEvents = 'none';
}
</script> 