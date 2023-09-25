{literal}
<h5 class="text-muted fw-normal mb-4">Créer votre mot de passe</h5>

<form method="post" action="{$smarty.server.REQUEST_URI}">
    <div class="mb-3">
        <label for="password-input" class="form-label">Mot de passe</label>
        <input type="password" name="newPassword"  id="password-input" class="form-control" autocomplete="current-password" placeholder="Mot de passe">
    </div>
    <div>
        <button role="submit" class="btn btn-primary me-2 mb-2 mb-md-0 text-white btn-icon-text">
            <i class="btn-icon-prepend" data-feather="plus"></i> Créer mon mot de passe</button>
    </div>
</form>
{/literal}
