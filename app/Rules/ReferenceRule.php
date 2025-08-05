<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class ReferenceRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // se value puder vir nulo (por segurança)
        if (!is_string($value) || $value === '') {
            $fail('A referência é obrigatória.');
            return;
        }

        // Valida formato MM/AAAA
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/', $value)) {
            $fail('O formato deve ser MM/AAAA.');
            return;
        }

        [
            $mes,
            $ano
        ] = explode('/', $value);

        $anoAtual =  Carbon::now()->year;

        if ((int)$ano < $anoAtual) {
            $fail("O ano deve ser igual ou maior que {$anoAtual}.");
        }
    }
}
