{literal}
{if $ERROR_CODE == 403}
    <div class="row w-100 mx-0 auth-page">
        <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
            <img src="{URL_CDN}/themes/{/literal}{$THEME}/{$THEME_IMAGES}/404.svg{literal}" class="img-fluid mb-2" alt="404">
            <h1 class="fw-bolder mb-22 mt-2 tx-80 text-muted">403</h1>
            <h4 class="mb-2">Page non accessible</h4>
            <h6 class="text-muted mb-3 text-center">Oopps!! Vous n'avez pas les droits pour accéder à cette page.</h6>
            <a href="/Redirect/" real="1">Retour à l'accueil</a>
        </div>
    </div>
{elseif $ERROR_CODE == 500}
    <div class="row w-100 mx-0 auth-page">
        <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
            <img src="{URL_CDN}/themes/{/literal}{$THEME}/{$THEME_IMAGES}/404.svg{literal}" class="img-fluid mb-2" alt="404">
            <h1 class="fw-bolder mb-22 mt-2 tx-80 text-muted">500</h1>
            <h4 class="mb-2">Erreur serveur</h4>
            <h6 class="text-muted mb-3 text-center">Oopps!! Un problème est survenu, retentez plus tard.</h6>
            <a href="/Redirect/" real="1">Retour à l'accueil</a>
        </div>
    </div>
{elseif $ERROR_CODE == 404}
    <div class="row w-100 mx-0 auth-page">
        <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
            <img src="{URL_CDN}/themes/{/literal}{$THEME}/{$THEME_IMAGES}/404.svg{literal}" class="img-fluid mb-2" alt="404">
            <h1 class="fw-bolder mb-22 mt-2 tx-80 text-muted">404</h1>
            <h4 class="mb-2">Page introuvable</h4>
            <h6 class="text-muted mb-3 text-center">Oopps!! Cette page n'existe pas.</h6>
            <a href="/Redirect/" real="1">Retour à l'accueil</a>
        </div>
    </div>
{/if}
{/literal}