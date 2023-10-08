
    public function __plugin(): Plugin
    {
        return Plugin::fromCache({$PLUGIN->_primary()});
    }

    public function __controller(): Controller
    {
        return Controller::fromCache({$CONTROLLERS[$name]->_primary()});
    }

    public function __uri(string $uri = ''): string
    {
        return {if $uri}sprintf( '/{$uri}/%s', $uri){else}'/'{/if};
    }
