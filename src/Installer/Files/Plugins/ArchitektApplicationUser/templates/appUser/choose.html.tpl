{literal}
<h5 class="text-muted fw-normal mb-4">Choisissez votre compte</h5>

{foreach from=$ACCOUNTS item=account}
<div class="d-flex justify-content-between align-items-center w-100 px-2 px-md-4 border-bottom border-top">
    <div>
        <img class="wd-70 rounded-circle" src="{URL_THEME_IMAGES}/user_default.png" alt="profile">
        <span class="h4 ms-3 text-dark">{$account->label()}</span>
    </div>
    <div class="d-none d-md-block">
        <a href="/{/literal}{$APPLICATION_USER_CAMEL}{literal}/use/{$account->_primary()}" real="1" class="btn btn-primary btn-icon-text">
            <i data-feather="log-in" class="btn-icon-prepend"></i> Utiliser
        </a>
    </div>
</div>
{/foreach}
{if $CREATE_ALLOW}
<hr />
<div class="align-items-center w-100 px-2 px-md-4 text-end">
    <a href="/{/literal}{$APPLICATION_USER_CAMEL}{literal}/createModal/" eventType="modal" class="btn btn-primary btn-icon-text">
        <i data-feather="plus" class="btn-icon-prepend"></i> Créer
    </a>
</div>
{/if}

<a href="/User/logout" real="1" class="d-block mt-3 text-muted">Se déconnecter</a>
{/literal}