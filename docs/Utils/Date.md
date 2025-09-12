# Date Utility

Provides functions for working with Jalali (Persian) and Gregorian dates, conversion, and formatting.

---

## Methods

### tr_num
Converts numbers between Persian and English formats.

**Signature:**
```php
public static function tr_num($str, $mod = 'en', $mf = '\u066b')
```

**Parameters:**
- `str` (string): Input string.
- `mod` (string): 'fa' for Persian, 'en' for English.
- `mf` (string): Decimal separator.

**Returns:**
- (string) Converted string.

---

### jdate_words
Returns Persian words for date parts (month, day, etc.).

**Signature:**
```php
public static function jdate_words($array, $mod = '')
```

**Parameters:**
- `array` (array): Date parts.
- `mod` (string): Separator.

**Returns:**
- (array|string) Persian words or joined string.

---

### jdate
Formats a Jalali date string.

**Signature:**
```php
public static function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
```

**Parameters:**
- `format` (string): Date format.
- `timestamp` (string): Timestamp.
- `time_zone` (string): Timezone.
- `tr_num` (string): Number format.

**Returns:**
- (string) Formatted Jalali date.

---

### gregorian_to_jalali / jalali_to_gregorian
Convert between Gregorian and Jalali dates.

**Signature:**
```php
public static function gregorian_to_jalali($gy, $gm, $gd, $mod='')
public static function jalali_to_gregorian($jy, $jm, $jd, $mod='')
```

**Parameters:**
- Year, month, day.
- `mod` (string): Separator.

**Returns:**
- (array|string) Converted date.

---

### j2g / g2j
Convert Jalali date to Gregorian and vice versa (Y-m-d H:i:s or Y-m-d format).

**Signature:**
```php
public static function j2g(string $date): string
public static function g2j(string $date): string
```

**Parameters:**
- `date` (string): Date string.

**Returns:**
- (string) Converted date.

---

### maybe_j2g / maybe_g2j
Check and convert Jalali/Gregorian if needed.

**Signature:**
```php
public static function maybe_j2g(string $date): string
public static function maybe_g2j(string $date): string
```

**Parameters:**
- `date` (string): Date string.

**Returns:**
- (string) Converted date if needed.

---

### get_time_period
Returns Persian label for a time period (morning, noon, etc.)

**Signature:**
```php
public static function get_time_period(int $hour)
```

**Parameters:**
- `hour` (int): Hour of day.

**Returns:**
- (string) Time period label.

---

### first_day_of_jalali_month / last_day_of_jalali_month
Get Gregorian date for first/last day of current Jalali month.

**Signature:**
```php
public static function first_day_of_jalali_month()
public static function last_day_of_jalali_month()
```

**Returns:**
- (string) Gregorian date in 'Y-m-d' format.

**Notes:**
- Useful for Persian calendar integrations.
- Handles leap years and month rules.
