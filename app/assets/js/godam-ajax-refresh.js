document.addEventListener('DOMContentLoaded', () => {
  const commentsContainers = document.querySelectorAll('.activity-comments');
  const processingComments = new Set(); // Track comments being processed

  // If no comment containers exist, exit early
  if (commentsContainers.length === 0) {
    return;
  }

  commentsContainers.forEach((container) => {
    // Initialize GODAMPlayer on existing comment container
    GODAMPlayer(container);

    // Observe DOM changes to detect new comments being added dynamically
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1 && node.matches('li[id^="acomment-"]')) {
            // Extract the comment ID
            const commentId = node.id.replace('acomment-', '');

            // Prevent duplicate processing
            if (processingComments.has(commentId)) {
              return;
            }

            // Add delay to ensure node is fully rendered
            setTimeout(() => {
              // Check if node still exists and needs processing
              if (document.getElementById(`acomment-${commentId}`)) {
                // Initialize GODAMPlayer on the new comment node
                GODAMPlayer(node);

                // Only refresh if it looks like a placeholder/incomplete node
                if (shouldRefreshComment(node)) {
                  refreshSingleComment(commentId, node);
                }
              }
            }, 100); // Small delay to avoid race conditions
          }
        });
      });
    });

    observer.observe(container, {
      childList: true,
      subtree: true
    });
  });
});

/**
 * Determines if a comment node needs to be refreshed
 * @param {Element} node - The comment node to check
 * @returns {boolean} - Whether the comment should be refreshed
 */
function shouldRefreshComment(node) {
  // Add your logic here to determine if the comment is a placeholder
  // For example, check if it's missing expected content or has placeholder classes
  return node.classList.contains('loading') ||
         node.querySelector('.godam-placeholder') ||
         !node.querySelector('.comment-content');
}

/**
 * Refreshes a single BuddyPress comment via AJAX to fetch updated content,
 * including Godam video player shortcode rendering.
 *
 * @param {string} commentId - The ID of the comment to refresh
 * @param {Element} node - The existing DOM node being replaced
 */
function refreshSingleComment(commentId, node) {
  const processingComments = window.processingComments || (window.processingComments = new Set());

  // Prevent duplicate requests
  if (processingComments.has(commentId)) {
    return;
  }

  processingComments.add(commentId);

  // Add loading indicator
  const originalContent = node.innerHTML;
  node.classList.add('loading');

  // Create AbortController for timeout handling
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

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

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  })
  .then(data => {
    if (data.success && data.data && data.data.html) {
      // Identify the parent activity from the comment DOM node
      const parentActivityId = node.closest('.activity-item')?.id?.replace('activity-', '');

      if (!parentActivityId) {
        throw new Error('Could not find parent activity ID');
      }

      // Locate or create the container for activity comments
      let commentList = document.querySelector(`#activity-${parentActivityId} .activity-comments`);
      if (!commentList) {
        commentList = document.createElement('ul');
        commentList.classList.add('activity-comments');
        const activityElement = document.querySelector(`#activity-${parentActivityId}`);
        if (!activityElement) {
          throw new Error('Parent activity element not found');
        }
        activityElement.appendChild(commentList);
      }

      // Create a temporary container to parse the HTML
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = data.data.html;
      const newCommentNode = tempDiv.firstElementChild;

      if (newCommentNode) {
        // Replace the old node with the new one
        if (node.parentNode) {
          node.parentNode.replaceChild(newCommentNode, node);
        }

        // Reinitialize GODAMPlayer for the new comment node
        GODAMPlayer(newCommentNode);
      }
    } else {
      throw new Error(data.data || 'Unknown server error');
    }
  })
  .catch(error => {
    console.error('AJAX error for comment', commentId, ':', error);

    // Restore original content on error
    node.innerHTML = originalContent;
    node.classList.remove('loading');

    // Retry logic (optional)
    if (!error.name === 'AbortError') { // Don't retry timeouts
      setTimeout(() => {
        processingComments.delete(commentId);
        // Could implement retry logic here
      }, 5000);
    }
  })
  .finally(() => {
    clearTimeout(timeoutId);
    processingComments.delete(commentId);
    node.classList.remove('loading');
  });
}
