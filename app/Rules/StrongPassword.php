<?php

namespace App\Rules;

use App\Services\Auth\PasswordPolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! PasswordPolicy::meetsRequirements($value)) {
            $fail(PasswordPolicy::requirementsMessage());
        }
    }
}
