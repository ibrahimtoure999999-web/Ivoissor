<?php
/*
🎯 Fiche d'Identité du Code
- Rôle principal : Représente un rendez-vous planifié entre un citoyen (via sa demande) et un agent.
- Objectif (Le "Pourquoi") : Organiser la validation physique des demandes d'enrôlement.
- Connexions et Dépendances : Lié aux modèles `Demande` et `User` (l'agent en charge).

💻 Code Commenté
*/


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendezVous extends Model
{
    use HasUuids;

    /** @var string Override auto-guessed table name */
    // On précise explicitement le nom de la table car Laravel ne le devine pas bien avec le pluriel.
    protected $table = 'rendez_vous';

    protected $fillable = [
        'demande_id',
        'date_heure',
        'lieu',
        'statut',
        'agent_id',
    ];

    // "Casting" : On s'assure que la date est bien traitée comme un objet "Date/Heure" pour Laravel.
    protected $casts = [
        'date_heure' => 'datetime',
    ];

    /**
     * Retourne le fuseau horaire correspondant à un lieu/consulat donné.
     */
    public static function getTimezoneForLieu(?string $lieu): string
    {
        return match ($lieu) {
            "Consulat Général de Côte d'Ivoire à Paris" => 'Europe/Paris',
            "Ambassade de Côte d'Ivoire à Bruxelles" => 'Europe/Brussels',
            "Ambassade de Côte d'Ivoire à Dakar" => 'Africa/Dakar',
            "Ambassade de Côte d'Ivoire à Rabat" => 'Africa/Casablanca',
            "Ambassade de Côte d'Ivoire à Ottawa" => 'America/Toronto',
            "Ambassade de Côte d'Ivoire à Washington" => 'America/New_York',
            "Ambassade de Côte d'Ivoire à Abidjan (SNEDAI)" => 'Africa/Abidjan',
            default => 'UTC',
        };
    }

    /**
     * Accesseur pour renvoyer la date dans le fuseau horaire local du consulat.
     */
    public function getDateHeureAttribute($value)
    {
        if (!$value) {
            return null;
        }
        $utcDateTime = $this->asDateTime($value);
        return $utcDateTime->copy()->setTimezone(self::getTimezoneForLieu($this->lieu));
    }

    // Quelle demande est concernée par ce rendez-vous ?
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    // Quel agent est responsable du rendez-vous ?
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
