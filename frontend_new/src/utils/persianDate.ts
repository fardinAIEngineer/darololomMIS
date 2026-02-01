/**
 * Persian/Jalali Date Utilities
 * For converting and formatting Persian calendar dates
 */

import { format, parseISO } from 'date-fns';
import { toPersianNumber } from './persianNumbers';

/**
 * Format Gregorian date to Persian display
 * @param date - Date string or Date object
 * @returns Formatted Persian date string
 */
export function formatPersianDate(date: string | Date): string {
  try {
    const dateObj = typeof date === 'string' ? parseISO(date) : date;
    // For now, format in Gregorian but with Persian numerals
    // Can be extended with full Jalali conversion if needed
    const formatted = format(dateObj, 'yyyy/MM/dd');
    return toPersianNumber(formatted);
  } catch {
    return '';
  }
}

/**
 * Format time to Persian display
 * @param time - Time string (HH:mm:ss)
 * @returns Formatted Persian time string
 */
export function formatPersianTime(time: string): string {
  try {
    const [hours, minutes] = time.split(':');
    return `${toPersianNumber(hours)}:${toPersianNumber(minutes)}`;
  } catch {
    return time;
  }
}

/**
 * Format date and time to Persian display
 * @param datetime - DateTime string
 * @returns Formatted Persian datetime string
 */
export function formatPersianDateTime(datetime: string | Date): string {
  try {
    const dateObj = typeof datetime === 'string' ? parseISO(datetime) : datetime;
    const date = format(dateObj, 'yyyy/MM/dd');
    const time = format(dateObj, 'HH:mm');
    return `${toPersianNumber(date)} - ${toPersianNumber(time)}`;
  } catch {
    return '';
  }
}

/**
 * Get Persian day names
 */
export const persianDayNames = [
  'یکشنبه',
  'دوشنبه',
  'سه‌شنبه',
  'چهارشنبه',
  'پنج‌شنبه',
  'جمعه',
  'شنبه',
];

/**
 * Get Persian month names (Gregorian)
 */
export const persianMonthNames = [
  'جنوری',
  'فبروری',
  'مارچ',
  'اپریل',
  'می',
  'جون',
  'جولای',
  'اگست',
  'سپتمبر',
  'اکتوبر',
  'نومبر',
  'دسمبر',
];

/**
 * Get day name in Persian
 * @param date - Date object
 * @returns Persian day name
 */
export function getPersianDayName(date: Date): string {
  return persianDayNames[date.getDay()];
}

/**
 * Get month name in Persian
 * @param date - Date object
 * @returns Persian month name
 */
export function getPersianMonthName(date: Date): string {
  return persianMonthNames[date.getMonth()];
}
