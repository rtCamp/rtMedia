/**
 * GODAMPlayer Integration Script
 *
 * Initializes GODAMPlayer safely across the site, including:
 * - Initial load
 * - Popups using Magnific Popup
 * - Dynamically added elements (e.g., via BuddyPress activities)
 *
 * Ensures robust handling of null or invalid elements and minimizes the risk of runtime errors.
 */

const safeGODAMPlayer = (element = null) => {
    try {
        if (element) {
            if (element.nodeType === 1 && element.isConnected) {
                GODAMPlayer(element);
            } else {
                GODAMPlayer();
            }
        } else {
            GODAMPlayer();
        }
        return true;
    } catch (error) {
        return false;
    }
};

// Initial load
safeGODAMPlayer();

// Debounced popup initializer
let popupInitTimeout = null;
const initializePopupVideos = () => {
    clearTimeout(popupInitTimeout);
    popupInitTimeout = setTimeout(() => {
        const popupContent = document.querySelector('.mfp-content');
        if (popupContent) {
            const videos = popupContent.querySelectorAll('video');
            if (videos.length > 0) {
                if (!safeGODAMPlayer(popupContent)) {
                    safeGODAMPlayer();
                }
            }
        }
    }, 200);
};

document.addEventListener('DOMContentLoaded', () => {
    safeGODAMPlayer();

    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType === 1) {
                    const isPopup = node.classList?.contains('mfp-content') ||
                                   node.querySelector?.('.mfp-content');
                    const hasVideos = node.tagName === 'VIDEO' ||
                                     node.querySelector?.('video');

                    if (isPopup || (hasVideos && node.closest('.mfp-content'))) {
                        initializePopupVideos();
                    }

                    if (node.classList?.contains('activity')) {
                        setTimeout(() => safeGODAMPlayer(node), 100);
                    }
                }
            }
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    if (typeof $ !== 'undefined' && $.magnificPopup) {
        $(document).on('mfpOpen mfpChange', () => {
            initializePopupVideos();
        });

        $(document).on('mfpOpen', () => {
            setTimeout(initializePopupVideos, 500);
        });
    }
});
