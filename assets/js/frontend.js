/**
 * SmartNotify AI - Frontend JavaScript
 * Handles news modal functionality
 */

(function() {
    'use strict';

    /**
     * SmartNotify Frontend Handler
     */
    const SmartNotifyFrontend = {

        /**
         * Initialize
         */
        init: function() {
            this.modal = document.getElementById('smartnotify-news-modal');

            if (!this.modal) {
                return;
            }

            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Open modal on card click
            document.addEventListener('click', function(e) {
                // Check if clicked element is within a news card
                const card = e.target.closest('.smartnotify-news-card');

                if (!card) {
                    return;
                }

                // Check if clicked on "Leer más" button
                const openButton = e.target.closest('.smartnotify-open-modal');

                // Or clicked on card image
                const cardImage = e.target.closest('.smartnotify-card-image');

                // Or clicked on card title
                const cardTitle = e.target.closest('.smartnotify-card-title');

                if (openButton || cardImage || cardTitle) {
                    e.preventDefault();
                    self.openModal(card);
                }
            });

            // Close modal on close button click
            const closeButton = this.modal.querySelector('.smartnotify-modal-close');
            if (closeButton) {
                closeButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.closeModal();
                });
            }

            // Close modal on overlay click
            const overlay = this.modal.querySelector('.smartnotify-modal-overlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.closeModal();
                });
            }

            // Close modal on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && self.modal.classList.contains('smartnotify-modal-open')) {
                    self.closeModal();
                }
            });
        },

        /**
         * Open modal
         */
        openModal: function(card) {
            // Get data from card
            const data = {
                title: card.getAttribute('data-title'),
                content: card.getAttribute('data-content'),
                summary: card.getAttribute('data-summary'),
                image: card.getAttribute('data-image'),
                date: card.getAttribute('data-date'),
                author: card.getAttribute('data-author'),
                sentiment: card.getAttribute('data-sentiment'),
                sentimentClass: card.getAttribute('data-sentiment-class'),
                categories: card.getAttribute('data-categories'),
                tags: card.getAttribute('data-tags')
            };

            // Populate modal
            this.populateModal(data);

            // Show modal
            this.modal.style.display = 'flex';

            // Trigger animation
            setTimeout(() => {
                this.modal.classList.add('smartnotify-modal-open');
            }, 10);

            // Prevent body scroll
            document.body.classList.add('smartnotify-modal-open');

            // Scroll modal to top
            const modalBody = this.modal.querySelector('.smartnotify-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        },

        /**
         * Close modal
         */
        closeModal: function() {
            this.modal.classList.remove('smartnotify-modal-open');

            setTimeout(() => {
                this.modal.style.display = 'none';
            }, 300);

            // Re-enable body scroll
            document.body.classList.remove('smartnotify-modal-open');
        },

        /**
         * Populate modal with data
         */
        populateModal: function(data) {
            // Set image
            const modalImage = this.modal.querySelector('.smartnotify-modal-image');
            const imageContainer = this.modal.querySelector('.smartnotify-modal-image-container');

            if (data.image && modalImage) {
                modalImage.src = data.image;
                modalImage.alt = data.title;
                imageContainer.style.display = 'block';
            } else {
                imageContainer.style.display = 'none';
            }

            // Set sentiment badge
            const sentimentBadge = this.modal.querySelector('.smartnotify-modal-sentiment');
            if (data.sentiment && sentimentBadge) {
                sentimentBadge.textContent = data.sentiment;
                sentimentBadge.className = 'smartnotify-modal-sentiment ' + data.sentimentClass;
                sentimentBadge.style.display = 'block';
            } else if (sentimentBadge) {
                sentimentBadge.style.display = 'none';
            }

            // Set categories
            const categoriesContainer = this.modal.querySelector('.smartnotify-modal-categories');
            if (data.categories && categoriesContainer) {
                const categories = data.categories.split(',').filter(cat => cat.trim() !== '');

                if (categories.length > 0) {
                    categoriesContainer.innerHTML = categories.map(cat =>
                        '<span class="smartnotify-modal-category">' + this.escapeHtml(cat.trim()) + '</span>'
                    ).join('');
                    categoriesContainer.style.display = 'flex';
                } else {
                    categoriesContainer.style.display = 'none';
                }
            }

            // Set title
            const modalTitle = this.modal.querySelector('.smartnotify-modal-title');
            if (modalTitle) {
                modalTitle.textContent = data.title;
            }

            // Set author
            const modalAuthor = this.modal.querySelector('.smartnotify-modal-author');
            const modalSeparator = this.modal.querySelector('.smartnotify-modal-separator');
            if (data.author && modalAuthor) {
                modalAuthor.textContent = data.author;
                modalAuthor.style.display = 'inline';
                if (modalSeparator) modalSeparator.style.display = 'inline';
            } else if (modalAuthor) {
                modalAuthor.style.display = 'none';
                if (modalSeparator) modalSeparator.style.display = 'none';
            }

            // Set date
            const modalDate = this.modal.querySelector('.smartnotify-modal-date');
            if (modalDate) {
                modalDate.textContent = data.date;
            }

            // Set summary
            const modalSummary = this.modal.querySelector('.smartnotify-modal-summary');
            if (data.summary && modalSummary) {
                modalSummary.innerHTML = '<p>' + this.escapeHtml(data.summary) + '</p>';
                modalSummary.style.display = 'block';
            } else if (modalSummary) {
                modalSummary.style.display = 'none';
            }

            // Set content
            const modalContent = this.modal.querySelector('.smartnotify-modal-content');
            if (modalContent) {
                modalContent.innerHTML = data.content;
            }

            // Set tags
            const tagsContainer = this.modal.querySelector('.smartnotify-modal-tags');
            if (data.tags && tagsContainer) {
                const tags = data.tags.split(',').filter(tag => tag.trim() !== '');

                if (tags.length > 0) {
                    tagsContainer.innerHTML = '<div class="smartnotify-modal-tags-title">Etiquetas:</div>' +
                        tags.map(tag =>
                            '<span class="smartnotify-modal-tag">#' + this.escapeHtml(tag.trim()) + '</span>'
                        ).join('');
                    tagsContainer.style.display = 'block';
                } else {
                    tagsContainer.style.display = 'none';
                }
            } else if (tagsContainer) {
                tagsContainer.style.display = 'none';
            }
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            SmartNotifyFrontend.init();
        });
    } else {
        SmartNotifyFrontend.init();
    }

})();
