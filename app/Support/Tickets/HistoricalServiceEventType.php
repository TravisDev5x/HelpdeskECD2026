<?php

namespace App\Support\Tickets;

/**
 * Valores de {@see \App\Models\HistoricalServices::$event_type} para filas con semántica explícita.
 * null en BD = instantánea genérica (comportamiento histórico).
 */
final class HistoricalServiceEventType
{
    public const ESCALATION = 'escalation';
}
