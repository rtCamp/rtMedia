/**
 * Enhanced AJAX Comment Refresh - GODAM Player Integration
 *
 * Ensures GODAM player loads properly for comment replies in multisite setups
 */
class CommentRefreshManager {
  constructor() {
    // Track comments being refreshed (avoid duplicates)
    this.refreshingComments = new Set();

    // Track retry counts for failed requests
    this.retryAttempts = new Map();

    // Max number of retry attempts before giving up
    this.maxRetries = 3;

    // How long to wait for GODAMPlayer availability (ms)
    this.godamCheckTimeout = 200;
  }

  // Wait until GODAMPlayer is available and functional
  waitForGODAMPlayer(timeout = this.godamCheckTimeout) {
    return new Promise((resolve) => {
      const startTime = Date.now();

      const checkPlayer = () => {
        if (typeof GODAMPlayer === 'function') {
          try {
            // Create a test element to confirm GODAMPlayer actually works
            const testDiv = document.createElement('div');
            testDiv.innerHTML = '<video></video>';
            document.body.appendChild(testDiv);
            GODAMPlayer(testDiv);
            document.body.removeChild(testDiv);
            resolve(true);
            return;
          } catch {}
        }

        // Timeout reached -> not available
        if (Date.now() - startTime > timeout) {
          resolve(false);
          return;
        }

        // Retry check
        setTimeout(checkPlayer, 100);
      };

      checkPlayer();
    });
  }

  // Ensure request is valid before sending AJAX
  validateRequest(commentId, node) {
    if (!commentId || !node) return false;
    if (typeof GodamAjax === 'undefined' || !GodamAjax.ajax_url || !GodamAjax.nonce) return false;
    if (!document.contains(node)) return false;
    if (this.refreshingComments.has(commentId)) return false;
    return true;
  }

  // Initialize GODAM player for comment replies
  async initializeGODAMPlayerForReply(node, commentId) {
    const isGODAMReady = await this.waitForGODAMPlayer();
    if (!isGODAMReady) return false;

    const videos = node.querySelectorAll('video');
    const videoContainers = node.querySelectorAll('.easydam-video-container');

    // If no media, no need to init player
    if (videos.length === 0 && videoContainers.length === 0) return true;

    node.setAttribute('data-godam-reply-processing', 'true');

    // Try initializing directly on node
    try {
      GODAMPlayer(node);
    } catch {
      // Fallback: try initializing each container individually
      for (const container of videoContainers) {
        try { GODAMPlayer(container); } catch {}
      }
      for (const video of videos) {
        try {
          const videoContainer = video.closest('.easydam-video-container') || video.parentElement;
          GODAMPlayer(videoContainer);
        } catch {}
      }
    }

    // Global re-init (for skins, multiple players, etc.)
    try { GODAMPlayer(); } catch {}

    node.removeAttribute('data-godam-reply-processing');
    node.setAttribute('data-godam-reply-initialized', 'true');

    // Remove loading animation once ready
    setTimeout(() => {
      node.querySelectorAll('.animate-video-loading')
        .forEach(el => el.classList.remove('animate-video-loading'));
    }, 300);

    return true;
  }

  // Handle successful AJAX response -> replace comment HTML + re-init player
  async handleSuccessfulResponse(data, commentId, node) {
    const activityItem = node.closest('.activity-item');
    if (!activityItem) return;

    // Ensure the parent has a comments container
    const parentActivityId = activityItem.id.replace('activity-', '');
    let commentList = document.querySelector(`#activity-${parentActivityId} .activity-comments`);

    if (!commentList) {
      commentList = document.createElement('ul');
      commentList.classList.add('activity-comments');
      activityItem.appendChild(commentList);
    }

    // Parse returned comment HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = data.data.html.trim();
    const newCommentNode = tempDiv.firstElementChild;
    if (!newCommentNode) return;

    // Replace old node with refreshed one
    node.replaceWith(newCommentNode);

    // Try initializing GODAMPlayer (up to 3 attempts with backoff)
    let initSuccess = false;
    for (let attempt = 1; attempt <= 3; attempt++) {
      await new Promise(resolve => setTimeout(resolve, attempt * 100));
      initSuccess = await this.initializeGODAMPlayerForReply(newCommentNode, commentId);
      if (initSuccess) break;
    }

    // Dispatch custom event so other scripts can hook into comment refresh
    setTimeout(() => {
      document.dispatchEvent(new CustomEvent('commentRefreshed', {
        detail: { commentId, node: newCommentNode, playerReady: initSuccess, isReply: true },
        bubbles: true
      }));
    }, 200);
  }

