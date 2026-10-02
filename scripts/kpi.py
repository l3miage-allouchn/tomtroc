"""Calcul des indicateurs (KPI) DevSecOps de TomTroc à partir de l'historique GitHub Actions.

Le script lit les exécutions du pipeline sur la branche devsecops (API publique de GitHub),
calcule les 6 indicateurs décrits dans docs/devsecops/3-kpi-roadmap.md,
les affiche dans le terminal et génère le tableau de bord docs/devsecops/dashboard.html.

Utilisation :
    python scripts/kpi.py                     # indicateurs du pipeline
    python scripts/kpi.py --trivy trivy.json  # + nombre de failles d'un rapport Trivy (JSON)

Variable d'environnement facultative : GITHUB_TOKEN (évite la limite de 60 appels/heure de l'API).
Aucune bibliothèque à installer : uniquement la bibliothèque standard de Python.
"""

import argparse
import html
import json
import os
import statistics
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

DEPOT = "l3miage-allouchn/tomtroc"
BRANCHE = "devsecops"
WORKFLOW = "pipeline.yml"
API = "https://api.github.com"

# jobs du pipeline qui sont des contrôles de sécurité (nom affiché dans GitHub)
JOBS_SECURITE = ("Secrets (Gitleaks)", "Analyse du code (Semgrep)", "Build et scan de l'image (Docker, Trivy)")
JOB_DEPLOIEMENT = "Déploiement (recette Docker)"

SORTIE = Path(__file__).resolve().parent.parent / "docs" / "devsecops" / "dashboard.html"


def appel_api(chemin):
    """Appel GET à l'API GitHub, renvoie le JSON décodé."""
    requete = urllib.request.Request(API + chemin, headers={"Accept": "application/vnd.github+json"})
    jeton = os.environ.get("GITHUB_TOKEN")
    if jeton:
        requete.add_header("Authorization", "Bearer " + jeton)
    with urllib.request.urlopen(requete, timeout=30) as reponse:
        return json.load(reponse)


def date(texte):
    """Convertit une date de l'API ("2026-09-30T13:50:44Z") en datetime."""
    return datetime.fromisoformat(texte.replace("Z", "+00:00"))


def minutes(debut, fin):
    return (fin - debut).total_seconds() / 60


def recuperer_executions():
    """Exécutions terminées du pipeline sur la branche, de la plus ancienne à la plus récente, avec leurs jobs."""
    donnees = appel_api(f"/repos/{DEPOT}/actions/workflows/{WORKFLOW}/runs?branch={BRANCHE}&per_page=100")
    executions = [r for r in donnees["workflow_runs"] if r["status"] == "completed"]
    executions.sort(key=lambda r: r["created_at"])
    for r in executions:
        r["jobs"] = appel_api(f"/repos/{DEPOT}/actions/runs/{r['id']}/jobs")["jobs"]
    return executions


def calculer(executions, rapport_trivy=None):
    """Calcule les 6 KPI. Une valeur None signifie « pas encore de données »."""
    # on ne garde que les exécutions qui livrent du code (push), pas les pull requests ni le scan du lundi
    livraisons = [r for r in executions if r["event"] == "push"]
    reussies = [r for r in livraisons if r["conclusion"] == "success"]
    echouees = [r for r in livraisons if r["conclusion"] == "failure"]

    # 1. fréquence de déploiement : déploiements réussis par semaine
    deploiements = []
    for r in livraisons:
        for j in r["jobs"]:
            if j["name"] == JOB_DEPLOIEMENT and j["conclusion"] == "success":
                deploiements.append((r, j))
    frequence = None
    if deploiements:
        premier = date(deploiements[0][1]["completed_at"])
        semaines = max(minutes(premier, datetime.now(timezone.utc)) / (60 * 24 * 7), 1)
        frequence = len(deploiements) / semaines

    # 2. délai de mise en production : du commit jusqu'à la fin du déploiement (médiane)
    delais = [minutes(date(r["head_commit"]["timestamp"]), date(j["completed_at"])) for r, j in deploiements]
    delai = statistics.median(delais) if delais else None

    # 3. taux d'échec des changements : part des livraisons dont le pipeline a échoué
    taux_echec = 100 * len(echouees) / len(livraisons) if livraisons else None

    # 4. temps moyen de rétablissement (MTTR) : d'un échec jusqu'à la prochaine exécution réussie
    retablissements = []
    for e in echouees:
        suivante = next((r for r in reussies if r["created_at"] > e["created_at"]), None)
        if suivante:
            retablissements.append(minutes(date(e["created_at"]), date(suivante["updated_at"])))
    mttr = statistics.mean(retablissements) if retablissements else None

    # 5. durée du pipeline : du démarrage à la fin (médiane des exécutions réussies)
    durees = [minutes(date(r["run_started_at"]), date(r["updated_at"])) for r in reussies]
    duree = statistics.median(durees) if durees else None

    # 6. sécurité : exécutions bloquées par un contrôle de sécurité, et failles de l'image (si rapport Trivy)
    bloquees = sum(
        1 for r in executions
        if any(j["name"] in JOBS_SECURITE and j["conclusion"] == "failure" for j in r["jobs"])
    )
    failles = None
    if rapport_trivy:
        donnees = json.loads(Path(rapport_trivy).read_text(encoding="utf-8"))
        failles = {g: 0 for g in ("CRITICAL", "HIGH", "MEDIUM", "LOW")}
        for resultat in donnees.get("Results", []):
            for v in resultat.get("Vulnerabilities") or []:
                if v["Severity"] in failles:
                    failles[v["Severity"]] += 1

    return {
        "frequence": frequence,
        "nb_deploiements": len(deploiements),
        "delai": delai,
        "taux_echec": taux_echec,
        "nb_livraisons": len(livraisons),
        "nb_echecs": len(echouees),
        "mttr": mttr,
        "duree": duree,
        "bloquees": bloquees,
        "failles": failles,
    }


