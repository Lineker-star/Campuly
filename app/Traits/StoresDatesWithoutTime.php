<?php

namespace App\Traits;

/**
 * Write date-only attributes as "Y-m-d", without a time.
 *
 * Eloquent writes every date cast with the model's full date format, so a
 * "date" column receives "2026-11-05 00:00:00". MySQL and PostgreSQL date
 * columns drop the time; SQLite stores the text as given, and then
 * where('starts_on', '<=', '2026-11-05') is false. Every model with a
 * date-only cast uses this trait; tests/Feature/DateOnlyCastTest.php fails
 * when one does not.
 */
trait StoresDatesWithoutTime
{
    /**
     * Set an attribute, keeping only the date of a date-only cast.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        $result = parent::setAttribute($key, $value);

        if (($this->attributes[$key] ?? null) !== null && $this->isDateOnlyCast($key)) {
            $this->attributes[$key] = $this->asDateTime($this->attributes[$key])->format('Y-m-d');
        }

        return $result;
    }

    /**
     * Determine whether the attribute is cast to a date without a time.
     */
    public function isDateOnlyCast(string $key): bool
    {
        $cast = $this->getCasts()[$key] ?? null;

        if (!is_string($cast)) {
            return false;
        }

        $type = explode(':', $cast, 2)[0];

        return in_array($type, ['date', 'immutable_date'], true);
    }
}
