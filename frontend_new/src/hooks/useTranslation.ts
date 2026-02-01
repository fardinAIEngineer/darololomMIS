/**
 * Translation Hook
 * Provides easy access to translations throughout the app
 */

import { fa, TranslationKeys } from '@/locales/fa';

type NestedKeyOf<ObjectType extends object> = {
  [Key in keyof ObjectType & (string | number)]: ObjectType[Key] extends object
    ? `${Key}` | `${Key}.${NestedKeyOf<ObjectType[Key]>}`
    : `${Key}`;
}[keyof ObjectType & (string | number)];

type TranslationPath = NestedKeyOf<TranslationKeys>;

/**
 * Get nested value from object using dot notation path
 * Example: get(obj, 'common.save') => obj.common.save
 */
function get<T>(obj: any, path: string): T {
  const keys = path.split('.');
  let result = obj;
  
  for (const key of keys) {
    if (result && typeof result === 'object' && key in result) {
      result = result[key];
    } else {
      return path as T; // Return path if not found (fallback)
    }
  }
  
  return result;
}

/**
 * Main translation hook
 * Usage: const { t } = useTranslation();
 * Then: t('common.save') => 'ذخیره'
 */
export function useTranslation() {
  /**
   * Translate function
   * @param key - Translation key in dot notation (e.g., 'common.save')
   * @param params - Optional parameters for dynamic values (e.g., {min: 5})
   * @returns Translated string
   */
  const t = (key: TranslationPath, params?: Record<string, any>): string => {
    let translation = get<string>(fa, key);
    
    // Replace parameters in translation
    if (params && typeof translation === 'string') {
      Object.keys(params).forEach((param) => {
        translation = translation.replace(`{${param}}`, String(params[param]));
      });
    }
    
    return translation;
  };

  return { t };
}
