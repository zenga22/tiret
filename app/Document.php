<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/*
    Le informazioni conservate nella tabella "documents" non rappresentano
    necessariamente tutti i documenti caricati, per quelli fa fede sempre e solo
    lo storage S3.
    Qui semmai vengono conservate informazioni accessorie utili per arricchire
    l'interfaccia
*/

class Document extends Model
{
}
