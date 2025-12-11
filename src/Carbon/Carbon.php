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
        $intlPattern = strtr($pattern, [
            'dddd' => 'EEEE',
            'MMMM' => 'MMMM',
            'YYYY' => 'yyyy',
            'HH' => 'HH',
            'mm' => 'mm',
            'D' => 'd',
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

        $phpPattern = strtr($pattern, [
            'dddd' => 'l',
            'MMMM' => 'F',
            'YYYY' => 'Y',
            'HH' => 'H',
            'mm' => 'i',
            'D' => 'j',
        ]);

        return $this->dateTime->format($phpPattern);
    }

    public function toDateString(): string
    {
        return $this->dateTime->format('Y-m-d');
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
