{literal}
<h5 class="text-muted fw-normal mb-4">Connectez-vous à votre compte</h5>
{if $LOGIN_ALLOW}<form method="post" action="{$smarty.server.REQUEST_URI}">{/if}
    <div class="mb-3">
        <label for="email-input" class="form-label">Email</label>
        <input type="text" id="email-input" class="form-control" name="email" placeholder="Votre email de connexion">
    </div>
    <div class="mb-3">
        <label for="password-input" class="form-label">Mot de passe</label>
        <input type="password" name="password"  id="password-input" class="form-control" autocomplete="current-password" placeholder="Mot de passe">
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="stayLogged" value="1" class="form-check-input" id="authCheck" checked>
        <label class="form-check-label" for="authCheck" >
            Rester connecté
        </label>
    </div>
    <div>
        {if $LOGIN_ALLOW}
        <button role="submit" class="btn btn-primary me-2 mb-2 mb-md-0 text-white btn-icon-text">
            <i class="btn-icon-prepend" data-feather="log-in"></i> Se connecter</button>
        <!--<button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
            <i class="btn-icon-prepend" data-feather="twitter"></i>
            Login with twitter
        </button>-->
        {else}
        <div class="alert alert-warning" role="alert">Vous devez attendre pour pouvoir vous connecter</div>
        {/if}
    </div>
    {if $SETTINGS->is('account','create')}
    <a href="/User/create" class="d-block mt-3 text-muted">Pas encore enregistré ? Créer votre compte</a>
    {/if}
    <a href="/User/passwordRecover" class="d-block mt-3 text-muted">J'ai oublié mon mot de passe</a>
{if $LOGIN_ALLOW}</form>{/if}
{/literal}