def indicateurs(k):
    """Liste des tuiles : (nom, valeur affichée, objectif, objectif atteint ?, détail)."""
    def fmt(valeur, unite, decimales=1):
        return "—" if valeur is None else f"{valeur:.{decimales}f} {unite}".strip()

    def atteint(valeur, test):
        return None if valeur is None else test(valeur)

    tuiles = [
        ("Fréquence de déploiement", fmt(k["frequence"], "/ semaine"), "≥ 3 / semaine",
         atteint(k["frequence"], lambda v: v >= 3), f"{k['nb_deploiements']} déploiement(s) réussi(s)"),
        ("Délai de mise en production", fmt(k["delai"], "min"), "< 30 min",
         atteint(k["delai"], lambda v: v < 30), "du commit au site déployé (médiane)"),
        ("Taux d'échec des changements", fmt(k["taux_echec"], "%", 0), "< 15 %",
         atteint(k["taux_echec"], lambda v: v < 15), f"{k['nb_echecs']} échec(s) sur {k['nb_livraisons']} push"),
        ("Temps de rétablissement (MTTR)", fmt(k["mttr"], "min"), "< 60 min",
         atteint(k["mttr"], lambda v: v < 60), "d'un pipeline rouge au retour au vert"),
        ("Durée du pipeline", fmt(k["duree"], "min"), "< 10 min",
         atteint(k["duree"], lambda v: v < 10), "médiane des exécutions réussies"),
    ]
    if k["failles"] is not None:
        graves = k["failles"]["CRITICAL"] + k["failles"]["HIGH"]
        detail = " · ".join(f"{g.title()} {n}" for g, n in k["failles"].items())
        tuiles.append(("Failles HIGH/CRITICAL (image)", str(graves), "0 corrigeable", graves == 0, detail))
    else:
        tuiles.append(("Exécutions bloquées (sécurité)", str(k["bloquees"]), "suivi", None,
                       "Gitleaks, Semgrep ou Trivy en échec"))
    return tuiles


def afficher(tuiles):
    print(f"\nIndicateurs DevSecOps — {DEPOT} (branche {BRANCHE})\n")
    for nom, valeur, objectif, ok, detail in tuiles:
        etat = "à suivre" if ok is None else ("OK" if ok else "À AMÉLIORER")
        print(f"  {nom:<34} {valeur:>16}   objectif {objectif:<14} {etat:<12} {detail}")
    print(f"\nTableau de bord généré : {SORTIE}\n")


