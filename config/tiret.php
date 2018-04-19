<?php

/*
    Qui è possibile definire la navigazione dei documenti assegnati da parte
    degli utenti.
    È possibile organizzarli in tabs e sotto-tabs, all'interno dei quali saranno
    aggregati in funzione di espressioni regolari applicate sui nomi dei files
    stessi.
    Nell'esempio qui sotto, si assume che i files siano chiamati nella forma
    NomeUtente_Gennaio_2018.pdf (dove 'Gennaio' è il mese, e 2018 l'anno)
    e questi saranno automaticamente organizzati in tabs per ogni anno, e
    sotto-tabs per ogni mese.

    regexp:         espressione regolare da cui estrapolare il valore da usare
                    come nome del gruppo
    sort_direction: da valorizzare con "reverse" per ordinare le tabs in ordine
                    inverso rispetto all'ordinamento (naturale, o definito in
                    sorting)
    enforced:       se definito, vengono considerati solo i gruppi con nomi
                    inclusi in questo array. I files il cui nome non matcha con
                    l'espressione regolare di riferimento vengono messi nella
                    tab "Altri"
    sorting:        se definito, viene usato come array di riferimento per
                    l'ordinamento. Utile per ordinare ad esempio i mesi (che non
                    sono in ordine alfabetico)
*/

/*

return [
	'grouping_rules' => [
		'regexp' => '/_(?P<key>[0-9]{4})\.[Pp]df/',
		'sort_direction' => 'reverse',
		'children' => [
			'regexp' => '/_(?P<key>[A-Za-z]*)_[0-9]{4}/',
			'sort_direction' => 'reverse',
			'enforced' => [
				'Gennaio',
				'Febbraio',
				'Marzo',
				'Aprile',
				'Maggio',
				'Giugno',
				'Luglio',
				'Agosto',
				'Settembre',
				'Ottobre',
				'Novembre',
				'Dicembre'
			],
			'sorting' => [
				'Gennaio',
				'Febbraio',
				'Marzo',
				'Aprile',
				'Maggio',
				'Giugno',
				'Luglio',
				'Agosto',
				'Settembre',
				'Ottobre',
				'Novembre',
				'Dicembre'
			]
		]
	]
];

*/
