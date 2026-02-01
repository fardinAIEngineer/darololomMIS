/**
 * Persian Number Utilities
 * Convert between English and Persian numerals
 */

const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
const englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

/**
 * Convert English numbers to Persian numerals
 * Example: 123 => '۱۲۳'
 */
export function toPersianNumber(num: string | number): string {
  const str = String(num);
  return str.replace(/\d/g, (digit) => persianDigits[parseInt(digit)]);
}

/**
 * Convert Persian numerals to English numbers
 * Example: '۱۲۳' => '123'
 */
export function toEnglishNumber(str: string): string {
  return str.replace(/[۰-۹]/g, (digit) => {
    const index = persianDigits.indexOf(digit);
    return index !== -1 ? englishDigits[index] : digit;
  });
}

/**
 * Format number with Persian thousand separators
 * Example: 1234567 => '۱,۲۳۴,۵۶۷'
 */
export function formatPersianNumber(num: number | string): string {
  const numStr = String(num);
  const parts = numStr.split('.');
  const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  const formatted = parts.length > 1 ? `${integerPart}.${parts[1]}` : integerPart;
  return toPersianNumber(formatted);
}

/**
 * Parse Persian number string to JavaScript number
 * Example: '۱۲۳' => 123
 */
export function parsePersianNumber(str: string): number {
  const englishStr = toEnglishNumber(str).replace(/,/g, '');
  return parseFloat(englishStr);
}
