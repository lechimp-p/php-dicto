<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration for the app and engine.
 */
class Config implements ConfigurationInterface {
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $values;

    /**
     * Build the configuration from nested arrays using a processor.
     *
     * @param   string  $path
     */
    public function __construct($path, array $values) {
        assert('is_string($path)');
        if (substr($path, strlen($path) - 1, 1) == "/") {
            $path = substr($path, 0, strlen($path) - 1);
        }
        $this->path = $path;
        $processor = new \Symfony\Component\Config\Definition\Processor();
        $this->values = $processor->processConfiguration($this, $values);
    }

    /**
     * Definition of configuration for symfony.
     *
     * @inheritdocs
     */
    public function getConfigTreeBuilder() {
        $tree_builder = new TreeBuilder();
        $root = $tree_builder->root("dicto");
        $root
            ->children()
                ->arrayNode("project")
                    ->children()
                        ->scalarNode("root")
                            ->isRequired()
                        ->end()
                        ->scalarNode("storage")
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("analysis")
                    ->children()
                        ->arrayNode("ignore")
                            ->prototype("scalar")
                            ->end()
                            ->defaultValue(array())
                        ->end()
                    ->end()
                    ->addDefaultsIfNotSet()
                ->end()
            ->end()
        ->end();

        return $tree_builder;
    }

    /**
     * @return  string
     */
    public function path() {
        return $this->path;
    }

    protected function maybe_prepend_path($path) {
        assert('is_string($path)');
        if (substr($path, 0, 2) === "./") {
            return $this->path()."/".substr($path, 2);
        }
        return $path;
    }

    /**
     * @return  string
     */
    public function project_root() {
        return $this->maybe_prepend_path($this->values["project"]["root"]);
    }

    /**
     * @return  string
     */
    public function project_storage() {
        return $this->maybe_prepend_path($this->values["project"]["storage"]);
    }

    /**
     * @return  string[]
     */
    public function analysis_ignore() {
        return $this->values["analysis"]["ignore"];
    }
}