def graphique(executions):
    """Diagramme en barres SVG : durée de chaque exécution, couleur = résultat (avec icône dans la légende)."""
    if not executions:
        return "<p class='vide'>Aucune exécution pour le moment.</p>"
    largeur, hauteur, marge_g, marge_b, haut = 720, 240, 44, 28, 12
    durees = [minutes(date(r["run_started_at"]), date(r["updated_at"])) for r in executions]
    maxi = max(max(durees), 1)
    graduation = 1 if maxi <= 5 else 2 if maxi <= 10 else 5
    plafond = graduation * (int(maxi // graduation) + 1)
    zone = hauteur - marge_b - haut
    pas = (largeur - marge_g) / len(executions)
    barre = min(28, pas - 2)
    elements = []
    for g in range(0, int(plafond) + 1, graduation):
        y = haut + zone - zone * g / plafond
        elements.append(f"<line class='grille' x1='{marge_g}' x2='{largeur}' y1='{y:.1f}' y2='{y:.1f}'/>"
                        f"<text class='axe' x='{marge_g - 8}' y='{y + 4:.1f}' text-anchor='end'>{g}</text>")
    for i, (r, d) in enumerate(zip(executions, durees)):
        h = max(zone * d / plafond, 2)
        x = marge_g + i * pas + (pas - barre) / 2
        y = haut + zone - h
        statut = "ok" if r["conclusion"] == "success" else "ko"
        libelle = "réussi" if statut == "ok" else "échec"
        info = f"{date(r['created_at']):%d/%m %H:%M} · {r['head_sha'][:7]} · {d:.1f} min · {libelle}"
        # barre arrondie en haut seulement (posée sur l'axe)
        rayon = min(4, h / 2, barre / 2)
        chemin = (f"M{x:.1f},{y + h:.1f} V{y + rayon:.1f} Q{x:.1f},{y:.1f} {x + rayon:.1f},{y:.1f} "
                  f"H{x + barre - rayon:.1f} Q{x + barre:.1f},{y:.1f} {x + barre:.1f},{y + rayon:.1f} V{y + h:.1f} Z")
        elements.append(f"<g class='barre {statut}' tabindex='0' data-info='{html.escape(info)}'>"
                        f"<rect class='cible' x='{marge_g + i * pas:.1f}' y='{haut}' width='{pas:.1f}' height='{zone}'/>"
                        f"<path d='{chemin}'/><title>{html.escape(info)}</title></g>")
    elements.append(f"<line class='base' x1='{marge_g}' x2='{largeur}' y1='{haut + zone}' y2='{haut + zone}'/>")
    elements.append(f"<text class='axe' x='{marge_g}' y='{hauteur - 6}'>plus ancienne</text>"
                    f"<text class='axe' x='{largeur}' y='{hauteur - 6}' text-anchor='end'>plus récente</text>")
    return (f"<svg viewBox='0 0 {largeur} {hauteur}' role='img' "
            f"aria-label='Durée de chaque exécution du pipeline en minutes'>{''.join(elements)}</svg>")


def tableau(executions):
    lignes = []
    for r in reversed(executions):
        d = minutes(date(r["run_started_at"]), date(r["updated_at"]))
        ok = r["conclusion"] == "success"
        rate = [j["name"] for j in r["jobs"] if j["conclusion"] == "failure"]
        lignes.append(
            f"<tr><td>{date(r['created_at']):%d/%m/%Y %H:%M}</td>"
            f"<td><a href='{html.escape(r['html_url'])}'>{r['head_sha'][:7]}</a></td>"
            f"<td>{html.escape(r['event'])}</td><td class='num'>{d:.1f}</td>"
            f"<td><span class='statut {'ok' if ok else 'ko'}'>{'✓ réussi' if ok else '✕ échec'}</span></td>"
            f"<td>{html.escape(', '.join(rate)) or '—'}</td></tr>")
    return "".join(lignes)


def generer_html(tuiles, executions):
    cartes = []
    for nom, valeur, objectif, ok, detail in tuiles:
        if ok is None:
            etat = "<span class='statut neutre'>● à suivre</span>"
        elif ok:
            etat = "<span class='statut ok'>✓ objectif atteint</span>"
        else:
            etat = "<span class='statut ko'>✕ à améliorer</span>"
        cartes.append(f"<article class='tuile'><h3>{html.escape(nom)}</h3><p class='valeur'>{html.escape(valeur)}</p>"
                      f"<p class='objectif'>Objectif : {html.escape(objectif)}</p>{etat}"
                      f"<p class='detail'>{html.escape(detail)}</p></article>")
    maintenant = datetime.now().strftime("%d/%m/%Y à %H:%M")
    modele = (Path(__file__).resolve().parent / "dashboard_modele.html").read_text(encoding="utf-8")
    return (modele.replace("{{DATE}}", maintenant)
            .replace("{{DEPOT}}", DEPOT)
            .replace("{{BRANCHE}}", BRANCHE)
            .replace("{{NB}}", str(len(executions)))
            .replace("{{TUILES}}", "".join(cartes))
            .replace("{{GRAPHIQUE}}", graphique(executions))
            .replace("{{LIGNES}}", tableau(executions)))


def main():
    parametres = argparse.ArgumentParser(description="KPI DevSecOps de TomTroc")
    parametres.add_argument("--trivy", help="rapport Trivy au format JSON (facultatif)")
    args = parametres.parse_args()

    executions = recuperer_executions()
    tuiles = indicateurs(calculer(executions, args.trivy))
    SORTIE.parent.mkdir(parents=True, exist_ok=True)
    SORTIE.write_text(generer_html(tuiles, executions), encoding="utf-8")
    afficher(tuiles)


if __name__ == "__main__":
    main()
