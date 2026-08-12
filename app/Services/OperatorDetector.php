<?php

namespace App\Services;

class OperatorDetector
{
    private const PREFIXES = [
        'Telkomsel' => ['0852', '0853', '0811', '0812', '0813', '0821', '0822', '0823'],
        'BYU' => ['0851'],
        'Indosat' => ['0855', '0856', '0857', '0858', '0814', '0815', '0816'],
        'XL' => ['0817', '0818', '0819', '0859', '0877', '0878'],
        'Axis' => ['0832', '0833', '0838'],
        'Three' => ['0895', '0896', '0897', '0898', '0899'],
        'Smartfren' => ['0881', '0882', '0883', '0884', '0885', '0886', '0887', '0888', '0889'],
    ];

    public function detect(string $phone): ?array
    {
        $prefix = substr($phone, 0, 4);
        foreach (self::PREFIXES as $name => $prefixes) {
            if (in_array($prefix, $prefixes, true)) {
                return ['name' => $name, 'database' => strtoupper($name === 'BYU' ? 'TELKOMSEL' : $name)];
            }
        }

        return null;
    }
}
