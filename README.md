# eedomus script : Nuki smartlock

![Nuki Logo](./dist/img/nikya_nukismartlock.png "Logo Nuki smartlock by Nikya")

* Plugin version : 1.5
* Origine : [GitHub/Nikya/nuki_smartlock](https://github.com/Nikya/eedomusScript_nuki_smartlock "Origine sur GitHub")
* Modifié (Fork) : [GitHub/jluc2808/nuki_smartlock](https://github.com/jluc2808/eedomusScript_nuki_smartlock "Origine sur GitHub")
* Nuki Bridge HTTP-API : 1.6 ([API documentation](https://nuki.io/fr/api/))

## Description
***Nikya eedomus Script Nuki Smartlock*** est un plugin pour la box domotique eedomus, qui permet de piloter et connaitre l'état d'une serrure intelligent _Nuki_.

Ce plugin est composé d'un script PHP et d'une déclaration pour 4 périphériques :
- Commande d'ouverture/fermeture
- État de la serrure
- Indicateur de % batterie (modifié)
- État de la porte (ajout)

Son avantage principal est de mettre à jour l'état de la serrure, seulement si nécessaire, en utilisant la fonctionnalité _callback_ de l'API Nuki. (au lieu de créer des _polling_ côté eedomus)
Le script va aussi synchroniser l'action (verrouiller, déverrouiller, ...) sur la serrure, quelque soit le dispositif utilisé y compris si celui-ci n'est pas eedomus.

## Prérequis

Une serrure Nuki Smartlock et son bridge (Matériel ou logiciel)

## Installation via store

Depuis le portail _eedomus_, cliquez sur
- `Configuration`
- `Ajouter ou supprimer un périphérique`
- `Store eedomus`
- puis sélectionner _Nuki Smartlock_

Des informations seront demandées pour la création du plugin.  
Puis noter les **codeAPI** des périphériques créés. (A utiliser à l'étape _register_)

## Installation manuelle

1. Télécharger le projet sur GitHub : [GitHub/jluc2808/nuki_smartlock](https://github.com/jluc2808/eedomusScript_nuki_smartlock "Origine sur GitHub")
1. Uploader le fichier `dist/nukismartlock.php` sur la box ([Doc eedomus script](http://doc.eedomus.com/view/Scripts#Script_HTTP_sur_la_box_eedomus))
2. Créer manuellement les 4 périphériques et noter leur **codeAPI** (A utiliser à l'étape _register_)

### Paramétrage

Informations à prendre en note, car à réutiliser ultérieurement.

1. **Discovery** : Appeler l'URL suivante pour connaitre l'IP et le port local de votre Bridge
	* URL : https://factory.nuki.io/discover/bridges.
	* Résultat : Une **IP** et un **port**
2. **Get Token (auth)** : S'authentifier sur le brige, avec l'IP et le port obtenu précédemment, en appelant l'URL suivante et en confirmant par un **appui sur le bouton physique du bridge**.
 	* URL : http://192.168.1.50:8080/auth
 	* Résutat : Un token
3. **Setup script** : Configurer le script eedomus, avec les informations obtenues, en appelant la _fonction setup_ (Voir ci-après)
5. **Register script** : Configurer le script eedomus, avec les informations obtenues, en appelant la _fonction register_  (Voir ci-après)

### Les fonctions du script

Executer le script eedomus en précisant une `function`.

* Format : https://[ip_box_eedomus]/script/?exec=nukismartlock.php&function=
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=toto

#### Fonction _setup_

Configurer ce script.

* params
	- function : `setup`
	- nukihost_port : IP et Port du bridge Nuki au format `ip:port`
	- token : Token d'identification
* Résultat
	- (XML) Un listing des équipements trouvés sur le bridge ciblé (noter le **Nuki ID**)
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=setup&nukihost_port=192.168.1.50:8080&token=909090

#### Fonction _register_

Abonner la box eedomus en tant que _Callback_ souhaitant être informé des changements d'état de la serrure.

* params
	- function : `register`
	- eedomushost : IP de votre eedomus qu'appelera le bridge Nuki (Na pas mettre localhost !)
	- nukiid : Id du Nuki (Voir _fonction list_)
	- periph_id_state : **codeAPI** eedomus du périphérique qui contiendra l'information _ETAT_ de la serrure
	- periph_id_batterycritical : **codeAPI** eedomus du périphérique qui contiendra l'information % _Batterie de la serrure
	- periph_id_doorstate : **codeAPI** eedomus du périphérique qui contiendra l'information _ETAT de la porte
	- periph_id_lockaction : **codeAPI** eedomus du périphérique qui contiendra l'information _ACTION de la serrure
* Résultat
	- (XML) Une confirmation ou non du succès de la fonction
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=register&eedomushost=192.168.1.60&nukiid=111&periph_id_state=222&periph_id_batterycritical=333&periph_id_doorstate=444&periph_id_lockaction=555

#### Fonction _add_periph_registration_

Enregistrer un périphérique pour gérer les changements d'état de la serrure et de la porte.

* params
	- function : `add_periph_registration`
	- nukiid : Id du Nuki (Voir _fonction list_)
	- periph_type : contiendra le type du périphérique à ajouter _TYPE_ (valeur possible: state, door, action, battery)
	- periph_id : **codeAPI** eedomus du périphérique que l'on veut ajouter 
* Résultat
	- (XML) Une confirmation ou non du succès de la fonction
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=register&eedomushost=192.168.1.60&nukiid=111&periph_type=action&periph_id=555

#### Fonction _list_

Lister les équipements connus par le bridge Nuki ciblé

* params :
	- function : `list`
* Résultat
	- (XML) Listing
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=list

#### Fonction _callback list_

Lister les callback enregistrés par le brige Nuki.

* params :
	- function : `callback_list`
* Résultat
	- (XML) Listing des équipements
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=callback_list

#### Fonction _callback remove_

Supprimer un callback enregistré sur le Bridge Nuki.  
(Utile pour pallier à l'éventuel erreur _too many callbacks registered_)

* params :
	- function : `callback_remove`
	- id : Id du callback à supprimer (obtenue avec la _fonction callback list_)
* Résultat
	- (XML) Listing des équipements
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=callback_remove&id=222

#### Fonction _incomingcall_

Fonction coeur de ce script, c'est cette fonction qu'appellera le bridge Nuki à chaque changement d'état d'une serrure.  
Elle lit les informations reçues et met à jour les périphériques concernés avec les nouvelles valeurs.  
Inutile de l'appeler manuellement, mais un appel permet de savoir si l'ensemble est correctement configuré et opérationnel.

* params :
	- function : `incomingcall`
* Résultat
	- (XML) Résultat des valeurs lues
* Exemple : https://192.168.1.60/script/?exec=nukismartlock.php&function=incomingcall

### Valeurs possibles

#### Pour _periph value batterycritical_

* Unité : %
* Charge restante batterie

#### Pour _periph value state_

ID  | Name
----|-----------------------
0   | uncalibrated
1   | locked
2   | unlocking
3   | unlocked
4   | locking
5   | unlatched
6   | unlocked (lock ‘n’ go)
7   | unlatching
254 | motor blocked
255 | undefined

#### Pour _periph value doorstate_

ID  | Name
----|-----------------------
1   | deactivated
2   | door closed
3   | door opened
4   | door state unknown
5   | calibrating
16  | uncalibrated
240 | removed
255 | unknown