  // Refresh a single comment via AJAX
  async refreshSingleComment(commentId, node) {
    if (!this.validateRequest(commentId, node)) return;

    this.refreshingComments.add(commentId);
    node.classList.add('refreshing');

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000); // 15s max wait

    try {
      const response = await fetch(GodamAjax.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'get_single_activity_comment_html',
          comment_id: commentId,
          nonce: GodamAjax.nonce,
        }),
        signal: controller.signal
      });

      clearTimeout(timeoutId);
      if (!response.ok) throw new Error();

      const data = await response.json();

      // If successful, process and replace comment
      if (data && data.success && data.data && data.data.html) {
        await this.handleSuccessfulResponse(data, commentId, node);
      } else {
        this.scheduleRetry(commentId, node);
      }
    } catch {
      clearTimeout(timeoutId);
      this.scheduleRetry(commentId, node);
    } finally {
      clearTimeout(timeoutId);
      this.refreshingComments.delete(commentId);
      if (document.contains(node)) node.classList.remove('refreshing');
    }
  }

  // Retry failed refresh with exponential backoff
  scheduleRetry(commentId, node) {
    const attempts = this.retryAttempts.get(commentId) || 0;
    if (attempts >= this.maxRetries) return;

    const delay = Math.pow(2, attempts) * 1000; // 1s, 2s, 4s...
    this.retryAttempts.set(commentId, attempts + 1);

    setTimeout(() => {
      if (document.contains(node)) this.refreshSingleComment(commentId, node);
    }, delay);
  }

  // Initialize GODAMPlayer for comments already on the page
  async initializeExistingComments() {
    const existingComments = document.querySelectorAll('li[id^="acomment-"]');
    for (const comment of existingComments) {
      const commentId = comment.id.replace('acomment-', '');
      await this.initializeGODAMPlayerForReply(comment, commentId);
    }
  }

  // Entry point
  initialize() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupSystem());
    } else {
      this.setupSystem();
    }
  }

  // System setup: init existing + observe for new ones
  async setupSystem() {
    await this.initializeExistingComments();
    this.setupMutationObservers();
  }

  // Simple debounce utility
  debounce(func, wait) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  }

  // Watch for new comments being added to DOM
  setupMutationObservers() {
    const commentsContainers = document.querySelectorAll('.activity-comments');
    const debouncedHandler = this.debounce((mutations) => {
      this.handleNewComments(mutations);
    }, 200);

    // If no containers yet, observe entire body
    if (commentsContainers.length === 0) {
      const bodyObserver = new MutationObserver(debouncedHandler);
      bodyObserver.observe(document.body, { childList: true, subtree: true });
      return;
    }

    // Otherwise, attach observers to each comments container
    commentsContainers.forEach((container) => {
      const observer = new MutationObserver(debouncedHandler);
      observer.observe(container, { childList: true, subtree: true });
    });
  }

  // Handle new comments inserted into DOM
  async handleNewComments(mutations) {
    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (node.nodeType === 1 && node.matches('li[id^="acomment-"]')) {
          const commentId = node.id.replace('acomment-', '');
          setTimeout(async () => {
            if (document.contains(node)) {
              await this.initializeGODAMPlayerForReply(node, commentId);
              await this.refreshSingleComment(commentId, node);
            }
          }, 200);
        }
      }
    }
  }
}

// Initialize system
const commentRefreshManager = new CommentRefreshManager();
commentRefreshManager.initialize();
window.CommentRefreshManager = commentRefreshManager;
