<?php

namespace Carbon;

use DateTimeImmutable;
use DateTimeZone;
use IntlDateFormatter;

class Carbon
{
    private DateTimeImmutable $dateTime;
    private string $locale = 'en';

    public function __construct(DateTimeImmutable $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public static function now(string $timezone = 'UTC'): self
    {
        return new self(new DateTimeImmutable('now', new DateTimeZone($timezone)));
    }

    public static function parse(string $time, ?string $timezone = null): self
    {
        $tz = $timezone ? new DateTimeZone($timezone) : null;
        $dt = new DateTimeImmutable($time, $tz);
        return new self($dt);
    }

    public static function createFromFormat(string $format, string $time, ?string $timezone = null): self
    {
        $tz = $timezone ? new DateTimeZone($timezone) : null;
        $dt = DateTimeImmutable::createFromFormat($format, $time, $tz);
        if (!$dt) {
            $dt = new DateTimeImmutable($time, $tz);
        }
        return new self($dt);
    }

    public function setTimezone(string $timezone): self
    {
        $clone = clone $this;
        $clone->dateTime = $this->dateTime->setTimezone(new DateTimeZone($timezone));
        return $clone;
    }

    public function locale(string $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale;
        return $clone;
    }

    public function isoFormat(string $pattern): string
    {
        // Map Moment-like tokens to IntlDateFormatter tokens.
        $intlPattern = strtr($pattern, [
            'dddd' => 'EEEE',
            'ddd' => 'EEE',
            'dd' => 'dd',
            'D' => 'd',
            'MMMM' => 'MMMM',
            'MMM' => 'MMM',
            'MM' => 'MM',
            'M' => 'M',
            'YYYY' => 'yyyy',
            'YY' => 'yy',
            'HH' => 'HH',
            'H' => 'H',
            'mm' => 'mm',
            'm' => 'm',
            'ss' => 'ss',
            's' => 's',
        ]);

        if (class_exists(IntlDateFormatter::class)) {
            $formatter = new IntlDateFormatter(
                $this->locale,
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                $this->dateTime->getTimezone()->getName(),
                null,
                $intlPattern
            );

            $formatted = $formatter->format($this->dateTime);
            if ($formatted !== false) {
                return $formatted;
            }
        }

        // Fallback to PHP date formatting (English-only) when intl is unavailable.
        // Extra mappings keep numeric output sane instead of repeating tokens.
        $phpPattern = strtr($pattern, [
            'dddd' => 'l',
            'ddd' => 'D',
            'dd' => 'd',
            'D' => 'j',
            'MMMM' => 'F',
            'MMM' => 'M',
            'MM' => 'm',
            'M' => 'n',
            'YYYY' => 'Y',
            'YY' => 'y',
            'HH' => 'H',
            'H' => 'G',
            'mm' => 'i',
            'm' => 'i',
            'ss' => 's',
            's' => 's',
        ]);

        return $this->dateTime->format($phpPattern);
    }

    public function toDateString(): string
    {
        return $this->dateTime->format('Y-m-d');
    }

    public function format(string $format): string
    {
        return $this->dateTime->format($format);
    }

    public function toIso8601String(): string
    {
        return $this->dateTime->format(\DateTimeInterface::ATOM);
    }

    public function subDay(int $days = 1): self
    {
        $clone = clone $this;
        $clone->dateTime = $this->dateTime->modify("-{$days} day");
        return $clone;
    }

    public function greaterThanOrEqualTo(self $other): bool
    {
        return $this->getTimestamp() >= $other->getTimestamp();
    }

    public function getTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    public function copy(): self
    {
        return new self($this->dateTime);
    }
}
