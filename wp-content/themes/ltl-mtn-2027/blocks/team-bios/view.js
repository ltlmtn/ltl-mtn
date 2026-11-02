/**
 * Team Bios Block - Frontend Interactivity
 *
 * Handles toggling the bio overlay and rotating the "+" icon to "x" (-45deg).
 */

(function () {
	'use strict';

	function initTeamBios() {
		var blocks = document.querySelectorAll('.team-bios');

		blocks.forEach(function (block) {
			var cards = block.querySelectorAll('.team-bio.has-bio');

			cards.forEach(function (card) {
				var toggleBtn = card.querySelector('.team-bio-toggle');
				var overlay = card.querySelector('.team-bio-overlay');

				if (!toggleBtn) {
					return;
				}

				function closeCard() {
					card.classList.remove('is-active');
					toggleBtn.setAttribute('aria-expanded', 'false');
					if (overlay) {
						overlay.setAttribute('aria-hidden', 'true');
					}
				}

				function openCard() {
					// Close other open cards in this block
					cards.forEach(function (otherCard) {
						if (otherCard !== card && otherCard.classList.contains('is-active')) {
							otherCard.classList.remove('is-active');
							var otherBtn = otherCard.querySelector('.team-bio-toggle');
							if (otherBtn) {
								otherBtn.setAttribute('aria-expanded', 'false');
							}
							var otherOverlay = otherCard.querySelector('.team-bio-overlay');
							if (otherOverlay) {
								otherOverlay.setAttribute('aria-hidden', 'true');
							}
						}
					});

					card.classList.add('is-active');
					toggleBtn.setAttribute('aria-expanded', 'true');
					if (overlay) {
						overlay.setAttribute('aria-hidden', 'false');
					}
				}

				function toggle(e) {
					if (e) {
						e.preventDefault();
						e.stopPropagation();
					}

					if (card.classList.contains('is-active')) {
						closeCard();
					} else {
						openCard();
					}
				}

				toggleBtn.addEventListener('click', toggle);

				var imgWrap = card.querySelector('.team-bio-image');
				if (imgWrap) {
					imgWrap.addEventListener('click', function (e) {
						if (!card.classList.contains('is-active')) {
							e.preventDefault();
							openCard();
						}
					});
				}

				// Close on Escape key if focus is inside the card
				card.addEventListener('keydown', function (e) {
					if (e.key === 'Escape' && card.classList.contains('is-active')) {
						closeCard();
						toggleBtn.focus();
					}
				});
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initTeamBios);
	} else {
		initTeamBios();
	}
})();
