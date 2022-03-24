# Projet Tuteuré
<!-- 
> TODO: description / images
-->

## Technologies utilisées

- MySQL (8.0)
- NginX (1.20.2)
- Symfony (6.0.0)
- PHP (8.0.13)
- PHP-FPM
- Docker
- Tailwindcss (3.0)
<!-- 

### Pourquoi utilisons-nous ces technologies ?

> TODO
-->
## Structure du projet

```text
PROJET-TUT
│           
├───app                     -- le projet symfony qui est synchronisé avec nos conteneurs
│   ├───bin                 -- les fichiers executables (e.g bin/console)
│   ├───config              -- les fichiers de config du projet symfony
│   ├───migrations          
│   ├───public
│   ├───src                 -- le code en php du projet
│   │   ├───Controller      
│   │   ├───Entity
│   │   └───Repository
│   ├───templates           -- les templates twigs
│   │   ├───home
│   │   └───...
│   │
│   ├───tests               -- dossier contenant les tests automatic (e.g tests unitaire)
│   ├───translations    
│   ├───var                 -- dossier contenant les fichiers généré par symfony (e.g log, cache etc.)
│   └───vendor              -- dossier contenant les dépendances du projet          
│
├───mysql                   -- volume synchronisé avec le conteneur pour la base de données
├───nginx                   -- dossier contenant le fichier default.conf synchronisé avec le conteneur nginx pour sa config
└───php                     -- dossier contenant le Dockerfile utilisé pour build notre conteneur php

```

## Démarrer

### installation

