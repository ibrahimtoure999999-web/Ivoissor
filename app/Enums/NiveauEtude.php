<?php

declare(strict_types=1);
namespace App\Enums;

enum NiveauEtude: string 
{
    case AUCUN = 'aucun';
    case PRIMAIRE = 'Primaire';
    case SECONDAIRE = 'Secondaire';
    case SUPERIEUR = 'Superieur';

    public stactic function choices(): array
        {
            return
            [
                self::AUCUN->value =>'Aucun';
                self::PRIMAIRE->value =>'Primaire';
                self::SECONDAIRE->value =>'Secondaire';
                self::SUPERIEUR->value =>'Supérieur';
            ]
        }
}