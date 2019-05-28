# Persoonsobservaties in Schepenakten

Het script `findpersons.php` vindt personen - met reguliere expressies - in transcripties en beschrijvingen van akten en markeert deze tussen {{dubbele accolades}}. Het script is toegespitst op de schepenakten van het [BHIC](https://www.bhic.nl/het-geheugen-van-brabant) en maakt gebruik van het voorbeeldige hoofdlettergebruik voor namen daarin en daarnaast van een [lijst (Brabantse) voornamen](firstnames.csv). 

Verder bevat het script functies die de gevonden voornamen splitsen in naamdelen zoals gedefinieerd in de [Person Name Vocabulary (PNV)](https://w3id.org/pnv), relaties (ouder/kind, echtgenoten) zoeken tussen verschillende personen en de observatie wegschrijven als RDF - gebruikmakend van de [roar](https://leonvanwissen.nl/vocab/roar/docs/) vocabulary.

### input - csv akte 12258333

```
12258333,"Ursula Hartongh weduwe van Cornelis Thooft oud-president van 's-Hertogenbosch verkoopt aan  de mombers van de kinderen van Elias van Valentijn een  erfelijke (schuld)brief ter waarde van 1000 gulden. Dit is een schuld ten laste van de stad Dordrecht vermeld in de Statenbrieven gepasseerd te ’s-Gravenhage op 12-01-1640 getekend door Herbert van Beaumonts. Deze brief is de transportante in bezit gekomen bij het overlijden van kapitein-luitenant Paulus Frederick van de Poll.",02-09-1700
```

### gevonden namen worden {{tussen accolades}} gemarkeerd

```
{{Ursula Hartongh}} weduwe van {{Cornelis Thooft}} oud-president van 's-Hertogenbosch verkoopt aan de mombers van de kinderen van {{Elias van Valentijn}} een erfelijke (schuld)brief ter waarde van 1000 gulden. Dit is een schuld ten laste van de stad Dordrecht vermeld in de Statenbrieven gepasseerd te ’s-Gravenhage op 12-01-1640 getekend door {{Herbert van Beaumonts}}. Deze brief is de transportante in bezit gekomen bij het overlijden van kapitein-luitenant {{Paulus Frederick van de Poll}}.
```

### namen splitsen en relaties zoeken, persoonsobservaties weergeven in RDF

Het script concludeert uit de string `{{Persoon 1}} weduwe van {{Persoon 2}}` dat Persoon 1 en Persoon 2 echtgenoten zijn, maar ook dat Persoon 1 vrouw is en Persoon 2 man en overleden voor de datum van de akte.

```
personobs:12258333-1
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Paulus Frederick van de Poll" ;
		pnv:givenName "Paulus" ;
		pnv:patronym "Frederick" ;
		pnv:surName "van de Poll" ;
		pnv:baseSurname "Poll" ;
		pnv:surnamePrefix "van de" ;
	]
	a roar:PersonObservation .

personobs:12258333-2
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Elias van Valentijn" ;
		pnv:givenName "Elias" ;
		pnv:surName "van Valentijn" ;
		pnv:baseSurname "Valentijn" ;
		pnv:surnamePrefix "van" ;
	]
	a roar:PersonObservation .

personobs:12258333-3
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Herbert van Beaumonts" ;
		pnv:givenName "Herbert" ;
		pnv:surName "van Beaumonts" ;
		pnv:baseSurname "Beaumonts" ;
		pnv:surnamePrefix "van" ;
	]
	a roar:PersonObservation .

personobs:12258333-4
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Ursula Hartongh" ;
		pnv:givenName "Ursula" ;
		pnv:surName "Hartongh" ;
	]
	schema:spouse personobs:12258333-5 ;
	schema:gender schema:Female ;
	a roar:PersonObservation .

personobs:12258333-5
	roar:documentedIn akte:12258333 ;
	pnv:hasName [
		pnv:literalName "Cornelis Thooft" ;
		pnv:givenName "Cornelis" ;
		pnv:surName "Thooft" ;
	]
	schema:spouse personobs:12258333-4 ;
	schema:gender schema:Male ;
	bio:death [
		a bio:Death ;
		bio:principal personobs:12258333-5 ;
		sem:hasLatestTimeStamp "1700-09-02"^^xsd:date ;
	]
	a roar:PersonObservation .
```

