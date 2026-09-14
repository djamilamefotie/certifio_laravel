<style>
    {{-- Ce fichier contient des styles personnalisés pour l'apparence Filament. --}}
    {{-- Import des polices: 'Fraunces' pour titres, 'Inter' pour le texte courant. --}}
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap');

    {{--
        Définition des variables CSS (tokens de couleur) :
        - Changer ces valeurs modifie l'ensemble du thème.
        - Utiliser des variables facilite la maintenance et la cohérence.
    --}}
    :root {
        --certifio-green-950: #081F18;
        --certifio-green-800: #0F3D2E;
        --certifio-gold: #C9A227;
        --certifio-gold-light: #E4C766;
        --certifio-cream: #F4F1E6;
    }

    {{-- Fond principal des pages simples Filament — écrase les styles par défaut. --}}
    body.fi-simple-page,
    .fi-simple-layout {
        background: var(--certifio-green-950) !important;
    }

    {{--
        Utilisation de 'Fraunces' pour le logo et les en-têtes :
        - serif pour un rendu plus 'brandé' / élégant.
        - la couleur utilise la variable cream pour contraste sur fond foncé.
    --}}
    .fi-simple-layout .fi-logo,
    .fi-simple-layout .fi-simple-header-heading {
        font-family: 'Fraunces', serif;
        color: var(--certifio-cream) !important;
    }

    {{--
        Boîte principale: gradient + bord doré léger + ombre
        - le gradient crée de la profondeur
        - la bordure et l'ombre donnent un effet 'carte' sur le fond.
    --}}
    .fi-simple-layout .fi-simple-main {
        background: linear-gradient(160deg, var(--certifio-green-800), #0B3226) !important;
        border: 1px solid rgba(228,199,102,0.18) !important;
        border-radius: 20px !important;
        box-shadow: 0 40px 80px -30px rgba(0,0,0,0.6) !important;
    }

    {{-- Couleur des labels: légèrement translucide pour diminuer le contraste. --}}
    .fi-simple-layout label {
        color: rgba(244,241,230,0.6) !important;
    }

    {{--
        Styles des champs (inputs) :
        - fond semi-transparent pour laisser transparaître le gradient
        - bord doré pâle pour rester dans la palette
        - border-radius pour homogénéité visuelle
    --}}
    .fi-simple-layout .fi-input,
    .fi-simple-layout input {
        background: rgba(0,0,0,0.18) !important;
        border: 1px solid rgba(228,199,102,0.22) !important;
        color: var(--certifio-cream) !important;
        border-radius: 8px !important;
    }

    {{--
        Focus des inputs :
        - on change la couleur de bordure pour indiquer l'état actif
        - suppression de l'ombre par défaut pour garder un rendu plat/cohérent
    --}}
    .fi-simple-layout .fi-input:focus,
    .fi-simple-layout input:focus {
        border-color: var(--certifio-gold) !important;
        box-shadow: none !important;
    }

    {{--
        Boutons/CTA :
        - fond doré pour attirer l'œil
        - texte sombre pour contraste et lisibilité
        - font-weight augmenté pour emphase
    --}}
    .fi-simple-layout .fi-btn,
    .fi-simple-layout button[type="submit"] {
        background: var(--certifio-gold) !important;
        color: var(--certifio-green-950) !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
    }je veux des  commentaire dans tout se qui est difficile pour comprendre mieux uniquement des commentaire

    {{-- Hover des boutons : variation plus claire du doré. --}}
    .fi-simple-layout .fi-btn:hover,
    .fi-simple-layout button[type="submit"]:hover {
        background: var(--certifio-gold-light) !important;
    }

    {{-- Liens: couleur dorée claire pour rester dans la palette. --}}
    .fi-simple-layout a {
        color: var(--certifio-gold-light) !important;
    }
        .fi-simple-layout {
        min-height: 100vh !important;
    }

    {{-- Limite de largeur du conteneur principal pour garder une colonne lisible. --}}
    .fi-simple-layout .fi-simple-main-ctn {
        max-width: 24rem !important;
    }

    {{-- Padding interne de la carte principale pour espacement cohérent. --}}
    .fi-simple-layout .fi-simple-main {
        padding: 2rem !important;
    }

    {{-- Taille du logo (texte) — garde la hiérarchie visuelle. --}}
    .fi-simple-layout .fi-logo {
        font-size: 1.25rem !important;
    }

    {{-- Taille de l'en-tête principal dans la mise en page simple. --}}
    .fi-simple-layout .fi-simple-header-heading {
        font-size: 1.25rem !important;
    }
</style>