<?php

namespace App\Services\Auth;

class PasswordPolicy
{
    public const MIN_LENGTH = 8;

    public static function meetsRequirements(string $password): bool
    {
        if (strlen($password) < self::MIN_LENGTH) {
            return false;
        }

        return self::hasUppercase($password)
            && self::hasNumber($password)
            && self::hasSpecialCharacter($password);
    }

    public static function hasUppercase(string $password): bool
    {
        return (bool) preg_match('/[A-Z]/', $password);
    }

    public static function hasNumber(string $password): bool
    {
        return (bool) preg_match('/[0-9]/', $password);
    }

    public static function hasSpecialCharacter(string $password): bool
    {
        return (bool) preg_match('/[^A-Za-z0-9]/', $password);
    }

    public static function requirementsMessage(): string
    {
        return 'Password must be at least ' . self::MIN_LENGTH . ' characters and include an uppercase letter, a number, and a special character.';
    }

    /**
     * @return array{score: int, label: string, checks: array<string, bool>}
     */
    public static function analyze(string $password): array
    {
        $checks = [
            'length' => strlen($password) >= self::MIN_LENGTH,
            'uppercase' => self::hasUppercase($password),
            'number' => self::hasNumber($password),
            'special' => self::hasSpecialCharacter($password),
        ];

        $met = count(array_filter($checks));
        $length = strlen($password);

        if ($met < 2 || $length < 6) {
            $label = 'Weak';
            $score = 1;
        } elseif ($met === 2 || ($met === 3 && $length < self::MIN_LENGTH)) {
            $label = 'Fair';
            $score = 2;
        } elseif ($met === 3 || ($met === 4 && $length < 12)) {
            $label = 'Good';
            $score = 3;
        } else {
            $label = 'Strong';
            $score = 4;
        }

        return compact('score', 'label', 'checks');
    }
}
