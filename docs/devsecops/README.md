# TP DevSecOps — TomTroc

Branche `devsecops` uniquement (la branche `main` reste le rendu OpenClassrooms).

| Module | Livrable |
|---|---|
| 1. Schéma du pipeline et choix des outils | [1-pipeline.md](1-pipeline.md) |
| 3. Indicateurs, tableau de bord et feuille de route | [3-kpi-roadmap.md](3-kpi-roadmap.md), [dashboard.html](dashboard.html) |
| 4. Pipeline CI/CD | [.github/workflows/pipeline.yml](../../.github/workflows/pipeline.yml) |

Le module 2 (organisation de l'équipe) n'est pas demandé.

## Module 4 : ce que fait le pipeline

lint (php -l, PHPCS PSR-12) → tests (PHPUnit) → secrets (Gitleaks) et SAST (Semgrep) → build Docker → scan Trivy (Dockerfile et image) → déploiement de recette (docker compose + smoke test) → alerte Discord si un job échoue.

## Mise en place (une seule fois)

1. **Alerte Discord** : dans Discord, *Paramètres du salon > Intégrations > Webhooks > Nouveau webhook*, copier l'URL.
   Sur GitHub : *Settings > Secrets and variables > Actions > New repository secret*, nom `DISCORD_WEBHOOK_URL`, valeur = l'URL.
   Sans ce secret, le job d'alerte affiche un message et ne bloque rien.
2. **Environnement de recette** : créé automatiquement par GitHub au premier déploiement (*Settings > Environments > recette*).

## Lancer le site en local

```bash
cp .env.example .env   # puis choisir les mots de passe
docker compose up -d --build
# http://localhost:8080/tomtroc/public/
```

## Mettre à jour le tableau de bord

```bash
python scripts/kpi.py
```
