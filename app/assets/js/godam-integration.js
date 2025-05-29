/**
 * Enhanced GODAM Player Initialization Script
 *
 * This script handles automatic initialization and reinitialization of the GODAM video player
 * across various WordPress/BuddyPress contexts, with special support for RTMedia galleries.
 *
 * Key Features:
 * - Initializes GODAM player on page load and dynamic content changes
 * - Handles BuddyPress activity streams with AJAX loading
 * - Supports Magnific Popup lightbox integration
 * - Automatically reinitializes when RTMedia gallery navigation occurs (prev/next arrows)
 * - Tracks dynamic RTMedia element IDs (rtmedia-media-###) and reinitializes on changes
 * - Prevents duplicate initializations using WeakSet tracking
 * - Handles video element detection in popups and dynamic content
 *
 * RTMedia Integration:
 * - Monitors for changes in elements with IDs matching pattern "rtmedia-media-{number}"
 * - Detects navigation events (previous/next arrows) that change video content
 * - Automatically reinitializes GODAM player when new media is loaded
 * - Cleans up tracking when popups are closed
 *
 * Dependencies: GODAM Player, jQuery (optional), Magnific Popup (optional)
 */

GODAMPlayer();

const activityStream = document.querySelector('#buddypress #activity-stream');
if (activityStream) {
	const observer = new MutationObserver((mutations) => {
		for (const mutation of mutations) {
			mutation.addedNodes.forEach((node) => {
				if (node.nodeType === 1 && (node.matches('.activity') || node.matches('.groups'))) {
					requestAnimationFrame(() => GODAMPlayer(node));
				}
			});
		}
	});
	observer.observe(activityStream, { childList: true, subtree: true });
}

