# GRH Textile - Gestion des Ressources Humaines

Application de gestion des employés pour entreprise textile. Gère les pointages, congés, et fiches de paie.

## Fonctionnalités

- **Tableau de bord** - Vue d'ensemble des statistiques RH
- **Gestion des employés** - CRUD complet avec informations détaillées
- **Pointages** - Suivi des entrées/sorties avec calcul automatique des heures
- **Congés** - Demandes, approbations et suivi des congés
- **Fiches de paie** - Génération automatique avec calcul CNSS/AMO/IR
- **Export Excel** - Export des données pour comptabilité

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ ou MariaDB 10.4+
- XAMPP ou WAMP (recommandé pour déploiement local)

## Installation

### 1. Cloner le projet
```bash
git clone <repository-url>
cd textile
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Installer les dépendances JavaScript
```bash
npm install
```

### 4. Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurer la base de données
Modifier le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_employes
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Créer la base de données
Dans phpMyAdmin ou MySQL CLI :
```sql
CREATE DATABASE gestion_employes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 7. Exécuter les migrations
```bash
php artisan migrate
```

### 8. Compiler les assets
```bash
npm run build
```

## Lancement

### Développement
```bash
# Terminal 1 - Serveur Laravel
php artisan serve

# Terminal 2 - Vite (hot reload)
npm run dev
```

### Production (XAMPP/WAMP)

1. Copier le projet dans `htdocs/textile`
2. Configurer un Virtual Host Apache :
```apache
<VirtualHost *:80>
    ServerName grh.local
    DocumentRoot "C:/xampp/htdocs/textile/public"
    <Directory "C:/xampp/htdocs/textile/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Ajouter dans `C:\Windows\System32\drivers\etc\hosts` :
```
127.0.0.1 grh.local
```

4. Compiler les assets pour production :
```bash
npm run build
```

## Déploiement réseau local

Pour accéder depuis d'autres ordinateurs du réseau :

1. Remplacer `127.0.0.1` par l'IP de la machine serveur (ex: `192.168.1.100`)
2. Modifier `.env` :
```env
APP_URL=http://192.168.1.100
```

3. Ouvrir le port 80 dans le pare-feu Windows

## Structure du projet

```
textile/
├── app/
│   ├── Http/Controllers/    # Contrôleurs
│   └── Models/              # Modèles Eloquent
├── database/
│   └── migrations/          # Migrations DB
├── resources/
│   ├── js/
│   │   ├── Layouts/         # Layout Vue principal
│   │   └── Pages/           # Pages Vue (Inertia)
│   └── css/                 # Styles Tailwind
└── routes/
    └── web.php              # Routes web
```

## Intégration Pointeuse Réseau

L'application expose une API REST pour connecter les pointeuses (ZKTeco, Hikvision, etc.).

### Configuration de la pointeuse

URL de base de l'API : `http://[IP_SERVEUR]:8000/api/pointeuse/`

### Endpoints disponibles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/ping` | Test de connexion |
| GET | `/employes` | Liste des employés (pour sync) |
| GET | `/statut/{matricule}` | Statut d'un employé |
| POST | `/pointage` | Enregistrer un pointage |
| POST | `/import` | Import en masse |

### Exemple - Enregistrer un pointage

```bash
curl -X POST http://192.168.1.100:8000/api/pointeuse/pointage \
  -H "Content-Type: application/json" \
  -d '{
    "matricule": "EMP001",
    "type": "entree",
    "datetime": "2024-01-15 08:30:00",
    "device_id": "POINTEUSE_01"
  }'
```

### Réponse
```json
{
  "success": true,
  "message": "Pointage enregistré",
  "data": {
    "employe": "Ahmed Benali",
    "matricule": "EMP001",
    "type": "entree",
    "heure": "08:30:00",
    "date": "2024-01-15"
  }
}
```

### Configuration ZKTeco (K40, K50, MB360, SpeedFace, etc.)

#### Sur la pointeuse (écran tactile):
1. **Menu** → **Comm.** → **Cloud Server Setting**
2. Configurer:
   - Enable Cloud Server: **Yes**
   - Server Address: `192.168.1.100` (IP du serveur)
   - Server Port: `8000`
   - HTTPS: **No**

#### Sur le logiciel ZKAccess (PC):
1. **System** → **Communication** → **Cloud Service**
2. Server URL: `http://192.168.1.100:8000/api/zkteco/receive`
3. Enable Push: **Yes**

#### Via ZKBioTime:
1. **Personnel** → **Device** → sélectionner la pointeuse
2. **Communication** → **Cloud Server**
3. URL: `http://192.168.1.100:8000/api/iclock/cdata`

### Formats supportés
- ADMS Protocol (standard ZKTeco)
- Push JSON (modèles récents)
- ZKAccess Table format
- iClock protocol (anciens modèles)

### Test de connexion
```bash
# Depuis le serveur
curl http://localhost:8000/api/pointeuse/ping

# Depuis la pointeuse (vérifier logs)
tail -f storage/logs/laravel.log
```

## Technologies

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS
- **Base de données**: MySQL
- **Build**: Vite
- **API**: REST JSON pour intégration pointeuses
