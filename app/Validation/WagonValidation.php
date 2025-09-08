<?php

declare(strict_types=1);

namespace App\Validation;

use CodeIgniter\Config\BaseConfig;

/**
 * Wagon Validation Rules
 * 
 * @package App\Validation
 */
class WagonValidation extends BaseConfig
{
    /**
     * Wagon validation rules
     */
    public array $rules = [
        'ilosc_miejsc'    => 'required|integer|greater_than[0]',
        'predkosc_wagonu' => 'required|decimal|greater_than[0]',
    ];

    /**
     * Wagon validation messages
     */
    public array $messages = [
        'ilosc_miejsc' => [
            'required'      => 'Pole ilość miejsc jest wymagane',
            'integer'       => 'Ilość miejsc musi być liczbą całkowitą',
            'greater_than'  => 'Ilość miejsc musi być większa od 0',
        ],
        'predkosc_wagonu' => [
            'required'      => 'Pole prędkość wagonu jest wymagane',
            'decimal'       => 'Prędkość wagonu musi być liczbą',
            'greater_than'  => 'Prędkość wagonu musi być większa od 0',
        ],
    ];
}
