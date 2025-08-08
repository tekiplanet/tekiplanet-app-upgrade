/**
 * Utility functions for handling return URLs consistently across the application
 */

export const returnUrlUtils = {
  /**
   * Save the current URL as a return URL
   */
  saveReturnUrl: (url?: string) => {
    const returnUrl = url || window.location.href;
    localStorage.setItem('returnUrl', returnUrl);
    console.log('🔗 ReturnUrlUtils: Saved return URL:', returnUrl);
  },

  /**
   * Get and clear the return URL
   */
  getAndClearReturnUrl: () => {
    const returnUrl = localStorage.getItem('returnUrl');
    if (returnUrl) {
      localStorage.removeItem('returnUrl');
      console.log('🔗 ReturnUrlUtils: Retrieved and cleared return URL:', returnUrl);
      return returnUrl;
    }
    console.log('🔗 ReturnUrlUtils: No return URL found');
    return null;
  },

  /**
   * Get return URL without clearing it (for debugging)
   */
  peekReturnUrl: () => {
    const returnUrl = localStorage.getItem('returnUrl');
    console.log('🔗 ReturnUrlUtils: Peeking return URL:', returnUrl);
    return returnUrl;
  },

  /**
   * Check if there's a return URL and redirect to it, otherwise redirect to dashboard
   */
  redirectToReturnUrlOrDashboard: (navigate: any) => {
    console.log('🔗 ReturnUrlUtils: Starting redirectToReturnUrlOrDashboard');
    const returnUrl = returnUrlUtils.getAndClearReturnUrl();
    if (returnUrl) {
      console.log('🔗 ReturnUrlUtils: Redirecting to return URL:', returnUrl);
      // Use a more reliable redirect method
      try {
        // Try using navigate first for internal routes
        if (returnUrl.includes(window.location.origin)) {
          // Extract the path including hash for hash routing
          const url = new URL(returnUrl);
          // For hash routing, we need to handle the path differently
          // If the URL has a hash, use the hash as the path
          if (url.hash) {
            const path = url.hash.substring(1); // Remove the # from the hash
            console.log('🔗 ReturnUrlUtils: Using navigate for internal route with hash:', path);
            navigate(path, { replace: true });
          } else {
            // If no hash, use the pathname
            console.log('🔗 ReturnUrlUtils: Using navigate for internal route:', url.pathname);
            navigate(url.pathname, { replace: true });
          }
        } else {
          // For external URLs or complex URLs, use window.location
          console.log('🔗 ReturnUrlUtils: Using window.location.href for redirect');
          window.location.href = returnUrl;
        }
      } catch (error) {
        console.error('🔗 ReturnUrlUtils: Redirect failed, falling back to window.location:', error);
        window.location.href = returnUrl;
      }
    } else {
      console.log('🔗 ReturnUrlUtils: No return URL, redirecting to dashboard');
      navigate('/dashboard');
    }
  },

  /**
   * Check if there's a return URL and redirect to it, otherwise use the provided fallback
   */
  redirectToReturnUrlOrFallback: (navigate: any, fallbackPath: string) => {
    console.log('🔗 ReturnUrlUtils: Starting redirectToReturnUrlOrFallback');
    const returnUrl = returnUrlUtils.getAndClearReturnUrl();
    if (returnUrl) {
      console.log('🔗 ReturnUrlUtils: Redirecting to return URL:', returnUrl);
      try {
        // Try using navigate first for internal routes
        if (returnUrl.includes(window.location.origin)) {
          // Extract the path including hash for hash routing
          const url = new URL(returnUrl);
          // For hash routing, we need to handle the path differently
          // If the URL has a hash, use the hash as the path
          if (url.hash) {
            const path = url.hash.substring(1); // Remove the # from the hash
            console.log('🔗 ReturnUrlUtils: Using navigate for internal route with hash:', path);
            navigate(path, { replace: true });
          } else {
            // If no hash, use the pathname
            console.log('🔗 ReturnUrlUtils: Using navigate for internal route:', url.pathname);
            navigate(url.pathname, { replace: true });
          }
        } else {
          // For external URLs or complex URLs, use window.location
          console.log('🔗 ReturnUrlUtils: Using window.location.href for redirect');
          window.location.href = returnUrl;
        }
      } catch (error) {
        console.error('🔗 ReturnUrlUtils: Redirect failed, falling back to window.location:', error);
        window.location.href = returnUrl;
      }
    } else {
      console.log('🔗 ReturnUrlUtils: No return URL, redirecting to fallback:', fallbackPath);
      navigate(fallbackPath);
    }
  },

  /**
   * Debug function to check current return URL state
   */
  debugReturnUrl: () => {
    const returnUrl = localStorage.getItem('returnUrl');
    console.log('🔗 ReturnUrlUtils Debug:', {
      hasReturnUrl: !!returnUrl,
      returnUrl: returnUrl,
      currentUrl: window.location.href
    });
    return returnUrl;
  }
};
