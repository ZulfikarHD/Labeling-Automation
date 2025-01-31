import { onBeforeUnmount } from "vue";
/**
 * Debounce function to limit API calls
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 */
export const debounce = (func, delay) => {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), delay);
  };
};

/**
 * Calculate print timeout duration based on label count
 * @param {number} labelCount - Number of labels to print
 * @returns {number} Timeout duration in milliseconds
 */
export const calculateTimeoutDuration = (labelCount) => {
  return labelCount > 10 ? Math.round(1000 * (labelCount / 5)) : 1000;
};

/**
 * Memory management for printing operations
 */
export const memoryManager = {
  clearDOMRefs: () => {
    // Implementation from your existing code
  },
  clearReactiveData: () => {
    // Implementation from your existing code
  },
  resetForms: () => {
    // Implementation from your existing code
  },
  stringCache: new Map(),
  internString(str) {
    // Implementation from your existing code
  },
  clearCache() {
    // Implementation from your existing code
  },
  optimizeDOM() {
    // Implementation from your existing code
  }
};

/**
 * Composable for print-related functionality
 */
export function usePrinting() {
  const cleanup = () => {
    memoryManager.clearDOMRefs();
    memoryManager.clearReactiveData();
    memoryManager.resetForms();
    memoryManager.optimizeDOM();
    memoryManager.clearCache();

    if (window._timeouts) {
      window._timeouts.forEach(timeout => clearTimeout(timeout));
      window._timeouts = [];
    }

    // Force garbage collection hint
    if (window.gc) {
      window.gc();
    }
  };

  const setupPrintCleanup = () => {
    window._timeouts = [];
    // Pre-allocate common strings
    ['Kiri', 'Kanan', 'INS'].forEach(str =>
      memoryManager.internString(str)
    );

    let cleanupInterval = setInterval(() => {
      memoryManager.optimizeDOM();
    }, 60000); // Run every minute

    onBeforeUnmount(() => {
      if (cleanupInterval) {
        clearInterval(cleanupInterval);
      }
      cleanup();
    });
  };

  return {
    cleanup,
    setupPrintCleanup,
    debounce,
    calculateTimeoutDuration,
    memoryManager
  };
}
