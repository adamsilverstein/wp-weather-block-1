/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 */

document.addEventListener('DOMContentLoaded', () => {
  const weatherBlocks = document.querySelectorAll('.wp-block-create-block-weather-block');

  weatherBlocks.forEach((block) => {
    const displayMode = block.dataset.displayMode || 'auto';

    if (displayMode === 'auto') {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (prefersDark) {
        block.classList.add('is-dark-mode');
      } else {
        block.classList.remove('is-dark-mode');
      }
    }
  });
});
