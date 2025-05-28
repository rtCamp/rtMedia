document.addEventListener('DOMContentLoaded', () => {
    const commentsContainers = document.querySelectorAll('.activity-comments');

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
              // Initialize GODAMPlayer on the new comment node
              GODAMPlayer(node);

              // Extract the comment ID and refresh the comment via AJAX
              const commentId = node.id.replace('acomment-', '');
              refreshSingleComment(commentId, node);
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
   * Refreshes a single BuddyPress comment via AJAX to fetch updated content,
   * including Godam video player shortcode rendering.
   *
   * @param {string} commentId - The ID of the comment to refresh
   * @param {Element} node - The existing DOM node being replaced
   */
  function refreshSingleComment(commentId, node) {
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
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Identify the parent activity from the comment DOM node
        const parentActivityId = node.closest('.activity-item').id.replace('activity-', '');

        // Locate or create the container for activity comments
        let commentList = document.querySelector(`#activity-${parentActivityId} .activity-comments`);
        if (!commentList) {
          commentList = document.createElement('ul');
          commentList.classList.add('activity-comments');
          document.querySelector(`#activity-${parentActivityId}`).appendChild(commentList);
        }

        // Inject the freshly rendered comment HTML
        commentList.insertAdjacentHTML('beforeend', data.data.html);

        // Remove the placeholder node that triggered the refresh
        if (node.parentNode) {
          node.parentNode.removeChild(node);
        }

        // Reinitialize GODAMPlayer for the new comment node
        const refreshedNode = document.querySelector(`#acomment-${commentId}`);
        if (refreshedNode) GODAMPlayer(refreshedNode);
      } else {
        console.error('AJAX error:', data.data);
      }
    })
    .catch(error => {
      console.error('Fetch error:', error);
    });
  }
