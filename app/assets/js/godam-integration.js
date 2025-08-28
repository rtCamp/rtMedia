/**
 * GODAMPlayer Integration Script
 *
 * Safely initializes GODAMPlayer across the site, including:
 * - Initial load with retry mechanism
 * - Popups using Magnific Popup
 * - Dynamically added elements (BuddyPress activities, comments, replies)
 * - Robust error handling and performance optimization
 */

(function() {
  'use strict';

  // Configuration
  const CONFIG = {
    DEBOUNCE_DELAY: 200,
    RETRY_DELAY: 100,
    MAX_RETRIES: 3,
    INIT_DELAY: 1000,
    POPUP_DELAY: 500
  };

  // State tracking
  let isInitialized = false;

  // Helper: Removes shimmer class from video containers (debounced)
  const removeLoadingShimmer = (() => {
    let timeoutId;
    return () => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => {
        try {
          const videoContainers = document.querySelectorAll('.easydam-video-container.animate-video-loading');
          videoContainers.forEach(container => container.classList.remove('animate-video-loading'));
        } catch (error) {}
      }, CONFIG.DEBOUNCE_DELAY);
    };
  })();

  // Safe GODAMPlayer initialization with retry logic
  const safeGODAMPlayer = (element = null, retryCount = 0) => {
    if (typeof GODAMPlayer !== 'function') {
      if (retryCount < CONFIG.MAX_RETRIES) {
        setTimeout(() => safeGODAMPlayer(element, retryCount + 1), CONFIG.RETRY_DELAY * (retryCount + 1));
      }
      return false;
    }

    try {
      if (element && (element.nodeType !== 1 || !element.isConnected)) {
        element = null; // fallback to global init
      }
      element ? GODAMPlayer(element) : GODAMPlayer();
      return true;
    } catch (error) {
      if (retryCount < CONFIG.MAX_RETRIES) {
        setTimeout(() => safeGODAMPlayer(element, retryCount + 1), CONFIG.RETRY_DELAY * (retryCount + 1));
      }
      return false;
    }
  };

  // Initialize videos inside Magnific Popup
  let popupInitTimeout = null;
  const initializePopupVideos = () => {
    clearTimeout(popupInitTimeout);
    popupInitTimeout = setTimeout(() => {
      try {
        const popupContent = document.querySelector('.mfp-content');
        if (!popupContent) return;
        if (!safeGODAMPlayer(popupContent)) {
          safeGODAMPlayer(); // fallback
        }
        removeLoadingShimmer();
      } catch (error) {}
    }, CONFIG.DEBOUNCE_DELAY);
  };

  // Handle DOM changes (BuddyPress activities, comments, popups, etc.)
  const handleMutations = (mutations) => {
    const nodesToProcess = new Set();
    let hasNewVideos = false;

    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (node.nodeType !== 1) continue;
        nodesToProcess.add(node);

        if (node.tagName === 'VIDEO' || node.querySelector?.('video')) {
          hasNewVideos = true;
        }
      }
    }

    if (hasNewVideos) {
      setTimeout(() => {
        safeGODAMPlayer();
        removeLoadingShimmer();
      }, CONFIG.RETRY_DELAY);

      setTimeout(() => {
        safeGODAMPlayer();
        removeLoadingShimmer();
      }, CONFIG.RETRY_DELAY * 3);
    }

    for (const node of nodesToProcess) {
      try {
        const isPopup = node.classList?.contains('mfp-content') || node.querySelector?.('.mfp-content');
        const hasVideos = node.tagName === 'VIDEO' || node.querySelector?.('video');

        if (isPopup || (hasVideos && node.closest('.mfp-content'))) {
          initializePopupVideos();
        }

        if (
          node.classList?.contains('activity') ||
          node.classList?.contains('groups') ||
          node.classList?.contains('bp-activity-item') ||
          node.classList?.contains('activity-comment') ||
          node.classList?.contains('acomment-reply') ||
          node.classList?.contains('comment-item') ||
          node.querySelector?.('.activity-comment') ||
          node.querySelector?.('.acomment-reply') ||
          node.querySelector?.('.comment-item')
        ) {
          setTimeout(() => {
            if (safeGODAMPlayer(node)) removeLoadingShimmer();
          }, CONFIG.RETRY_DELAY);

          setTimeout(() => {
            safeGODAMPlayer(node);
            removeLoadingShimmer();
          }, CONFIG.RETRY_DELAY * 2);
        }

        if (hasVideos) {
          setTimeout(() => {
            const container = node.closest('.activity') ||
                              node.closest('.activity-comment') ||
                              node.closest('.comment-item') ||
                              node;
            if (safeGODAMPlayer(container)) removeLoadingShimmer();
          }, CONFIG.RETRY_DELAY);

          setTimeout(() => {
            const container = node.closest('.activity') ||
                              node.closest('.activity-comment') ||
                              node.closest('.comment-item') ||
                              node;
            safeGODAMPlayer(container);
            removeLoadingShimmer();
          }, CONFIG.RETRY_DELAY * 4);
        }
      } catch (error) {}
    }
  };

  // Main initialization
  const initialize = () => {
    if (isInitialized) return;

    safeGODAMPlayer();

    setTimeout(() => {
      if (safeGODAMPlayer()) {
        removeLoadingShimmer();
        isInitialized = true;
      }
    }, CONFIG.INIT_DELAY);

    if (typeof MutationObserver !== 'undefined') {
      const observer = new MutationObserver(handleMutations);
      observer.observe(document.body, { childList: true, subtree: true, attributeFilter: ['class'] });
    }

    if (typeof $ !== 'undefined' && $.magnificPopup) {
      $(document).on('mfpOpen mfpChange', () => {
        initializePopupVideos();
        removeLoadingShimmer();
      });
      $(document).on('mfpOpen', () => {
        setTimeout(() => {
          initializePopupVideos();
          removeLoadingShimmer();
        }, CONFIG.POPUP_DELAY);
      });
    }
  };

  // Init when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

  // Handle comment refresh
  document.addEventListener('commentRefreshed', (event) => {
    try {
      const element = event?.detail?.node || null;
      if (safeGODAMPlayer(element)) removeLoadingShimmer();
    } catch (error) {}
  });

  // BuddyPress events
  document.addEventListener('bp_activity_loaded', () => {
    safeGODAMPlayer();
    removeLoadingShimmer();
  });

  document.addEventListener('bp_activity_comment_posted', (event) => {
    setTimeout(() => {
      safeGODAMPlayer(event.target || document);
      removeLoadingShimmer();
    }, CONFIG.RETRY_DELAY);

    setTimeout(() => {
      safeGODAMPlayer();
      removeLoadingShimmer();
    }, CONFIG.RETRY_DELAY * 3);
  });

  document.addEventListener('bp_activity_reply_posted', (event) => {
    setTimeout(() => {
      safeGODAMPlayer(event.target || document);
      removeLoadingShimmer();
    }, CONFIG.RETRY_DELAY);

    setTimeout(() => {
      safeGODAMPlayer();
      removeLoadingShimmer();
    }, CONFIG.RETRY_DELAY * 3);
  });

  // Generic BuddyPress AJAX complete
  if (typeof $ !== 'undefined') {
    $(document).ajaxComplete(function(event, xhr, settings) {
      if (settings.url && (
          settings.url.includes('bp-nouveau') ||
          settings.url.includes('buddypress') ||
          settings.url.includes('activity') ||
          settings.url.includes('comment')
      )) {
        setTimeout(() => {
          safeGODAMPlayer();
          removeLoadingShimmer();
        }, CONFIG.RETRY_DELAY);

        setTimeout(() => {
          safeGODAMPlayer();
          removeLoadingShimmer();
        }, CONFIG.RETRY_DELAY * 4);

        setTimeout(() => {
          safeGODAMPlayer();
          removeLoadingShimmer();
        }, CONFIG.RETRY_DELAY * 8);
      }
    });

    $(document).on('DOMNodeInserted', function(e) {
      const target = e.target;
      if (target && target.nodeType === 1 && (target.tagName === 'VIDEO' || target.querySelector?.('video'))) {
        setTimeout(() => {
          safeGODAMPlayer(target);
          removeLoadingShimmer();
        }, CONFIG.RETRY_DELAY * 2);
      }
    });
  }

  // Global error handler
  window.addEventListener('unhandledrejection', (event) => {
    if (event.reason && event.reason.toString().includes('GODAM')) {
      event.preventDefault(); // prevent noisy logs
    }
  });

})();
