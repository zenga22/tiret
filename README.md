## Tiret

**Tiret** è un semplice frontend per il servizio di storage Amazon S3, pensato per esporre grandi
quantità di files ad un gran numero di utenti.

# Features

* utenti divisi in gruppi
* ruoli possibili: amministratore generale, amministratore di gruppo ed utente
* cartelle private per gli utenti e cartelle comuni per i gruppi
* solo gli amministratori possono creare e rimuovere files
* auto-assegnazione dei files caricati in base al loro nome
* importazione degli utenti da file CSV
* possibilità di customizzazione del tema grafico

# Requisiti

* PHP >= 5.5.9
* composer ( https://getcomposer.org/ )
* un account AWS ( http://aws.amazon.com/ )
* un webserver ed un database

# Installazione

```
git clone https://github.com/OfficineDigitali/tiret
cd tiret
composer install
cp .env.example .env
(editare .env con i propri parametri di accesso al database, a S3 e all'SMTP)
php artisan migrate
php artisan db:seed
php artisan key:generate
```

Le credenziali di default sono username: admin, password: cippalippa

# Auto-Assegnazione

È possibile fare in modo che i files arbitrariamente caricati in _storage/app_ vengano assegnati
ad utenti o gruppi in funzione del loro nome. Dal pannello "_Regole Assegnazione_" si specificano
le proprie regular expressions, con cui estrarre il nome della cartella di destinazione.

È necessario abilitare lo scheduler interno a Laravel affinché la routine di assegnazione venga
eseguita: per far ciò, eseguire per mezzo di cron il comando `php artisan schedule:run` ogni
minuto.

# Plugins

Viene fornito un semplice meccanismo per implementare funzionalità custom all'interno della
applicazione, per maggiori dettagli si veda l'esempio in app/Plugins/SampleFileHandler.php

Gli eventi sinora esistenti, per i quali i plugins possono registrarsi, sono:

* *FileToHandle*: lanciato dal comando di auto-assegnazione dei files, permette di intercettare
files speciali e trattarli separatamente. Si consiglia di eliminare o spostare il file eventualmente
processato dalla cartella destinata agli uploads.

# Temi

L'aspetto grafico di **Tiret** è volutamente semplice e limitato, per permettere una facile
personalizzazione ed un facile adattamento al look'n'feel di siti esistenti.

Per personalizzare l'aspetto grafico è possibile creare un nuovo file in
_public/themes/nome_del_tema/views/app.blade.php_, contenente il proprio template di base
all'interno del quale il contenuto della pagina sarà iniettato. Si prenda come esempio il file
_resources/views/app.blade.php_.

Una volta creato il tema, è possibile attivarlo modificando la relativa configurazione in
_config/themes.php_

# Storia

**Tiret** è stato inizialmente sviluppato per una agenzia di assistenza fiscale con la necessità
di distribuire ed esporre ai propri 6000+ clienti documenti sulle buste paga, per un totale di
svariati gigabytes al mese.

Il nome _tirét_ in piemontese sta per _cassetto_.

# Licenza

**Tiret** è distribuito in licenza AGPLv3.

Copyright (C) 2015 Officine Digitali <info@officinedigitali.org>.
