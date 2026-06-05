<?php

declare(strict_types=1);
namespace App\Enums;

enum Sexe: string
{
    
    case MASCULIN = 'M';
    case FEMININ = 'F';
        


    /**
     * Génère un tableau associatif propre pour remplir facilement un composant Select en HTML.
     *
     * @return array<string, string>
     */

    pubic static function choices(): array
    {
        return
            [
                self::MASCULIN->value =>'Masculin',
                self::FEMININ->value =>'Féminin',
            ];
    }
}