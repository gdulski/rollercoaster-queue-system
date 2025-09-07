<?php

declare(strict_types=1);

namespace App\Validation;

use CodeIgniter\Config\BaseConfig;

/**
 * Coaster Validation Rules
 * 
 * @package App\Validation
 */
class CoasterValidation extends BaseConfig
{
    /**
     * Coaster validation rules
     */
    public array $rules = [
        'staff_count'      => 'required|integer|greater_than[0]',
        'daily_customers'  => 'required|integer|greater_than[0]',
        'track_length'     => 'required|decimal|greater_than[0]',
        'opening_time'     => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'closing_time'     => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
    ];

    /**
     * Coaster validation messages
     */
    public array $messages = [
        'staff_count' => [
            'required'      => 'Pole liczba personelu jest wymagane',
            'integer'       => 'Liczba personelu musi być liczbą całkowitą',
            'greater_than'  => 'Liczba personelu musi być większa od 0',
        ],
        'daily_customers' => [
            'required'      => 'Pole liczba klientów jest wymagane',
            'integer'       => 'Liczba klientów musi być liczbą całkowitą',
            'greater_than'  => 'Liczba klientów musi być większa od 0',
        ],
        'track_length' => [
            'required'      => 'Pole długość trasy jest wymagane',
            'decimal'       => 'Długość trasy musi być liczbą',
            'greater_than'  => 'Długość trasy musi być większa od 0',
        ],
        'opening_time' => [
            'required'      => 'Pole godziny od jest wymagane',
            'regex_match'   => 'Godziny od muszą być w formacie HH:MM',
        ],
        'closing_time' => [
            'required'      => 'Pole godziny do jest wymagane',
            'regex_match'   => 'Godziny do muszą być w formacie HH:MM',
        ],
    ];
}