document.addEventListener('DOMContentLoaded', () => {
	GODAMPlayer();
	const initializedElements = new WeakSet();
	let currentRTMediaId = null; // Track current RTMedia ID

	const safeGODAMInit = (element) => {
		if (!element) {
			GODAMPlayer();
			return;
		}

		if (initializedElements.has(element)) {
			return;
		}

		const existingPlayers = element.querySelectorAll('[data-godam-initialized], .godam-player-initialized');
		if (existingPlayers.length > 0) {
			return;
		}

		GODAMPlayer(element);
		initializedElements.add(element);
		if (element.setAttribute) {
			element.setAttribute('data-godam-processed', 'true');
		}
	};

	// Function to check for RTMedia ID changes
	const checkRTMediaIdChange = () => {
		const rtmediaElement = document.querySelector('[id^="rtmedia-media-"]');
		if (rtmediaElement) {
			const newId = rtmediaElement.id;
			if (newId !== currentRTMediaId) {
				currentRTMediaId = newId;
				console.log('RTMedia ID changed to:', newId);

				// Remove from initialized set to allow reinitialization
				initializedElements.delete(rtmediaElement);

				// Find the container (could be the element itself or its parent)
				const container = rtmediaElement.closest('.mfp-content') || rtmediaElement;
				if (container) {
					container.removeAttribute('data-godam-processed');
					initializedElements.delete(container);

					// Reinitialize after a short delay to ensure content is loaded
					setTimeout(() => {
						safeGODAMInit(container);
					}, 100);
				}
				return true;
			}
		}
		return false;
	};

	// BuddyPress Activity Stream Observer
	const activityStream = document.querySelector('#buddypress #activity-stream');
	if (activityStream) {
		const observer = new MutationObserver((mutations) => {
			for (const mutation of mutations) {
				mutation.addedNodes.forEach((node) => {
					if (
						node.nodeType === 1 &&
						node.matches('.activity') &&
						!node.hasAttribute('data-godam-processed')
					) {
						requestAnimationFrame(() => safeGODAMInit(node));
					}
				});
			}
		});

		observer.observe(activityStream, { childList: true, subtree: true });
	}

	let popupInitializationTimeout = null;

	// Enhanced Magnific Popup Observer with RTMedia support
	const setupMagnificObserver = () => {
		const magnificObserver = new MutationObserver((mutations) => {
			let shouldCheckRTMedia = false;

			for (const mutation of mutations) {
				// Check for attribute changes on RTMedia elements
				if (mutation.type === 'attributes' &&
					mutation.target.id &&
					mutation.target.id.startsWith('rtmedia-media-')) {
					shouldCheckRTMedia = true;
				}

				mutation.addedNodes.forEach((node) => {
					if (node.nodeType === 1) {
						let mfpContent = null;

						if (node.classList && node.classList.contains('mfp-content')) {
							mfpContent = node;
						} else {
							mfpContent = node.querySelector('.mfp-content');
						}

						// Check if this node or its children contain RTMedia elements
						const hasRTMedia = node.querySelector && node.querySelector('[id^="rtmedia-media-"]');
						if (hasRTMedia) {
							shouldCheckRTMedia = true;
						}

						if (mfpContent && !mfpContent.hasAttribute('data-godam-processed')) {
							if (popupInitializationTimeout) {
								clearTimeout(popupInitializationTimeout);
							}

							popupInitializationTimeout = setTimeout(() => {
								safeGODAMInit(mfpContent);
								popupInitializationTimeout = null;
							}, 300);
						}
					}
				});

				// Handle removed nodes (cleanup)
				mutation.removedNodes.forEach((node) => {
					if (node.nodeType === 1 && node.id && node.id.startsWith('rtmedia-media-')) {
						if (currentRTMediaId === node.id) {
							currentRTMediaId = null;
						}
					}
				});
			}

			// Check for RTMedia ID changes if needed
			if (shouldCheckRTMedia) {
				setTimeout(checkRTMediaIdChange, 100);
			}
		});

		magnificObserver.observe(document.body, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['id', 'class']
		});
	};

	setupMagnificObserver();

	// Enhanced Magnific Popup event handlers
	if (typeof $.magnificPopup !== 'undefined') {
		let eventTimeout = null;

		const handleMagnificEvent = () => {
			if (eventTimeout) {
				clearTimeout(eventTimeout);
			}

			eventTimeout = setTimeout(() => {
				const mfpContent = document.querySelector('.mfp-content:not([data-godam-processed])');
				if (mfpContent) {
					safeGODAMInit(mfpContent);
				}

				// Also check for RTMedia changes
				checkRTMediaIdChange();

				eventTimeout = null;
			}, 250);
		};

		$(document).on('mfpOpen mfpChange mfpBeforeChange', handleMagnificEvent);

		// Handle navigation events specifically
		$(document).on('mfpNext mfpPrev', () => {
			setTimeout(() => {
				checkRTMediaIdChange();
			}, 300);
		});
	}

	if (typeof $ !== 'undefined') {
		$(document).on('mfpClose', function () {
			if (popupInitializationTimeout) {
				clearTimeout(popupInitializationTimeout);
				popupInitializationTimeout = null;
			}
			// Reset RTMedia tracking on close
			currentRTMediaId = null;
		});
	}

	// Enhanced Video Observer with RTMedia support
	const videoObserver = new MutationObserver((mutations) => {
		for (const mutation of mutations) {
			mutation.addedNodes.forEach((node) => {
				if (node.nodeType === 1) {
					const isInMfpContent = node.closest('.mfp-content') ||
										  (node.classList && node.classList.contains('mfp-content'));

					if (isInMfpContent) {
						const videos = node.tagName === 'VIDEO' ? [node] : node.querySelectorAll('video');
						if (videos.length > 0) {
							const container = node.closest('.mfp-content') || node;
							if (!container.hasAttribute('data-godam-processed')) {
								requestAnimationFrame(() => safeGODAMInit(container));
							}
						}
					}

					// Check for RTMedia elements specifically
					const rtmediaElement = node.id && node.id.startsWith('rtmedia-media-') ?
										   node :
										   node.querySelector && node.querySelector('[id^="rtmedia-media-"]');

					if (rtmediaElement) {
						setTimeout(checkRTMediaIdChange, 100);
					}
				}
			});
		}
	});

	videoObserver.observe(document.body, {
		childList: true,
		subtree: true
	});

	// Additional RTMedia-specific observer for DOM changes
	const rtmediaObserver = new MutationObserver((mutations) => {
		let hasRTMediaChanges = false;

		for (const mutation of mutations) {
			// Check for changes in elements with RTMedia IDs
			if (mutation.target.id && mutation.target.id.startsWith('rtmedia-media-')) {
				hasRTMediaChanges = true;
				break;
			}

			// Check added/removed nodes for RTMedia elements
			const checkNodes = (nodes) => {
				for (const node of nodes) {
					if (node.nodeType === 1) {
						if ((node.id && node.id.startsWith('rtmedia-media-')) ||
							(node.querySelector && node.querySelector('[id^="rtmedia-media-"]'))) {
							hasRTMediaChanges = true;
							return true;
						}
					}
				}
				return false;
			};

			if (checkNodes(mutation.addedNodes) || checkNodes(mutation.removedNodes)) {
				break;
			}
		}

		if (hasRTMediaChanges) {
			setTimeout(checkRTMediaIdChange, 150);
		}
	});

	// Observe the document for RTMedia changes
	rtmediaObserver.observe(document.body, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: ['id']
	});

	// Initialize RTMedia tracking on load
	setTimeout(checkRTMediaIdChange, 500);
});
