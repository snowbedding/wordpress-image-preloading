/**
 * Image Preloading JavaScript
 *
 * Modern image preloading using Promise-based approach with error handling
 * and performance optimizations.
 *
 * @version 2.0.0
 */

(function() {
    'use strict';

    /**
     * Image Preloader Class
     */
    class ImagePreloader {
        constructor() {
            this.preloadedImages = new Set();
            this.maxConcurrent = 3; // Default concurrent loads
            this.init();
        }

        /**
         * Initialize the preloader
         */
        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.startPreloading());
            } else {
                this.startPreloading();
            }

            // Also listen for window load to preload additional images
            window.addEventListener('load', () => this.onWindowLoad());
        }

        /**
         * Start preloading images
         */
        startPreloading() {
            if (typeof imagePreloadingData === 'undefined' || !imagePreloadingData.images) {
                console.log('[Image Preloading] No image data found or plugin not configured');
                return;
            }

            const images = imagePreloadingData.images;
            if (!Array.isArray(images) || images.length === 0) {
                console.log('[Image Preloading] No images to preload');
                return;
            }

            console.log(`[Image Preloading] Starting preload of ${images.length} images using method: ${imagePreloadingData.method || 'javascript'}`);

            // Additional method-specific logging
            if (imagePreloadingData.method === 'both') {
                console.log('[Image Preloading] Using both JavaScript preloading and Link preload headers');
                console.log('[Image Preloading] Note: Link preload headers are added to HTML <head> via PHP');
            } else if (imagePreloadingData.method === 'link_preload') {
                console.log('[Image Preloading] Using Link preload headers only (check HTML source for <link rel="preload"> tags)');
                console.log('[Image Preloading] Note: All preloading is handled by HTML <link> tags in the page head');
            }

            // Set max concurrent loads
            if (imagePreloadingData.maxConcurrent) {
                this.maxConcurrent = Math.min(imagePreloadingData.maxConcurrent, 10);
                console.log(`[Image Preloading] Max concurrent loads: ${this.maxConcurrent}`);
            }

            // Debug info in development
            if (typeof imagePreloadingDebug !== 'undefined') {
                console.log('[Image Preloading] Debug info:', imagePreloadingDebug);
            }

            // Use requestIdleCallback if available for better performance
            if ('requestIdleCallback' in window) {
                requestIdleCallback(() => this.preloadImages(images), { timeout: 2000 });
            } else {
                // Fallback for browsers without requestIdleCallback
                setTimeout(() => this.preloadImages(images), 100);
            }
        }

        /**
         * Preload images using modern Promise-based approach with concurrency control
         *
         * @param {Array} imageUrls Array of image URLs to preload
         */
        preloadImages(imageUrls) {
            let index = 0;
            const results = [];
            const total = imageUrls.length;

            const loadNext = () => {
                if (index >= total) {
                    // All images processed
                    this.logPreloadingResults(results);
                    return;
                }

                const currentIndex = index;
                index++;

                this.preloadSingleImage(imageUrls[currentIndex])
                    .then(() => {
                        results[currentIndex] = { status: 'fulfilled' };
                        loadNext(); // Load next image
                    })
                    .catch(error => {
                        results[currentIndex] = { status: 'rejected', reason: error };
                        loadNext(); // Continue with next image even if this one failed
                    });
            };

            // Start loading initial batch
            for (let i = 0; i < Math.min(this.maxConcurrent, total); i++) {
                loadNext();
            }
        }

        /**
         * Preload a single image
         *
         * @param {string} url Image URL to preload
         * @return {Promise} Promise that resolves when image is loaded
         */
        preloadSingleImage(url) {
            return new Promise((resolve, reject) => {
                // Skip if already preloaded
                if (this.preloadedImages.has(url)) {
                    resolve();
                    return;
                }

                // Skip invalid URLs
                if (!url || typeof url !== 'string') {
                    reject(new Error('Invalid URL'));
                    return;
                }

                const img = new Image();

                // Set up event handlers
                img.onload = () => {
                    this.preloadedImages.add(url);
                    resolve();
                };

                img.onerror = () => {
                    reject(new Error(`Failed to load image: ${url}`));
                };

                // Add timeout for safety
                setTimeout(() => {
                    if (!img.complete) {
                        img.src = ''; // Cancel loading
                        reject(new Error(`Timeout loading image: ${url}`));
                    }
                }, 10000); // 10 second timeout

                // Start loading
                img.src = url;

                // Handle CORS if needed
                if (url.indexOf('http') === 0 && url.indexOf(window.location.origin) !== 0) {
                    img.crossOrigin = 'anonymous';
                }
            });
        }

        /**
         * Handle window load event
         */
        onWindowLoad() {
            // Could be used for additional lazy preloading logic
            // For now, just mark that the page has fully loaded
            this.pageLoaded = true;
        }

        /**
         * Log preloading results
         *
         * @param {Array} results Results from preloading process
         */
        logPreloadingResults(results) {
            const successful = results.filter(result => result && result.status === 'fulfilled').length;
            const failed = results.filter(result => result && result.status === 'rejected').length;
            const total = results.length;

            console.log(`[Image Preloading] Preload completed: ${successful}/${total} successful, ${failed} failed`);

            if (successful > 0) {
                console.log('[Image Preloading] Images preloaded successfully and cached in browser');
            }

            if (failed > 0) {
                console.warn('[Image Preloading] Some images failed to load. Check URLs, network connectivity, and CORS policies.');
                // Log failed URLs for debugging
                results.forEach((result, index) => {
                    if (result && result.status === 'rejected') {
                        console.warn(`[Image Preloading] Failed to load: ${imagePreloadingData.images[index]}`);
                    }
                });
            }
        }

        /**
         * Public method to preload additional images dynamically
         *
         * @param {Array|string} urls Image URL(s) to preload
         */
        preload(urls) {
            const urlArray = Array.isArray(urls) ? urls : [urls];
            this.preloadImages(urlArray);
        }
    }

    // Initialize the preloader when DOM is ready
    new ImagePreloader();

})();
