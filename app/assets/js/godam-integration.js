/**
 * This script ensures GODAMPlayer is initialized dynamically across:
 * - BuddyPress activity streams updated via AJAX.
 * - Magnific Popup content.
 * - Any newly added video elements in the DOM (e.g., via infinite scroll or modals).
 *
 * It uses MutationObservers and event listeners to watch for new content and
 * initializes the player only when necessary, avoiding duplicate setups.
 */

GODAMPlayer();

const activityStream = document.querySelector('#buddypress #activity-stream');
if (activityStream) {
	const observer = new MutationObserver((mutations) => {
		for (const mutation of mutations) {
			mutation.addedNodes.forEach((node) => {
				if (node.nodeType === 1 && node.matches('.activity')) {
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

	const setupMagnificObserver = () => {
		const magnificObserver = new MutationObserver((mutations) => {
			for (const mutation of mutations) {
				mutation.addedNodes.forEach((node) => {
					if (node.nodeType === 1) {
						let mfpContent = null;

						if (node.classList && node.classList.contains('mfp-content')) {
							mfpContent = node;
						} else {
							mfpContent = node.querySelector('.mfp-content');
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
			}
		});

		magnificObserver.observe(document.body, {
			childList: true,
			subtree: true
		});
	};

	setupMagnificObserver();

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
				eventTimeout = null;
			}, 250);
		};

		$(document).on('mfpOpen mfpChange', handleMagnificEvent);
	}

	if (typeof $ !== 'undefined') {
		$(document).on('mfpClose', function () {
			if (popupInitializationTimeout) {
				clearTimeout(popupInitializationTimeout);
				popupInitializationTimeout = null;
			}
		});
	}

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
				}
			});
		}
	});

	videoObserver.observe(document.body, {
		childList: true,
		subtree: true
	});
});
