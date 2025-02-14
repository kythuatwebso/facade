<?php

namespace Websovn\Facades;

use Illuminate\Support\Traits\Conditionable;

class SmartyTemplateFacade
{
    use Conditionable;

    public const PREFIX = 'Facade';

    public const METHOD = 'registerClass';

    protected $smarty;

    public function __construct(protected array $classes = [])
    {
    }

    public function handle()
    {
        $smarty = $this->getSmarty();

        foreach ($this->classes as $name => $class) {

            if (! is_string($name) || ! is_string($class)) {
                continue;
            }

            $smarty->{self::METHOD}(
                self::PREFIX.'\\'.$name,
                $class
            );
        }
    }

    protected function getSmarty()
    {
        if (isset($this->smarty)) {
            return $this->smarty;
        }

        if (function_exists('smarty')) {
            return smarty();
        }

        throw new \InvalidArgumentException('"Smarty" not found.');
    }

    public function setSmarty($smarty): self
    {
        $this->smarty = $smarty;

        return $this;
    }
}
