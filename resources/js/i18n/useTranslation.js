import i18n from './index';

/**
 * A hook to use translations in components
 * @returns {Object} An object containing the translation function
 */
export function useTranslation() {
  /**
   * Translate a key
   * @param {string} key - The translation key
   * @param {Object|string} value - Parameters for interpolation or fallback value
   * @returns {string} The translated string
   */
  const t = (key, value) => {
    // Handle case where key is an object (e.g., for interpolation)
    if (typeof key === 'object') {
      return key;
    }

    try {
      // For keys with parameters like 'showing_results'
      if (typeof value === 'object' && value !== null) {
        return i18n.global.t(key, value);
      }

      // If value is provided as a string, it's a fallback value
      if (typeof value === 'string') {
        return i18n.global.te(key) ? i18n.global.t(key) : value;
      }

      // Otherwise, just translate the key
      return i18n.global.t(key);
    } catch (error) {
      console.error(`Translation error for key "${key}":`, error);
      return typeof value === 'string' ? value : key;
    }
  };

  return { t };
}