# 🛒 Mon Message Panier (MyCartInfo)

Module PrestaShop pour afficher un encart personnalisé dans le panier et recommander des produits ajoutables en un clic.

## ✨ Fonctionnalités

- Message personnalisé HTML depuis le Back-office
- Switch d’activation/désactivation du message
- Bloc de recommandations produits (jusqu’à 3 IDs conseillés)
- Grille responsive (desktop/tablette/mobile)
- Bouton Ajouter au panier directement depuis la grille
- Interface de configuration modernisée (style Bento)
- Deux hooks séparés pour placer le bandeau et les produits où vous voulez

## 🚀 Cas d’usage

- Informer vos clients d’un délai ou d’un changement transporteur
- Afficher un code promo juste avant le paiement
- Pousser des ventes additionnelles via 3 produits recommandés

## ⚙️ Configuration rapide

1. Activez le module dans le BO.
2. Rédigez le message dans le champ éditeur HTML.
3. Activez les recommandations produits.
4. Renseignez les IDs produits séparés par des virgules (ex: `12,45,78`).
5. Enregistrez puis videz le cache PrestaShop si nécessaire.
6. Placez les hooks séparément dans votre thème si besoin : `displayMyCartInfoBanner` et `displayMyCartInfoProducts`.

## 📚 Documentation & Liens utiles

- [Guide utilisateur (Notion)](https://few-volleyball-409.notion.site/Guide-Utilisateur-Modifier-le-message-du-Panier-32829fb0a29c807ca492fecc1e508c7c?pvs=74)
- [Repository GitHub](https://github.com/Lucas-tsl/mycartinfo)

## 📦 Historique des versions

### v1.1.0
- Ajout du switch d’activation/désactivation du message
- Ajout des recommandations produits configurables depuis le BO
- Ajout d’une grille responsive de produits dans le panier
- Ajout du bouton Ajouter au panier dans chaque carte produit
- Ajout du lien de documentation Notion dans le module et le README

### v1.2.0
- Séparation du bandeau et des produits en deux hooks distincts
- Conservation du hook historique pour compatibilité
- Mise à jour de la description BO et de la documentation

### v1.2.1
- Enregistrement automatique des nouveaux hooks en mise à jour du module
- Correction pour affichage séparé du bandeau et des produits dans le thème

### v1.0.0
- Création du module MyCartInfo
- Ajout du message personnalisable sur le panier