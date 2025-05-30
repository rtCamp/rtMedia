// Enhanced AJAX function with better error handling and retry logic
function refreshSingleComment(commentId, node) {
  // Validation checks
  if (!commentId || !node) {
    return;
  }

  // Check if GodamAjax object exists
  if (typeof GodamAjax === 'undefined' || !GodamAjax.ajax_url || !GodamAjax.nonce) {
    return;
  }

  // Check if node is still in the DOM
  if (!document.contains(node)) {
    return;
  }

  // Prevent duplicate requests
  if (node.classList.contains('refreshing')) {
    return;
  }
  node.classList.add('refreshing');

  // Create AbortController for timeout handling
  const controller = new AbortController();
  const timeoutId = setTimeout(() => {
    controller.abort();
  }, 15000); // 15 second timeout

  fetch(GodamAjax.ajax_url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      action: 'get_single_activity_comment_html',
      comment_id: commentId,
      nonce: GodamAjax.nonce,
    }),
    signal: controller.signal
  })
  .then(response => {
    clearTimeout(timeoutId);

    // Check if response is ok
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    // Check content type
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      throw new Error('Server returned non-JSON response');
    }

    return response.json();
  })
  .then(data => {
    if (data && data.success && data.data && data.data.html) {
      // Success - handle the response
      handleSuccessfulResponse(data, commentId, node);
    } else {
      // AJAX returned error
      const errorMsg = data && data.data ? data.data : 'Unknown AJAX error';
      console.error('AJAX error:', errorMsg);

      // Optional: Retry once after a delay
      setTimeout(() => {
        retryRefreshComment(commentId, node, 1);
      }, 2000);
    }
  })
  .catch(error => {
    clearTimeout(timeoutId);
    console.error('Fetch error:', error);

    // Handle specific error types
    if (error.name === 'AbortError') {
      console.error('Request timed out');
    } else if (error.message.includes('Failed to fetch')) {
      console.error('Network error - possible connectivity issue');
      // Retry after network error
      setTimeout(() => {
        retryRefreshComment(commentId, node, 1);
      }, 3000);
    }
  })
  .finally(() => {
    clearTimeout(timeoutId);
    // Always remove refreshing class
    if (document.contains(node)) {
      node.classList.remove('refreshing');
    }
  });
}

// Retry function with exponential backoff
function retryRefreshComment(commentId, node, attempt = 1) {
  const maxRetries = 2;

  if (attempt > maxRetries) {
    console.error(`Failed to refresh comment ${commentId} after ${maxRetries} retries`);
    return;
  }

  // Check if node still exists
  if (!document.contains(node)) {
    return;
  }

  // Exponential backoff delay
  const delay = Math.pow(2, attempt) * 1000; // 2s, 4s, 8s...

  setTimeout(() => {
    // Remove any existing refreshing class
    node.classList.remove('refreshing');

    // Try again with modified fetch (more conservative approach)
    fetch(GodamAjax.ajax_url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Cache-Control': 'no-cache',
      },
      body: new URLSearchParams({
        action: 'get_single_activity_comment_html',
        comment_id: commentId,
        nonce: GodamAjax.nonce,
        retry: attempt.toString()
      }),
    })
    .then(response => response.json())
    .then(data => {
      if (data && data.success && data.data && data.data.html) {
        handleSuccessfulResponse(data, commentId, node);
      } else {
        // Retry again if not max attempts
        if (attempt < maxRetries) {
          retryRefreshComment(commentId, node, attempt + 1);
        }
      }
    })
    .catch(error => {
      console.error(`Retry ${attempt} failed:`, error);
      if (attempt < maxRetries) {
        retryRefreshComment(commentId, node, attempt + 1);
      }
    });
  }, delay);
}

// Handle successful AJAX response
function handleSuccessfulResponse(data, commentId, node) {
  try {
    // Find parent activity more safely
    const activityItem = node.closest('.activity-item');
    if (!activityItem) {
      console.error('Could not find parent activity item');
      return;
    }

    const parentActivityId = activityItem.id.replace('activity-', '');

    // Locate comment container
    let commentList = document.querySelector(`#activity-${parentActivityId} .activity-comments`);
    if (!commentList) {
      commentList = document.createElement('ul');
      commentList.classList.add('activity-comments');
      activityItem.appendChild(commentList);
    }

    // Create temporary container for HTML parsing
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = data.data.html.trim();
    const newCommentNode = tempDiv.firstElementChild;

    if (newCommentNode) {
      // Insert new comment
      commentList.appendChild(newCommentNode);

      // Remove old node safely
      if (node.parentNode && document.contains(node)) {
        node.parentNode.removeChild(node);
      }

      // Initialize GODAMPlayer if available
      if (typeof GODAMPlayer === 'function') {
        try {
          GODAMPlayer(newCommentNode);
        } catch (playerError) {
          console.error('GODAMPlayer initialization failed:', playerError);
        }
      }

      // Dispatch custom event for other scripts
      document.dispatchEvent(new CustomEvent('commentRefreshed', {
        detail: { commentId, node: newCommentNode }
      }));

    } else {
      console.error('No valid comment node found in response HTML');
    }
  } catch (error) {
    console.error('Error handling successful response:', error);
  }
}

// Enhanced DOM observer with debouncing
document.addEventListener('DOMContentLoaded', () => {
  const commentsContainers = document.querySelectorAll('.activity-comments');

  if (commentsContainers.length === 0) {
    return;
  }

  // Debounce function to prevent rapid-fire calls
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  commentsContainers.forEach((container) => {
    // Initialize GODAMPlayer on existing comments
    if (typeof GODAMPlayer === 'function') {
      try {
        GODAMPlayer(container);
      } catch (error) {
        console.error('GODAMPlayer initialization failed:', error);
      }
    }

    // Debounced mutation handler
    const debouncedHandler = debounce((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1 && node.matches && node.matches('li[id^="acomment-"]')) {
            // Initialize GODAMPlayer first
            if (typeof GODAMPlayer === 'function') {
              try {
                GODAMPlayer(node);
              } catch (error) {
                console.error('GODAMPlayer initialization failed:', error);
              }
            }

            // Extract comment ID and refresh with delay
            const commentId = node.id.replace('acomment-', '');

            // Add longer delay to ensure DOM stability
            setTimeout(() => {
              if (document.contains(node)) {
                refreshSingleComment(commentId, node);
              }
            }, 250);
          }
        });
      });
    }, 100); // 100ms debounce

    // Create observer
    const observer = new MutationObserver(debouncedHandler);

    observer.observe(container, {
      childList: true,
      subtree: true
    });
  });
});

// Debug function to test AJAX connectivity
function testAjaxConnection() {
  if (typeof GodamAjax === 'undefined') {
    console.error('GodamAjax not defined');
    return;
  }

  fetch(GodamAjax.ajax_url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      action: 'heartbeat',
      nonce: GodamAjax.nonce,
    }),
  })
  .then(response => response.json())
  .then(data => {
    console.log('AJAX connection test:', data);
  })
  .catch(error => {
    console.error('AJAX connection test failed:', error);
  });
}

// Uncomment to test AJAX connection
// testAjaxConnection();
