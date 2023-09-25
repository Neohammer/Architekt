{literal}
<h5 class="text-muted fw-normal mb-4">Créer votre compte</h5>

{if $CREATE_ALLOW}<form method="post" action="{$smarty.server.REQUEST_URI}">{/if}
<div class="mb-3">
    <label for="email-input" class="form-label">Email</label>
    <input type="email" id="email-input" class="form-control" name="email" placeholder="Email">
</div>
<div class="mb-3">
    <label for="password-input" class="form-label">Mot de passe</label>
    <input type="password" id="password-input" class="form-control" name="password" autocomplete="current-password" placeholder="Mot de passe">
</div>
<div>
    {if $CREATE_ALLOW}
    <button role="submit" class="btn btn-primary me-2 mb-2 mb-md-0 text-white btn-icon-text">
        <i class="btn-icon-prepend" data-feather="log-in"></i> Créer mon compte</button>
    <!--<button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
        <i class="btn-icon-prepend" data-feather="twitter"></i>
        Login with twitter
    </button>-->
    {else}
    <div class="alert alert-warning" role="alert">Vous devez attendre pour pouvoir créer un compte</div>
    {/if}
</div>
<a href="/User/login" class="d-block mt-3 text-muted">Déjà enregistré ? Connectez-vous</a>
<a href="/User/passwordRecover" class="d-block mt-3 text-muted">J'ai oublié mon mot de passe</a>
{if $CREATE_ALLOW}</form>{/if}
{/literal}
