/**
 * SmartNotify AI - Admin JavaScript
 *
 * @package SmartNotifyAI
 */

(function($) {
    'use strict';

    /**
     * SmartNotify AI Admin Class
     */
    const SmartNotifyAdmin = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTooltips();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Generate title
            $(document).on('click', '.smartnotify-generate-title', this.generateTitle.bind(this));

            // Generate summary
            $(document).on('click', '.smartnotify-generate-summary', this.generateSummary.bind(this));

            // Generate tags
            $(document).on('click', '.smartnotify-generate-tags', this.generateTags.bind(this));

            // Analyze sentiment
            $(document).on('click', '.smartnotify-analyze-sentiment', this.analyzeSentiment.bind(this));

            // Test API connection
            $(document).on('click', '#smartnotify_test_api', this.testApiConnection.bind(this));
            $(document).on('click', '#smartnotify_test_image_api', this.testImageApiConnection.bind(this));

            // Provider change handler
            $('#smartnotify_ai_provider').on('change', this.handleProviderChange.bind(this));
            $('#smartnotify_image_provider').on('change', this.handleImageProviderChange.bind(this));

            // API key change handler - enable/disable test button
            $('#smartnotify_ai_api_key').on('input', this.handleApiKeyChange.bind(this));
            $('#smartnotify_image_api_key').on('input', this.handleImageApiKeyChange.bind(this));

            // Modal handlers
            $(document).on('click', '.smartnotify-modal-close', this.closeModal.bind(this));
            $(document).on('click', '.smartnotify-modal-backdrop', this.closeModal.bind(this));
            $(document).on('click', '#smartnotify-save-draft', this.saveDraft.bind(this));
            $(document).on('submit', '#smartnotify-news-form', this.publishNews.bind(this));

            // Modal AI generation buttons
            $(document).on('click', '.smartnotify-generate-title-modal', this.generateTitleModal.bind(this));
            $(document).on('click', '.smartnotify-generate-summary-modal', this.generateSummaryModal.bind(this));
            $(document).on('click', '.smartnotify-generate-tags-modal', this.generateTagsModal.bind(this));
            $(document).on('click', '.smartnotify-analyze-sentiment-modal', this.analyzeSentimentModal.bind(this));

            // Tags input handler
            $('#news_tags').on('input', this.updateTagsPreview.bind(this));

            // Listen for sentiment display event (when editing a post with existing sentiment)
            $(document).on('smartnotify-display-sentiment', this.displayStoredSentiment.bind(this));

            // Image handlers
            $(document).on('click', '.smartnotify-upload-image', this.uploadImage.bind(this));
            $(document).on('click', '.smartnotify-generate-image-prompt', this.generateImageFromPrompt.bind(this));
            $(document).on('click', '.smartnotify-generate-image-content', this.generateImageFromContent.bind(this));
            $(document).on('click', '.smartnotify-remove-image', this.removeImage.bind(this));

            // Drag & Drop handlers with event delegation
            $(document).on('click', '#smartnotify-dropzone', this.handleDropzoneClick.bind(this));
            $(document).on('change', '#smartnotify-file-input', this.handleFileInputChange.bind(this));
            this.initDragAndDrop();
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltips to buttons
            $('.smartnotify-ai-buttons button').each(function() {
                const $button = $(this);
                const title = $button.attr('title');

                if (title) {
                    $button.attr('data-tooltip', title);
                }
            });
        },

        /**
         * Generate title
         */
        generateTitle: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');

            this.showLoading($button, smartnotifyAI.i18n.generating);

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_title',
                    nonce: smartnotifyAI.nonce,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        // Update title field
                        $('#title').val(response.data.title);

                        // Show success message
                        this.showMessage('success', response.data.message);
                    } else {
                        this.showMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showMessage('error', smartnotifyAI.i18n.error + ': ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Generate summary
         */
        generateSummary: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');

            this.showLoading($button, smartnotifyAI.i18n.generating);

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_summary',
                    nonce: smartnotifyAI.nonce,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        // Update summary field
                        $('#smartnotify_summary').val(response.data.summary);

                        // Show success message
                        this.showMessage('success', response.data.message);
                    } else {
                        this.showMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showMessage('error', smartnotifyAI.i18n.error + ': ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Generate tags
         */
        generateTags: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');

            this.showLoading($button, smartnotifyAI.i18n.generating);

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_tags',
                    nonce: smartnotifyAI.nonce,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        // Update tags in the UI
                        this.updateTags(response.data.tags);

                        // Show success message
                        this.showMessage('success', response.data.message);
                    } else {
                        this.showMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showMessage('error', smartnotifyAI.i18n.error + ': ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Analyze sentiment
         */
        analyzeSentiment: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const postId = $button.data('post-id');

            this.showLoading($button, smartnotifyAI.i18n.analyzing);

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_analyze_sentiment',
                    nonce: smartnotifyAI.nonce,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        // Update sentiment display
                        this.updateSentimentDisplay(response.data);

                        // Show success message
                        this.showMessage('success', response.data.message);
                    } else {
                        this.showMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showMessage('error', smartnotifyAI.i18n.error + ': ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Update tags in UI
         */
        updateTags: function(tags) {
            const $tagsBox = $('.tagsdiv[id*="smartnotify_tag"]');

            if ($tagsBox.length) {
                const $input = $tagsBox.find('.newtag');

                if ($input.length) {
                    tags.forEach(tag => {
                        $input.val(tag);
                        $tagsBox.find('.tagadd').click();
                    });
                }
            }
        },

        /**
         * Update sentiment display
         */
        updateSentimentDisplay: function(data) {
            const $display = $('.smartnotify-sentiment-display');

            if ($display.length) {
                const html = `
                    <div class="sentiment-result" style="text-align: center; padding: 20px;">
                        <div class="sentiment-badge" style="display: inline-block; padding: 12px 24px; border-radius: 9999px; background-color: ${data.color}; color: white; font-size: 16px; font-weight: 600; margin-bottom: 10px;">
                            ${data.label}
                        </div>
                        <p class="sentiment-confidence" style="color: #6b7280; font-size: 14px; margin: 0;">
                            Confianza: ${Math.round(data.confidence * 100)}%
                        </p>
                    </div>
                `;

                $display.html(html);
            }
        },

        /**
         * Show loading state
         */
        showLoading: function($button, text) {
            $button.addClass('loading');
            $button.data('original-text', $button.text());
            $button.find('.dashicons').addClass('dashicons-update');

            const buttonText = $button.contents().filter(function() {
                return this.nodeType === 3;
            }).first();

            buttonText.replaceWith(' ' + text);
        },

        /**
         * Hide loading state
         */
        hideLoading: function($button) {
            $button.removeClass('loading');
            $button.find('.dashicons').removeClass('dashicons-update');

            const originalText = $button.data('original-text');
            if (originalText) {
                const buttonText = $button.contents().filter(function() {
                    return this.nodeType === 3;
                }).first();

                buttonText.replaceWith(' ' + originalText.trim());
            }
        },

        /**
         * Show message
         */
        showMessage: function(type, message) {
            const $status = $('.smartnotify-ai-status');

            if ($status.length) {
                $status.removeClass('success error info');
                $status.addClass('show ' + type);
                $status.find('.smartnotify-status-message').text(message);

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    $status.removeClass('show');
                }, 5000);
            } else {
                // Fallback to WordPress notices
                const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
                const $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');

                $('.wrap h1').after($notice);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    $notice.fadeOut(() => $notice.remove());
                }, 5000);
            }
        },

        /**
         * Handle provider change
         */
        handleProviderChange: function(e) {
            const provider = $(e.currentTarget).val();
            const $modelSelect = $('#smartnotify_ai_model');

            // Clear existing options
            $modelSelect.empty();

            // Add new options based on provider
            if (provider === 'openai') {
                $modelSelect.append('<option value="gpt-4">GPT-4</option>');
                $modelSelect.append('<option value="gpt-4-turbo">GPT-4 Turbo</option>');
                $modelSelect.append('<option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>');
            } else if (provider === 'anthropic') {
                $modelSelect.append('<option value="claude-sonnet-4-20250514">Claude Sonnet 4.5 (Recomendado)</option>');
                $modelSelect.append('<option value="claude-opus-4-20250514">Claude Opus 4.5</option>');
                $modelSelect.append('<option value="claude-haiku-4-20250417">Claude Haiku 4 (Rápido)</option>');
                $modelSelect.append('<option value="claude-3-7-sonnet-20250219">Claude Sonnet 3.7</option>');
                $modelSelect.append('<option value="claude-3-5-haiku-20241022">Claude Haiku 3.5</option>');
            }
        },

        /**
         * Handle API key change
         */
        handleApiKeyChange: function(e) {
            const $input = $(e.currentTarget);
            const $button = $('#smartnotify_test_api');

            if ($input.val().trim().length > 0) {
                $button.prop('disabled', false);
            } else {
                $button.prop('disabled', true);
            }
        },

        /**
         * Handle image provider change
         */
        handleImageProviderChange: function(e) {
            const $select = $(e.currentTarget);
            const provider = $select.val();
            const $apiKeyInput = $('#smartnotify_image_api_key');
            const $testButton = $('#smartnotify_test_image_api');

            if (provider === 'none') {
                $apiKeyInput.prop('disabled', true);
                $testButton.prop('disabled', true);
            } else {
                $apiKeyInput.prop('disabled', false);
                if ($apiKeyInput.val().trim().length > 0) {
                    $testButton.prop('disabled', false);
                }
            }
        },

        /**
         * Handle image API key change
         */
        handleImageApiKeyChange: function(e) {
            const $input = $(e.currentTarget);
            const $button = $('#smartnotify_test_image_api');
            const provider = $('#smartnotify_image_provider').val();

            if ($input.val().trim().length > 0 && provider !== 'none') {
                $button.prop('disabled', false);
            } else {
                $button.prop('disabled', true);
            }
        },

        /**
         * Test API connection
         */
        testApiConnection: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const $result = $('#smartnotify_api_test_result');

            // Show loading state
            $button.prop('disabled', true);
            const originalHtml = $button.html();
            $button.html('<span class="dashicons dashicons-update" style="animation: rotation 2s infinite linear; margin-top: 3px;"></span> Probando...');

            // Clear previous result
            $result.empty();

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_test_api',
                    nonce: smartnotifyAI.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $result.html(`
                            <div class="notice notice-success inline" style="margin: 0; padding: 8px 12px;">
                                <p style="margin: 0;">
                                    <strong>✓ ${response.data.message}</strong><br>
                                    <small>Proveedor: ${response.data.provider.toUpperCase()} | Modelo: ${response.data.model}</small><br>
                                    <small style="color: #666;">Respuesta: "${response.data.response}"</small>
                                </p>
                            </div>
                        `);
                    } else {
                        $result.html(`
                            <div class="notice notice-error inline" style="margin: 0; padding: 8px 12px;">
                                <p style="margin: 0;"><strong>✗ ${response.data.message}</strong></p>
                            </div>
                        `);
                    }
                },
                error: (xhr) => {
                    $result.html(`
                        <div class="notice notice-error inline" style="margin: 0; padding: 8px 12px;">
                            <p style="margin: 0;"><strong>✗ Error de conexión: ${xhr.statusText}</strong></p>
                        </div>
                    `);
                },
                complete: () => {
                    $button.prop('disabled', false);
                    $button.html(originalHtml);
                }
            });
        },

        /**
         * Test image API connection
         */
        testImageApiConnection: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const $result = $('#smartnotify_image_api_test_result');

            // Show loading state
            $button.prop('disabled', true);
            const originalHtml = $button.html();
            $button.html('<span class="dashicons dashicons-update" style="animation: rotation 2s infinite linear; margin-top: 3px;"></span> Probando...');

            // Clear previous result
            $result.empty();

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_test_image_api',
                    nonce: smartnotifyAI.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $result.html(`
                            <div class="notice notice-success inline" style="margin: 0; padding: 8px 12px;">
                                <p style="margin: 0;">
                                    <strong>${response.data.message}</strong><br>
                                    <small>Proveedor: ${response.data.provider} | Modelo: ${response.data.model}</small>
                                </p>
                            </div>
                        `);
                    } else {
                        $result.html(`
                            <div class="notice notice-error inline" style="margin: 0; padding: 8px 12px;">
                                <p style="margin: 0;"><strong>✗ ${response.data.message}</strong></p>
                            </div>
                        `);
                    }
                },
                error: (xhr) => {
                    $result.html(`
                        <div class="notice notice-error inline" style="margin: 0; padding: 8px 12px;">
                            <p style="margin: 0;"><strong>✗ Error de conexión: ${xhr.statusText}</strong></p>
                        </div>
                    `);
                },
                complete: () => {
                    $button.prop('disabled', false);
                    $button.html(originalHtml);
                }
            });
        },

        /**
         * Close modal
         */
        closeModal: function(e) {
            e.preventDefault();
            $('#smartnotify-news-modal').fadeOut(300);
            $('body').removeClass('modal-open');

            // Reset form
            $('#smartnotify-news-form')[0].reset();
            $('#news_tags_preview').empty();
            $('#smartnotify-form-status').hide();
            $('#smartnotify-sentiment-result').hide().empty();
            $('#analyzed_sentiment').val('');
            $('#analyzed_sentiment_confidence').val('');
            $('#featured_image_id').val('');
            $('#image_prompt').val('');

            // Reset image section
            $('#smartnotify-image-preview img').attr('src', '');
            $('#smartnotify-image-preview').hide();
            $('#smartnotify-image-loading').hide();
            $('#smartnotify-dropzone').show();
            $('.smartnotify-ai-generation-section').show();
            $('.smartnotify-library-upload').show();

            // Reset modal to create mode
            $('#smartnotify-news-form').removeData('post-id');
            $('#smartnotify-news-modal .smartnotify-modal-header h2').html(
                '<span class="dashicons dashicons-edit-large"></span> Crear Nueva Noticia'
            );
            $('#smartnotify-publish-news').html(
                '<span class="dashicons dashicons-yes"></span> Publicar Noticia'
            );

            // Remove edit_news parameter from URL
            if (window.location.href.indexOf('edit_news=') > -1) {
                window.history.replaceState({}, document.title, window.location.pathname + '?post_type=smartnotify_news');
            }
        },

        /**
         * Generate title in modal
         */
        generateTitleModal: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const content = $('#news_content').val();

            if (!content.trim()) {
                this.showModalMessage('error', 'Por favor, ingresa el contenido primero.');
                return;
            }

            this.showLoading($button, 'Generando...');

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_title_from_content',
                    nonce: smartnotifyAI.nonce,
                    content: content
                },
                success: (response) => {
                    if (response.success) {
                        $('#news_title').val(response.data.title);
                        this.showModalMessage('success', response.data.message);
                    } else {
                        this.showModalMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showModalMessage('error', 'Error: ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Generate summary in modal
         */
        generateSummaryModal: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const content = $('#news_content').val();

            if (!content.trim()) {
                this.showModalMessage('error', 'Por favor, ingresa el contenido primero.');
                return;
            }

            this.showLoading($button, 'Generando...');

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_summary_from_content',
                    nonce: smartnotifyAI.nonce,
                    content: content
                },
                success: (response) => {
                    if (response.success) {
                        $('#news_summary').val(response.data.summary);
                        this.showModalMessage('success', response.data.message);
                    } else {
                        this.showModalMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showModalMessage('error', 'Error: ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Generate tags in modal
         */
        generateTagsModal: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const content = $('#news_content').val();

            if (!content.trim()) {
                this.showModalMessage('error', 'Por favor, ingresa el contenido primero.');
                return;
            }

            this.showLoading($button, 'Generando...');

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_tags_from_content',
                    nonce: smartnotifyAI.nonce,
                    content: content
                },
                success: (response) => {
                    if (response.success) {
                        $('#news_tags').val(response.data.tags.join(', '));
                        $('#news_tags').trigger('input');
                        this.showModalMessage('success', response.data.message);
                    } else {
                        this.showModalMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showModalMessage('error', 'Error: ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Analyze sentiment in modal
         */
        analyzeSentimentModal: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const content = $('#news_content').val();

            if (!content.trim()) {
                this.showModalMessage('error', 'Por favor, ingresa el contenido primero.');
                return;
            }

            this.showLoading($button, 'Analizando...');

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_analyze_sentiment_from_content',
                    nonce: smartnotifyAI.nonce,
                    content: content
                },
                success: (response) => {
                    if (response.success) {
                        this.updateSentimentDisplayModal(response.data);
                        // Store sentiment in hidden fields
                        $('#analyzed_sentiment').val(response.data.sentiment);
                        $('#analyzed_sentiment_confidence').val(response.data.confidence);
                        this.showModalMessage('success', response.data.message);
                    } else {
                        this.showModalMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    this.showModalMessage('error', 'Error: ' + xhr.statusText);
                },
                complete: () => {
                    this.hideLoading($button);
                }
            });
        },

        /**
         * Update sentiment display in modal
         */
        updateSentimentDisplayModal: function(data) {
            const $display = $('#smartnotify-sentiment-result');

            // Determine confidence level text and color
            let confidenceLevel = '';
            let confidenceLevelColor = '#6b7280';

            if (data.confidence) {
                const confidencePercent = Math.round(data.confidence * 100);
                if (confidencePercent >= 90) {
                    confidenceLevel = 'Muy alta';
                    confidenceLevelColor = '#059669';
                } else if (confidencePercent >= 75) {
                    confidenceLevel = 'Alta';
                    confidenceLevelColor = '#0891b2';
                } else if (confidencePercent >= 60) {
                    confidenceLevel = 'Media';
                    confidenceLevelColor = '#d97706';
                } else {
                    confidenceLevel = 'Baja';
                    confidenceLevelColor = '#dc2626';
                }
            }

            const html = `
                <div class="sentiment-result" style="margin-top: 15px; padding: 15px; background: #f9fafb; border-radius: 8px; text-align: center;">
                    <div class="sentiment-badge" style="display: inline-block; padding: 8px 20px; border-radius: 9999px; background-color: ${data.color}; color: white; font-size: 14px; font-weight: 600;">
                        ${data.label}
                    </div>
                    ${data.confidence ? `
                        <p style="color: #6b7280; font-size: 13px; margin: 8px 0 4px 0;">
                            Confianza: <strong style="color: ${confidenceLevelColor};">${Math.round(data.confidence * 100)}%</strong> (${confidenceLevel})
                        </p>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 11px; margin: 0; line-height: 1.4;">
                                <strong>Nivel de confianza:</strong><br>
                                <span style="color: #059669;">■</span> Muy alta (90-100%) ·
                                <span style="color: #0891b2;">■</span> Alta (75-89%) ·
                                <span style="color: #d97706;">■</span> Media (60-74%) ·
                                <span style="color: #dc2626;">■</span> Baja (<60%)
                            </p>
                        </div>
                    ` : ''}
                </div>
            `;

            $display.html(html).show();
        },

        /**
         * Display stored sentiment data (when editing)
         */
        displayStoredSentiment: function() {
            const sentimentData = $('#smartnotify-news-form').data('sentiment-data');
            if (sentimentData) {
                this.updateSentimentDisplayModal(sentimentData);
            }
        },

        /**
         * Update tags preview
         */
        updateTagsPreview: function(e) {
            const tags = $(e.currentTarget).val();
            const $preview = $('#news_tags_preview');

            if (!tags.trim()) {
                $preview.empty();
                return;
            }

            const tagArray = tags.split(',').map(tag => tag.trim()).filter(tag => tag);
            const html = tagArray.map(tag =>
                `<span class="smartnotify-tag">${tag}</span>`
            ).join('');

            $preview.html(html);
        },

        /**
         * Save draft
         */
        saveDraft: function(e) {
            e.preventDefault();
            this.saveNews('draft');
        },

        /**
         * Publish news
         */
        publishNews: function(e) {
            e.preventDefault();
            this.saveNews('publish');
        },

        /**
         * Save news (draft or publish)
         */
        saveNews: function(status) {
            const $form = $('#smartnotify-news-form');
            const title = $('#news_title').val();
            const content = $('#news_content').val();
            const postId = $form.data('post-id');

            // Validate required fields
            if (!title.trim() || !content.trim()) {
                this.showModalMessage('error', 'Por favor, completa el título y el contenido.');
                return;
            }

            // Disable buttons
            const $publishBtn = $('#smartnotify-publish-news');
            const $draftBtn = $('#smartnotify-save-draft');
            $publishBtn.prop('disabled', true);
            $draftBtn.prop('disabled', true);

            // Show loading message
            let loadingMsg;
            if (postId) {
                loadingMsg = 'Actualizando noticia...';
            } else {
                loadingMsg = status === 'publish' ? 'Publicando noticia...' : 'Guardando borrador...';
            }
            this.showModalMessage('info', loadingMsg);

            const data = {
                action: postId ? 'smartnotify_update_news' : 'smartnotify_save_news',
                nonce: $('#smartnotify_news_nonce').val(),
                status: status,
                title: title,
                content: content,
                summary: $('#news_summary').val(),
                tags: $('#news_tags').val(),
                category: $('#news_category').val(),
                analyzed_sentiment: $('#analyzed_sentiment').val(),
                analyzed_sentiment_confidence: $('#analyzed_sentiment_confidence').val(),
                featured_image_id: $('#featured_image_id').val()
            };

            if (postId) {
                data.post_id = postId;
            }

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.showModalMessage('success', response.data.message);

                        // Redirect after 1 second
                        setTimeout(() => {
                            window.location.href = 'edit.php?post_type=smartnotify_news';
                        }, 1000);
                    } else {
                        this.showModalMessage('error', response.data.message);
                        $publishBtn.prop('disabled', false);
                        $draftBtn.prop('disabled', false);
                    }
                },
                error: (xhr) => {
                    this.showModalMessage('error', 'Error: ' + xhr.statusText);
                    $publishBtn.prop('disabled', false);
                    $draftBtn.prop('disabled', false);
                }
            });
        },

        /**
         * Show modal message
         */
        showModalMessage: function(type, message) {
            const $status = $('#smartnotify-form-status');
            const iconMap = {
                success: 'yes',
                error: 'warning',
                info: 'info'
            };

            $status.removeClass('success error info');
            $status.addClass(type);
            $status.html(`
                <span class="dashicons dashicons-${iconMap[type]}"></span>
                ${message}
            `);
            $status.show();

            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    $status.fadeOut();
                }, 5000);
            }
        },

        /**
         * Upload image using WordPress media uploader
         */
        uploadImage: function(e) {
            e.preventDefault();

            // If the media frame already exists, reopen it
            if (this.imageFrame) {
                this.imageFrame.open();
                return;
            }

            // Create the media frame
            this.imageFrame = wp.media({
                title: 'Seleccionar Imagen Destacada',
                button: {
                    text: 'Usar esta imagen'
                },
                multiple: false
            });

            // When an image is selected, run a callback
            this.imageFrame.on('select', () => {
                const attachment = this.imageFrame.state().get('selection').first().toJSON();
                this.setFeaturedImage(attachment.id, attachment.url);
            });

            // Open the modal
            this.imageFrame.open();
        },

        /**
         * Generate image from prompt
         */
        generateImageFromPrompt: function(e) {
            e.preventDefault();

            const prompt = $('#image_prompt').val().trim();

            if (!prompt) {
                this.showModalMessage('error', 'Por favor ingresa una descripción para la imagen');
                return;
            }

            this.generateImage(prompt);
        },

        /**
         * Generate image from content
         */
        generateImageFromContent: function(e) {
            e.preventDefault();

            const title = $('#news_title').val().trim();
            const content = $('#news_content').val().trim();

            if (!content) {
                this.showModalMessage('error', 'Por favor escribe el contenido de la noticia primero');
                return;
            }

            // Create a prompt based on the content
            const prompt = `Crear una imagen profesional y atractiva para un artículo de noticias titulado: "${title}". El artículo trata sobre: ${content.substring(0, 200)}...`;

            this.generateImage(prompt);
        },

        /**
         * Generate image via AJAX
         */
        generateImage: function(prompt) {
            const $generateBtn = $('.smartnotify-generate-image-prompt, .smartnotify-generate-image-content');

            // Disable buttons and show loading
            $generateBtn.prop('disabled', true);
            $('#smartnotify-dropzone').hide();
            $('#smartnotify-image-preview').hide();
            $('#smartnotify-image-loading').show();
            $('.smartnotify-ai-generation-section').hide();
            $('.smartnotify-library-upload').hide();

            $.ajax({
                url: smartnotifyAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smartnotify_generate_image',
                    nonce: smartnotifyAI.nonce,
                    prompt: prompt
                },
                success: (response) => {
                    $generateBtn.prop('disabled', false);
                    $('#smartnotify-image-loading').hide();

                    if (response.success) {
                        this.setFeaturedImage(response.data.attachment_id, response.data.url);
                        this.showModalMessage('success', response.data.message);
                        // Clear prompt field
                        $('#image_prompt').val('');
                    } else {
                        $('#smartnotify-dropzone').show();
                        $('.smartnotify-ai-generation-section').show();
                        $('.smartnotify-library-upload').show();
                        this.showModalMessage('error', response.data.message);
                    }
                },
                error: (xhr) => {
                    $generateBtn.prop('disabled', false);
                    $('#smartnotify-image-loading').hide();
                    $('#smartnotify-dropzone').show();
                    $('.smartnotify-ai-generation-section').show();
                    $('.smartnotify-library-upload').show();
                    this.showModalMessage('error', 'Error: ' + xhr.statusText);
                }
            });
        },

        /**
         * Set featured image
         */
        setFeaturedImage: function(attachmentId, imageUrl) {
            $('#featured_image_id').val(attachmentId);
            $('#smartnotify-image-preview img').attr('src', imageUrl);
            $('#smartnotify-image-preview').show();
            $('#smartnotify-dropzone').hide();
            $('.smartnotify-ai-generation-section').hide();
            $('.smartnotify-library-upload').hide();
        },

        /**
         * Remove featured image
         */
        removeImage: function(e) {
            e.preventDefault();
            e.stopPropagation();

            $('#featured_image_id').val('');
            $('#smartnotify-image-preview img').attr('src', '');
            $('#smartnotify-image-preview').hide();
            $('#smartnotify-dropzone').show();
            $('.smartnotify-ai-generation-section').show();
            $('.smartnotify-library-upload').show();
        },

        /**
         * Handle dropzone click - opens file picker
         */
        handleDropzoneClick: function(e) {
            // Prevent clicking on the file input from triggering this handler
            if (e.target.id === 'smartnotify-file-input') {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const fileInput = document.getElementById('smartnotify-file-input');
            if (fileInput) {
                // Use a setTimeout to break out of the event loop
                setTimeout(() => {
                    fileInput.click();
                }, 10);
            }
        },

        /**
         * Handle file input change
         */
        handleFileInputChange: function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                this.handleFileUpload(files[0]);
                // Reset file input
                e.target.value = '';
            }
        },

        /**
         * Initialize Drag & Drop functionality
         */
        initDragAndDrop: function() {
            const self = this;

            // Use event delegation for drag events
            $(document).on('dragover', '#smartnotify-dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            $(document).on('dragleave', '#smartnotify-dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            $(document).on('drop', '#smartnotify-dropzone', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files && files.length > 0) {
                    self.handleFileUpload(files[0]);
                }
            });
        },

        /**
         * Handle file upload
         */
        handleFileUpload: function(file) {
            // Validate file type
            if (!file.type.match('image.*')) {
                this.showModalMessage('error', 'Por favor selecciona una imagen válida (JPG, PNG, GIF).');
                return;
            }

            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                this.showModalMessage('error', 'La imagen es demasiado grande. Tamaño máximo: 10MB');
                return;
            }

            // Show loading
            $('#smartnotify-dropzone').hide();
            $('#smartnotify-image-loading').show();
            $('.smartnotify-ai-generation-section').hide();
            $('.smartnotify-library-upload').hide();

            // Convert file to base64
            const reader = new FileReader();

            reader.onload = (e) => {
                const base64Data = e.target.result;

                // Upload via custom AJAX handler
                $.ajax({
                    url: smartnotifyAI.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'smartnotify_upload_image',
                        nonce: smartnotifyAI.nonce,
                        image_data: base64Data,
                        filename: file.name
                    },
                    success: (response) => {
                        $('#smartnotify-image-loading').hide();

                        if (response.success && response.data && response.data.attachment_id) {
                            this.setFeaturedImage(response.data.attachment_id, response.data.url);
                            this.showModalMessage('success', 'Imagen subida exitosamente');
                        } else {
                            $('#smartnotify-dropzone').show();
                            $('.smartnotify-ai-generation-section').show();
                            $('.smartnotify-library-upload').show();
                            const errorMsg = response.data && response.data.message ? response.data.message : 'Error al subir la imagen';
                            this.showModalMessage('error', errorMsg);
                        }
                    },
                    error: (xhr, status, error) => {
                        $('#smartnotify-image-loading').hide();
                        $('#smartnotify-dropzone').show();
                        $('.smartnotify-ai-generation-section').show();
                        $('.smartnotify-library-upload').show();
                        console.error('Upload error:', error, xhr.responseText);
                        this.showModalMessage('error', 'Error al subir la imagen. Por favor intenta de nuevo.');
                    }
                });
            };

            reader.onerror = () => {
                $('#smartnotify-image-loading').hide();
                $('#smartnotify-dropzone').show();
                $('.smartnotify-ai-generation-section').show();
                $('.smartnotify-library-upload').show();
                this.showModalMessage('error', 'Error al leer el archivo');
            };

            reader.readAsDataURL(file);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        SmartNotifyAdmin.init();
    });

})(jQuery);