Pour commencer, installer docker depuis le [site officiel](https://docs.docker.com/get-docker/).

Puis clonez le répertoire sur votre machine  

```bash
git clone https://github.com/DenisChon/Projet-Tut.git 
```

Ensuite placez-vous dans ce répertoire

```bash
cd Projet-Tut
```

Maintenant, vous devriez voir plusieurs dossiers et un fichier [`docker-compose.yml`](https://docs.docker.com/compose/). Ce fichier contient les informations requises pour créer nos conteneurs docker.

Pour construire ces conteneurs et les lancer, une seule commande est requise :

```bash
docker-compose up -d --force-recreate --build
```

- `up` Crée et démarre les conteneurs
- `-d` Mode détaché: lance les conteneurs en arrière-plan
- `--force-recreate` (optionnel) Recrée les conteneurs même si la configuration et les images n'ont pas changé
- `--build` Construis l'image avant de lancer les conteneurs

Voir documentation : [docker-compose](https://docs.docker.com/compose/reference/)

Une fois les conteneurs lancés, vous pouvez les visualiser avec la commande :

```bash
docker ps -a
```

- `ps` Affiche les conteneurs allumés
- `-a` (optionnel) Affiche tous les conteneurs, même les conteneurs fermés

Voir documentation : [docker ps](https://docs.docker.com/engine/reference/commandline/ps/)

Une fois cette commande tapée, vous devriez avoir quelque chose comme ceci a l'écran :

```bash
CONTAINER ID   IMAGE                 COMMAND                  CREATED          STATUS          PORTS                               NAMES   
6af110f50ef3   nginx:stable-alpine   "/docker-entrypoint.…"   21 minutes ago   Up 21 minutes   0.0.0.0:8080->80/tcp                nginx   
bb9bff3733ee   projet-tut_php        "docker-php-entrypoi…"   21 minutes ago   Up 21 minutes   0.0.0.0:9000->9000/tcp              php     
3581a6c4e74d   mysql:8.0             "docker-entrypoint.s…"   21 minutes ago   Up 21 minutes   33060/tcp, 0.0.0.0:4306->3306/tcp   database
```

On y retrouve plusieurs informations :

- `CONTAINER ID` L'identifiant du conteneur
- `IMAGE` Le nom de l'image utilisée pour construire le conteneur
- `COMMAND` Le terminal qui est utilisé dans le conteneur (e.g : Bash, sh, ...)
- `CREATED` Quand le conteneur a été construit
- `STATUS` Le statut du conteneur (e.g : Up, Exited, ...) et depuis quand
- `PORTS` L'association entre les ports dans le conteneur et ceux sur la machine hôte (votre machine)
- `NAMES` Le nom du conteneur

Voir documentation : [docker ps](https://docs.docker.com/engine/reference/commandline/ps/)

À ce niveau, vos 3 conteneurs sont allumés, vous pouvez donc tenter de vous connecter sur [localhost:8080](http://localhost:8080), il peut y avoir une erreur, si ce n'est pas le cas, passez a la suite [ici](#-fichier-.env). Si l'erreur ressemble a ça :

```bash
Fatal error: Uncaught Error: Failed opening required '/var/www/symfony_docker/vendor/autoload_runtime.php'...
```

C'est normal ! Si vous regarder dans le fichier [.gitignore](/app/.gitignore), on ignore le dossier `/vendor/` et ce dossier contient toutes les dépendances de notre projet symfony. Pour installer ce dossier, il suffit d'utiliser composer qui est un gestionnaire de dépendance. Sauf que composer n'est pas disponible n'importe où. Pour y avoir accès, vous devez rentrer dans le conteneur php a l'aide des commandes suivantes :

Cette commande permet d'afficher le nom de vos conteneurs :

```bash
docker ps
```

Voir documentation : [docker ps](https://docs.docker.com/engine/reference/commandline/ps/)

Une fois que vous avez le nom de votre conteneur php et qu'il est allumé (dans notre cas il se nomme `php`), vous pouvez vous y connecter dans un terminal. Pour cela, on utilise la commande suivante :

```bash
docker exec -it php bash
```

- `exec` Permet d'exécuter une commande dans un conteneur
- `-i` Maintient STDIN ouvert même en mode détaché
- `-t` Alloue un pseudo-TTY
- `php` Le nom du conteneur
- `bash` La commande à executer

Voir documentation : [docker exec](https://docs.docker.com/engine/reference/commandline/exec/)

Maintenant, que vous avez votre terminal d'ouvert, vous devez vous trouver à la racine du projet symfony et c'est donc ici que vous allez installer toutes les dépendances du projet a l'aide de la commande suivante :

```bash
composer install 
```

Voir documentation : [composer install](https://getcomposer.org/doc/03-cli.md#install-i)

Normalement, l'installation des dépendances devrait prendre quelques secondes, voir quelques minutes. Une fois l'installation des dépendances terminée, vous pouvez tenter de vous reconnecter sur [localhost:8080](http://localhost:8080) Et normalement, cette erreur disparaît. Cependant, il reste quelques petites manipulations à faire.

##### Fichier .env

Il faut lier le projet symfony avec la base de données, pour ça, copier le fichier `/app/.env` et renommez le en `/app/.env.local` et dans ce dernier décommenter la ligne correspondante a l'URL de connexion pour MySQL (ligne 30) en remplacant les valeurs suivantes :

- `db_user` : root
- `db_password` : root
- `127.0.0.1:3306` : database
- `db_name` : symfony

Il faut également modifier une ligne afin d'activer les mail :

```bash
MAILER_DSN=gmail+smtp://USERNAME:PASSWORD@localhost?verify_peer=0
```

En remplacant

- `USERNAME` : par l'adresse mail
- `PASSWORD` : par le mot de passe du compte

Et en autorisant les applications moins sécurisé a se connecter. [ici](https://www.google.com/settings/security/lesssecureapps)

> en cas de problèmes reconstruisez les conteneurs

Maintenant votre application est liée a votre base de données. Cependant, votre base de données est vide, elle ne contient pas les tables dont l'application symfony a besoin. Pour resoudre ce probleme, vous devez vous placer dans le conteneur php.

```bash
docker exec -it php bash
```

- `exec` Permet d'exécuter une commande dans un conteneur
- `-i` Maintient STDIN ouvert même en mode détaché
- `-t` Alloue un pseudo-TTY
- `php` Le nom du conteneur
- `bash` La commande à executer

Voir documentation : [docker exec](https://docs.docker.com/engine/reference/commandline/exec/)

Puis il vous faut executer la commande suivante

```bash
php bin/console doctrine:migration:migrate
```

Voir documentation : [doctrine](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.3/index.html)

Pour terminer votre installation, il ne reste plus qu'un utilisateur admin a créer, pour cela, executez les commandes suivantes (dans le conteneur php):

 ```bash
 php bin/console app:add:admin
 ```

 Les identifiants du compte administrateur par default sont `admin` et `admin`.
 Félicitation, votre projet est prêt, tout est bon 🎉!!!

> En cas d'erreur, contactez un membre de l'équipe de dev !

### Modification du projet

Pour commencer à modifiez votre projet, vous pouvez ouvrir le dossier `./app` dans votre IDE.

Si vous voulez modifier le projet symfony en ajoutant des dépendances ou en exécutant le `maker-bundle` par exemple, il faut avoir accès à `symfony` ou à `composer` et ces derniers sont installés dans notre conteneur php. Par conséquent, pour exécuter ce genre de commandes, vous devez vous connecter dans le conteneur php avec la même commande que pour `composer install` càd :

```bash
docker exec -it php bash
```

Voir documentation : [docker exec](https://docs.docker.com/engine/reference/commandline/exec/)

Une fois dans votre conteneur, vous pouvez exécuter toutes les commandes que vous voulez pour modifier votre projet:

Voir documentation : [Symfony](https://symfony.com/legacy/doc/cookbook/1_0/en/cli)
Voir documentation : [Composer](https://getcomposer.org/doc/03-cli.md)

Pour modifier le css, etant donner que dans notre projet, nous utilisont [tailwindcss](https://tailwindcss.com/) il faut l'installer avec le package manager de node qui ets npm qui vas lire le fichier package.json pour installer tout ce dont il a besoin.

```bash
npm install
```

Voir documentation : [npm install](https://docs.npmjs.com/cli/v8/commands/npm-install)

maintenant, il ne reste plus qu'a demarrer tailwind pour qu'il genere le css en tant réel, pour cela il faut executer le script npm

```bash
npm run watch 
```

Voir documentation : [npm run](https://docs.npmjs.com/cli/v8/commands/npm-run-script)

Vous pouvez a présent modifier les fichier se trouvant dans le repertoire `./templates`, a chaque sauvegarde, tailwind vas regenerer le css dans le fichier `./public/styles/output.css`

## Docker dans le projet

> TODO

## Membres du projet

<a href="https://github.com/denischon"><img src="https://avatars.githubusercontent.com/u/83774444?v=4" alt="drawing" style="width:175px;border-radius:50%;"/></a>
<a href="https://github.com/ibysnow"><img src="https://avatars.githubusercontent.com/u/91325753?v=4" alt="drawing" style="width:175px;border-radius:50%;"/></a>
<a href="https://github.com/justiniut"><img src="https://avatars.githubusercontent.com/u/82156035?v=4" alt="drawing" style="width:175px;border-radius:50%;"/></a>
<a href="https://github.com/sylph33"><img src="https://avatars.githubusercontent.com/u/38839842?v=4" alt="drawing" style="width:175px;border-radius:50%;"/></a>
<a href="https://github.com/xernois"><img src="https://avatars.githubusercontent.com/u/32645608?v=4" alt="drawing" style="width:175px;border-radius:50%;"/></a>
