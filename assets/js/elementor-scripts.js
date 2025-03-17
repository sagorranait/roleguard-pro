console.log('Coming Elementor scripts'); // Debugging

// Function to remove the button
function removeAddTemplateButton() {
    const exportTemplateElement = document.querySelector('.elementor-add-template-button');
    if (exportTemplateElement) {
        console.log('Button found and removed'); // Debugging
        exportTemplateElement.remove();
    }
}

// Create a MutationObserver to watch for DOM changes
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        // Check if nodes are added
        if (mutation.addedNodes.length) {
            // Try to remove the button
            removeAddTemplateButton();
        }
    });
});

// Start observing the document body for changes
observer.observe(document.body, { childList: true, subtree: true });

// Initial check in case the button is already in the DOM
removeAddTemplateButton();