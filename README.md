# Sparkling18, Client restful PHP v1.0

[toc]

Questo è il client restful per la comunicazione con il server Sparkling18 SecQuick secondo
le specifiche delle nuove API restful documentate in [API-Doc](http://sparkling18.com/api-docs/).
Questo client comprende la parte di gestione della comunicazione crittografata e la relativa gestione delle chiavi.


Nel codice sorgente si fa riferimento a alle chiavi che vengono configurate nel file __/src/Config.php__. Accertarsi che i path siano riferiti correttamente.

| | |
|---------------------------|-----------------------|
| main.1app8_rest_base_url| L'URL base dell'API restful (es api.test.sparkling18.com)|
| main.1app8_rest_pci_base_url | L'url base dell'API restful per il servizio PCI (es. api.test.sparkling18.com/v1/server-pci) |
| main.rest_public_server_key | Il path della chiave pubblica rilasciata dalla procedura di generazione di 1APP8 |
| main.rest_private_key | Il path della chiave privata del cliente |
| main.1app8_rest_key_id | ID della chiave rilasciata dalla procedura di generazione di 1APP8 |

E' corredata da classi di test unitario in (PHPUnit) che fungono da esempio per l'utilizzo della libreria. Questi vanno eseguit dopo aver configurato correttamente il file **Config.php**.


## Sicurezza
L'API Restful &egrave; invocata applicando, in momenti diversi, meccanismi di crittografia simmetrica e asimmettica abbinato alla firma del messaggio.

## Protocollo
L'API Restful implemente un meccanismo di sicurezza basato su firma dei messaggi scambiati e crittografia del corpo del messaggio. In particolare:
- Crittografia del corpo del messaggio con chiave simmetrica condivisa generata in modo casuale; valida per la durata della comunicazione corrente;
 - Firma del messaggio in *sha-256* degli http header con crittografia RSA PKCS#1 e chiave privata.

I passi da compiere per ogni invocazione dell'API si possono riassumere come di seguito:
- Generazione di una chave simmetrica casuale **symmetricKey**, AES 128 CBC. Questa &egrave; impiegata per la cifratura del corpo del messaggio della richiesta;
- Cifratura della chiave simmetrica generata in precedenza applicando la chiave pubblica del server secondo l'algoritmo di cifratura **RSA/ECB/PKCS1** come segue:
	- ```Base64.encode(encrypt(<public_server_key>, simmetricKey))```
- Cifratura del corpo della richieta http secondo l'algoritmo ** AES/CTR/NoPadding** come di seguito:
	- ```Base64.encode(encrypt(simmetricKey, <http_request_body>))```
- Firma dello header http:
	- Per generare la firma del messaggio il client deve utilizzare ciascuno dei campi key presenti nello hader http e valorizzare il campo ** Authorization ** dello stesso header http.

Esempio di firma:
```http
(request-line): post /v1/server/users
host: api.sparkling18.com
sign-date: Tue, 26 May 2017 16:36:56 GMT
content-lenght: 344
key: D8Dr8Xj/m3v2DhxiRD8Dr8XpugN5wpy8iBVJtpkHUIp4qBYpzx2QvD16t8X0BUMiKc53Age+baQFWwb2iYYJzvuUL+krrl/Q7H6fPBADBsHqEZ7IE8rR0 Ys3lb7J5A6VB9J/4yVTRiBcxTypW/2iYY
```

La firma del messaggio viene costruita concatenando a lettere minuscole (lowercase) il il nome del campo dello Http Header seguito dal carattere ':' (due punti), dal valore del campo stesso e dal carattere newline '"\n'"; tranne per l'ultimo valore concatenato.

Infine il campo ** Authorization ** dello Http header ha la setuente forma:

```http
Authorization: Signature keyId="RSAKeyId",algorithm="rsa-sha256",headers="(request-line) host sign-date content-length key", signature="Base64(RSA-SHA256(<firma_del_messaggio>))
```

** RSAKeyId: ** l'id della chiave fornito dalla dashboard di Sparkling18.


I passi da compiere per ogni risposta ricevuta si possono riassumere come di setuito:
- Verifica della firma dello header http della risposta;
- Decifratura della chiave simmetrica;
- Decifratura del corpo della risposta.

![Passi per la cifratura e decifratura dei messaggi](http://www.sparkling18.com/static/images/api-doc/draft_encryption_and_signature.jpg)

Dato che la comunicazione client/server ha luogo tramite crittografia asimmetrica, il client e il server debbono procedere
allo scambio delle loro chiavi pubbliche.
Lo scambio delle stesse avviene tramite il Backoffice di Sparkling18. Quando la procedura per lo scambio delle chiavi
sar&agrave; conclusa il client sar&agrave; in possesso dei seguenti dati;
- ID del merchant assegnato dal Backoffice, *keyId*;
- Le seguenti chiavi:
- Chiave privata del client;
- Chiave pubblica del client;
- Chiave pubblica del server Sparkling18.

## Procedura scambio chiavi
Qui di seguto viene spiegata la procedura per la generazione e lo scambio chievi tra il merchant e Sparkling18.
** Requisito **
La generazione delle chieve richiede l'utilizzo di * OpenSSL. *
- [Linux](https://www.openssl.org/source)
- [MS Windows](https://wiki.openssl.org/index.php/Binaries)
- [Mac OSX](https://mac-dev-env.patrickbogie.com/openssl)

### Generazione chiavi RSA

Per generare una coppia di chiavi RSA eseguire il seguente comando da console:

```bash
openssl genrsa -out <path/privateKeyFilename>.pem 2048
```

Esempio

```bash
openssl genrsa -out /home/user/.ssh/privateKey.pem 2048
```

Il risultato del precedente comando avremo a disposizione la nostra chiave privata.

Adesso ci occorre generare la corrispondente chiave pubblica. Questa viene generata a partire dalla chiave privata generata nel passo precedente nel seguente modo:

```bash
openssl rsa -in <path/privateKeyFilename>.pem -pubout -out <path/publicKeyFilename>.pem
```

Esempio

```bash
openssl rsa -in /home/user/.ssh/privateKey.pem -pubout -out /home/user/.ssh/publicKey.pem
```



Avendo cos&igrave; generato la chiave privata e quella pubblica del merchant possiamo procedere allo scambio delle chiavi con Sparkling18.

### Conversione chiave pubblica in formato DER
Per consentire l'accesso all'API Restful, Sparkling18 deve essere a conoscenza della chiave pubblica del merchant che vuole accedervi.
Questa deve essere caricata dal merchant nel Backoffice Sparkling18 previa opportuna conversione in formato **der**.

Par la conversione della chiave pubblica in formato der, procedere come segue:
```bash
openssl rsa -in <path/privateFilename>.pem -outform DER -pubout -out <path/publicKeyFilename>.der
```

Esempio

```bash
openssl rsa -in /home/user/.ssh/privateKey.pem -outform DER -pubout -out /home/user/.ssh/publicKey.der
```

L'output del precedente comando &egrave; la chiave pubblica in formato *pem* convertita nel formato *der*.

### Scambio chiavi
Prima di procedere allo scambio delle chiavi, il merchant deve ottenere da Sparkling18 le autorizzazioni per l'accesso all'area dedicagta del Backoffice Sparkling18.
Ottenute tali autorizzazioni si pu&ograve; procedere come segue:

1. Collegarsi e accedere al Backoffice Sparkling18;
2. Nel men&ugrave; a sinistra selezionare Gestione Account > Gestione chiavi
(Se questa voce di men&ugrave; non dovessere essere disponibile contattare [mailto:support@sparkling18.com](Sparkling18))
3. Cliccare sul pulsante *"Configura chiavi"*
4. Cliccare sul pulsantge *"Carica chiave pubblica"*.
(La chiave pubblica da caricare &egrave; la chiave del merchant in formato DER)
5. Cliccare sul pulsante *"Genera chiavi"*;
6. Conservare l'*"Identificativo chiavi"* (**keyId**). E' una stringa numerica utilizzata per configurare il restful client;
7. Cliccare ul pulsante *"Scarica chiave pubblica"*. Questa &egrave; la chiave pubblica del server Sparkling18 contattato dalle chiamate restful.

# In Breve

Per la configurazione del client restful lato merchant, serviranno:
1. La **keyId** del merchant ottenuta al punto 7 del paragrafo [Scambio chiavi](Scambio chiavi)
2. Chiave privata del merchant
3. Chiave pubblica del server Sparkling18