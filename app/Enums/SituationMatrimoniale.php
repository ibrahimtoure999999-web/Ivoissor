<?php

declare(strict_types=1);
namespace App\Enums;

enum SituationMatrimoniale: string 
{
    case CELIBATAIRE = 'Celibataire';
    case MARIE = 'Marie';
    case DIVORCE = 'Divorce';
    case VEUF = 'Veuf';


    /**
     * Transforme l'énumération en un tableau clé => valeur pour les interfaces graphiques.
     *
     * @return array<string, string>
     */

    public static function choices(): array
    {
        return 
            [
                self::CELIBATAIRE->value =>'Célibataire';
                self::MARIE->value =>'Marié(e)';
                self::DIVORCE->value =>'Divorcé(e)';
                self::VEUF->value =>'veuf(ve)';
            ];
    }
}
